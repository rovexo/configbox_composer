<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'desc_display_method') == true) {
	$query = "ALTER TABLE `#__configbox_elements` MODIFY desc_display_method enum('0', '1', '2', '3') NOT NULL DEFAULT '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'desc_display_method') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD desc_display_method enum('tooltip', 'modal') NOT NULL DEFAULT 'tooltip'";
	$db->setQuery($query);
	$db->query();
}
