<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__configbox_strings')) {
	$db = KenedoPlatform::getDb();

	$query = "
	DELETE FROM `#__configbox_strings` 
    WHERE (`type` IS NULL OR `type` = 0) OR (`language_tag` IS NULL or `language_tag` = '')
    ";
	$db->setQuery($query);
	$db->query();

	$query = "
	ALTER TABLE `#__configbox_strings` 
	    MODIFY `type` INT UNSIGNED NOT NULL,
	    MODIFY `language_tag` VARCHAR(5) NOT NULL
	";
	$db->setQuery($query);
	$db->query();

}