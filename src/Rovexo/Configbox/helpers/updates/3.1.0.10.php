<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'maxmind_user_id') == false) {

	$query = "
	ALTER TABLE `#__configbox_config` ADD `maxmind_user_id` VARCHAR(32) NOT NULL AFTER `maxmind_license_key`;
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'geolocation_type') == false) {

	$query = "
	ALTER TABLE `#__configbox_config` ADD `geolocation_type` VARCHAR(32) NOT NULL DEFAULT 'maxmind_geoip2_db' AFTER `enable_geolocation`;
	";
	$db->setQuery($query);
	$db->query();

}