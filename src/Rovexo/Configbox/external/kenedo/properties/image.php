<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyImage extends KenedoProperty {

	protected const outputFormats = ['jpg', 'gif', 'png', 'webp'];

	public $file = null;

	function getFile() {
		return $this->file;
	}

	/**
	 * Like the parent method, but it also looks for an uploaded file and keeps a record of it in ->file
	 * @param $data
	 * @return void
	 */
	function getDataFromRequest( &$data ) {
		parent::getDataFromRequest( $data );
		$fileUpload = KRequest::getFile($this->propertyName);
		if ($fileUpload) {
			$this->file = $fileUpload;
		}
	}

	/**
	 * Like parent, but also looks for a provided image to be used just as it was uploaded
	 * @param $data
	 * @param $transferRecord
	 * @param $propertyFilePaths
	 * @param $filesBaseDir
	 * @return void
	 */
	function getDataFromTransferRecord(&$data, $transferRecord, $propertyFilePaths, $filesBaseDir) {

		$data->{$this->propertyName} = $transferRecord[$this->propertyName] ?? '';
		$this->file = null;

		if ($data->{$this->propertyName} && isset($propertyFilePaths[$this->propertyName])) {
			$srcFile = $filesBaseDir .'/'. $propertyFilePaths[$this->propertyName][0];
			if (is_file($srcFile)) {
				$this->file = [
					'tmp_name' => $srcFile,
					'name' => basename($srcFile),
					'size' => filesize($srcFile),
					'error' => null,
				];
			}
		}
	}

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
			$fileUpload = $this->getFile();
			$currentFile = $this->getCurrentOriginalFileName($data->id);
			if (!$fileUpload && !$currentFile) {
				$this->setError( KText::sprintf('You need to upload a file for field %s.',$this->getPropertyDefinition('label')) );
				return false;
			}
		}

		$file = $this->getFile();

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

				try	{
					$size = getimagesize($file['tmp_name']);
					$dimensions = [
						'width' => $size[0],
						'height' => $size[1],
					];
				}
				catch (Throwable $e) {
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

		// Get the uploaded file's info
		$file = $this->getFile();

		// If the user checked the 'delete' checkbox, delete the item
		if ($shouldDelete) {

			if ($canDelete == false) {
				throw new Exception('User tried to delete a file that should not be deletable (by settings)');
			}

			$fileToRemove = $storageFolder . '/' . $currentOriginalFileName;

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
		$destinationPath = $storageFolder . '/' . $newOriginalFileName;

		// Move the file from tmp to destination
		$moveSuccess = copy($file['tmp_name'], $destinationPath);

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

			$fileToRemove = $storageFolder . '/' . $currentOriginalFileName;

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

		// Get the current filename of the original and the mutations
		$originalFilename = $data->{$this->propertyName};

		if (empty($originalFilename)) {
			return true;
		}

		// Get new filenames
		$newOriginalFilename = $this->getNewFileNameForCopy($data, $newId);

		// Get the path to the image directory
		$pathFilesystem = $this->getPropertyDefinition('dirBase');

		// Check if the original file exists
		if (is_file($pathFilesystem.'/'.$originalFilename) == false) {
			KLog::log('Original file to copy not found in location "'.$pathFilesystem.'/'.$originalFilename.'"', 'error');
			throw new Exception('Original image not found for property '.$this->getPropertyDefinition('label').' in model "'.get_class($this->model).'", record ID '.$oldId.'.');
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
		$result = copy($pathFilesystem.'/'.$originalFilename, $pathFilesystem.'/'.$newOriginalFilename);

		if ($result === false) {
			KLog::log('Could not copy original image for property '.$this->propertyName.' in model "'.get_class($this->model).'". Last PHP message was '.error_get_last(), 'error');
			throw new Exception('Could not copy file for property '.$this->getPropertyDefinition('label').' in model "'.get_class($this->model).'".');
		}

		$existingMutationFileNames = $this->getExistingMutationFileNames($originalFilename);

		// Copy each mutation image
		foreach($existingMutationFileNames as $mutationKey => $mutationSourceName) {

			$fileExtension = KenedoFileHelper::getExtension($mutationSourceName);
			$extensionMutationNames = $this->getMutationFileNames($newOriginalFilename, $fileExtension);
			$mutationDestName = $extensionMutationNames[$mutationKey];

			$result = copy(
				$pathFilesystem.'/'.$mutationSourceName,
				$pathFilesystem.'/'.$mutationDestName
			);

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
	 * @return bool
	 */
	function delete($id, $tableName) {

		$currentFilename = $this->getCurrentOriginalFileName($id);

		if (!empty($currentFilename)) {
			if (is_file($this->getPropertyDefinition('dirBase').'/'.$currentFilename)) {
				unlink($this->getPropertyDefinition('dirBase').'/'.$currentFilename);
			}
		}

		$this->deleteMutations($currentFilename);

		// Let the base class method take care of externally stored data (may try to delete an already deleted external row)
		return parent::delete($id, $tableName);

	}

	/**
	 * Appends full URL and filesystem path to the file (if bases are defined in prop defs)
	 * @param object $data
	 */
	function appendDataForGetRecord( &$data ) {

		if(!empty($data->{$this->propertyName})) {

			$fileNameOriginal = $data->{$this->propertyName};

			// Add URL to original
			$pathUrl = $this->getPropertyDefinition('urlBase');
			if ($pathUrl) {
				$data->{$this->propertyName.'_href'} = $pathUrl.'/'.$fileNameOriginal;
			}

			// Add file path to original
			$pathFilesystem = $this->getPropertyDefinition('dirBase');
			if ($pathFilesystem) {
				$data->{$this->propertyName.'_path'} = $pathFilesystem.'/'.$fileNameOriginal;
			}

			// Get existing mutations
			$mutationFileNames = $this->getExistingMutationFileNames($fileNameOriginal);

			// Add URL and path to each mutation
			foreach ($mutationFileNames as $mutationName => $mutationFileName) {

				$objectKeyNamePath = $this->propertyName . '_' . $mutationName . '_path';
				$objectKeyNameHref = $this->propertyName . '_' . $mutationName . '_href';

				$data->$objectKeyNamePath = $pathFilesystem . '/' . $mutationFileName;
				$data->$objectKeyNameHref = $pathUrl . '/' . $mutationFileName;

			}

		}

		parent::appendDataForGetRecord( $data );

	}

	function getPropertyFilePaths($record) {

		$filePaths = [];

		$dirDataStore = KenedoPlatform::p()->getDirDataStore();

		if (empty($record->{$this->propertyName})) {
			return [];
		}

		$fileNameOriginal = $record->{$this->propertyName};

		$basePath = $this->getPropertyDefinition('dirBase');

		// The base path should be relative based on the store's data dir
		$basePath = str_replace($dirDataStore, '', $basePath);

		// Add the original image
		if ($basePath) {
			$filePaths[] = $basePath.'/'.$fileNameOriginal;
		}

		// Mutations won't be copied, on import they will be regenerated

		// Get rid of reference to any missing images
		foreach ($filePaths as $k=>$filePath) {
			if (!is_file($dirDataStore.$filePath)) {
				unset($filePaths[$k]);
			}
		}

		return $filePaths;

	}

	/**
	 * Checks settings and current file upload and figures out the right file name to store.
	 * @param object $data
	 * @return string
	 */
	protected function getNewOriginalFilename($data) {

		$file = $this->getFile();

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

		return (string)$filename;

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
				UPDATE `".$table."` 
				SET `".$this->propertyName."` = '".$db->getEscaped($filename)."' 
				WHERE `".$keyColumn."` = '".$db->getEscaped($id)."'";
		$db->setQuery($query);
		$db->query();

	}

	/**
	 * @param string $filenameOriginal
	 * @return string[]
	 */
	protected function getExistingMutationFileNames($filenameOriginal) {

		// No current original file, done then
		if (empty($filenameOriginal)) {
			return array();
		}

		$mutations = $this->getPropertyDefinition('mutations', array());

		// No mutations? All done then
		if (count($mutations) == 0) {
			return array();
		}

		$baseDir = $this->getPropertyDefinition('dirBase');

		// Get the original file name excl. file extension
		$baseNameOriginal = KenedoFileHelper::stripExtension($filenameOriginal);

		// Store the extension separately for later
		$extensionOriginal = KenedoFileHelper::getExtension($filenameOriginal);

		$isUsingSerials = $this->getPropertyDefinition('appendSerial', false);

		// Extract the file's original name (excl. serial)
		if ($isUsingSerials == true) {
			$parts = explode('-', $baseNameOriginal);
			$serialPart = '-'.array_pop($parts);
			$baseMutation = implode('-', $parts);
		}
		else {
			$baseMutation = $baseNameOriginal;
			$serialPart = '';
		}

		// Collect all mutation file names
		$mutationFileNames = array();

		foreach ($mutations as $mutationName => $mutationData) {
			$mutationExtension = $mutationData['params']['outputFormat'] ?? $extensionOriginal;
			$mutationFileName = $baseMutation.'-'.$mutationName.$serialPart.'.'.$mutationExtension;
			if (file_exists($baseDir.'/'.$mutationFileName)) {
				$mutationFileNames[$mutationName] = $mutationFileName;
			}
			else {
				foreach (self::outputFormats as $mutationExtension) {
					$mutationFileName = $baseMutation.'-'.$mutationName.$serialPart.'.'.$mutationExtension;
					if (file_exists($baseDir.'/'.$mutationFileName)) {
						$mutationFileNames[$mutationName] = $mutationFileName;
						break;
					}
				}
			}
		}

		return $mutationFileNames;

	}

	/**
	 * Returns an array of mutation file names (incl. extension, excl. folder info)
	 * @param string $filenameOriginal File name of the original file
	 * @param string $extOverride Optional extension to use (if omitted, it uses the outputFormat extension)
	 * @return string[]
	 */
	protected function getMutationFileNames($filenameOriginal, $extOverride = null) {

		// No current original file, done then
		if (empty($filenameOriginal)) {
			return array();
		}

		$mutations = $this->getPropertyDefinition('mutations', array());

		// No mutations? All done then
		if (count($mutations) == 0) {
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

		foreach ($mutations as $mutationName=>$mutationData) {

			$fileName = $baseMutation . '-' . $mutationName;

			if ($isUsingSerials == true) {
				$fileName .= '-'.$serial;
			}

			if ($extOverride) {
				$ext = $extOverride;
			}
			elseif (!empty($mutationData['params']['outputFormat'])) {
				$ext = $mutationData['params']['outputFormat'];
			}
			else {
				$ext = $extensionOriginal;
			}

			$fileName .= '.'. $ext;

			$mutationFileNames[$mutationName] = $fileName;

		}

		return $mutationFileNames;
	}

	/**
	 * Perform Mutation of an Image file
	 * @param string $filenameSource (excl. folder info)
	 * @param string $filenameTarget (excl. folder info)
	 * @param string $mode Mutation mode
	 * @param array $mutationParameters Parameters for the mutation mode
	 * @throws Exception
	 */
	protected function makeMutation($filenameSource, $filenameTarget, $mode, $mutationParameters) {

		if (empty($filenameSource)) {
			throw new Exception('Could not create image variant for image. Source file does not exist.');
		}

		try {
			if (extension_loaded('imagick') && class_exists('Imagick')) {
				$driver = new Intervention\Image\Drivers\Imagick\Driver();
			}
			else {
				$driver = new Intervention\Image\Drivers\GD\Driver();
			}

			$manager = new Intervention\Image\ImageManager($driver);
			$baseDir = $this->getPropertyDefinition('dirBase');

			$image = $manager->read($baseDir . '/' . $filenameSource);

			// Set to RBG and remove color profile
			$image->setColorspace(Intervention\Image\Colors\Rgb\Colorspace::class);
			$image->removeProfile();

			switch($mode) {

				// Scales the image to fit into target dimensions, cropping any excess
				case 'forceRatioAndContain': // Deprecated old name
				case 'coverAndCrop':
					$image->cover($mutationParameters['width'], $mutationParameters['height'], 'center');
					break;
				// Scales the image to fit into target dimensions leaving one dimension shorter if need be
				case 'contain':
					$image->scale($mutationParameters['width'] ?? null, $mutationParameters['height'] ?? null);
					break;
				// Scales the image to fit into target dimensions leaving one dimension longer if need be
				case 'cover':
					$image = $this->getImageCoveredWithoutCrop($image, $mutationParameters['width'] ?? null, $mutationParameters['height'] ?? null);
					break;
				default:
					throw new Exception('Unknown mutation mode "'.$mode.'" used. Model name is '. get_class($this->model).', property name is '.$this->propertyName);

			}

			$quality = $mutationParameters['outputQuality'] ?? 70;

			switch ($mutationParameters['outputFormat'] ?? 'asInput') {

				case 'webp':
					$image->toWebp($quality);
					break;
				case 'jpg':
					$image->toJpeg($quality);
					break;
				case 'png':
					$image->toPng();
					break;
				case 'gif':
					$image->toGif();
					break;

			}
		}
		catch (Throwable $e) {
			KLog::logException($e);
			throw new Exception('Could not create image variant for image. Error message was "'.$e->getMessage().'".');
		}

		try {
			$image->save($baseDir . '/' . $filenameTarget);
		}
		catch (Exception $e) {
			KLog::logException($e);
			throw new Exception('Saving image variant for '.$this->propertyDefinition['label'].' failed.');
		}

	}

	/**
	 * @param \Intervention\Image\Interfaces\ImageInterface $image
	 * @param ?int $targetWidth
	 * @param ?int $targetHeight
	 * @return \Intervention\Image\Interfaces\ImageInterface
	 */
	protected function getImageCoveredWithoutCrop($image, $targetWidth = null, $targetHeight = null) {
		$targetWidth = $mutationParameters['width'] ?? null;
		$targetHeight = $mutationParameters['height'] ?? null;
		if ($targetWidth) {
			if ($image->width() != $targetWidth) {
				$image->scale($targetWidth);
			}
			if ($targetHeight) {
				if ($image->height() < $targetHeight) {
					$image->scale(null, $targetHeight);
				}
			}
		}
		elseif ($targetHeight) {
			if ($image->height() != $targetHeight) {
				$image->scale(null, $targetHeight);
			}
			if ($targetWidth) {
				if ($image->width() < $targetWidth) {
					$image->scale($targetWidth);
				}
			}
		}
		return $image;
	}

	/**
	 * Performs complete Mutation Image files deletion
	 * @param string $originalFilename Current Filename
	 * @return void
	 */
	protected function deleteMutations($originalFilename) {

		$folder = $this->getPropertyDefinition('dirBase');

		$mutationsToDelete = $this->getExistingMutationFileNames($originalFilename);

		foreach ($mutationsToDelete as $item) {
			$file2delete =  $folder . '/' . $item;
			if (is_file($file2delete)) {
				unlink($file2delete);
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

		// Check for mutations
		$mutations = $this->getPropertyDefinition('mutations', array());
		$baseDir = $this->getPropertyDefinition('dirBase');

		foreach ($records as $record) {

			$fileName = $record->{$this->propertyName};

			if (!is_file($baseDir.'/'.$fileName)) {
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