<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_user_field_definitions', 'show_assistance') == true) {

	$query = "ALTER TABLE `#__configbox_user_field_definitions` DROP `show_assistance`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_user_field_definitions', 'require_assistance') == true) {

	$query = "ALTER TABLE `#__configbox_user_field_definitions` DROP `require_assistance`";
	$db->setQuery($query);
	$db->query();

}