<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Adding an internal name field to the answers and populating data by copying the option titles in it
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_session', 'updated') == false) {

	$query = "ALTER TABLE `#__configbox_session` MODIFY `updated` INT(10) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_session` ENGINE InnoDB";
	$db->setQuery($query);
	$db->query();

	$query = "ANALYZE TABLE `#__configbox_session`";
	$db->setQuery($query);
	$db->query();

}
