<?php
defined('CB_VALID_ENTRY') or die();

$indices = ConfigboxUpdateHelper::getKeyNames('#__configbox_xref_element_option', 'shapediver_choice_value');

foreach ($indices as $index) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` DROP INDEX `".$index."`";
	$db->setQuery($query);
	$db->query();
}
