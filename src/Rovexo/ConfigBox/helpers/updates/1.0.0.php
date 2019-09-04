<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__configbox_strings') == false) {
	$query = "
	CREATE TABLE `#__configbox_strings` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`type` int(5) unsigned NOT NULL,
	`key` bigint(20) unsigned NOT NULL,
	`lang_id` int(10) unsigned NOT NULL,
	`text` text NOT NULL,
	PRIMARY KEY (`type`,`key`,`lang_id`),
	UNIQUE KEY `id` (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_strings') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_strings` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`type` int(5) unsigned NOT NULL,
	`key` bigint(20) unsigned NOT NULL,
	`lang_id` int(10) unsigned NOT NULL,
	`text` text NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `type/key/lang_id` (`type`,`key`,`lang_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculationmodels') == false) {
	$query = "
	CREATE TABLE `#__configbox_calculationmodels` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(200) NOT NULL,
	`type` varchar(10) NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


// Table renamed in 3.0.0 to configbox_calculation_codes (col formula renamed to code)
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_formulas') == false) {
	$query = "
	CREATE TABLE `#__configbox_calculation_formulas` (
		`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		`element_id_a` mediumint(8) unsigned NOT NULL,
		`element_id_b` mediumint(8) unsigned NOT NULL,
		`element_id_c` mediumint(8) unsigned NOT NULL,
		`element_id_d` mediumint(8) unsigned NOT NULL,
		`formula` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


// Was renamed in 3.0.0 to configbox_calculation_matrices
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_tables') == false) {
	$query = "
	CREATE TABLE `#__configbox_calculation_tables` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `inputx` mediumint(8) unsigned NOT NULL,
  `inputy` mediumint(8) unsigned NOT NULL,
  `round` smallint(5) unsigned NOT NULL,
  `nexthigher` tinyint(1) NOT NULL,
  `multiplicator` float NOT NULL,
  `multielementid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_categories') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_categories` (
		`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		`imagescope` varchar(50) NOT NULL,
		`section_id` mediumint(8) unsigned NOT NULL,
		`layoutname` varchar(100) NOT NULL,
		`published` tinyint(1) NOT NULL,
		`ordering` mediumint(9) NOT NULL,
		`checked_out` mediumint(9) NOT NULL,
		`checked_out_time` datetime NOT NULL,
		`lock_on_required` tinyint(2) NOT NULL DEFAULT '2',
		`finish_last_page_only` tinyint(2) NOT NULL DEFAULT '2',
		PRIMARY KEY (`id`),
		KEY `section_id` (`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_config') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_config` (
  `id` smallint(6) NOT NULL,
  `lastcleanup` bigint(20) unsigned NOT NULL,
  `usertime` bigint(20) unsigned NOT NULL,
  `unorderedtime` bigint(20) unsigned NOT NULL,
  `orderedtime` bigint(20) unsigned NOT NULL,
  `intervals` bigint(20) unsigned NOT NULL,
  `labelexpiry` bigint(20) unsigned NOT NULL,
  `editmetadata` tinyint(1) NOT NULL,
  `securecheckout` tinyint(1) NOT NULL,
  `weightunits` varchar(20) NOT NULL,
  `defaultprodimage` varchar(100) NOT NULL,
  `allow_checkout` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allow_quotation` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `allow_assistance` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `vat_exclusive` tinyint(1) NOT NULL DEFAULT '0',
  `show_net_prices` tinyint(1) NOT NULL,
  `downloaddir` varchar(255) NOT NULL,
  `product_key` varchar(200) NOT NULL,
  `license_manager_satellites` text,
  `finish_last_page_only` tinyint(1) NOT NULL DEFAULT '0',
  `lock_on_required` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
	
	$query = "
	INSERT INTO `#__configbox_config` (`id`, `lastcleanup`, `usertime`, `unorderedtime`, `orderedtime`, `intervals`, `labelexpiry`, `editmetadata`, `securecheckout`, `weightunits`, `defaultprodimage`, `allow_checkout`, `allow_quotation`, `allow_assistance`, `vat_exclusive`, `show_net_prices`, `downloaddir`, `product_key`, `license_manager_satellites`, `finish_last_page_only`, `lock_on_required`) VALUES
	(1, 1311701670, 90000, 86400, 86400, 86400, 3024000, 1, 0, 'kg', 'default_prod_image.jpg', 1, 1, 1, 1, 1, '../downloads', '', 'licenses.configbox.at', 0, 0);
	";
	
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_elements') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_elements` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` mediumint(8) unsigned NOT NULL,
  `el_image` varchar(100) NOT NULL,
  `layoutname` varchar(50) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `dependencies` varchar(100) NOT NULL,
  `hidenonapplying` tinyint(1) NOT NULL,
  `confirm_deselect` tinyint(1) NOT NULL DEFAULT '1',
  `autoselect_default` tinyint(1) NOT NULL DEFAULT '0',
  `validate` tinyint(1) NOT NULL,
  `minval` float DEFAULT NULL,
  `maxval` float DEFAULT NULL,
  `integer_only` tinyint(4) NOT NULL,
  `article` mediumint(8) unsigned NOT NULL,
  `calcmodel` mediumint(8) unsigned NOT NULL,
  `calcmodel_recurring` mediumint(8) unsigned NOT NULL,
  `multiplicator` float NOT NULL,
  `discontinued` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `checked_out` mediumint(9) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `class` varchar(20) NOT NULL,
  `classparams` text NOT NULL,
  `asproducttitle` tinyint(1) unsigned DEFAULT '0',
  `default_value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ordering` (`ordering`),
  KEY `cat_id` (`cat_id`,`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}




if (ConfigboxUpdateHelper::tableExists('#__configbox_grandorders') == false) {
	$query = "
	CREATE TABLE `#__configbox_grandorders` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}




if (ConfigboxUpdateHelper::tableExists('#__configbox_languages') == false) {

	$query = "
	CREATE TABLE `#__configbox_languages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `tag` varchar(5) NOT NULL,
  `default` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
	
	$platformLanguages = KenedoPlatform::p()->getLanguages();
	foreach ($platformLanguages as $platformLanguage) {
		$query = "INSERT INTO `#__configbox_languages` SET `label` = '".$db->getEscaped($platformLanguage->label)."', `tag` = '".$db->getEscaped($platformLanguage->tag)."', `default` = '0'";
		$db->setQuery($query);
		$db->query();
	}

	// Set the current default language
	$query = "UPDATE `#__configbox_languages` SET `default` = '1' WHERE `tag` = '".$db->getEscaped( KenedoPlatform::p()->getLanguageTag() )."'";
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_currencies') == false) {
	$query = "
	CREATE TABLE `#__configbox_currencies` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`base` tinyint(1) NOT NULL DEFAULT '0',
	`multiplicator` float unsigned NOT NULL,
	`symbol` varchar(10) NOT NULL,
	`code` varchar(10) NOT NULL,
	`default` tinyint(1) NOT NULL DEFAULT '0',
	`ordering` mediumint(9) NOT NULL,
	`published` tinyint(1) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `code` (`code`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();


	$query = "INSERT INTO `#__configbox_currencies` (`id`, `base`, `multiplicator`, `symbol`, `code`, `default`, `ordering`, `published`) VALUES (1, 1, '1.00000', '€', 'EUR', '1', 1, '1')";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_languages`";
	$db->setQuery($query);
	$languages = $db->loadObjectList('id');

	foreach ($languages as $id=>$language) {
		$query = "INSERT INTO `#__configbox_strings` VALUES(NULL, 6, 1, ".intval($id).", 'Euro')";
		$db->setQuery($query);
		$db->query();
	}

}


if (ConfigboxUpdateHelper::tableExists('#__configbox_oldlabels') == false) {
	$query = "
	CREATE TABLE `#__configbox_oldlabels` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) unsigned NOT NULL,
  `key` mediumint(8) unsigned NOT NULL,
  `lang_id` smallint(5) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `prod_id` mediumint(8) unsigned NOT NULL,
  `last_visit` bigint(20) unsigned NOT NULL,
  `created` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type-key-lang_id` (`type`,`key`,`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_options') == false) {
	$query = "
	CREATE TABLE `#__configbox_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(60) NOT NULL,
  `price` float NOT NULL,
  `price_recurring` float NOT NULL,
  `weight` float NOT NULL,
  `checked_out` mediumint(9) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_orderitems') == false) {
	$query = "
	CREATE TABLE `#__configbox_orderitems` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` mediumint(8) unsigned NOT NULL,
  `prod_id` mediumint(9) NOT NULL DEFAULT '1',
  `element_id` mediumint(8) unsigned NOT NULL,
  `element_option_xref_id` mediumint(8) unsigned NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id/element_id` (`order_id`,`element_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_orders') == false) {
	$query = "
	CREATE TABLE `#__configbox_orders` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `grandorder_id` mediumint(8) unsigned NOT NULL,
  `prod_id` mediumint(8) unsigned NOT NULL,
  `quantity` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `finished` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_pcategories') == false && ConfigboxUpdateHelper::tableExists('#__configbox_listings') == false) {
	$query = "
	CREATE TABLE `#__configbox_pcategories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `layoutname` varchar(100) NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `checked_out` mediumint(8) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_products') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(60) NOT NULL,
  `desc` mediumtext NOT NULL,
  `class` varchar(50) NOT NULL,
  `prod_image` varchar(50) NOT NULL,
  `baseimage` varchar(100) NOT NULL,
  `prod_download` varchar(255) NOT NULL,
  `opt_image_x` mediumint(8) unsigned DEFAULT NULL,
  `opt_image_y` mediumint(8) unsigned NOT NULL,
  `baseprice` float unsigned NOT NULL,
  `baseprice_recurring` float NOT NULL,
  `baseweight` float unsigned NOT NULL,
  `discontinued` tinyint(1) NOT NULL,
  `taxrate` float unsigned NOT NULL,
  `layoutname` varchar(100) NOT NULL,
  `longdesctemplate` tinyint(1) NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `pcat_id` mediumint(8) unsigned NOT NULL,
  `checked_out` int(8) unsigned NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `show_totals_only` smallint(6) NOT NULL DEFAULT '2',
  `show_totals_only_recurring` smallint(6) NOT NULL DEFAULT '2',
  `show_recurring` smallint(6) NOT NULL DEFAULT '4',
  `show_elements` smallint(6) NOT NULL DEFAULT '2',
  `show_options` smallint(6) NOT NULL DEFAULT '2',
  `show_calculation_elements` smallint(6) NOT NULL DEFAULT '2',
  `show_vattext` smallint(6) NOT NULL DEFAULT '2',
  `show_element_prices` smallint(6) NOT NULL DEFAULT '2',
  `expand_categories` smallint(6) NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_sections') == false) {
	$query = "
	CREATE TABLE `#__configbox_sections` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `prod_id` mediumint(8) unsigned NOT NULL,
  `published` tinyint(1) NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `checked_out` mediumint(9) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prod_id` (`prod_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}




if (ConfigboxUpdateHelper::tableExists('#__configbox_tables') == false) {
	$query = "
	CREATE TABLE `#__configbox_tables` (
  `id` mediumint(8) unsigned NOT NULL,
  `x` bigint(20) NOT NULL,
  `y` bigint(20) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`,`x`,`y`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_users') == false) {
	$query = "
	CREATE TABLE `#__configbox_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_element_option') == false) {
	$query = "
	CREATE TABLE `#__configbox_xref_element_option` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` mediumint(8) unsigned NOT NULL,
  `option_id` mediumint(8) unsigned NOT NULL,
  `dependencies` varchar(255) NOT NULL,
  `default` int(1) unsigned NOT NULL,
  `opt_image` varchar(100) NOT NULL,
  `layer` mediumint(9) NOT NULL,
  `imagescope` varchar(50) NOT NULL,
  `hidenonapplying` int(1) unsigned NOT NULL,
  `confirm_deselect` tinyint(1) NOT NULL DEFAULT '1',
  `calcmodel` mediumint(8) unsigned NOT NULL,
  `calcmodel_recurring` mediumint(8) unsigned NOT NULL,
  `discontinued` int(1) unsigned NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `published` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id/option_id` (`element_id`,`option_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_pcategory_product') == false) {
	$query = "
	CREATE TABLE `#__configbox_xref_pcategory_product` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pcategory_id` mediumint(8) unsigned NOT NULL,
  `product_id` mediumint(8) unsigned NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_availablein') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_availablein` (
  `payment_id` mediumint(8) unsigned NOT NULL,
  `country_id` mediumint(8) unsigned NOT NULL,
  KEY `shippers/countries` (`payment_id`,`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_config') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_config` (
  `id` smallint(5) unsigned NOT NULL,
  `securecheckout` tinyint(1) NOT NULL,
  `offerpdfinvoice` tinyint(1) NOT NULL,
  `autoquote` tinyint(1) NOT NULL,
  `showrefundpolicy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `showrefundpolicyinline` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `weightunits` varchar(50) NOT NULL,
  `tpladdress` varchar(100) NOT NULL,
  `tpldelivery` varchar(100) NOT NULL,
  `tplpayment` varchar(100) NOT NULL,
  `tplconfirmation` varchar(100) NOT NULL,
  `tplpaymentresult` varchar(100) NOT NULL,
  `vatfree_countries` tinyint(1) NOT NULL DEFAULT '0',
  `vatfree_with_vatin_countries` tinyint(1) NOT NULL DEFAULT '0',
  `disable_delivery` tinyint(4) NOT NULL DEFAULT '0',
  `contractdownload` tinyint(1) NOT NULL,
  `user_group` mediumint(8) unsigned NOT NULL,
  `show_recurring_login_cart` tinyint(1) NOT NULL DEFAULT '0',
  `default_customer_group` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `quotewithpricing` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
	
	
	$query = "
	INSERT INTO `#__cbcheckout_config` (`id`, `securecheckout`, `offerpdfinvoice`, `autoquote`, `showrefundpolicy`, `showrefundpolicyinline`, `weightunits`, `tpladdress`, `tpldelivery`, `tplpayment`, `tplconfirmation`, `tplpaymentresult`, `vatfree_countries`, `vatfree_with_vatin_countries`, `disable_delivery`, `contractdownload`, `user_group`, `show_recurring_login_cart`, `default_customer_group`, `quotewithpricing`) VALUES
(1, 0, 1, 1, 1, 0, 'kg', 'default', 'default', 'default', 'default', 'default', 1, 1, 0, 0, 18, 1, 1, 1);
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_countries') == false && ConfigboxUpdateHelper::tableExists('#__configbox_countries') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__cbcheckout_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(64) DEFAULT NULL,
  `country_3_code` char(3) DEFAULT NULL,
  `country_2_code` char(2) DEFAULT NULL,
  `vat_free` tinyint(1) NOT NULL DEFAULT '1',
  `vat_free_with_vatin` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_country_name` (`country_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();

	$query = "
	INSERT INTO `#__cbcheckout_countries` (`id`, `country_name`, `country_3_code`, `country_2_code`, `vat_free`, `vat_free_with_vatin`, `published`, `ordering`) VALUES
(1, 'Afghanistan', 'AFG', 'AF', 1, 1, 1, 0),
(2, 'Albania', 'ALB', 'AL', 1, 1, 1, 0),
(3, 'Algeria', 'DZA', 'DZ', 1, 1, 1, 0),
(4, 'American Samoa', 'ASM', 'AS', 1, 1, 1, 0),
(5, 'Andorra', 'AND', 'AD', 1, 1, 1, 0),
(6, 'Angola', 'AGO', 'AO', 1, 1, 1, 0),
(7, 'Anguilla', 'AIA', 'AI', 1, 1, 1, 0),
(8, 'Antarctica', 'ATA', 'AQ', 1, 1, 1, 0),
(9, 'Antigua and Barbuda', 'ATG', 'AG', 1, 1, 1, 0),
(10, 'Argentina', 'ARG', 'AR', 1, 1, 1, 0),
(11, 'Armenia', 'ARM', 'AM', 1, 1, 1, 0),
(12, 'Aruba', 'ABW', 'AW', 1, 1, 1, 0),
(13, 'Australia', 'AUS', 'AU', 1, 1, 1, 0),
(14, 'Austria', 'AUT', 'AT', 0, 0, 1, 0),
(15, 'Azerbaijan', 'AZE', 'AZ', 1, 1, 1, 0),
(16, 'Bahamas', 'BHS', 'BS', 1, 1, 1, 0),
(17, 'Bahrain', 'BHR', 'BH', 1, 1, 1, 0),
(18, 'Bangladesh', 'BGD', 'BD', 1, 1, 1, 0),
(19, 'Barbados', 'BRB', 'BB', 1, 1, 1, 0),
(20, 'Belarus', 'BLR', 'BY', 1, 1, 1, 0),
(21, 'Belgium', 'BEL', 'BE', 0, 1, 1, 0),
(22, 'Belize', 'BLZ', 'BZ', 1, 1, 1, 0),
(23, 'Benin', 'BEN', 'BJ', 1, 1, 1, 0),
(24, 'Bermuda', 'BMU', 'BM', 1, 1, 1, 0),
(25, 'Bhutan', 'BTN', 'BT', 1, 1, 1, 0),
(26, 'Bolivia', 'BOL', 'BO', 1, 1, 1, 0),
(27, 'Bosnia and Herzegowina', 'BIH', 'BA', 1, 1, 1, 0),
(28, 'Botswana', 'BWA', 'BW', 1, 1, 1, 0),
(29, 'Bouvet Island', 'BVT', 'BV', 1, 1, 1, 0),
(30, 'Brazil', 'BRA', 'BR', 1, 1, 1, 0),
(31, 'British Indian Ocean Territory', 'IOT', 'IO', 1, 1, 1, 0),
(32, 'Brunei Darussalam', 'BRN', 'BN', 1, 1, 1, 0),
(33, 'Bulgaria', 'BGR', 'BG', 0, 1, 1, 0),
(34, 'Burkina Faso', 'BFA', 'BF', 1, 1, 1, 0),
(35, 'Burundi', 'BDI', 'BI', 1, 1, 1, 0),
(36, 'Cambodia', 'KHM', 'KH', 1, 1, 1, 0),
(37, 'Cameroon', 'CMR', 'CM', 1, 1, 1, 0),
(38, 'Canada', 'CAN', 'CA', 1, 1, 1, 0),
(39, 'Cape Verde', 'CPV', 'CV', 1, 1, 1, 0),
(40, 'Cayman Islands', 'CYM', 'KY', 1, 1, 1, 0),
(41, 'Central African Republic', 'CAF', 'CF', 1, 1, 1, 0),
(42, 'Chad', 'TCD', 'TD', 1, 1, 1, 0),
(43, 'Chile', 'CHL', 'CL', 1, 1, 1, 0),
(44, 'China', 'CHN', 'CN', 1, 1, 1, 0),
(45, 'Christmas Island', 'CXR', 'CX', 1, 1, 1, 0),
(46, 'Cocos (Keeling) Islands', 'CCK', 'CC', 1, 1, 1, 0),
(47, 'Colombia', 'COL', 'CO', 1, 1, 1, 0),
(48, 'Comoros', 'COM', 'KM', 1, 1, 1, 0),
(49, 'Congo', 'COG', 'CG', 1, 1, 1, 0),
(50, 'Cook Islands', 'COK', 'CK', 1, 1, 1, 0),
(51, 'Costa Rica', 'CRI', 'CR', 1, 1, 1, 0),
(52, 'Cote D''Ivoire', 'CIV', 'CI', 1, 1, 1, 0),
(53, 'Croatia', 'HRV', 'HR', 1, 1, 1, 0),
(54, 'Cuba', 'CUB', 'CU', 1, 1, 1, 0),
(55, 'Cyprus', 'CYP', 'CY', 0, 1, 1, 0),
(56, 'Czech Republic', 'CZE', 'CZ', 0, 1, 1, 0),
(57, 'Denmark', 'DNK', 'DK', 0, 1, 1, 0),
(58, 'Djibouti', 'DJI', 'DJ', 1, 1, 1, 0),
(59, 'Dominica', 'DMA', 'DM', 1, 1, 1, 0),
(60, 'Dominican Republic', 'DOM', 'DO', 1, 1, 1, 0),
(61, 'East Timor', 'TMP', 'TP', 1, 1, 1, 0),
(62, 'Ecuador', 'ECU', 'EC', 1, 1, 1, 0),
(63, 'Egypt', 'EGY', 'EG', 1, 1, 1, 0),
(64, 'El Salvador', 'SLV', 'SV', 1, 1, 1, 0),
(65, 'Equatorial Guinea', 'GNQ', 'GQ', 1, 1, 1, 0),
(66, 'Eritrea', 'ERI', 'ER', 1, 1, 1, 0),
(67, 'Estonia', 'EST', 'EE', 0, 1, 1, 0),
(68, 'Ethiopia', 'ETH', 'ET', 1, 1, 1, 0),
(69, 'Falkland Islands (Malvinas)', 'FLK', 'FK', 1, 1, 1, 0),
(70, 'Faroe Islands', 'FRO', 'FO', 1, 1, 1, 0),
(71, 'Fiji', 'FJI', 'FJ', 1, 1, 1, 0),
(72, 'Finland', 'FIN', 'FI', 0, 1, 1, 0),
(73, 'France', 'FRA', 'FR', 0, 1, 1, 0),
(74, 'France, Metropolitan', 'FXX', 'FX', 1, 1, 1, 0),
(75, 'French Guiana', 'GUF', 'GF', 1, 1, 1, 0),
(76, 'French Polynesia', 'PYF', 'PF', 1, 1, 1, 0),
(77, 'French Southern Territories', 'ATF', 'TF', 1, 1, 1, 0),
(78, 'Gabon', 'GAB', 'GA', 1, 1, 1, 0),
(79, 'Gambia', 'GMB', 'GM', 1, 1, 1, 0),
(80, 'Georgia', 'GEO', 'GE', 1, 1, 1, 0),
(81, 'Germany', 'DEU', 'DE', 0, 1, 1, 0),
(82, 'Ghana', 'GHA', 'GH', 1, 1, 1, 0),
(83, 'Gibraltar', 'GIB', 'GI', 1, 1, 1, 0),
(84, 'Greece', 'GRC', 'GR', 1, 1, 1, 0),
(85, 'Greenland', 'GRL', 'GL', 0, 1, 1, 0),
(86, 'Grenada', 'GRD', 'GD', 1, 1, 1, 0),
(87, 'Guadeloupe', 'GLP', 'GP', 1, 1, 1, 0),
(88, 'Guam', 'GUM', 'GU', 1, 1, 1, 0),
(89, 'Guatemala', 'GTM', 'GT', 1, 1, 1, 0),
(90, 'Guinea', 'GIN', 'GN', 1, 1, 1, 0),
(91, 'Guinea-bissau', 'GNB', 'GW', 1, 1, 1, 0),
(92, 'Guyana', 'GUY', 'GY', 1, 1, 1, 0),
(93, 'Haiti', 'HTI', 'HT', 1, 1, 1, 0),
(94, 'Heard and Mc Donald Islands', 'HMD', 'HM', 1, 1, 1, 0),
(95, 'Honduras', 'HND', 'HN', 1, 1, 1, 0),
(96, 'Hong Kong', 'HKG', 'HK', 1, 1, 1, 0),
(97, 'Hungary', 'HUN', 'HU', 0, 1, 1, 0),
(98, 'Iceland', 'ISL', 'IS', 1, 1, 1, 0),
(99, 'India', 'IND', 'IN', 1, 1, 1, 0),
(100, 'Indonesia', 'IDN', 'ID', 1, 1, 1, 0),
(101, 'Iran (Islamic Republic of)', 'IRN', 'IR', 1, 1, 1, 0),
(102, 'Iraq', 'IRQ', 'IQ', 1, 1, 1, 0),
(103, 'Ireland', 'IRL', 'IE', 0, 1, 1, 0),
(104, 'Israel', 'ISR', 'IL', 1, 1, 1, 0),
(105, 'Italy', 'ITA', 'IT', 0, 1, 1, 0),
(106, 'Jamaica', 'JAM', 'JM', 1, 1, 1, 0),
(107, 'Japan', 'JPN', 'JP', 1, 1, 1, 0),
(108, 'Jordan', 'JOR', 'JO', 1, 1, 1, 0),
(109, 'Kazakhstan', 'KAZ', 'KZ', 1, 1, 1, 0),
(110, 'Kenya', 'KEN', 'KE', 1, 1, 1, 0),
(111, 'Kiribati', 'KIR', 'KI', 1, 1, 1, 0),
(112, 'Korea, Democratic People''s Republic of', 'PRK', 'KP', 1, 1, 1, 0),
(113, 'Korea, Republic of', 'KOR', 'KR', 1, 1, 1, 0),
(114, 'Kuwait', 'KWT', 'KW', 1, 1, 1, 0),
(115, 'Kyrgyzstan', 'KGZ', 'KG', 1, 1, 1, 0),
(116, 'Lao People''s Democratic Republic', 'LAO', 'LA', 1, 1, 1, 0),
(117, 'Latvia', 'LVA', 'LV', 0, 1, 1, 0),
(118, 'Lebanon', 'LBN', 'LB', 1, 1, 1, 0),
(119, 'Lesotho', 'LSO', 'LS', 1, 1, 1, 0),
(120, 'Liberia', 'LBR', 'LR', 1, 1, 1, 0),
(121, 'Libyan Arab Jamahiriya', 'LBY', 'LY', 1, 1, 1, 0),
(122, 'Liechtenstein', 'LIE', 'LI', 1, 1, 1, 0),
(123, 'Lithuania', 'LTU', 'LT', 0, 1, 1, 0),
(124, 'Luxembourg', 'LUX', 'LU', 0, 1, 1, 0),
(125, 'Macau', 'MAC', 'MO', 1, 1, 1, 0),
(126, 'Macedonia', 'MKD', 'MK', 1, 1, 1, 0),
(127, 'Madagascar', 'MDG', 'MG', 1, 1, 1, 0),
(128, 'Malawi', 'MWI', 'MW', 1, 1, 1, 0),
(129, 'Malaysia', 'MYS', 'MY', 1, 1, 1, 0),
(130, 'Maldives', 'MDV', 'MV', 1, 1, 1, 0),
(131, 'Mali', 'MLI', 'ML', 1, 1, 1, 0),
(132, 'Malta', 'MLT', 'MT', 0, 1, 1, 0),
(133, 'Marshall Islands', 'MHL', 'MH', 1, 1, 1, 0),
(134, 'Martinique', 'MTQ', 'MQ', 1, 1, 1, 0),
(135, 'Mauritania', 'MRT', 'MR', 1, 1, 1, 0),
(136, 'Mauritius', 'MUS', 'MU', 1, 1, 1, 0),
(137, 'Mayotte', 'MYT', 'YT', 1, 1, 1, 0),
(138, 'Mexico', 'MEX', 'MX', 1, 1, 1, 0),
(139, 'Micronesia, Federated States of', 'FSM', 'FM', 1, 1, 1, 0),
(140, 'Moldova, Republic of', 'MDA', 'MD', 1, 1, 1, 0),
(141, 'Monaco', 'MCO', 'MC', 1, 1, 1, 0),
(142, 'Mongolia', 'MNG', 'MN', 1, 1, 1, 0),
(143, 'Montserrat', 'MSR', 'MS', 1, 1, 1, 0),
(144, 'Morocco', 'MAR', 'MA', 1, 1, 1, 0),
(145, 'Mozambique', 'MOZ', 'MZ', 1, 1, 1, 0),
(146, 'Myanmar', 'MMR', 'MM', 1, 1, 1, 0),
(147, 'Namibia', 'NAM', 'NA', 1, 1, 1, 0),
(148, 'Nauru', 'NRU', 'NR', 1, 1, 1, 0),
(149, 'Nepal', 'NPL', 'NP', 1, 1, 1, 0),
(150, 'Netherlands', 'NLD', 'NL', 0, 1, 1, 0),
(151, 'Netherlands Antilles', 'ANT', 'AN', 1, 1, 1, 0),
(152, 'New Caledonia', 'NCL', 'NC', 1, 1, 1, 0),
(153, 'New Zealand', 'NZL', 'NZ', 1, 1, 1, 0),
(154, 'Nicaragua', 'NIC', 'NI', 1, 1, 1, 0),
(155, 'Niger', 'NER', 'NE', 1, 1, 1, 0),
(156, 'Nigeria', 'NGA', 'NG', 1, 1, 1, 0),
(157, 'Niue', 'NIU', 'NU', 1, 1, 1, 0),
(158, 'Norfolk Island', 'NFK', 'NF', 1, 1, 1, 0),
(159, 'Northern Mariana Islands', 'MNP', 'MP', 1, 1, 1, 0),
(160, 'Norway', 'NOR', 'NO', 1, 1, 1, 0),
(161, 'Oman', 'OMN', 'OM', 1, 1, 1, 0),
(162, 'Pakistan', 'PAK', 'PK', 1, 1, 1, 0),
(163, 'Palau', 'PLW', 'PW', 1, 1, 1, 0),
(164, 'Panama', 'PAN', 'PA', 1, 1, 1, 0),
(165, 'Papua New Guinea', 'PNG', 'PG', 1, 1, 1, 0),
(166, 'Paraguay', 'PRY', 'PY', 1, 1, 1, 0),
(167, 'Peru', 'PER', 'PE', 1, 1, 1, 0),
(168, 'Philippines', 'PHL', 'PH', 1, 1, 1, 0),
(169, 'Pitcairn', 'PCN', 'PN', 1, 1, 1, 0),
(170, 'Poland', 'POL', 'PL', 0, 1, 1, 0),
(171, 'Portugal', 'PRT', 'PT', 0, 1, 1, 0),
(172, 'Puerto Rico', 'PRI', 'PR', 1, 1, 1, 0),
(173, 'Qatar', 'QAT', 'QA', 1, 1, 1, 0),
(174, 'Reunion', 'REU', 'RE', 1, 1, 1, 0),
(175, 'Romania', 'ROM', 'RO', 0, 1, 1, 0),
(176, 'Russian Federation', 'RUS', 'RU', 1, 1, 1, 0),
(177, 'Rwanda', 'RWA', 'RW', 1, 1, 1, 0),
(178, 'Saint Kitts and Nevis', 'KNA', 'KN', 1, 1, 1, 0),
(179, 'Saint Lucia', 'LCA', 'LC', 1, 1, 1, 0),
(180, 'Saint Vincent and the Grenadines', 'VCT', 'VC', 1, 1, 1, 0),
(181, 'Samoa', 'WSM', 'WS', 1, 1, 1, 0),
(182, 'San Marino', 'SMR', 'SM', 1, 1, 1, 0),
(183, 'Sao Tome and Principe', 'STP', 'ST', 1, 1, 1, 0),
(184, 'Saudi Arabia', 'SAU', 'SA', 1, 1, 1, 0),
(185, 'Senegal', 'SEN', 'SN', 1, 1, 1, 0),
(186, 'Seychelles', 'SYC', 'SC', 1, 1, 1, 0),
(187, 'Sierra Leone', 'SLE', 'SL', 1, 1, 1, 0),
(188, 'Singapore', 'SGP', 'SG', 1, 1, 1, 0),
(189, 'Slovakia (Slovak Republic)', 'SVK', 'SK', 0, 1, 1, 0),
(190, 'Slovenia', 'SVN', 'SI', 0, 1, 1, 0),
(191, 'Solomon Islands', 'SLB', 'SB', 1, 1, 1, 0),
(192, 'Somalia', 'SOM', 'SO', 1, 1, 1, 0),
(193, 'South Africa', 'ZAF', 'ZA', 1, 1, 1, 0),
(194, 'South Georgia and the South Sandwich Islands', 'SGS', 'GS', 1, 1, 1, 0),
(195, 'Spain', 'ESP', 'ES', 0, 1, 1, 0),
(196, 'Sri Lanka', 'LKA', 'LK', 1, 1, 1, 0),
(197, 'St. Helena', 'SHN', 'SH', 1, 1, 1, 0),
(198, 'St. Pierre and Miquelon', 'SPM', 'PM', 1, 1, 1, 0),
(199, 'Sudan', 'SDN', 'SD', 1, 1, 1, 0),
(200, 'Suriname', 'SUR', 'SR', 1, 1, 1, 0),
(201, 'Svalbard and Jan Mayen Islands', 'SJM', 'SJ', 1, 1, 1, 0),
(202, 'Swaziland', 'SWZ', 'SZ', 1, 1, 1, 0),
(203, 'Sweden', 'SWE', 'SE', 0, 1, 1, 0),
(204, 'Switzerland', 'CHE', 'CH', 1, 1, 1, 0),
(205, 'Syrian Arab Republic', 'SYR', 'SY', 1, 1, 1, 0),
(206, 'Taiwan', 'TWN', 'TW', 1, 1, 1, 0),
(207, 'Tajikistan', 'TJK', 'TJ', 1, 1, 1, 0),
(208, 'Tanzania, United Republic of', 'TZA', 'TZ', 1, 1, 1, 0),
(209, 'Thailand', 'THA', 'TH', 1, 1, 1, 0),
(210, 'Togo', 'TGO', 'TG', 1, 1, 1, 0),
(211, 'Tokelau', 'TKL', 'TK', 1, 1, 1, 0),
(212, 'Tonga', 'TON', 'TO', 1, 1, 1, 0),
(213, 'Trinidad and Tobago', 'TTO', 'TT', 1, 1, 1, 0),
(214, 'Tunisia', 'TUN', 'TN', 1, 1, 1, 0),
(215, 'Turkey', 'TUR', 'TR', 1, 1, 1, 0),
(216, 'Turkmenistan', 'TKM', 'TM', 1, 1, 1, 0),
(217, 'Turks and Caicos Islands', 'TCA', 'TC', 1, 1, 1, 0),
(218, 'Tuvalu', 'TUV', 'TV', 1, 1, 1, 0),
(219, 'Uganda', 'UGA', 'UG', 1, 1, 1, 0),
(220, 'Ukraine', 'UKR', 'UA', 1, 1, 1, 0),
(221, 'United Arab Emirates', 'ARE', 'AE', 1, 1, 1, 0),
(222, 'United Kingdom', 'GBR', 'GB', 0, 1, 1, 0),
(223, 'United States', 'USA', 'US', 1, 1, 1, 0),
(224, 'United States Minor Outlying Islands', 'UMI', 'UM', 1, 1, 1, 0),
(225, 'Uruguay', 'URY', 'UY', 1, 1, 1, 0),
(226, 'Uzbekistan', 'UZB', 'UZ', 1, 1, 1, 0),
(227, 'Vanuatu', 'VUT', 'VU', 1, 1, 1, 0),
(228, 'Vatican City State (Holy See)', 'VAT', 'VA', 1, 1, 1, 0),
(229, 'Venezuela', 'VEN', 'VE', 1, 1, 1, 0),
(230, 'Viet Nam', 'VNM', 'VN', 1, 1, 1, 0),
(231, 'Virgin Islands (British)', 'VGB', 'VG', 1, 1, 1, 0),
(232, 'Virgin Islands (U.S.)', 'VIR', 'VI', 1, 1, 1, 0),
(233, 'Wallis and Futuna Islands', 'WLF', 'WF', 1, 1, 1, 0),
(234, 'Western Sahara', 'ESH', 'EH', 1, 1, 1, 0),
(235, 'Yemen', 'YEM', 'YE', 1, 1, 1, 0),
(236, 'Yugoslavia', 'YUG', 'YU', 1, 1, 1, 0),
(237, 'The Democratic Republic of Congo', 'DRC', 'DC', 1, 1, 1, 0),
(238, 'Zambia', 'ZMB', 'ZM', 1, 1, 1, 0),
(239, 'Zimbabwe', 'ZWE', 'ZW', 1, 1, 1, 0),
(240, 'East Timor', 'XET', 'XE', 1, 1, 1, 0),
(241, 'Jersey', 'XJE', 'XJ', 1, 1, 1, 0),
(242, 'St. Barthelemy', 'XSB', 'XB', 1, 1, 1, 0),
(243, 'St. Eustatius', 'XSE', 'XU', 1, 1, 1, 0),
(244, 'Canary Islands', 'XCA', 'XC', 1, 1, 1, 0);
	";
	
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_country_zone') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_country_zone` (
  `zone_id` mediumint(8) unsigned NOT NULL,
  `country_id` mediumint(8) unsigned NOT NULL,
  KEY `zone-country` (`zone_id`,`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_emails') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_emails` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `type` varchar(50) NOT NULL,
  `statuscode` mediumint(9) NOT NULL,
  `send_customer` tinyint(1) NOT NULL DEFAULT '1',
  `send_manager` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
	
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orderaddress') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_orderaddress` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `companyname` varchar(255) NOT NULL,
  `gender` enum('1','2') NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `zipcode` varchar(15) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `billingcompanyname` varchar(255) NOT NULL,
  `billingfirstname` varchar(255) NOT NULL,
  `billinglastname` varchar(255) NOT NULL,
  `billinggender` enum('1','2') NOT NULL,
  `billingaddress1` varchar(255) NOT NULL,
  `billingaddress2` varchar(255) NOT NULL,
  `billingzipcode` varchar(15) NOT NULL,
  `billingcity` varchar(255) NOT NULL,
  `billingcountry` varchar(255) NOT NULL,
  `billingemail` varchar(255) NOT NULL,
  `billingphone` varchar(255) NOT NULL,
  `billinglanguage` smallint(6) unsigned NOT NULL,
  `language` mediumint(8) unsigned NOT NULL,
  `samedelivery` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `password` varchar(300) NOT NULL,
  `vatin` varchar(200) NOT NULL DEFAULT '',
  `group_id` mediumint(8) unsigned DEFAULT NULL,
  `newsletter` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orders') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_orders` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `grandorder_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `status` smallint(5) unsigned NOT NULL,
  `delivery` mediumint(8) unsigned NOT NULL,
  `payment` mediumint(8) unsigned NOT NULL,
  `price` float unsigned NOT NULL,
  `inctaxorder` float unsigned NOT NULL,
  `price_recurring` float NOT NULL,
  `inctaxorder_recurring` float NOT NULL,
  `deliveryprice` float NOT NULL,
  `paymentprice` float NOT NULL,
  `inctaxdelivery` float unsigned NOT NULL,
  `inctaxpayment` float unsigned NOT NULL,
  `description` mediumtext NOT NULL,
  `sent_time` datetime NOT NULL,
  `confirmed_time` datetime NOT NULL,
  `weight` float unsigned NOT NULL,
  `comment` mediumtext NOT NULL,
  `group_id` MEDIUMINT UNSIGNED NOT NULL,
  `newsletter` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `grandorder_id` (`grandorder_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_paymentoptions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `class` varchar(100) NOT NULL,
  `price` float NOT NULL,
  `taxrate` float unsigned NOT NULL,
  `params` varchar(255) NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippers') == false && ConfigboxUpdateHelper::tableExists('#__configbox_shippers') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_shippers` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippingrates') == false && ConfigboxUpdateHelper::tableExists('#__configbox_shipping_methods') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_shippingrates` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `shipper` mediumint(8) unsigned NOT NULL,
  `zone` mediumint(8) unsigned NOT NULL,
  `minweight` float unsigned NOT NULL,
  `maxweight` float unsigned NOT NULL,
  `deliverytime` mediumint(9) NOT NULL,
  `price` float unsigned NOT NULL,
  `taxrate` float unsigned NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shopdata') == false && ConfigboxUpdateHelper::tableExists('#__configbox_shopdata') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_shopdata` (
  `id` mediumint(8) unsigned NOT NULL,
  `shopname` varchar(200) NOT NULL,
  `shoplogo` varchar(100) NOT NULL,
  `shopaddress1` varchar(200) NOT NULL,
  `shopaddress2` varchar(200) NOT NULL,
  `shopzipcode` varchar(40) NOT NULL,
  `shopcity` varchar(100) NOT NULL,
  `shopcountry` varchar(100) NOT NULL,
  `shopphonesales` varchar(100) NOT NULL,
  `shopphonesupport` varchar(100) NOT NULL,
  `shopemailsales` varchar(100) NOT NULL,
  `shopemailsupport` varchar(100) NOT NULL,
  `shoplinktotc` varchar(150) NOT NULL,
  `shopfax` varchar(100) NOT NULL,
  `shopbankname` varchar(100) NOT NULL,
  `shopbankaccountholder` varchar(100) NOT NULL,
  `shopbankaccount` varchar(100) NOT NULL,
  `shopbankcode` varchar(100) NOT NULL,
  `shopbic` varchar(100) NOT NULL,
  `shopiban` varchar(100) NOT NULL,
  `shopuid` varchar(100) NOT NULL,
  `shopcomreg` varchar(100) NOT NULL,
  `shopwebsite` varchar(255) DEFAULT NULL,
  `shopowner` varchar(255) DEFAULT NULL,
  `shoplegalvenue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
	
	$query = "INSERT IGNORE INTO `#__cbcheckout_shopdata` (`id`) VALUES (1);";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_statistics') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_statistics` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `checkout_started` mediumint(8) unsigned NOT NULL,
  `checkout_finished` mediumint(8) unsigned NOT NULL,
  `reached_1` mediumint(8) unsigned NOT NULL,
  `reached_2` mediumint(8) unsigned NOT NULL,
  `reached_3` mediumint(8) unsigned NOT NULL,
  `reached_4` mediumint(8) unsigned NOT NULL,
  `amount_started` float unsigned NOT NULL,
  `amount_finished` float unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_userfields` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `field_name` varchar(30) NOT NULL,
  `show_checkout` tinyint(1) unsigned NOT NULL,
  `require_checkout` tinyint(1) unsigned NOT NULL,
  `show_quotation` tinyint(1) unsigned NOT NULL,
  `require_quotation` tinyint(1) unsigned NOT NULL,
  `show_assistance` tinyint(1) unsigned NOT NULL,
  `require_assistance` tinyint(1) unsigned NOT NULL,
  `validation_browser` text NOT NULL,
  `validation_server` text NOT NULL,
  `group_id` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();

	$query = "
	INSERT INTO `#__cbcheckout_userfields` (`id`, `field_name`, `show_checkout`, `require_checkout`, `show_quotation`, `require_quotation`, `show_assistance`, `require_assistance`, `validation_browser`, `validation_server`, `group_id`) VALUES
(1, 'companyname', 1, 0, 0, 0, 0, 0, '', '', 1),
(2, 'gender', 1, 1, 0, 1, 0, 0, '', '', 1),
(3, 'firstname', 1, 1, 0, 1, 0, 0, '', '', 1),
(4, 'lastname', 1, 1, 0, 1, 0, 0, '', '', 1),
(5, 'address1', 1, 1, 0, 0, 0, 0, '', '', 1),
(6, 'address2', 1, 0, 0, 0, 0, 0, '', '', 1),
(7, 'zipcode', 1, 1, 0, 0, 0, 0, '', '', 1),
(8, 'city', 1, 1, 0, 0, 0, 0, '', '', 1),
(9, 'country', 1, 1, 0, 0, 0, 0, '', '', 1),
(10, 'email', 1, 1, 0, 0, 0, 0, '', '', 1),
(11, 'phone', 1, 0, 0, 0, 0, 0, '', '', 1),
(12, 'language', 1, 1, 0, 0, 0, 0, '', '', 1),
(13, 'vatin', 1, 0, 0, 0, 0, 0, '', '', 1),
(14, 'billingcompanyname', 1, 1, 1, 1, 1, 0, '', '', 1),
(15, 'billinggender', 1, 1, 1, 1, 1, 1, '', '', 1),
(16, 'billingfirstname', 1, 1, 1, 1, 1, 1, '', '', 1),
(17, 'billinglastname', 1, 1, 1, 1, 1, 1, '', '', 1),
(18, 'billingaddress1', 1, 1, 1, 1, 0, 0, '', '', 1),
(19, 'billingaddress2', 1, 0, 1, 1, 0, 0, '', '', 1),
(20, 'billingzipcode', 1, 1, 1, 1, 0, 0, '', '', 1),
(21, 'billingcity', 1, 1, 1, 1, 0, 0, '', '', 1),
(22, 'billingcountry', 1, 1, 1, 1, 0, 0, '', '', 1),
(23, 'billingemail', 1, 1, 1, 1, 1, 1, '', '', 1),
(24, 'billingphone', 1, 1, 1, 1, 0, 0, '', '', 1),
(25, 'billinglanguage', 1, 1, 1, 1, 1, 1, '', '', 1),
(26, 'samedelivery', 1, 1, 0, 0, 0, 0, '', '', 1),
(27, 'newsletter', 1, 0, 1, 0, 0, 0, '', '', 1),
(28, 'state', 1, 0, 0, 0, 0, 0, '', '', 1),
(29, 'billingstate', 1, 0, 0, 0, 0, 0, '', '', 1);
	";
	$db->setQuery($query);
	$db->query();

}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_users') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_users` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `joomlaid` mediumint(8) unsigned NOT NULL,
  `companyname` varchar(255) NOT NULL,
  `gender` enum('1','2') NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `zipcode` varchar(15) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `billingcompanyname` varchar(255) NOT NULL,
  `billingfirstname` varchar(255) NOT NULL,
  `billinglastname` varchar(255) NOT NULL,
  `billinggender` enum('1','2') NOT NULL,
  `billingaddress1` varchar(255) NOT NULL,
  `billingaddress2` varchar(255) NOT NULL,
  `billingzipcode` varchar(15) NOT NULL,
  `billingcity` varchar(255) NOT NULL,
  `billingcountry` varchar(255) NOT NULL,
  `billingemail` varchar(255) NOT NULL,
  `billingphone` varchar(255) NOT NULL,
  `billinglanguage` smallint(6) unsigned NOT NULL,
  `language` mediumint(8) unsigned NOT NULL,
  `samedelivery` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `password` varchar(300) NOT NULL,
  `vatin` varchar(200) NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `newsletter` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_zones') == false && ConfigboxUpdateHelper::tableExists('#__configbox_zones') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_zones` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}
