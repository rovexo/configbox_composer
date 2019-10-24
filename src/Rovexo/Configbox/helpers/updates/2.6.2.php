<?php
defined('CB_VALID_ENTRY') or die();


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','use_one_page_checkout') == false) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD `use_one_page_checkout` TINYINT UNSIGNED NOT NULL DEFAULT '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','explicit_agreement_terms') == false) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD `explicit_agreement_terms` TINYINT UNSIGNED NOT NULL DEFAULT '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','explicit_agreement_rp') == false) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD `explicit_agreement_rp` TINYINT UNSIGNED NOT NULL DEFAULT '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shopdata')) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shopdata','use_custom_invoice') == false) {
		$query = "ALTER TABLE `#__cbcheckout_shopdata` ADD `use_custom_invoice` TINYINT UNSIGNED NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','send_invoice') == false) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD `send_invoice` TINYINT UNSIGNED NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records','transaction_id') == false) {
	$query = "ALTER TABLE `#__cbcheckout_order_records` ADD `transaction_id` TEXT NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}
