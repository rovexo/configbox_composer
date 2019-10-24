<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'show_recurring_login_cart') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_config` ADD `show_recurring_login_cart` TINYINT( 1 ) NULL DEFAULT '1';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'show_recurring_login_cart') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_config` ADD `show_recurring_login_cart` TINYINT( 1 ) NULL DEFAULT '1';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'default_customer_group') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'default_user_group_id') == false) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'default_customer_group_id') == false) {
			$query = "ALTER TABLE  `#__cbcheckout_config` ADD `default_customer_group` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '1';";
			$db->setQuery($query);
			$db->query();
		}
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'quotewithpricing') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_config` ADD `quotewithpricing` TINYINT( 1 ) UNSIGNED NULL DEFAULT '1';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == false) {
	$query = "
	CREATE TABLE  `#__cbcheckout_userfields` (
	`id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`field_name` VARCHAR( 30 ) NOT NULL ,
	`show_checkout` TINYINT( 1 ) UNSIGNED NOT NULL ,
	`require_checkout` TINYINT( 1 ) UNSIGNED NOT NULL ,
	`show_quotation` TINYINT( 1 ) UNSIGNED NOT NULL ,
	`require_quotation` TINYINT( 1 ) UNSIGNED NOT NULL ,
	`show_assistance` TINYINT( 1 ) UNSIGNED NOT NULL ,
	`require_assistance` TINYINT( 1 ) UNSIGNED NOT NULL ,
	`validation_browser` TEXT NOT NULL ,
	`validation_server` TEXT NOT NULL ,
	`group_id` MEDIUMINT UNSIGNED NULL
	) ENGINE = MYISAM ;
	
	";
	$db->setQuery($query);
	$db->query();
	
	$query = "
	INSERT INTO `#__cbcheckout_userfields` (`id`, `field_name`, `show_checkout`, `require_checkout`, `show_quotation`, `require_quotation`, `show_assistance`, `require_assistance`, `validation_browser`, `validation_server`, `group_id`) VALUES
	(1, 'companyname', 1, 0, 1, 0, 1, 0, '', '', 1),
	(2, 'gender', 1, 1, 1, 1, 1, 1, '', '', 1),
	(3, 'firstname', 1, 1, 1, 1, 1, 1, '', '', 1),
	(4, 'lastname', 1, 1, 1, 1, 1, 1, '', '', 1),
	(5, 'address1', 1, 1, 1, 0, 1, 1, '', '', 1),
	(6, 'address2', 1, 0, 1, 0, 1, 0, '', '', 1),
	(7, 'zipcode', 1, 1, 1, 0, 1, 1, '', '', 1),
	(8, 'city', 1, 1, 1, 0, 1, 1, '', '', 1),
	(9, 'country', 1, 1, 1, 1, 1, 1, '', '', 1),
	(10, 'email', 1, 1, 1, 1, 1, 1, '', '', 1),
	(11, 'phone', 1, 0, 1, 0, 1, 0, '', '', 1),
	(12, 'language', 1, 1, 1, 1, 1, 1, '', '', 1),
	(13, 'vatin', 1, 0, 0, 0, 0, 0, '', '', 1),
	(14, 'billingcompanyname', 1, 1, 0, 0, 0, 0, '', '', 1),
	(15, 'billinggender', 1, 1, 0, 0, 0, 0, '', '', 1),
	(16, 'billingfirstname', 1, 1, 0, 0, 0, 0, '', '', 1),
	(17, 'billinglastname', 1, 1, 0, 0, 0, 0, '', '', 1),
	(18, 'billingaddress1', 1, 1, 0, 0, 0, 0, '', '', 1),
	(19, 'billingaddress2', 1, 0, 0, 0, 0, 0, '', '', 1),
	(20, 'billingzipcode', 1, 1, 0, 0, 0, 0, '', '', 1),
	(21, 'billingcity', 1, 1, 0, 0, 0, 0, '', '', 1),
	(22, 'billingcountry', 1, 1, 0, 0, 0, 0, '', '', 1),
	(23, 'billingemail', 1, 1, 0, 0, 0, 0, '', '', 1),
	(24, 'billingphone', 1, 1, 0, 0, 0, 0, '', '', 1),
	(25, 'billinglanguage', 1, 1, 0, 0, 0, 0, '', '', 1),
	(26, 'samedelivery', 1, 1, 0, 0, 0, 0, '', '', 1),
	(27, 'newsletter', 1, 0, 1, 0, 1, 0, '', '', 1);
	";
	
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'group_id') == false) {
	$query = "ALTER TABLE `#__cbcheckout_users` ADD  `group_id` mediumint(8) unsigned NULL DEFAULT '1';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'newsletter') == false) {
	$query = "ALTER TABLE `#__cbcheckout_users` ADD `newsletter` TINYINT( 1 ) UNSIGNED NULL DEFAULT '0';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'group_id') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD `group_id` MEDIUMINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'newsletter') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD `newsletter` TINYINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'group_id') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orders` ADD `group_id` MEDIUMINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'newsletter') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orders` ADD `newsletter` TINYINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shopdata')) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shopdata', 'shopwebsite') == false) {
		$query = "ALTER TABLE `#__cbcheckout_shopdata` ADD `shopwebsite` VARCHAR( 255 ) NULL";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shopdata', 'shopowner') == false) {
		$query = "ALTER TABLE `#__cbcheckout_shopdata` ADD `shopowner` VARCHAR( 255 ) NULL";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shopdata', 'shoplegalvenue') == false) {
		$query = "ALTER TABLE `#__cbcheckout_shopdata` ADD `shoplegalvenue` VARCHAR( 255 ) NULL";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'asproducttitle') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD  `asproducttitle` TINYINT( 1 ) UNSIGNED NULL DEFAULT  '0';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'default_value') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `default_value` TEXT NOT NULL DEFAULT  '';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_categories', 'lock_on_required') == false) {
	$query = "ALTER TABLE `#__configbox_categories` ADD `lock_on_required` tinyint(2) NOT NULL DEFAULT '2';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_categories', 'finish_last_page_only') == false) {
	$query = "ALTER TABLE `#__configbox_categories` ADD  `finish_last_page_only` TINYINT( 2 ) NOT NULL DEFAULT  '2';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'license_manager_satellites') == false) {
	$query = "ALTER TABLE  `#__configbox_config`  ADD `license_manager_satellites` TEXT NULL;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'lock_on_required') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `lock_on_required` tinyint(1) NOT NULL DEFAULT '0';";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'finish_last_page_only') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `finish_last_page_only` TINYINT( 1 ) NOT NULL DEFAULT  '0';";
	$db->setQuery($query);
	$db->query();
}

