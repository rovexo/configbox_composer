<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'option_picker_image') == true) {

	$db = KenedoPlatform::getDb();

	$query = "
	ALTER TABLE #__configbox_xref_element_option
	MODIFY COLUMN `option_picker_image` VARCHAR(255) NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

}
