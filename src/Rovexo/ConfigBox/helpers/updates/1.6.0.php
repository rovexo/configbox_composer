<?php
defined('CB_VALID_ENTRY') or die();

// Only do these if the 2.5.0 updates were not applied already (they remove those columns)
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'pm_regular_show_categories') == false) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'show_element_prices') == false) {
		$query = "ALTER TABLE `#__configbox_products` ADD `show_element_prices` SMALLINT NOT NULL DEFAULT '3';";
		$db->setQuery($query);
		$db->query();
	}
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'expand_categories') == false) {
		$query = "ALTER TABLE `#__configbox_products` ADD `expand_categories` SMALLINT NOT NULL DEFAULT '3';";
		$db->setQuery($query);
		$db->query();
	}
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'calcmodel') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD  `calcmodel` mediumint(8) unsigned NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'calcmodel_recurring') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD  `calcmodel_recurring` mediumint(8) unsigned NOT NULL";
	$db->setQuery($query);
	$db->query();
}
