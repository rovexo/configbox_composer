<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_groups', 'enable_request_assistance') == true) {

	$query = "ALTER TABLE `#__configbox_groups` DROP `enable_request_assistance`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_groups', 'enable_recommendation') == true) {

	$query = "ALTER TABLE `#__configbox_groups` DROP `enable_recommendation`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'enable_request_assistance') == true) {

	$query = "ALTER TABLE `#__cbcheckout_order_user_groups` DROP `enable_request_assistance`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'enable_recommendation') == true) {

	$query = "ALTER TABLE `#__cbcheckout_order_user_groups` DROP `enable_recommendation`";
	$db->setQuery($query);
	$db->query();

}
