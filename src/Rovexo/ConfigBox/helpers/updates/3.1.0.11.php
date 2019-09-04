<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'title_display') == false) {

	$query = "ALTER TABLE `#__configbox_elements` ADD `title_display` ENUM('heading', 'label', 'none') NOT NULL DEFAULT 'heading';";
	$db->setQuery($query);
	$db->query();

}
