<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_groups') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_amount_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_amount_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_amount_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_amount_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_amount_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_amount_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_amount_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_amount_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_amount_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_amount_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_type_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_type_1` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_type_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_type_2` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_type_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_type_3` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_type_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_type_4` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'discount_type_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups` ADD  `discount_type_5` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_user_groups') == true) {


	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_amount_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_amount_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_amount_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_amount_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_amount_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_amount_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_amount_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_amount_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_amount_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_amount_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_type_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_type_1` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_type_2') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_type_2` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_type_3') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_type_3` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_type_4') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_type_4` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'discount_type_5') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_user_groups` ADD  `discount_type_5` VARCHAR(32) NOT NULL DEFAULT  'percentage'";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_config') == true) {


	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enter_net') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `enter_net`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'show_net_prices') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `show_net_prices`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allow_checkout') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `allow_checkout`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allow_quotation') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `allow_quotation`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allow_assistance') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `allow_assistance`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'vat_exclusive') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `vat_exclusive`";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'downloaddir') == true) {
		$query = "ALTER TABLE  `#__configbox_config` DROP `downloaddir`";
		$db->setQuery($query);
		$db->query();
	}

}
