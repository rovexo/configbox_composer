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
	KenedoPlatform::p()->getOldDirCustomizationAssets(),
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

$defaultImageDir = KenedoPlatform::p()->getDirDataStore().'/public/default_images';

// In case the default product image is not there, copy it from the system's image folder
if (!is_file($defaultImageDir.'/default_prod_image.jpg')) {
	$src = KenedoPlatform::p()->getDirAssets().'/images/default_prod_image.jpg';
	if (!is_dir($defaultImageDir)) {
		mkdir($defaultImageDir, 0775, true);
	}
	copy($src, $defaultImageDir.'/default_prod_image.jpg');
}
