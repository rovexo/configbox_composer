<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'option_picker_image') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `option_picker_image` VARCHAR( 100 ) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'option_picker_image') == true) {

	$query = "ALTER TABLE `#__configbox_xref_element_option` CHANGE `option_picker_image` `option_picker_image` VARCHAR( 100 ) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_xref_element_option` SET `option_picker_image` = '' WHERE `option_picker_image` = 0";
	$db->setQuery($query);
	$db->query();

}
