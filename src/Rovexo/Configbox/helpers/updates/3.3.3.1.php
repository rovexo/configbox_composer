<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calendar_validation_type_min') == false) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_elements` ADD `calendar_validation_type_min` VARCHAR(16) DEFAULT 'none';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calendar_validation_type_max') == false) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_elements` ADD `calendar_validation_type_max` VARCHAR(16) DEFAULT 'none';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calendar_days_min') == false) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_elements` ADD `calendar_days_min` INT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calendar_days_max') == false) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_elements` ADD `calendar_days_max` INT";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calendar_first_day') == false) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_elements` ADD `calendar_first_day` VARCHAR(10) DEFAULT 'locale'";
	$db->setQuery($query);
	$db->query();
}
