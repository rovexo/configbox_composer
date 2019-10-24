<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'display_while_disabled') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'hidenonapplying') == true) {

		$query = "ALTER TABLE `#__configbox_elements` ADD `display_while_disabled` ENUM('hide', 'grey_out') NULL DEFAULT 'hide'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_elements` SET `display_while_disabled` = 'hide' WHERE `hidenonapplying` = '1'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_elements` SET `display_while_disabled` = 'grey_out' WHERE `hidenonapplying` = '0'";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_elements` DROP `hidenonapplying`";
		$db->setQuery($query);
		$db->query();

	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'display_while_disabled') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'hidenonapplying') == true) {

		$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `display_while_disabled` ENUM('hide', 'grey_out') NULL DEFAULT 'hide'";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_xref_element_option` SET `display_while_disabled` = 'hide' WHERE `hidenonapplying` = 1";
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_xref_element_option` SET `display_while_disabled` = 'grey_out' WHERE `hidenonapplying` = 0";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__configbox_xref_element_option` DROP `hidenonapplying`";
		$db->setQuery($query);
		$db->query();

	}
}