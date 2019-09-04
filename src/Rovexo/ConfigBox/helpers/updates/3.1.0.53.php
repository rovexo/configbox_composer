<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'ga_client_id') == false) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` ADD `ga_client_id` VARCHAR(255) DEFAULT '' NULL";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_ga_ecommerce') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `use_ga_ecommerce` ENUM('0', '1') DEFAULT '1' AFTER `structureddata_in`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'ga_property_id') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `ga_property_id` VARCHAR(64) DEFAULT '' AFTER `use_ga_ecommerce`";
	$db->setQuery($query);
	$db->query();

}