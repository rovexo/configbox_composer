<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

/* Remove helper columns taxrate and taxrate_dec from products, shipping and payment - START */

if (ConfigboxUpdateHelper::tableExists('#__configbox_shipping_methods') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'taxrate') == true) {
		$query = "ALTER TABLE `#__configbox_shipping_methods` DROP `taxrate`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'taxrate_dec') == true) {
		$query = "ALTER TABLE `#__configbox_shipping_methods` DROP `taxrate_dec`";
		$db->setQuery($query);
		$db->query();
	}

	// Change configbox_shipping_methods.shipper to shipper_id
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'shipper') == true) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'shipper_id') == false) {
			$query = "ALTER TABLE `#__configbox_shipping_methods` CHANGE `shipper` `shipper_id` MEDIUMINT(8) UNSIGNED NOT NULL";
			$db->setQuery($query);
			$db->query();
		}
	}

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_payment_methods') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_payment_methods', 'taxrate') == true) {
		$query = "ALTER TABLE `#__configbox_payment_methods` DROP `taxrate`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_payment_methods', 'taxrate_dec') == true) {
		$query = "ALTER TABLE `#__configbox_payment_methods` DROP `taxrate_dec`";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'taxrate') == true) {
	$query = "ALTER TABLE `#__configbox_products` DROP `taxrate`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'taxrate_dec') == true) {
	$query = "ALTER TABLE `#__configbox_products` DROP `taxrate_dec`";
	$db->setQuery($query);
	$db->query();
}

/* Remove helper columns taxrate and taxrate_dec from products, shipping and payment - END */

function switchToFourDecimals3($tableName, $colName, $colType = "DECIMAL(20,4)") {

	// Stop if table doesn't exist
	if (ConfigboxUpdateHelper::tableExists($tableName) == false) {
		return;
	}

	// Get column information
	$db = KenedoPlatform::getDb();
	$query = "SHOW COLUMNS FROM `".$tableName."` WHERE `Field` = '".$colName."'";
	$db->setQuery($query);
	$col = $db->loadAssoc();

	// Can only mean column doesn't exist, so stop
	if (empty($col['Type'])) {
		return;
	}

	// Have the type normalized (no spaces, all uppercase)
	$isType = strtoupper(str_replace(' ', '', $col['Type']));
	$shouldType = strtoupper(str_replace(' ', '', $colType));

	// If type isn't right, change it
	if (true || strstr($isType, $shouldType) == false) {
		$isUnsigned = strstr($isType, 'UNSIGNED');
		$canBeNull = ($col['Null'] == 'NO') == false;

		$attributes = array();
		$attributes[] = ($isUnsigned) ? 'UNSIGNED':'';
		$attributes[] = ($canBeNull) ? 'NULL':'NOT NULL';

		$query = "ALTER TABLE `".$tableName."` CHANGE `".$colName."` `".$colName."` ".$colType." ".implode(' ', $attributes)." DEFAULT '0.0000'";
		$db->setQuery($query);
		$db->query();

	}

}


switchToFourDecimals3('#__cbcheckout_order_payment_methods', 'price');
switchToFourDecimals3('#__cbcheckout_order_payment_methods', 'price_min');
switchToFourDecimals3('#__cbcheckout_order_payment_methods', 'price_max');

switchToFourDecimals3('#__configbox_payment_methods', 'price');
switchToFourDecimals3('#__configbox_payment_methods', 'percentage');
switchToFourDecimals3('#__configbox_payment_methods', 'price_min');
switchToFourDecimals3('#__configbox_payment_methods', 'price_max');

switchToFourDecimals3('#__cbcheckout_order_shipping_methods', 'minweight');
switchToFourDecimals3('#__cbcheckout_order_shipping_methods', 'maxweight');
switchToFourDecimals3('#__cbcheckout_order_shipping_methods', 'price');

switchToFourDecimals3('#__configbox_calculation_matrices_data', 'value');