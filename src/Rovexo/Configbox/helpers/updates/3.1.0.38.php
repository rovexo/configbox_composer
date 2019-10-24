<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'structureddata') === false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `structureddata` ENUM('0','1') NOT NULL DEFAULT '1';";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'structureddata_in') === false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `structureddata_in` ENUM('configurator','product') NOT NULL DEFAULT 'configurator';";
	$db->setQuery($query);
	$db->query();

}