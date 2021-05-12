<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyImage extends KenedoProperty {

	protected $dirBase;
	protected $urlBase;
	protected $maxFileSizeKb;
	protected $allowedMimeTypes;
	protected $allowedExtensions;
	protected $minimumDimensions;
	protected $mutations;
	protected $filename;
	protected $appendSerial;

	/**
	 * Since the user can store data without supplying the image, we add the current file name info to the $data object.
	 * @param object $data
	 * @return bool
	 */
	function prepareForStorage(&$data) {
		$recordId = $data->{$this->model->getTableKey()};
		$data->{$this->propertyName} = $this->getCurrentOriginalFileName($recordId);
		return true;
	}

	function check($data) {

		$this->resetErrors();

		if ($this->isRequired() && $this->applies($data)) {
			$fileUpload = KRequest::getFile($this->propertyName);
			$currentFile = $this->getCurrentOriginalFileName($data->id);
			if (!$fileUpload && !$currentFile) {
				$this->setError( KText::sprintf('You need to upload a file for field %s.',$this->getPropertyDefinition('label')) );
				return false;
			}
		}

		$file = KRequest::getFile($this->propertyName);

		// Otherwise, if nothing got uploaded - let be ok
		if (empty($file['tmp_name'])) {
			return true;
		}

		$imageIsOk = true;

		// Check file size
		if ($this->getPropertyDefinition('maxFileSizeKb')) {

			$fileSizeInBytes = filesize($file['tmp_name']);
			$allowedSizeInBytes = $this->getPropertyDefinition('maxFileSizeKb') * 1024;

			if ($fileSizeInBytes > $allowedSizeInBytes) {
				$this->setError( KText::sprintf('The file for %s is not valid.',$this->getPropertyDefinition('label')). ' '.KText::sprintf('The file must not be bigger than %s KB.',$this->getPropertyDefinition('maxFileSizeKb')) );
				$imageIsOk = false;
			}

		}

		// Check file mime-type
		if ($this->getPropertyDefinition('allowedMimeTypes')) {

			$mimeType = KenedoFileHelper::getMimeType( $file['tmp_name'] );

			// Mime type determination may not be available on the system, only check if possible
			if ($mimeType && !in_array($mimeType, $this->getPropertyDefinition('allowedMimeTypes'))) {
				$this->setError( KText::sprintf('The file for %s is not valid.',$this->getPropertyDefinition('label')). ' '.KText::sprintf('The file has an invalid MIME-Type. MIME-Type is %s. Allowed are %s.',$mimeType, implode(', ',$this->getPropertyDefinition('allowedMimeTypes', array()))) );
				$imageIsOk = false;
			}

		}

		if ($this->getPropertyDefinition('allowedExtensions')) {

			$extension = KenedoFileHelper::getExtension( $filename = $file['name'] );

			if (!in_array($extension,$this->getPropertyDefinition('allowedExtensions'))) {
				$this->setError( KText::sprintf('The file for %s is not valid.',$this->getPropertyDefinition('label')). ' '.KText::sprintf('The file has an invalid extension of %s. Allowed are %s.',$extension, implode(', ',$this->getPropertyDefinition('allowedExtensions'))) );
				$imageIsOk = false;
			}

		}

		// Check dimensions, but only if other checks have passed (avoid image dimension checks on non-image files)
		if ($imageIsOk) {

			// check minimum image dimensions
			if ($this->getPropertyDefinition('minimumDimensions')) {

				// Get dimensions in an array
				$minDimensions = $this->getPropertyDefinition('minimumDimensions');

				// Load the GD library
				if (class_exists('PHPThumb') == false) {
					require_once(KenedoPlatform::p()->getComponentDir('com_configbox').'/external/kenedo/external/phpthumb/phpthumb.php');
				}

				try	{
					$actualImage = new GD($file['tmp_name']);
					$dimensions = $actualImage->getCurrentDimensions();
				}
				catch (Exception $e) {
					KLog::log('Issue with image validation. Exception text was "'. $e->getMessage(), 'error');
					$this->setError( KText::sprintf('The system could not determine image dimensions for %s. Please check the file, if the problem persists, try saving the file in an alternative photo editor.', $this->getPropertyDefinition('label')));
					return false;
				}

				if(!empty($minDimensions['width'])) {
					if($minDimensions['width'] > $dimensions['width']){
						$this->setError( KText::sprintf('The image in field %1$s needs to be at least %2$s wide.', $this->getPropertyDefinition('label'), $minDimensions['width'].'px'));
						$imageIsOk = false;
					}
				}

				if(!empty($minDimensions['height'])) {
					if($minDimensions['height'] > $dimensions['height']){
						$this->setError( KText::sprintf('The image in field %1$s needs to be at least %2$s high.', $this->getPropertyDefinition('label'), $minDimensions['height'].'px'));
						$imageIsOk = false;
					}
				}

			}

		}

		return $imageIsOk;

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
		$currentOriginalFileName = $this->getCurrentOriginalFileName($data->id);

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

			$fileToRemove = $storageFolder . DS . $currentOriginalFileName;

			// Go on and delete the file (if it exists, otherwise just let it be)
			if (is_file($fileToRemove)) {

				$success = unlink($fileToRemove);

				if ($success == false) {
					$errorMsg = KText::sprintf('Could not remove the file for field %s in %s. Please check if your data folder and its content are writable and try again.', $this->getPropertyDefinition('label'), $fileToRemove);
					$this->setError($errorMsg);
					return false;
				}

				$this->deleteMutations($currentOriginalFileName);

			}

			// Set the file name storage field to empty (if we store the file name)
			if (isset($optionTags['SAVE_FILENAME'])) {
				$data->{$this->propertyName} = '';
				$this->updateOriginalFileName($recordId, '');
			}

			return true;

		}

		// If the user uploaded no file, all stays the same for our prop.
		if (empty($file['tmp_name'])) {

			// Just sneak in the file name in the data object (just in case the data is used after storing)
			$data->{$this->propertyName} = $this->getCurrentOriginalFileName($recordId);

			return true;

		}


		// Prepare the filename (there are a few settings that control how the file name is 'built' together)
		$newOriginalFileName = $this->getNewOriginalFilename($data);

		// Figure out the full filesystem path for the file
		$destinationPath = $storageFolder . DS . $newOriginalFileName;

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

		// Check for mutations
		$mutations = $this->getPropertyDefinition('mutations', array());

		if(count($mutations)) {

			// Prepare all file names
			$mutationFileNames = $this->getMutationFileNames($newOriginalFileName);

			// Go through all mutations and make them
			foreach ($mutations as $mutationName => $mutation) {
				$this->makeMutation($newOriginalFileName, $mutationFileNames[$mutationName], $mutation['mode'], $mutation['params']);
			}
		}

		// Save the original file name in table if configured for that
		if (isset($optionTags['SAVE_FILENAME'])) {
			$this->updateOriginalFileName($recordId, $newOriginalFileName);
		}

		// Add the file name to the data object (just in case someone uses this object right after storing)
		$data->{$this->propertyName} = $newOriginalFileName;

		// If the file name changed (because of the 'appendSerial' setting or anything else), remove the old file
		if ($currentOriginalFileName && $currentOriginalFileName != $newOriginalFileName) {

			$fileToRemove = $storageFolder . DS . $currentOriginalFileName;

			if (is_file($fileToRemove)) {

				$success = unlink($fileToRemove);

				if ($success == false) {
					$errorMsg = KText::sprintf('Could not remove the previous file for field %s in %s. Please check if your data folder and its content are writable and try again.', $this->getPropertyDefinition('label'), $fileToRemove);
					$this->setError($errorMsg);
					return false;
				}

				$this->deleteMutations($currentOriginalFileName);

			}

		}

		return true;

	}

	public function copy($data, $newId, $oldId) {

		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';

		KLog::log($logPrefix.' - Copying started for Image Property');

		// Get the current filename of the original and the mutations
		$originalFilename = $data->{$this->propertyName};
		$mutationFilenames = $this->getMutationFileNames($originalFilename);

		if (empty($originalFilename)) {
			KLog::log($logPrefix.' - No file set, skipping copying.');
			return true;
		}

		// Get new filenames
		$newOriginalFilename = $this->getNewFileNameForCopy($data, $newId);
		$newMutationFilenames = $this->getMutationFileNames($newOriginalFilename);

		// Get the path to the image directory
		$pathFilesystem = $this->getPropertyDefinition('dirBase');

		// Check if the original file exists
		if (is_file($pathFilesystem.DS.$originalFilename) == false) {
			KLog::log('Original file to copy not found in location "'.$pathFilesystem.DS.$originalFilename.'"', 'error');
			throw new Exception('Original image not found for property '.$this->getPropertyDefinition('label').' in model "'.get_class($this->model).'", record ID '.$oldId.'.');
		}

		// Check if any of the mutation images are missing
		foreach($mutationFilenames as $mutationKey => $mutationFilename) {

			if (is_file($pathFilesystem.DS.$mutationFilename) == false) {
				KLog::log('Mutation file "'.$mutationKey.'" to copy not found in location "'.$pathFilesystem.DS.$originalFilename.'"', 'error');
				throw new Exception('Mutation image "'.$mutationKey.'" not found for property '.$this->getPropertyDefinition('label').' in model "'.get_class($this->model).'", record ID '.$oldId.'.');

			}

		}

		// Create the image folder if it does not exist yet
		if (!is_dir($pathFilesystem)) {
			mkdir($pathFilesystem, 0775, true);
		}

		// Check if the file folder is writable
		if (!is_writable($pathFilesystem)) {
			throw new Exception('Directory for images for '.$this->getPropertyDefinition('label').' is write protected. Please make sure all folders in the data folder are writable. See dashboard for details.');
		}

		// Copy the original image
		$result = copy($pathFilesystem.DS.$originalFilename, $pathFilesystem.DS.$newOriginalFilename);

		if ($result === false) {
			KLog::log('Could not copy original image for property '.$this->propertyName.' in model "'.get_class($this->model).'". Last PHP message was '.error_get_last(), 'error');
			throw new Exception('Could not copy file for property '.$this->getPropertyDefinition('label').' in model "'.get_class($this->model).'".');
		}

		// Copy each mutation image
		foreach($mutationFilenames as $mutationKey => $mutationFilename) {

			$result = copy($pathFilesystem.DS.$mutationFilename, $pathFilesystem.DS.$newMutationFilenames[$mutationKey]);

			if ($result === false) {
				KLog::log('Could not copy mutation image for property '.$this->propertyName.' in model "'.get_class($this->model).'". Last PHP message was '.error_get_last(), 'error');
				throw new Exception('Could not copy file for property '.$this->getPropertyDefinition('label').' in model "'.get_class($this->model).'".');
			}

		}

		// Store new filename
		$data->{$this->propertyName} = $newOriginalFilename;

		// In case data stores in external table, let the base copy method do it
		if ($this->getPropertyDefinition('storeExternally') == true) {
			return parent::copy($data, $newId, $oldId);
		}
		else {

			// store locally otherwise
			$db = KenedoPlatform::getDb();

			// Make sure that NULL values are in fact stored as NULL
			if (empty($data->{$this->propertyName})) {
				$value = 'NULL';
			} else {
				$value = "'" . $db->getEscaped($data->{$this->propertyName}) . "'";
			}

			$query = "
			UPDATE `" . $this->model->getTableName() . "` 
			SET `" . $this->propertyName . "` = ".$value."
			WHERE `" . $this->model->getTableKey() . "` = '" . $db->getEscaped($newId) . "' 
			";

			$db->setQuery($query);
			$db->query();

		}

		return true;

	}

	/**
	 * Mind that this method is part of deleting a whole record, not just just the image.
	 *
	 * @param int $id
	 * @param string $tableName
	 * @return bool|void
	 */
	function delete($id, $tableName) {

		$currentFilename = $this->getCurrentOriginalFileName($id);

		if (!empty($currentFilename)) {
			if (is_file($this->getPropertyDefinition('dirBase').DS.$currentFilename)) {
				unlink($this->getPropertyDefinition('dirBase').DS.$currentFilename);
			}
		}

		$this->deleteMutations($currentFilename);

		// Let the base class method take care of externally stored data (may try to delete an already deleted external row)
		parent::delete($id, $tableName);

	}

	/**
	 * Appends full URL and filesystem path to the file (if bases are defined in prop defs)
	 * @param object $data
	 */
	function appendDataForGetRecord( &$data ) {

		if(!empty($data->{$this->propertyName})) {

			$pathUrl = $this->getPropertyDefinition('urlBase');
			if ($pathUrl) {
				$data->{$this->propertyName.'_href'} = (!empty($data->{$this->propertyName})) ? $pathUrl.'/'.$data->{$this->propertyName} : '';
			}

			$pathFilesystem = $this->getPropertyDefinition('dirBase');
			if ($pathFilesystem) {
				$data->{$this->propertyName.'_path'} = (!empty($data->{$this->propertyName})) ? $pathFilesystem.'/'.$data->{$this->propertyName} : '';
			}

			$mutations = $this->getPropertyDefinition('mutations', array());

			$fileNames = $this->getMutationFileNames($data->{$this->propertyName});

			foreach ($mutations as $mutationKey => $mutationData) {
				$fileName = $fileNames[$mutationKey];
				$objectKeyNamePath = $this->propertyName . '_' . $mutationKey . '_path';
				$objectKeyNameHref = $this->propertyName . '_' . $mutationKey . '_href';

				$data->$objectKeyNamePath = $pathFilesystem . '/' . $fileName;
				$data->$objectKeyNameHref = $pathUrl . '/' . $fileName;
			}

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
	 * Checks settings and current file upload and figures out the right file name to store.
	 * @param object $data
	 * @return string
	 */
	protected function getNewOriginalFilename($data) {

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
			$newBasename = $data->id;
		}
		// Else use the sanitized filename
		elseif ($this->getPropertyDefinition('filename')) {
			$newBasename = $this->getPropertyDefinition('filename');
		}
		else {
			$newBasename = preg_replace('#\.[^.]*$#', '', $filenameRaw);
		}



		if ($this->getPropertyDefinition('appendSerial')) {
			// Append a number to circumvent outdated cache data
			$newBasename .= '-'.str_pad(rand(1,1000),4,0);
		}

		return $newBasename.'.'.$fileExtension;

	}

	/**
	 * Gives you the file name that is currently stored in the DB
	 * @param mixed $id Model record ID
	 * @return string
	 */
	protected function getCurrentOriginalFileName($id) {

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
		$filename = $db->loadResult();

		return $filename;

	}

	/**
	 * This updates the column storing the filename in the base table (necessary since properties store AFTER the
	 * KenedoModel stores 'base' data.
	 * @param mixed $id
	 * @param string $filename
	 * @throws Exception If $id is falseish
	 */
	protected function updateOriginalFileName($id, $filename) {

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

	/**
	 * Returns an array of mutation file names (incl. extension, excl. folder info)
	 * @param string $filenameOriginal File name of the original file
	 * @return string[]
	 */
	protected function getMutationFileNames($filenameOriginal) {

		$mutations = $this->getPropertyDefinition('mutations', array());

		// No mutations? All done then
		if (count($mutations) == 0) {
			return array();
		}

		// No mutations? All done then
		if (empty($filenameOriginal)) {
			return array();
		}

		// Get the original file name excl. file extension
		$nameOriginal = KenedoFileHelper::stripExtension($filenameOriginal);
		// Store the extension separately for later
		$extensionOriginal = KenedoFileHelper::getExtension($filenameOriginal);

		$isUsingSerials = $this->getPropertyDefinition('appendSerial', false);

		// Extract the file's original name (excl. serial)
		if ($isUsingSerials == true) {
			$parts = explode('-', $nameOriginal);
			$serial = array_pop($parts);
			$baseMutation = implode('-', $parts);
		}
		else {
			$baseMutation = $nameOriginal;
			$serial = '';
		}

		// Collect all mutation file names
		$mutationFileNames = array();
		foreach($mutations as $mutationName=>$mutationData) {

			$mutationFileNames[$mutationName] =
				$baseMutation . '-' . $mutationName .
				(($isUsingSerials == true) ? '-'.$serial : '') .
				'.' .$extensionOriginal;
		}

		return $mutationFileNames;
	}

	/**
	 * Perform Mutation of an Image file
	 * @param string $filenameOriginal (excl. folder info)
	 * @param string $filenameMutation (excl. folder info)
	 * @param string $mode Mutation mode
	 * @param array $mutationParameters Parameters for the mutation mode
	 * @return boolean Creation result
	 * @throws Exception
	 */
	protected function makeMutation($filenameOriginal, $filenameMutation, $mode, $mutationParameters) {

		if(empty($filenameOriginal)) {
			throw new Exception("Something went wrong with original image filename.");
		}

		$baseDir = $this->getPropertyDefinition('dirBase');

		if (class_exists('PHPThumb') == false) {
			require_once(KenedoPlatform::p()->getComponentDir('com_configbox').'/external/kenedo/external/phpthumb/phpthumb.php');
		}

		switch($mode) {

			// 'forceRatioAndContain' resizes to image to fit within the given dimensions and crops (centered) if ratio does not fit
			case 'forceRatioAndContain':

				try	{
					$mutation = new GD($baseDir . DS . $filenameOriginal, array('resizeUp' => true, 'jpegQuality' => 75));
				}
				catch (Exception $e) {
					KLog::log('Problem with processing image. Exception message from GD was '.$e->getMessage(), 'error');
					throw new Exception('System error during image saving. ConfigBox error log contains analytic information.');
				}

				// Check if parameters are valid, throw an exception if they are not
				if (empty($mutationParameters['width']) || empty($mutationParameters['height'])) {
					throw new Exception('Mutation mode "'.$mode.'" used, but either parameter width or height is missing. Check property definitions. Model name is '. get_class($this->model).', property name is '.$this->propertyName);
				}

				// Do the resizing and save the image
				try	{
					$mutation->adaptiveResize($mutationParameters['width'], $mutationParameters['height']);
					$mutation->save($baseDir . DS . $filenameMutation);
				}
				catch (Exception $e) {
					KLog::log('Mode "'.$mode.'" on image failed. Error message was '. $e->getMessage(), 'error');
					throw new Exception("Could not save thumbnail image.");
				}

				break;

			// 'Contain' keeps the ratio and resizes the image to fit within given width and height. You can have width or height 0/null, then only the other dimension will be used
			case 'contain':

				try	{
					$mutation = new GD($baseDir . DS . $filenameOriginal, array('resizeUp' => false, 'jpegQuality' => 75));
				}
				catch (Exception $e) {
					KLog::log('Problem with processing image. Exception message from GD was '.$e->getMessage(), 'error');
					throw new Exception('System error during image saving. ConfigBox error log contains analytic information.');
				}

				// Check if parameters are valid, throw an exception if they are not
				if (empty($mutationParameters['width']) && empty($mutationParameters['height'])) {
					throw new Exception('Mutation mode "'.$mode.'" used, but neither width or height is set (you must provide at least one of them). Check property definitions. Model name is '. get_class($this->model).', property name is '.$this->propertyName);
				}

				// Set to 0 if missing
				if (empty($mutationParameters['width'])) {
					$mutationParameters['width'] = 0;
				}

				// Set to 0 if missing
				if (empty($mutationParameters['height'])) {
					$mutationParameters['height'] = 0;
				}

				// Do the resizing and save the image
				try	{
					$mutation->resize($mutationParameters['width'], $mutationParameters['height']);
					$mutation->save($baseDir . DS . $filenameMutation);
				}
				catch (Exception $e) {
					KLog::log('Mode "'.$mode.'" on image failed. Error message was '. $e->getMessage(), 'error');
					throw new Exception("Could not save thumbnail image.");
				}

				break;

			// 'Cover' blows up the image to the given dimensions, making one dimension longer if needed
			case 'cover':

				try	{
					$mutation = new GD($baseDir . DS . $filenameOriginal, array('resizeUp' => true, 'jpegQuality' => 75));
				}
				catch (Exception $e) {
					KLog::log('Problem with processing image. Exception message from GD was '.$e->getMessage(), 'error');
					throw new Exception('System error during image saving. ConfigBox error log contains analytic information.');
				}

				// Check if parameters are valid, throw an exception if they are not
				if (empty($mutationParameters['width']) || empty($mutationParameters['height'])) {
					throw new Exception('Mutation mode "'.$mode.'" used, but neither width or height is set (you must provide at least one of them). Check property definitions. Model name is '. get_class($this->model).', property name is '.$this->propertyName);
				}

				// Do the resizing and save the image
				try	{
					$mutation->resize($mutationParameters['width'], $mutationParameters['height']);
					$mutation->save($baseDir . DS . $filenameMutation);
				}
				catch (Exception $e) {
					KLog::log('Mode "'.$mode.'" on image failed. Error message was '. $e->getMessage(), 'error');
					throw new Exception("Could not save thumbnail image.");
				}

				break;

			default:
				throw new Exception('Unknown mutation mode "'.$mode.'" used. Model name is '. get_class($this->model).', property name is '.$this->propertyName);

		}

		return true;

	}

	/**
	 * Performs complete Mutation Image files deletion
	 * @param string $originalFilename Current Filename
	 * @return void
	 */
	protected function deleteMutations($originalFilename) {

		$folder = $this->getPropertyDefinition('dirBase');

		$mutationsToDelete = $this->getMutationFileNames($originalFilename);

		if(!empty($mutationsToDelete)) {
			foreach ($mutationsToDelete as $key => $item) {
				$file2delete =  $folder . DS . $item;
				if(is_file($file2delete)) {
					unlink($file2delete);
				}
			}
		}
	}


	protected function getNewFileNameForCopy($data, $newId) {

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
		KLog::log('PropertyFileCopyMethodGotFilename - Class(' . get_class($this) . ') - ' . KLog::time('ModelCopyMethod'), 'custom_copying');
		return $newFilename.'.'.$fileExtension;

	}

	/**
	 * Experimental function - not to be used yet
	 * @throws Exception
	 */
	function recreateMutations() {

		$records = $this->model->getRecords();
		$missingFiles = [];

		// Check for mutations
		$mutations = $this->getPropertyDefinition('mutations', array());
		$baseDir = $this->getPropertyDefinition('dirBase');

		foreach ($records as $record) {

			$fileName = $record->{$this->propertyName};

			if (!is_file($baseDir.'/'.$fileName)) {
				$missingFiles[] = $fileName;
				continue;
			}

			if (count($mutations)) {

				// Prepare all file names
				$mutationFileNames = $this->getMutationFileNames($fileName);

				// Go through all mutations and make them
				foreach ($mutations as $mutationName => $mutation) {
					$this->makeMutation($fileName, $mutationFileNames[$mutationName], $mutation['mode'], $mutation['params']);
				}
			}

		}

	}

}