<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products]', 'prod_image') == true) {
	$query = "ALTER TABLE #__configbox_products MODIFY `prod_image` VARCHAR(50) NOT NULL DEFAULT '';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products]', 'baseimage') == true) {
	$query = "ALTER TABLE #__configbox_products MODIFY `baseimage` VARCHAR(50) NOT NULL DEFAULT '';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products]', 'layoutname') == true) {
	$query = "ALTER TABLE #__configbox_products MODIFY `layoutname` VARCHAR(100) NOT NULL DEFAULT '';";
	$db->setQuery($query);
	$db->query();
}