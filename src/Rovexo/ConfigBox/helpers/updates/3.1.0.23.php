<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'delivery_id') == true) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` ALTER COLUMN delivery_id SET DEFAULT NULL;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'payment_id') == true) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` ALTER COLUMN payment_id SET DEFAULT NULL;";
	$db->setQuery($query);
	$db->query();

}