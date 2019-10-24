<?php
class KenedoFileHelper {

	static function sanitizeFileName($filename) {
		$filename = str_replace('..','',$filename);
		$filename = str_replace(DS,'',$filename);
		return $filename;
	}

	static function getFiles($dir, $filter = '.', $recursive = false, $fullPath = false, $ignore = array('.svn', 'CVS', '.git','.DS_Store')) {

		$files = array();

		if (!is_dir($dir)) {
			return $files;
		}

		$handle = opendir($dir);

		while (($file = readdir($handle)) !== false) {
			if (($file == '.') || ($file == '..') || (in_array($file, $ignore))) {
				continue;
			}

			$path = $dir.DS.$file;

			if ($recursive && is_dir($path)) {
				$files2 = self::getFiles($path, $filter, $recursive, $fullPath, $ignore);
				$files = array_merge($files, $files2);
			}
			else {
				if ($filter == '.' || preg_match("/$filter/", $file)) {
					$files[] = ($fullPath) ? $path : $file;
				}
			}

		}
		closedir($handle);
		return $files;

	}

	static function makeFolder($path) {
		return mkdir($path, 0777, true);
	}

	static function getFolders($dir, $filter = '', $recursive = false, $fullPath = false, $ignore = array('.svn', 'CVS', '.git')) {

		$files = array();

		if (!is_dir($dir)) {
			return $files;
		}

		$handle = opendir($dir);

		while (($file = readdir($handle)) !== false) {

			if (!is_dir($dir.DS.$file) || ($file == '.') || ($file == '..') || (in_array($file, $ignore))) {
				continue;
			}

			$path = $dir.DS.$file;

			if ($recursive && is_dir($path)) {
				$files2 = self::getFolders($path, $filter, $recursive, $fullPath, $ignore);
				$files = array_merge($files, $files2);
			}

			if ($filter == '' or preg_match("/$filter/", $file)) {
				$files[] = ($fullPath) ? $path : $file;
			}

		}
		closedir($handle);

		return $files;

	}

	/**
	 * @param string $path
	 * @return bool
	 */
	static function deleteFolder($path) {

		clearstatcache(true, $path);

		if (!is_dir($path)) {
			KLog::log('Directory `'.$path.'` did not exist.','warning');
			return true;
		}

		$handle = opendir($path);
		while (($file = readdir($handle)) !== false) {
			if ( ($file == '.') || ($file == '..') ) {
				continue;
			}

			if (is_dir($path.DS.$file)) {
				$success = self::deleteFolder($path.DS.$file);
				if ($success == false) {
					closedir($handle);
					KLog::log('Directory `'.$path.DS.$file.'` could not be deleted.', 'error');
					return false;
				}
			}
			else {

				// If it's a PHP file, Invalidate any OPcache cache for the file
				if (self::getExtension($file) == 'php') {
					if (function_exists('opcache_invalidate')) {
						opcache_invalidate($path.DS.$file, true);
					}
					if (function_exists('apc_delete_file')) {
						apc_delete_file($path.DS.$file);
					}
				}

				$success = unlink($path.DS.$file);
				if ($success == false) {
					KLog::log('File `'.$path.DS.$file.'` could not be deleted.','error');
					closedir($handle);
					return false;
				}
			}

		}

		closedir($handle);

		$success = rmdir($path);
		if ($success == false) {
			KLog::log('Directory `'.$path.'` could not be deleted.','error');
			return false;
		}
		return true;
	}

	static function writeFile($path, $content) {

		$folder = dirname($path);

		if (!is_dir($folder)) {
			mkdir($folder,0777,true);
		}

		$succ = file_put_contents($path, $content);
		if ($succ === false) {
			return false;
		}
		else {
			return true;
		}

	}

	static function stripExtension($path) {
		$base = pathinfo($path, PATHINFO_FILENAME);
		return $base;
	}

	static function getExtension($path) {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		return $ext;
	}

	static function canCheckMimeType() {
		return (function_exists('mime_content_type') or function_exists('finfo_open'));
	}

	static function getMimeType($filePath) {

		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);

