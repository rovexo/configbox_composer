<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'layoutname') == true) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `layoutname`";
	$db->setQuery($query);
	$db->query();
}