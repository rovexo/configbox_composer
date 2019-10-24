<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'shapediver_geometry_name') == false) {

	$query = "ALTER TABLE `#__configbox_elements` ADD `shapediver_geometry_name` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();

}