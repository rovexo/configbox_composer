<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shopdata', 'shoplinktotc')) {
	$query = "ALTER TABLE `#__configbox_shopdata` DROP `shoplinktotc`";
	$db->setQuery($query);
	$db->query();
}
