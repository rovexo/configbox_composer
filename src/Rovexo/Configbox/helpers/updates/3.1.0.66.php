<?php
defined('CB_VALID_ENTRY') or die();

// Cleanup from possible error with custom CSS/JS file adaption
// Makes sure that the custom.css and custom.js file is in place

//$newAssetsPath = KenedoPlatform::p()->getDirCustomization().'/assets';
//
//if (!is_dir($newAssetsPath)) {
//	$success = mkdir($newAssetsPath, 0755);
//	if ($success == false) {
//		KLog::log('Could not create customization assets folder during upgrade', 'upgrade_errors');
//	}
//}
//
//// Rename or create the custom JS and CSS files
//$files = array(
//	$newAssetsPath.'/css/custom.css',
//	$newAssetsPath.'/javascript/custom.js',
//);
//
//foreach($files as $file) {
//	if (!is_dir(dirname($file))) {
//		mkdir(dirname($file), 0755, true);
//	}
//
//	if (!is_file($file)) {
//		file_put_contents($file, '');
//	}
//}