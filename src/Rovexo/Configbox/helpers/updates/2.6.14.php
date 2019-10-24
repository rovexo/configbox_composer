<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_counties') == false && ConfigboxUpdateHelper::tableExists('#__configbox_counties') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_counties` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `county_name` varchar(200) NOT NULL DEFAULT '',
	 `state_id` int(10) unsigned NOT NULL,
	 `custom_1` text NOT NULL,
	 `custom_2` text NOT NULL,
	 `custom_3` text NOT NULL,
	 `custom_4` text NOT NULL,
	 `ordering` mediumint(9) NOT NULL,
	 `published` enum('0','1') NOT NULL DEFAULT '1',
	 PRIMARY KEY (`id`),
	 KEY `state_id` (`state_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8
	";
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_cities') == false && ConfigboxUpdateHelper::tableExists('#__configbox_cities') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_cities` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `city_name` varchar(200) NOT NULL DEFAULT '',
	 `county_id` int(10) unsigned NOT NULL,
	 `custom_1` text NOT NULL,
	 `custom_2` text NOT NULL,
	 `custom_3` text NOT NULL,
	 `custom_4` text NOT NULL,
	 `ordering` mediumint(9) NOT NULL,
	 `published` enum('0','1') NOT NULL DEFAULT '1',
	 PRIMARY KEY (`id`),
	 KEY `state_id` (`county_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_tax_class_rates') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_tax_class_rates', 'city_id') == false) {
		$query = "
	ALTER TABLE  `#__cbcheckout_tax_class_rates` 
	ADD  `city_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER  `tax_class_id` ,
	ADD  `county_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER  `city_id`,
	ADD  `tax_code` VARCHAR( 100 ) NOT NULL DEFAULT  ''
	";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE `#__cbcheckout_tax_class_rates` DROP INDEX unq_class_zone_state_country";
		$db->setQuery($query);
		$db->query();

		$query = "
	ALTER TABLE  `#__cbcheckout_tax_class_rates` ADD UNIQUE `unique_all` (
		`tax_class_id` ,
		`city_id` ,
		`county_id` ,
		`state_id` ,
		`country_id`
	)
	";
		$db->setQuery($query);
		$db->query();

	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_tax_class_rates', 'city_id') == false) {
	$query = "
	ALTER TABLE  `#__cbcheckout_order_tax_class_rates`
	ADD  `city_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER  `tax_class_id` ,
	ADD  `county_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER  `city_id`,
	ADD  `tax_code` VARCHAR( 100 ) NOT NULL DEFAULT  ''
	";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_tax_class_rates` DROP INDEX unq_class_zone_state_country";
	$db->setQuery($query);
	$db->query();

	$query = "
	ALTER TABLE  `#__cbcheckout_order_tax_class_rates` ADD UNIQUE `unique_all` (
	`order_id` ,
	`tax_class_id` ,
	`city_id` ,
	`county_id` ,
	`state_id` ,
	`country_id`
	)
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {

	$query = "SELECT `id` FROM `#__cbcheckout_userfields` WHERE `field_name` = 'county'";
	$db->setQuery($query);
	$hasCounty = $db->loadResult();
	
	if (!$hasCounty) {
		
		$query = "
		INSERT INTO `#__cbcheckout_userfields` 
			(`id`, `field_name`, `show_checkout`, `require_checkout`, `show_quotation`, `require_quotation`, `show_assistance`, `require_assistance`, `show_saveorder`, `require_saveorder`, `show_profile`, `require_profile`, `validation_browser`, `validation_server`, `group_id`) 
			VALUES
			(NULL, 'county_id', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', 1),
			(NULL, 'billingcounty_id', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', 1);
		";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'county_id') == false) {
	$query = "
	ALTER TABLE `#__cbcheckout_orderaddress` ADD  `county_id` INT UNSIGNED NOT NULL ,
	ADD  `billingcounty_id` INT UNSIGNED NOT NULL ,
	ADD  `city_id` INT UNSIGNED NOT NULL ,
	ADD  `billingcity_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'county_id') == false) {
	$query = "
	ALTER TABLE `#__cbcheckout_users` ADD  `county_id` INT UNSIGNED NOT NULL ,
	ADD  `billingcounty_id` INT UNSIGNED NOT NULL ,
	ADD  `city_id` INT UNSIGNED NOT NULL ,
	ADD  `billingcity_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}