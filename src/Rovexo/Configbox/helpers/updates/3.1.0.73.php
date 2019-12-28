<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_cart_positions', 'finished') == true) {

	$db = KenedoPlatform::getDb();

	$query = "UPDATE #__configbox_cart_positions SET `finished` = 0 WHERE `finished` IS NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE #__configbox_cart_positions MODIFY `finished` INT UNSIGNED DEFAULT 0 NOT NULL;";
	$db->setQuery($query);
	$db->query();

}