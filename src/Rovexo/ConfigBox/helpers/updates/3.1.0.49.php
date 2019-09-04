<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$removals = array('checked_out', 'checked_out_time', 'ordering');

foreach ($removals as $removal) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_listings', $removal) == true) {

		$query = "ALTER TABLE `#__configbox_listings` DROP COLUMN `".$removal."`";
		$db->setQuery($query);
		$db->query();

	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_listings', 'published') == true) {

	$query = "
	ALTER TABLE `#__configbox_listings`
	  MODIFY `published` ENUM('0', '1') NOT NULL DEFAULT '1',
	  MODIFY `layoutname` VARCHAR(128) NOT NULL DEFAULT 'default',
	  MODIFY `product_sorting` ENUM('0', '1') NOT NULL DEFAULT '0'
	";
	$db->setQuery($query);
	$db->query();

}

$query = "SELECT COUNT(*) FROM `#__configbox_listings`";
$db->setQuery($query);
$count = $db->loadResult();

if ($count == 0) {

	$query = "INSERT INTO `#__configbox_listings` (`id`) VALUES (NULL)";
	$db->setQuery($query);
	$db->query();

	$listingId = $db->insertid();

	$platformLanguages = KenedoPlatform::p()->getLanguages();

	$values = array();
	foreach ($platformLanguages as $language) {
		$tag = $db->getEscaped($language->tag);
		if (in_array($language->tag, array('de-DE', 'de-CH', 'de-AT'))) {
			$values[] = "( 20, ".intval($listingId).", '".$tag."', 'Standardliste' )";
		}
		else {
			$values[] = "( 20, ".intval($listingId).", '".$tag."', 'Default List' )";
		}
	}

	// ..and translations
	$query = "INSERT INTO `#__configbox_strings` (`type`, `key`, `language_tag`, `text`) VALUES ".implode(',', $values);
	$db->setQuery($query);
	$db->query();

}
