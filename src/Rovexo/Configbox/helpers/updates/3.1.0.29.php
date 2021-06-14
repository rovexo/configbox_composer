<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (KenedoPlatform::getName() == 'magento') {
	$oldStoreFolder = Mage::getBaseDir('media').'/elovaris/configbox/store_data';
	$oldCustomerFolder = Mage::getBaseDir('media').'/elovaris/configbox/customer_data';
	$oldSettingsFolder = Mage::getBaseUrl('media').'/elovaris/configbox/settings';
}
else {
	$oldStoreFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data';
	$oldCustomerFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data';
	$oldSettingsFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data/settings';
}

$mappings = array(
	$oldStoreFolder.'/default_images' 				=> KenedoPlatform::p()->getDirDataStore().'/public/default_images',
	$oldStoreFolder.'/element_images' 				=> KenedoPlatform::p()->getDirDataStore().'/public/question_decorations',
	$oldStoreFolder.'/maxmind_geoip'				=> KenedoPlatform::p()->getDirDataStore().'/private/maxmind',
	$oldStoreFolder.'/opt_images'					=> KenedoPlatform::p()->getDirDataStore().'/public/vis_answer_images',
	$oldStoreFolder.'/option_images'				=> KenedoPlatform::p()->getDirDataStore().'/public/answer_images',
	$oldStoreFolder.'/option_picker_images'			=> KenedoPlatform::p()->getDirDataStore().'/public/answer_picker_images',
	$oldStoreFolder.'/prod_baseimages'				=> KenedoPlatform::p()->getDirDataStore().'/public/vis_product_images',
	$oldStoreFolder.'/prod_images'					=> KenedoPlatform::p()->getDirDataStore().'/public/product_images',
	$oldStoreFolder.'/product_detail_pane_icons'	=> KenedoPlatform::p()->getDirDataStore().'/public/product_detail_pane_icons',
	$oldStoreFolder.'/shoplogo'						=> KenedoPlatform::p()->getDirDataStore().'/public/shoplogos',

	$oldCustomerFolder.'/file_uploads'				=> KenedoPlatform::p()->getDirDataCustomer().'/public/file_uploads',
	$oldCustomerFolder.'/invoices'					=> KenedoPlatform::p()->getDirDataCustomer().'/private/invoices',
	$oldCustomerFolder.'/position_images'			=> KenedoPlatform::p()->getDirDataCustomer().'/public/position_images',
	$oldCustomerFolder.'/quotations'				=> KenedoPlatform::p()->getDirDataCustomer().'/private/quotations',

	$oldSettingsFolder								=> KenedoPlatform::p()->getDirCustomizationSettings(),

);

$deletions = array(
	$oldStoreFolder.'/opt_images_on',
	$oldStoreFolder.'/opt_images_off',
	$oldStoreFolder.'/order_files',
	$oldStoreFolder.'/downloads',
	$oldStoreFolder.'/cache',
);

function aea_write_note($mappings) {

	$rootDir = KenedoPlatform::p()->getRootDirectory();

	$lines = array();
	foreach ($mappings as $old=>$new) {
		$lines[] = str_replace($rootDir, '', $old). ' -> '. str_replace($rootDir, '', $new);
	}
	$text = implode("<br /><br />", $lines);

	// Store the addon information (mind it's not tamper proof, less trusting checks need to be done in encrypted code)
	$db = KenedoPlatform::getDb();
	$query = "REPLACE INTO `#__configbox_system_vars` SET `value` = '".$db->getEscaped($text)."', `key` = 'folder_movings'";
	$db->setQuery($query);
	$db->query();

}

$rootDir = KenedoPlatform::p()->getRootDirectory();

foreach ($mappings as $old=>$new) {

	$oldShort = str_replace($rootDir, '', $old);
	$newShort = str_replace($rootDir, '', $new);

	clearstatcache(true);

	KLog::log('Dealing with '.$oldShort.' to '.$newShort, 'custom_new_folders');

	if (is_dir($old) == false) {
		KLog::log('Old folder '.$oldShort.' was not created yet. Skipping it.', 'custom_new_folders');
		continue;
	}

	if (is_dir($new) == true) {
		KLog::log('New folder '.$newShort.' already exists. It is a problem, reporting.', 'custom_new_folders');
		aea_write_note($mappings);
		KLog::log('New folder structure: New dir "'.$new.'" already exists.', 'error');
		continue;
	}

	// Make parent folders if needed
	if (is_dir(dirname($new)) == false) {
		mkdir(dirname($new), 0777, true);
	}

	if (is_writable(dirname($new)) == false) {
		KLog::log('New folder '.$newShort.' parent folder is not writable. It is a problem, reporting.', 'custom_new_folders');

		KLog::log('New folder structure: Could not rename "'.$old.'" to "'.$new.'". Parent folder is not writable', 'error');
		aea_write_note($mappings);
		continue;
	}

	KLog::log('Moving folder '.$oldShort.' to '.$newShort, 'custom_new_folders');

	$success = rename($old, $new);
	if ($success == false) {
		KLog::log('Moving folder '.$oldShort.' to '.$newShort. ' FAILED. Reporting it.', 'custom_new_folders');

		aea_write_note($mappings);
		continue;
	}
}

foreach ($deletions as $dir) {
	if (is_dir($dir)) {
		KenedoFileHelper::deleteFolder($dir);
	}
}

// Remove old htaccess files
foreach ($mappings as $old=>$new) {
	if (is_file($new.'/.htaccess')) {
		unlink($new.'/.htaccess');
	}
}

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
	KenedoPlatform::p()->getDirDataCustomer().'/private',
	KenedoPlatform::p()->getDirDataStore().'/private',
);

$publicFolders = array(
	KenedoPlatform::p()->getDirAssets(),
	KenedoPlatform::p()->getDirDataCustomer().'/public',
	KenedoPlatform::p()->getDirDataStore().'/public',
	KenedoPlatform::p()->getOldDirCustomizationAssets(),
);

foreach ($privateFolders as $folder) {
	if (is_dir($folder)) {
		file_put_contents($folder.'/.htaccess', $htaccessPrivate);
	}
}

foreach ($publicFolders as $folder) {
	if (is_dir($folder)) {
		file_put_contents($folder.'/.htaccess', $htaccessPublic);
	}
}

// In case the default product image is not there, copy it from the system's image folder
if (!is_file(KenedoPlatform::p()->getDirDataStore().'/public/default_images/default_prod_image.jpg')) {
	$src = KenedoPlatform::p()->getDirAssets().'/images/default_prod_image.jpg';
	copy($src, KenedoPlatform::p()->getDirDataStore().'/public/default_images/default_prod_image.jpg');
}