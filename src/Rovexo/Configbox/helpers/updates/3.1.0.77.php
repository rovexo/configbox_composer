<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'ordering') == true) {

	$names = ConfigboxUpdateHelper::getKeyNames('#__configbox_xref_element_option', 'ordering');

	if (count($names) == 0) {
		$query = "CREATE INDEX ordering ON #__configbox_xref_element_option (ordering);";
		$db->setQuery($query);
		$db->query();
	}

}