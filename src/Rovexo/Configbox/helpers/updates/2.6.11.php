<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_groups') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_start_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_start_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_start_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_start_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_start_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_start_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_start_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_start_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_start_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_start_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}



	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_factor_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_factor_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_factor_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_factor_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_factor_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_factor_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_factor_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_factor_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_factor_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_factor_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}



	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_1` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_2` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_3` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_4` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_5` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_amount_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_amount_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_1` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_2` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_3` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_4` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_recurring_type_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_recurring_type_5` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_user_groups') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_start_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_start_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_start_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_start_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_start_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_start_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_start_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_start_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_start_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_start_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_factor_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_factor_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_factor_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_factor_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_factor_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_factor_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_factor_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_factor_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_factor_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_factor_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_1` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_2` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_3` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_4` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_5` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_amount_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_amount_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_1` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_2` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_3` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_4` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_recurring_type_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_recurring_type_5` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

}
