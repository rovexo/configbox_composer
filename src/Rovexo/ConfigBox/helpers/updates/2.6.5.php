<?php
defined('CB_VALID_ENTRY') or die();


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'transaction_data') == false) {
	$query = "ALTER TABLE `#__cbcheckout_order_records` ADD `transaction_data` TEXT NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}