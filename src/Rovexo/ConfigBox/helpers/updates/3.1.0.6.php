<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_element_option') == true) {

	$query = "ALTER TABLE `#__configbox_xref_element_option` DROP INDEX `element_id/option_id`";
	$db->setQuery($query);
	$db->query();

}