			if (!$finfo) {
				KLog::log("Opening fileinfo database failed",'warning');
				$mimeType = false;
			}
			else {
				$mimeType = finfo_file($finfo, $filePath);
			}
			if ($mimeType) {
				$mimeType = explode(';',$mimeType);
				$mimeType = $mimeType[0];
			}
		}
		else {
			KLog::log("Fileinfo extension not installed on webserver. Cannot determine MIME Type of files.",'debug');
			$mimeType = false;
		}

		return $mimeType;

	}

	static function isValidFile($path, $validExtensions = array(), $validMimeTypes = array(), $validSizeMb = NULL) {

		if (is_string($validExtensions) && !empty($validExtensions)) {
			$extensions = strtolower($validExtensions);
			$extensions = str_replace(',', ' ', $extensions);
			$extensions = str_replace('.', '', $extensions);
			$extensions = str_replace('  ', ' ', $extensions);
			$extensions = explode(' ',$extensions);
			$validExtensions = array_map('trim',$extensions);
		}

		if ($key = array_search('php',$validExtensions)) {
			unset($validExtensions[$key]);
		}

		if (is_string($validMimeTypes) && !empty($validMimeTypes)) {
			$mimeTypes = strtolower($validMimeTypes);
			$mimeTypes = str_replace(',', ' ', $mimeTypes);
			$mimeTypes = str_replace('.', '', $mimeTypes);
			$mimeTypes = str_replace('  ', ' ', $mimeTypes);
			$mimeTypes = explode(' ',$mimeTypes);
			$validMimeTypes = array_map('trim',$mimeTypes);
		}

		if (is_array($path)) {
			$filename = $path['name'];
			$path = $path['tmp_name'];
		}
		else {
			$filename = $path;
		}

		$fileExtension = pathinfo($filename, PATHINFO_EXTENSION);

		if (!in_array($fileExtension,$validExtensions)) {
			$response = KText::sprintf('Files with extension %s are not allowed.',$fileExtension);
			return $response;
		}

		$fileMimeType = self::getMimeType($path);
		if ($fileMimeType) {
			if (!in_array($fileMimeType,$validMimeTypes)) {
				$response = KText::sprintf('Files with MIME type %s are not allowed.',$fileMimeType);
				return $response;
			}
		}

		if ($validSizeMb) {
			$validFilesizeBytes = $validSizeMb * 1024 * 1024;
			if ( filesize($path) > $validFilesizeBytes ) {
				$response = KText::sprintf('File size is over the maximum of %s MB.', $validSizeMb);
				return $response;
			}
		}

		return true;

	}

	static function extractZip($archive, $destination) {

		$zip = zip_open($archive);
		if (is_resource($zip)) {

			if (!is_dir($destination)) {
				$succ = mkdir($destination,0755,true);
				if (!$succ) {
					return false;
				}
			}

			// Read files in the archive
			while ($file = @zip_read($zip)) {
				if (zip_entry_open($zip, $file, "r")) {

					if (substr(zip_entry_name($file), strlen(zip_entry_name($file)) - 1) != "/") {

						$fileContent = zip_entry_read($file, zip_entry_filesize($file));

						$dir = $destination . DS . dirname(zip_entry_name($file));

						if (!is_dir($dir)) {
							$succ = mkdir($dir,0755,true);
							if (!$succ) {
								return false;
							}
						}
						$fileName = $dir.DS.basename(zip_entry_name($file));
						$succ = file_put_contents($fileName, $fileContent);

						if ($succ === false) {
							return false;
						}

						zip_entry_close($file);
					}
				}
				else {
					return false;
				}
			}

			@zip_close($zip);
		}
		else {
			return false;
		}

		return true;
	}

	/**
	 * @param string $sourcePath Path of directory to be zip.
	 * @param string $outZipPath Path of output zip file.
	 */
	public static function zipFolder($sourcePath, $outZipPath)
	{
		$pathInfo = pathInfo($sourcePath);
		$parentPath = $pathInfo['dirname'];
		$dirName = $pathInfo['basename'];

		$z = new ZipArchive();
		$z->open($outZipPath, ZIPARCHIVE::CREATE);
		$z->addEmptyDir($dirName);
		self::addFolderToZip($sourcePath, $z, strlen("$parentPath/"));
		$z->close();
	}

	/**
	 * @param string $folder
	 * @param ZipArchive $zipFile
	 * @param int $exclusiveLength
	 */
	public static function addFolderToZip($folder, &$zipFile, $exclusiveLength) {
		$handle = opendir($folder);
		if (!$handle) {
			return;
		}
		while (false !== $f = readdir($handle)) {
			if ($f != '.' && $f != '..') {
				$filePath = "$folder/$f";
				// Remove prefix from file path before add to zip.
				$localPath = substr($filePath, $exclusiveLength);
				if (is_file($filePath)) {
					$zipFile->addFile($filePath, $localPath);
				} elseif (is_dir($filePath)) {
					// Add sub-directory.
					$zipFile->addEmptyDir($localPath);
					self::addFolderToZip($filePath, $zipFile, $exclusiveLength);
				}
			}
		}
		closedir($handle);
	}

	/**
	 * @param $file
	 * @return int[] array with width and height
	 */
	static function getImageDimensions($file) {
		$arr = getimagesize($file);
		$return['width'] = $arr[0];
		$return['height'] = $arr[1];
		return $return;
	}

}