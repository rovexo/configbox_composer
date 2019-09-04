<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_pages', 'visualization_view') == false) {

	$query = "ALTER TABLE `#__configbox_pages` DROP `visualization_view`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'visualization_view') == false) {

	$query = "ALTER TABLE `#__configbox_xref_element_option` DROP `visualization_view`";
	$db->setQuery($query);
	$db->query();

}


