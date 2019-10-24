<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();


if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'page_nav_show_tabs') == false) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'show_nav_as_tabs') == true) {
		$query = "ALTER TABLE `#__configbox_products` CHANGE `show_nav_as_tabs` `page_nav_show_tabs` ENUM('0','1','2') NOT NULL DEFAULT '2';";
		$db->setQuery($query);
		$db->query();
	}
	else {
		$query = "ALTER TABLE `#__configbox_products` ADD `page_nav_show_tabs` ENUM('0','1','2') NOT NULL DEFAULT '2';";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'page_nav_show_buttons') == false) {

	$query = "ALTER TABLE `#__configbox_products` ADD `page_nav_show_buttons` ENUM('0','1','2') NOT NULL DEFAULT '2';";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'page_nav_show_tabs') == false) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'show_nav_as_tabs') == true) {
		$query = "ALTER TABLE `#__configbox_config` CHANGE `show_nav_as_tabs` `page_nav_show_tabs` ENUM('0','1') NOT NULL DEFAULT '0';";
		$db->setQuery($query);
		$db->query();
	}
	else {
		$query = "ALTER TABLE `#__configbox_config` ADD `page_nav_show_tabs` ENUM('0','1') NOT NULL DEFAULT '1';";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'page_nav_show_buttons') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `page_nav_show_buttons` ENUM('0','1') NOT NULL DEFAULT '1';";
	$db->setQuery($query);
	$db->query();

}
