<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Adding an internal name field to the answers and populating data by copying the option titles in it
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_minified_js') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `use_minified_js` ENUM('0', '1') DEFAULT '1' NOT NULL";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_minified_css') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `use_minified_css` ENUM('0', '1') DEFAULT '1' NOT NULL";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_assets_cache_buster') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `use_assets_cache_buster` ENUM('0', '1') DEFAULT '1' NOT NULL";
	$db->setQuery($query);
	$db->query();

}
