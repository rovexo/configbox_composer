<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'shapediver_geometry_name')) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_elements` DROP `shapediver_geometry_name`";
	$db->setQuery($query);
	$db->query();
}
