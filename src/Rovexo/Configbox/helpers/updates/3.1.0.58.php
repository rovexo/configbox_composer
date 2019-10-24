<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_internal_element_names') == true) {
	$query = "ALTER TABLE `#__configbox_config` CHANGE `use_internal_element_names` `use_internal_question_names` ENUM('0', '1') NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_internal_answer_names') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `use_internal_answer_names` ENUM('0', '1') NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}
