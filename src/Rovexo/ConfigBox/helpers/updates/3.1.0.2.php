<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();


if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'baseprice_overrides') == false) {

	$query = "
	ALTER TABLE `#__configbox_products` 
	ADD `baseprice_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `baseprice_recurring`
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'baseprice_recurring_overrides') == false) {

	$query = "
	ALTER TABLE `#__configbox_products` 
	ADD `baseprice_recurring_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `baseprice_overrides`;
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'price_overrides') == false) {

	$query = "
	ALTER TABLE `#__configbox_options` 
	ADD `price_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `price`
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'price_recurring_overrides') == false) {

	$query = "
	ALTER TABLE `#__configbox_options` 
	ADD `price_recurring_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `price_recurring`;
	";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'product_base_price_overrides') == false) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_positions` 
	ADD `product_base_price_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `product_base_price_net`;
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'product_base_price_recurring_overrides') == false) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_positions` 
	ADD `product_base_price_recurring_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `product_base_price_recurring_net`;
	";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'price_overrides') == false) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_configurations` 
	ADD `price_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `price_net`;
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'price_recurring_overrides') == false) {

	$query = "
	ALTER TABLE `#__cbcheckout_order_configurations` 
	ADD `price_recurring_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `price_recurring_net`;
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'price_calculation_overrides') == false) {

	$query = "
	ALTER TABLE `#__configbox_xref_element_option` 
	ADD `price_calculation_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `calcmodel`
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'price_recurring_calculation_overrides') == false) {

	$query = "
	ALTER TABLE `#__configbox_xref_element_option` 
	ADD `price_recurring_calculation_overrides` VARCHAR(1024) NOT NULL DEFAULT '[]' AFTER `calcmodel_recurring`;
	";
	$db->setQuery($query);
	$db->query();

}