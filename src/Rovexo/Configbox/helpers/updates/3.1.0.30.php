<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'unit') == true) {

	$query = "SELECT `id`, `unit` FROM `#__configbox_elements` WHERE `unit` != ''";
	$db->setQuery($query);
	$units = $db->loadResultList('id', 'unit');

	$tags = KenedoLanguageHelper::getActiveLanguageTags();

	foreach ($tags as $tag) {
		foreach ($units as $id=>$unit) {
			$query = "REPLACE INTO `#__configbox_strings` SET `key` = ".intval($id).", `type` = 54, `language_tag` = '".$tag."', `text` = '".$db->getEscaped($unit)."'";
			$db->setQuery($query);
			$db->query();
		}
	}

	$query = "ALTER TABLE `#__configbox_elements` DROP `unit`";
	$db->setQuery($query);
	$db->query();

}