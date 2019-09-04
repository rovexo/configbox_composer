<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_ga_enhanced_ecommerce') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `use_ga_enhanced_ecommerce` ENUM('0', '1') DEFAULT '0' NULL";
	$db->setQuery($query);
	$db->query();

}