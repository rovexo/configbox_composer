<?php
defined('CB_VALID_ENTRY') or die();


if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_editor') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_calculation_editor` (
		`id` int(10) unsigned NOT NULL,
		`calc` text NOT NULL,
		PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	$db->setQuery($query);
	$db->query();
}


