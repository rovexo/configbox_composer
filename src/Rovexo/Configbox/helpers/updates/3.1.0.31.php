<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'behavior_on_inconsistency') == false) {

	$query = "ALTER TABLE `#__configbox_elements` ADD `behavior_on_inconsistency` ENUM('deselect', 'replace_with_default', 'replace_with_any') NULL DEFAULT 'deselect'";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_elements` SET `behavior_on_inconsistency` = 'replace_with_default' WHERE `behavior_on_activation` = 'select_default'";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_elements` SET `behavior_on_inconsistency` = 'replace_with_any' WHERE `behavior_on_activation` = 'select_any'";
	$db->setQuery($query);
	$db->query();

}