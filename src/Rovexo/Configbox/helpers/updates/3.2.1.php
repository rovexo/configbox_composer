<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Bug with settings/segment for the account page on WP. Copying text to new type number
$query = "SELECT `text` FROM `#__configbox_strings` WHERE `type` = 101 AND `key` = 1 AND `language_tag` = '".$db->getEscaped(KText::getLanguageTag())."'";
$db->setQuery($query);
$accountUrl = $db->loadResult();
if ($accountUrl) {
	$query = "
	REPLACE INTO `#__configbox_strings` (`type`, `key`, `language_tag`, `text`)
	VALUES (105, 1, '".$db->getEscaped(KText::getLanguageTag())."', '".$accountUrl."')";
	$db->setQuery($query);
	$db->query();
}