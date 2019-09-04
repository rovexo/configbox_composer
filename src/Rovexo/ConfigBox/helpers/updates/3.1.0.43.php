<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'display_while_disabled') == true) {

	$query = "ALTER TABLE `#__configbox_xref_element_option` MODIFY display_while_disabled ENUM('like_question', 'hide', 'grey_out') DEFAULT 'like_question'";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__configbox_xref_element_option` SET `display_while_disabled` = 'like_question' WHERE `rules` = '' OR `rules` = '[]'";
	$db->setQuery($query);
	$db->query();

}
