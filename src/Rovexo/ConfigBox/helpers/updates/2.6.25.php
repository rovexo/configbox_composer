<?php
defined('CB_VALID_ENTRY') or die();

if (!function_exists('switchToFourDecimals')) {
	function switchToFourDecimals($tableName, $colName, $colType = "DECIMAL(20,4)") {

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
}


switchToFourDecimals('#__cbcheckout_order_payment_options', 'price');
switchToFourDecimals('#__cbcheckout_order_payment_options', 'price_min');
switchToFourDecimals('#__cbcheckout_order_payment_options', 'price_max');

switchToFourDecimals('#__cbcheckout_order_positions', 'product_base_price_net');
switchToFourDecimals('#__cbcheckout_order_positions', 'product_base_price_recurring_net');
switchToFourDecimals('#__cbcheckout_order_positions', 'price_net');
switchToFourDecimals('#__cbcheckout_order_positions', 'price_recurring_net');
switchToFourDecimals('#__cbcheckout_order_positions', 'open_amount_net');

switchToFourDecimals('#__cbcheckout_order_records', 'coupon_discount_net');

switchToFourDecimals('#__cbcheckout_paymentoptions', 'price');
switchToFourDecimals('#__cbcheckout_paymentoptions', 'percentage');
switchToFourDecimals('#__cbcheckout_paymentoptions', 'price_min');
switchToFourDecimals('#__cbcheckout_paymentoptions', 'price_max');

switchToFourDecimals('#__cbcheckout_shippingrates', 'minweight');
switchToFourDecimals('#__cbcheckout_shippingrates', 'maxweight');
switchToFourDecimals('#__cbcheckout_shippingrates', 'price');

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_groups') == true) {

	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_start_1');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_factor_1');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_start_2');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_factor_2');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_start_3');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_factor_3');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_start_4');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_factor_4');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_start_5');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_factor_5');

	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_amount_1');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_amount_2');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_amount_3');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_amount_4');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_amount_5');

	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_start_1');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_factor_1');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_start_2');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_factor_2');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_start_3');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_factor_3');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_start_4');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_factor_4');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_start_5');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_factor_5');

	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_amount_1');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_amount_2');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_amount_3');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_amount_4');
	switchToFourDecimals('#__cbcheckout_user_groups', 'discount_recurring_amount_5');

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_user_groups') == true) {

	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_start_1');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_factor_1');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_start_2');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_factor_2');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_start_3');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_factor_3');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_start_4');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_factor_4');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_start_5');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_factor_5');

	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_amount_1');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_amount_2');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_amount_3');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_amount_4');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_amount_5');

	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_start_1');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_factor_1');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_start_2');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_factor_2');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_start_3');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_factor_3');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_start_4');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_factor_4');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_start_5');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_factor_5');

	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_amount_1');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_amount_2');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_amount_3');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_amount_4');
	switchToFourDecimals('#__cbcheckout_order_user_groups', 'discount_recurring_amount_5');

}


switchToFourDecimals('#__configbox_calculation_table_data', 'value');

switchToFourDecimals('#__configbox_options', 'price');
switchToFourDecimals('#__configbox_options', 'price_recurring');
switchToFourDecimals('#__configbox_options', 'was_price');
switchToFourDecimals('#__configbox_options', 'was_price_recurring');

switchToFourDecimals('#__configbox_products', 'baseprice');
switchToFourDecimals('#__configbox_products', 'baseprice_recurring');
switchToFourDecimals('#__configbox_products', 'was_price');
switchToFourDecimals('#__configbox_products', 'was_price_recurring');