<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'visualization_image') == true) {

	$db = KenedoPlatform::getDb();

	$query = "
	ALTER TABLE #__configbox_xref_element_option
	MODIFY COLUMN `visualization_image` VARCHAR(255) NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

}
