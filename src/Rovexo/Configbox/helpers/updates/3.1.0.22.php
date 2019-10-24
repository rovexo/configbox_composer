<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'usertime') == true) {

	$query = "UPDATE `#__configbox_config` SET `usertime` = `usertime` / 3600";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'unorderedtime') == true) {

	$query = "UPDATE `#__configbox_config` SET `unorderedtime` = `unorderedtime` / 3600";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'orderedtime') == true) {

	$query = "UPDATE `#__configbox_config` SET `orderedtime` = `orderedtime` / 3600";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'intervals') == true) {

	$query = "UPDATE `#__configbox_config` SET `intervals` = `intervals` / 3600";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'labelexpiry') == true) {

	$query = "UPDATE `#__configbox_config` SET `labelexpiry` = `labelexpiry` / 86400";
	$db->setQuery($query);
	$db->query();

}