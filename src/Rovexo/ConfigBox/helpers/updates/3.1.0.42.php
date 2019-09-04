<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'sku') == true) {
	$query = "ALTER TABLE `#__configbox_options` CHANGE `sku` `sku` VARCHAR(60) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}
