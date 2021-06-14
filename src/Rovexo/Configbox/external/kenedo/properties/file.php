<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyFile extends KenedoProperty {

	protected $dirBase;
	protected $urlBase;
	protected $size;
	protected $allow;
	protected $allowedExtensions;
	protected $filename;
	protected $appendSerial;

	/**
	 * Appends full URL and filesystem path to the file (if bases are defined in prop defs)
	 * @param object $data
	 */
	function appendDataForGetRecord( &$data ) {

		$pathUrl = $this->getPropertyDefinition('urlBase');
		if ($pathUrl) {
			$data->{$this->propertyName.'_href'} = (!empty($data->{$this->propertyName})) ? $pathUrl.'/'.$data->{$this->propertyName} : '';
		}

		$pathFilesystem = $this->getPropertyDefinition('dirBase');
		if ($pathFilesystem) {
			$data->{$this->propertyName.'_path'} = (!empty($data->{$this->propertyName})) ? $pathFilesystem.'/'.$data->{$this->propertyName} : '';
		}

		parent::appendDataForGetRecord( $data );
	}

	/**
	 * This updates the paths and URLs (just in case the request scheme changed or similar). Runs the
	 * appendDataForGetRecord again.
	 * @see KenedoPropertyFile::appendDataForGetRecord()
	 *
	 * @param object $data
	 */
	public function appendDataForPostCaching(&$data) {
		$this->appendDataForGetRecord($data);
	}

	/**
	 * @param int $id
	 * @param string $tableName
	 * @return bool
	 */
	function delete($id, $tableName) {

		$filename = $this->getCurrentFilename($id);

		if (!empty($filename)) {
			$fullPath = $this->getPropertyDefinition('dirBase').'/'.$filename;
			if (is_file($fullPath)) {
				$success = unlink($fullPath);
				if ($success == false) {
					KLog::log('Could not delete file for property '.$this->propertyName.' in model '.$this->model->getModelName().'. Path was '.$fullPath, 'error');
				}
			}
		}

		return parent::delete($id, $tableName);

	}

	/**
	 * This override just makes sure we have any current file name in the data object before base data storing.
	 * @param object $data
	 * @return bool
	 */
	function prepareForStorage(&$data) {
		$recordId = $data->{$this->model->getTableKey()};
		$data->{$this->propertyName} = $this->getCurrentFilename($recordId);
		return parent::prepareForStorage($data);
	}

	function check($data) {

		$this->resetErrors();

		$file = KRequest::getFile($this->propertyName);

		if (!$file && $this->isRequired() && $this->applies($data) && $data->id == 0) {
			$this->setError( KText::sprintf('You need to upload a file for field %s.',$this->getPropertyDefinition('label')) );
			return false;
		}

		if ($file && !empty($file['tmp_name'])) {

			// Check file size
			if ($this->getPropertyDefinition('size')) {

				$fileSizeInBytes = filesize($file['tmp_name']);
				$allowedSizeInBytes = $this->getPropertyDefinition('size') * 1024;

				if ($fileSizeInBytes > $allowedSizeInBytes) {
					$this->setError( KText::sprintf('The file for %s is not valid.',$this->getPropertyDefinition('label')). ' '.KText::sprintf('The file must not be bigger than %s KB.',$this->getPropertyDefinition('size')) );
					return false;
				}

			}

			// Check file mime-type
			if ($this->getPropertyDefinition('allow')) {

				$mimeType = KenedoFileHelper::getMimeType( $file['tmp_name'] );

				// Mime type determination may not be available on the system, only check if possible
				if ($mimeType && !in_array($mimeType,$this->getPropertyDefinition('allow', array()))) {
					$this->setError( KText::sprintf('The file for %s is not valid.',$this->getPropertyDefinition('label')). ' '.KText::sprintf('The file has an invalid MIME-Type. MIME-Type is %s. Allowed are %s.',$mimeType, implode(', ',$this->getPropertyDefinition('allow', array()))) );
					return false;
				}

			}

			if ($this->getPropertyDefinition('allowedExtensions')) {

				$extension = KenedoFileHelper::getExtension( $filename = $file['name'] );

				if (!in_array($extension,$this->getPropertyDefinition('allowedExtensions', array()))) {
					$this->setError( KText::sprintf('The file for %s is not valid.',$this->getPropertyDefinition('label')). ' '.KText::sprintf('The file has an invalid extension of %s. Allowed are %s.',$extension, implode(', ',$this->getPropertyDefinition('allowedExtensions'))) );
					return false;

				}

			}

		}

		return true;

	}

	/**
	 * Takes the file from $_FILES directly and puts it in place. Updates the base table itself (also external one if
	 * storeExternal is used).
	 * @param $data
	 * @return bool
	 * @throws Exception In case a delete flag is sent even though prop is set not be deletable
	 */
	function store(&$data) {

		// Prepare some info that we need all over
		$storageFolder = $this->getPropertyDefinition('dirBase');
		$optionTags = $this->getPropertyDefinition('optionTags');
		$canDelete = (!isset($optionTags['NODELETEFILE']) && $this->isRequired() === false);
		$shouldDelete = (KRequest::getInt($this->propertyName.'-delete', 0) == 1);
		$currentFileName = $this->getCurrentFilename($data->id);
		$recordId = $data->{$this->model->getTableKey()};

		// Create the storage folder if needed
		if (is_dir($storageFolder) == false) {
			$success = mkdir($storageFolder, 0777, true);
			if ($success == false) {
				$errorMsg = KText::sprintf('Could not create the file folder for %s in %s. Please check if your data folder and its content are writable and try again.', $this->getPropertyDefinition('label'), $storageFolder);
				$this->setError($errorMsg);
				return false;
			}
		}

		// Get the uploaded file's info (This is the typical array you'd get from $_FILES['your_file'])
		$file = KRequest::getFile($this->propertyName);

		// If the user checked the 'delete' checkbox, delete the item
		if ($shouldDelete) {

			if ($canDelete == false) {
				throw new Exception('User tried to delete a file that should not be deletable (by settings)');
			}

			$fileToRemove = $storageFolder.'/'.$currentFileName;

			// Go on and delete the file (if it exists, otherwise just let it be)
			if (is_file($fileToRemove)) {

				$success = unlink($fileToRemove);

				if ($success == false) {
					$errorMsg = KText::sprintf('Could not remove the file for field %s in %s. Please check if your data folder and its content are writable and try again.', $this->getPropertyDefinition('label'), $fileToRemove);
					$this->setError($errorMsg);
					return false;
				}

			}

			// Set the file name storage field to empty (if we store the file name)
			if (isset($optionTags['SAVE_FILENAME'])) {
				$data->{$this->propertyName} = '';
				$this->updateFilenameBaseTable($recordId, '');
			}

			return true;

		}


		// If the user uploaded no file, all stays the same for our prop.
		if (empty($file['tmp_name'])) {

			// Just sneak in the file name in the data object (just in case the data is used after storing)
			$data->{$this->propertyName} = $this->getCurrentFilename($recordId);

			return true;

		}


		// Prepare the filename (there are a few settings that control how the file name is 'built' together)
		$newFileName = $this->getNewFileName($data);

		// Figure out the full filesystem path for the file
		$destinationPath = $storageFolder . '/' . $newFileName;

		// Move the file from tmp to destination
		$moveSuccess = rename($file['tmp_name'], $destinationPath);

		// Send false and feedback if moving didn't go well
		if ($moveSuccess == false) {
			$errorMsg = KText::sprintf('Could not store file for field %s in %s. Please check if your data folder and its content are writable and try again.', $this->getPropertyDefinition('label'), $destinationPath);
			$this->setError($errorMsg);
			return false;
		}

		// Put file permissions to 0775 (it allows for having shared write permissions when you organise unix groups nicely)
		chmod($destinationPath, 0775);

		// Save the file name in table if configured for that
		if (isset($optionTags['SAVE_FILENAME'])) {
			$this->updateFilenameBaseTable($recordId, $newFileName);
		}

		// Add the file name to the data object (just in case someone uses this object right after storing)
		$data->{$this->propertyName} = $newFileName;

		// If the file name changed (because of the 'appendSerial' setting or anything else), remove the old file
		if ($currentFileName && $currentFileName != $newFileName) {

			$fileToRemove = $storageFolder . '/' . $currentFileName;

			if (is_file($fileToRemove)) {

				$success = unlink($fileToRemove);

				if ($success == false) {
					$errorMsg = KText::sprintf('Could not remove the previous file for field %s in %s. Please check if your data folder and its content are writable and try again.', $this->getPropertyDefinition('label'), $fileToRemove);
					$this->setError($errorMsg);
					return false;
				}

			}

		}

		return true;

	}

    /**
     * @param object $data
     * @param int $newId
     * @param int $oldId
     * @return bool
     */
    public function copy($data, $newId, $oldId) {

    	$data = clone $data;

		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';

		KLog::log($logPrefix.'Checking if there is a file. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

        $filename = $data->{$this->propertyName};

        // If there is no filename, there is nothing to copy
        if(empty($filename)) {
			KLog::log($logPrefix.'No file, moving on. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');
			return true;
		}

        // get newFilename
        $newFilename = $this->_getNewFileNameForCopy($data, $newId); // method rewrite without FILES array use
		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';
		KLog::log($logPrefix.'Got a file, new filename will be "'.$newFilename.'". Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

		$directory = $this->getPropertyDefinition('dirBase');

		// If the source file is gone, then warn in log file, but don't abort (just don't copy)
		if (is_file($directory.'/'.$filename) == false) {
			$msg = $logPrefix.'Issue with copying: Data shows there is a file, but we do not find it where it should be. Record ID was '.$oldId.'. Ignoring it for copying. Path was "'.$directory.'/'.$filename.'"';
			KLog::log($msg, 'warning');
			KLog::log($msg, 'custom_copying');

			// Do store in any case (because we might deal with a prop using storeExternally and the table has just this column and there must be a row for the record)
			$data->{$this->propertyName} = '';
			$data->{$this->model->getTableKey()} = $newId;

			return $this->copyFilenameInTable($data, $newId, $oldId);

		}

		if (is_writable($directory) == false) {
			$feedback = 'Cannot copy the file for "'.$this->getPropertyDefinition('label').'" because folder "'.$directory.'" is not writable.';
			$this->setError($feedback);
			$msg = $logPrefix.$feedback;
			KLog::log($msg, 'warning');
			KLog::log($msg, 'custom_copying');
			return false;
		}

		$result = copy($directory.'/'.$filename,$directory.'/'.$newFilename);

		if ($result === false) {

			$error = error_get_last();

			if ($error) {
				$msg = $logPrefix.'Copying file failed. Last PHP error message was '.$error['message'].'. Source path was "'.$directory.'/'.$filename.'". Destination path was '.$directory.'/'.$newFilename;
				KLog::log($msg, 'error');
				KLog::log($msg, 'custom_copying');
			}
			else {
				$msg = $logPrefix.'Copying file failed. No PHP error message was issued. Source path was "'.$directory.'/'.$filename.'". Destination path was '.$directory.'/'.$newFilename;
				KLog::log($msg, 'error');
				KLog::log($msg, 'custom_copying');
			}

			$feedback = 'Copying file for '.$this->getPropertyDefinition('label').' failed. See ConfigBox error log file for details.';
			$this->setError($feedback);
			return false;

		}

		// Prepare the data for copying the value in the DB table
		$data->{$this->propertyName} = $newFilename;
		$data->{$this->model->getTableKey()} = $newId;

		// This method handles setting an error if needed
		return $this->copyFilenameInTable($data, $newId, $oldId);

    }

	/**
	 * Helper function for copy to avoid code duplication
	 * @param object $data
	 * @param int $newId
	 * @param int $oldId
	 * @return bool
	 */
    protected function copyFilenameInTable($data, $newId, $oldId) {

    	// If it's an externally stored prop, just let the parent handle storing
		if ($this->getPropertyDefinition('storeExternally') == true) {
			return parent::copy($data, $newId, $oldId);
		}

		// store locally otherwise
		$db = KenedoPlatform::getDb();

		$tableName = $this->model->getTableName();
		$keyName = $this->model->getTableKey();

		// Make sure that NULL values are in fact stored as NULL (just in case, normally we store an empty string when no file)
		if ($data->{$this->propertyName} === NULL) {
			$value = 'NULL';
		}
		else {
			$value = "'".$db->getEscaped($data->{$this->propertyName})."'";
		}

		try {
			$query = "
			INSERT INTO `".$tableName."` 
			SET 
				`".$this->propertyName."`   = ".$value.",
				`".$keyName."`      = '".$db->getEscaped($newId)."' 
			ON DUPLICATE KEY UPDATE `".$this->propertyName."`   = ".$value;
			$db->setQuery($query);
			$db->query();
		}
		catch(Exception $e) {
			$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';
			KLog::log($logPrefix.'SQL error during data copying. Error was '.$db->getErrorMsg(), 'custom_copying');
			KLog::log('SQL error during data copying. Error was '.$db->getErrorMsg(), 'error');
			$this->setError('A database error occured during file data copying');
			return false;
		}

		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';
		KLog::log($logPrefix.'Copying successful - ' . KLog::time('ModelCopyMethod'), 'custom_copying');
		return true;
	}

    /**
     * Method gets filename for new file object copy
     * @param $data
     * @param $newId
     * @return string
     */
    protected function _getNewFileNameForCopy($data, $newId) {

        $file = $data->{$this->propertyName};

        // Prepare file's filename and extension
        $filenameRaw = mb_strtolower($file);
        $fileExtension = KenedoFileHelper::getExtension($filenameRaw);

        // If option FILENAME_TO_RECORD_ID is set, prepare the full path with new filename
        $optionTags = $this->getPropertyDefinition('optionTags', array());
        if (isset($optionTags['FILENAME_TO_RECORD_ID'])) {
            $newFilename = $newId;
        }
        // Else use the sanitized filename
        elseif ($this->getPropertyDefinition('filename')) {
            $newFilename = $this->getPropertyDefinition('filename');
        }
        else {
            $newFilename = preg_replace('#\.[^.]*$#', '', $filenameRaw);
        }

        if ($this->getPropertyDefinition('appendSerial')) {
            // Append a number to circumvent outdated cache data
            $newFilename .= '-'.str_pad(rand(1,1000),4,0);
        }

        return $newFilename.'.'.$fileExtension;

    }

	/**
	 * Checks settings and current file upload and figures out the right file name to store.
	 * @param object $data
	 * @return string
	 */
	protected function getNewFileName( $data ) {

		$file = KRequest::getFile($this->propertyName);

		if (!$file || empty($file['tmp_name'])) {
			return '';
		}

		// Prepare file's filename and extension
		$filenameRaw = mb_strtolower($file['name']);

		$fileExtension = KenedoFileHelper::getExtension($filenameRaw);

		// If option FILENAME_TO_RECORD_ID is set, prepare the full path with new filename
		$optionTags = $this->getPropertyDefinition('optionTags', array());
		if (isset($optionTags['FILENAME_TO_RECORD_ID'])) {
			$newFilename = $data->id;
		}
		// Else use the sanitized filename
		elseif ($this->getPropertyDefinition('filename')) {
			$newFilename = $this->getPropertyDefinition('filename');
		}
		else {
			$newFilename = preg_replace('#\.[^.]*$#', '', $filenameRaw);
		}

		if ($this->getPropertyDefinition('appendSerial')) {
			// Append a number to circumvent outdated cache data
			$newFilename .= '-'.str_pad(rand(1,1000),4,0);
		}

		return $newFilename.'.'.$fileExtension;

	}

	/**
	 * Gives you the file name that is currently stored in the DB
	 * @param mixed $id Model record ID
	 * @return string
	 */
	protected function getCurrentFilename($id) {

		if (!$id) {
			return '';
		}

		if ($this->getPropertyDefinition('storeExternally')) {
			$tableName = $this->getPropertyDefinition('foreignTableName');
			$key = $this->getPropertyDefinition('foreignTableKey');
		}
		else {
			$tableName = $this->model->getTableName();
			$key = $this->model->getTableKey();
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT `".$this->propertyName."` FROM `".$tableName."` WHERE `".$key."` = '".$db->getEscaped($id)."'";
		$db->setQuery($query);
		$filename = (string)$db->loadResult();

		return $filename;

	}

	/**
	 * This updates the column storing the filename in the base table (necessary since properties store AFTER the
	 * KenedoModel stores 'base' data.
	 * @param mixed $id
	 * @param string $filename
	 * @throws Exception If $id is falseish
	 */
	protected function updateFilenameBaseTable($id, $filename) {

		if (!$id) {
			throw new Exception('updateFilenameBaseTable called without a valid $id');
		}

		// Deal with 'storeExternally' right here
		if ($this->getPropertyDefinition('storeExternally') == true) {
			$table = $this->getPropertyDefinition('foreignTableName');
			$keyColumn = $this->getPropertyDefinition('foreignTableKey');
		}
		else {
			$table = $this->model->getTableName();
			$keyColumn = $this->model->getTableKey();
		}

		$db = KenedoPlatform::getDb();
		$query = "
				INSERT INTO `".$table."` 
				SET `".$keyColumn."` = '".$db->getEscaped($id)."', `".$this->propertyName."` = '".$db->getEscaped($filename)."' 
				ON DUPLICATE KEY UPDATE `".$this->propertyName."` = '".$db->getEscaped($filename)."' ";
		$db->setQuery($query);
		$db->query();

	}

}