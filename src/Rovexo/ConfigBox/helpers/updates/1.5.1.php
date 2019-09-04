<?php
defined('CB_VALID_ENTRY') or die();

// Only do these if the 2.5.0 updates were not applied already (they remove those columns)
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'pm_regular_show_categories') == false) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'show_totals_only') == false) {
		$query = "
		ALTER TABLE `#__configbox_products`
		ADD `show_totals_only` SMALLINT NOT NULL DEFAULT '2',
		ADD `show_totals_only_recurring` SMALLINT NOT NULL DEFAULT '2',
		ADD `show_recurring` SMALLINT NOT NULL DEFAULT '4',
		ADD `show_elements` SMALLINT NOT NULL DEFAULT '2',
		ADD `show_options` SMALLINT NOT NULL DEFAULT '2',
		ADD `show_calculation_elements` SMALLINT NOT NULL DEFAULT '2',
		ADD `show_vattext` SMALLINT NOT NULL DEFAULT '2';
		";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'class') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `class` VARCHAR( 20 ) NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'classparams') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `classparams` TEXT NOT NULL;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'autoselect_default') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'behavior_on_activation') == false) {
		$query = "ALTER TABLE `#__configbox_elements` ADD `autoselect_default` BOOLEAN NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'product_key') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `product_key` VARCHAR( 200 ) NOT NULL;";
	$db->setQuery($query);
	$db->query();
}