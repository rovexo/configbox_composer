<?php
defined('CB_VALID_ENTRY') or die();

/**
 * Changes all CHAR columns to fitting VARCHARs
 */

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'price') == true) {
	$query = "alter table #__configbox_shipping_methods modify price DECIMAL(20,4) default 0 not null;";
	$db->setQuery($query);
	$db->query();
}