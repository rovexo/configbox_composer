<?php
defined('CB_VALID_ENTRY') or die();

// Only do these if the 2.5.0 updates were not applied already (they remove those columns)
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'custom_1') == false) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'inctaxorder_recurring') == false) {
		$query = "ALTER TABLE `#__cbcheckout_orders` ADD  `inctaxorder_recurring` FLOAT NOT NULL";
		$db->setQuery($query);
		$db->query();
	}
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'price_recurring') == false) {
		$query = "ALTER TABLE `#__cbcheckout_orders` ADD  `price_recurring` FLOAT NOT NULL";
		$db->setQuery($query);
		$db->query();
	}
	
}


if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'vat_exclusive') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `vat_exclusive` BOOLEAN DEFAULT '0' NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'show_net_prices') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `show_net_prices` BOOLEAN DEFAULT '0' NOT NULL";
	$db->setQuery($query);
	$db->query();
}
