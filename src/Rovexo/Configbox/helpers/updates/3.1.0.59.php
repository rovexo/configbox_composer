<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Adding an internal name field to the answers and populating data by copying the option titles in it
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'internal_name') == false) {

	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `internal_name` VARCHAR(63) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT `language_tag` FROM `#__configbox_config` WHERE `id` = 1";
	$db->setQuery($query);
	$languageTag = $db->loadResult();

	$query = "
	SELECT answer.id, s.text
	FROM `#__configbox_xref_element_option` AS `answer`
	LEFT JOIN `#__configbox_strings` AS s ON s.key = answer.option_id AND `type` = 5 AND `language_tag` = '".$db->getEscaped($languageTag)."'";
	$db->setQuery($query);
	$titles = $db->loadResultList('id', 'text');

	foreach ($titles as $answerId => $title) {
		$query = "UPDATE `#__configbox_xref_element_option` SET `internal_name` = '".$db->getEscaped($title)."' WHERE `id` = ".intval($answerId);
		$db->setQuery($query);
		$db->query();
	}

}
