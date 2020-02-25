<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (KenedoPlatform::getName() == 'magento') {
	$oldStoreFolder = Mage::getBaseDir('media').DS.'elovaris'.DS.'configbox'.DS.'store_data';
	$oldCustomerFolder = Mage::getBaseDir('media').DS.'elovaris'.DS.'configbox'.DS.'customer_data';
	$oldSettingsFolder = Mage::getBaseUrl('media').DS.'elovaris'.DS.'configbox'.DS.'settings';
}
else {
	$oldStoreFolder = KenedoPlatform::p()->getComponentDir('com_configbox').DS.'data';
	$oldCustomerFolder = KenedoPlatform::p()->getComponentDir('com_configbox').DS.'data';
	$oldSettingsFolder = KenedoPlatform::p()->getComponentDir('com_configbox').DS.'data'.DS.'settings';
}

$mappings = array(
	$oldStoreFolder.'/default_images' 				=> CONFIGBOX_DIR_DEFAULT_IMAGES,
	$oldStoreFolder.'/element_images' 				=> CONFIGBOX_DIR_QUESTION_DECORATIONS,
	$oldStoreFolder.'/maxmind_geoip'				=> CONFIGBOX_DIR_MAXMIND_DBS,
	$oldStoreFolder.'/opt_images'					=> CONFIGBOX_DIR_VIS_ANSWER_IMAGES,
	$oldStoreFolder.'/option_images'				=> CONFIGBOX_DIR_ANSWER_IMAGES,
	$oldStoreFolder.'/option_picker_images'			=> CONFIGBOX_DIR_ANSWER_PICKER_IMAGES,
	$oldStoreFolder.'/prod_baseimages'				=> CONFIGBOX_DIR_VIS_PRODUCT_BASE_IMAGES,
	$oldStoreFolder.'/product_gallery_images'		=> CONFIGBOX_DIR_PRODUCT_GALLERY_IMAGES,
	$oldStoreFolder.'/prod_images'					=> CONFIGBOX_DIR_PRODUCT_IMAGES,
	$oldStoreFolder.'/product_detail_pane_icons'	=> CONFIGBOX_DIR_PRODUCT_DETAIL_PANE_ICONS,
	$oldStoreFolder.'/shoplogo'						=> CONFIGBOX_DIR_SHOP_LOGOS,

	$oldCustomerFolder.'/file_uploads'				=> CONFIGBOX_DIR_CONFIGURATOR_FILEUPLOADS,
	$oldCustomerFolder.'/invoices'					=> CONFIGBOX_DIR_INVOICES,
	$oldCustomerFolder.'/position_images'			=> CONFIGBOX_DIR_POSITION_IMAGES,
	$oldCustomerFolder.'/quotations'				=> CONFIGBOX_DIR_QUOTATIONS,

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

	$lines = array();
	foreach ($mappings as $old=>$new) {
		$lines[] = str_replace(KPATH_ROOT, '', $old). ' -> '. str_replace(KPATH_ROOT, '', $new);
	}
	$text = implode("<br /><br />", $lines);

	// Store the addon information (mind it's not tamper proof, less trusting checks need to be done in encrypted code)
	$db = KenedoPlatform::getDb();
	$query = "REPLACE INTO `#__configbox_system_vars` SET `value` = '".$db->getEscaped($text)."', `key` = 'folder_movings'";
	$db->setQuery($query);
	$db->query();

}

foreach ($mappings as $old=>$new) {

	$oldShort = str_replace(KPATH_ROOT, '', $old);
	$newShort = str_replace(KPATH_ROOT, '', $new);

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
	if (is_file($new.DS.'.htaccess')) {
		unlink($new.DS.'.htaccess');
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
	if (is_dir($folder)) {
		file_put_contents($folder.DS.'.htaccess', $htaccessPrivate);
	}
}

foreach ($publicFolders as $folder) {
	if (is_dir($folder)) {
		file_put_contents($folder.DS.'.htaccess', $htaccessPublic);
	}
}

// In case the default product image is not there, copy it from the system's image folder
if (!is_file(CONFIGBOX_DIR_DEFAULT_IMAGES.'/default_prod_image.jpg')) {
	$src = KenedoPlatform::p()->getDirAssets().'/images/default_prod_image.jpg';
	copy($src, CONFIGBOX_DIR_DEFAULT_IMAGES.'/default_prod_image.jpg');
}