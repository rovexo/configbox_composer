<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

clearstatcache();

// Remove a bunch of obsolete files/folders
$removals = array(
	'assets/themes',
	'assets/javascript/calc-editor.min.map',
	'assets/javascript/configbox.min.map',
	'assets/javascript/magento_custom.min.map',
	'assets/javascript/quickedit.min.map',
	'assets/javascript/rule-editor.min.map',

	'assets/external/codemirror',
	'assets/external/jqueryui',
	'assets/external/tinymce',

	'classes/element.php',
	'classes/option.php',
	'classes/orderitems.php',
	'classes/calc-items',
	'classes/rule-fields',

	'controllers/json.php',

	'external/codemirror',
	'external/maxmind_geoip',
	'external/recaptcha',
	'external/tinymce',
	'external/kenedo/assets',
	'external/kenedo/external/htmlpurifier.zip',
	'external/kenedo/fields',

	'helpers/common.php',
	'helpers/autoloads.php',
	'helpers/admin_tabs.php',

	'observers/Cbcheckout',

	'models/adminxrefelementoption.php',
	'models/ajaxapi.php',
	'models/bundle.php',
	'models/element.php',
	'models/elementclasses.php',
	'models/grandorder.php',
	'models/grandorders.php',
	'models/maintainance.php',
	'models/pcategories.php',
	'models/xrefelementoption.php',

	'views/block_cart',
	'views/block_currencies',
	'views/block_navigation',
	'views/block_pricing',
	'views/block_visualization',

	'data/customization/templates/configuration_page',
	'data/customization/templates/product_listing',

);

// Go on doing it
foreach ($removals as $removal) {
	$path = KenedoPlatform::p()->getComponentDir('com_configbox').'/'.$removal;
	if (is_file($path)) {
		unlink($path);
	}
	elseif (is_dir($path)) {
		KenedoFileHelper::deleteFolder($path);
	}
}


// Delete old language files (leaving only frontend.ini and backend.ini)
$languageBaseFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/language';

$languageFolders = KenedoFileHelper::getFolders($languageBaseFolder, NULL, false, true);

foreach ($languageFolders as $languageFolder) {
	$fileNames = KenedoFileHelper::getFiles($languageFolder);
	foreach ($fileNames as $fileName) {
		if (!in_array($fileName, array('frontend.ini', 'backend.ini'))) {
			unlink($languageFolder.'/'.$fileName);
		}
	}
}