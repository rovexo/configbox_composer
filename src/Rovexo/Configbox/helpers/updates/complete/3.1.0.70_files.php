<?php
defined('CB_VALID_ENTRY') or die();

// Prepare new ones
$htaccessPublic = "allow from all

<IfModule mod_expires.c>
	ExpiresActive on
	ExpiresDefault \"access plus 1 years\"
</IfModule>

<IfModule mod_headers.c>
	Header set Cache-Control \"public\"
	Header always set Access-Control-Allow-Origin *
</IfModule>";

$htaccessPrivate = "deny from all";

$privateFolders = array(
	KenedoPlatform::p()->getDirDataCustomer().DS.'private',
	KenedoPlatform::p()->getDirDataStore().DS.'private',
);

$publicFolders = array(
	KenedoPlatform::p()->getDirAssets(),
	KenedoPlatform::p()->getDirDataCustomer().DS.'public',
	KenedoPlatform::p()->getDirDataStore().DS.'public',
	KenedoPlatform::p()->getDirCustomizationAssets(),
);

foreach ($privateFolders as $folder) {

	if (is_dir($folder) == false) {
		mkdir($folder, 0775, true);
	}

	if (is_dir($folder)) {
		file_put_contents($folder.DS.'.htaccess', $htaccessPrivate);
	}
}

foreach ($publicFolders as $folder) {

	if (is_dir($folder) == false) {
		mkdir($folder, 0775, true);
	}

	if (is_dir($folder)) {
		file_put_contents($folder.DS.'.htaccess', $htaccessPublic);
	}
}

// In case the default product image is not there, copy it from the system's image folder
if (!is_file(CONFIGBOX_DIR_DEFAULT_IMAGES.'/default_prod_image.jpg')) {
	$src = KenedoPlatform::p()->getDirAssets().'/images/default_prod_image.jpg';
	if (!is_dir(CONFIGBOX_DIR_DEFAULT_IMAGES)) {
		mkdir(CONFIGBOX_DIR_DEFAULT_IMAGES, 0775, true);
	}
	copy($src, CONFIGBOX_DIR_DEFAULT_IMAGES.'/default_prod_image.jpg');
}

$assetsFolder = KenedoPlatform::p()->getDirCustomizationAssets();

$files = array(
	$assetsFolder.'/css/custom.css',
	$assetsFolder.'/css/custom.min.css',
	$assetsFolder.'/javascript/custom.js',
	$assetsFolder.'/javascript/custom.min.js',
	$assetsFolder.'/javascript/custom_questions.js',
	$assetsFolder.'/javascript/custom_questions.min.js',
);

foreach ($files as $file) {

	$dir = dirname($file);

	if (is_dir($dir) == false) {
		mkdir($dir, 0775, true);
	}

	file_put_contents($file, '');

}