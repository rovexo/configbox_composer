<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__configbox_bundle_items') == true) {

	$query = "DROP TABLE `#__configbox_bundle_items`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_bundles') == true) {

	$query = "DROP TABLE `#__configbox_bundles`";
	$db->setQuery($query);
	$db->query();

}