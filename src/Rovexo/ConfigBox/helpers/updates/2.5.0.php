<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'as_dropdown') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `as_dropdown` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_elements` SET `as_dropdown` = '1', `layoutname` = 'default' WHERE `layoutname` = 'select'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'widget') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `widget` VARCHAR(100) NOT NULL DEFAULT 'text'";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_elements` SET `widget` = 'calendar', `layoutname` = 'default', `minval` = '0', `maxval` = '365'  WHERE `layoutname` = 'date'";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_elements` SET `widget` = 'popuppicker' WHERE `article` != '0'";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_elements` CHANGE  `minval`  `minval` VARCHAR( 255 ) NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_elements` CHANGE  `maxval`  `maxval` VARCHAR( 255 ) NULL DEFAULT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_extensions') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `upload_extensions` VARCHAR(255) NOT NULL DEFAULT 'png, jpg, jpeg, gif, tif'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_mime_types') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `upload_mime_types` VARCHAR(255) NOT NULL DEFAULT 'image/pjpeg, image/jpg, image/jpeg, image/gif, image/tif, image/bmp, image/png, image/x-png'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_size_mb') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `upload_size_mb` FLOAT UNSIGNED NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'slider_steps') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `slider_steps` FLOAT UNSIGNED NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'unit') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD  `unit` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_visualize') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `upload_visualize` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_visualization_view') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD  `upload_visualization_view` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_visualization_stacking') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD  `upload_visualization_stacking` MEDIUMINT( 9 ) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'discontinued') == true) {
	$query = "ALTER TABLE `#__configbox_elements` DROP  `discontinued`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calcmodel_weight') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `calcmodel_weight` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'available') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD  `available` TINYINT( 1 ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'availibility_date') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD  `availibility_date` DATE NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enter_net') == false) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'vat_exclusive') == true) {
		$query = "ALTER TABLE  `#__configbox_config` CHANGE  `vat_exclusive`  `enter_net` TINYINT( 1 ) NOT NULL DEFAULT  '1'";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__configbox_config` SET `enter_net` = '1'";
		$db->setQuery($query);
		$db->query();
	}
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allow_recommendations') == false) {
	$query = "ALTER TABLE  `#__configbox_config` ADD `allow_recommendations`  TINYINT( 1 ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'pm_show_regular_first') == false) {
	$query = "
	ALTER TABLE  `#__configbox_config`
	ADD  `pm_show_regular_first` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_regular_show_overview` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_regular_show_prices` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_regular_show_categories` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_regular_show_elements` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_regular_show_elementprices` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_regular_expand_categories` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_show_overview` TINYINT( 1 ) NOT NULL DEFAULT  '0',
	ADD  `pm_recurring_show_prices` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_recurring_show_categories` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_recurring_show_elements` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_recurring_show_elementprices` TINYINT( 1 ) NOT NULL DEFAULT  '1',
	ADD  `pm_recurring_expand_categories` TINYINT( 2 ) NOT NULL DEFAULT  '2'
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'recaptcha_public_key') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `recaptcha_public_key` VARCHAR( 200 ) NOT NULL DEFAULT '', ADD `recaptcha_private_key` VARCHAR( 200 ) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_captcha_recommendation') == false) {
	$query = "ALTER TABLE  `#__configbox_config` ADD  `use_captcha_recommendation` TINYINT( 1 ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'pm_show_regular_first') == false) {
	$query = "
	ALTER TABLE  `#__configbox_products`
	ADD  `pm_show_regular_first` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_regular_show_overview` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_regular_show_prices` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_regular_show_categories` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_regular_show_elements` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_regular_show_elementprices` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_regular_expand_categories` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_show_overview` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_show_prices` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_show_categories` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_show_elements` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_show_elementprices` TINYINT( 2 ) NOT NULL DEFAULT  '2',
	ADD  `pm_recurring_expand_categories` TINYINT( 3 ) NOT NULL DEFAULT  '3'
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_categories') == true) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_categories', 'visualization_view') == false) {
		$query = "ALTER TABLE  `#__configbox_categories` CHANGE  `imagescope`  `visualization_view` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'visualization_view') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` CHANGE  `imagescope`  `visualization_view` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'visualization_stacking') == false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` CHANGE  `layer`  `visualization_stacking` MEDIUMINT( 9 ) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'visualization_image') == false) {
	$query = "ALTER TABLE  `#__configbox_xref_element_option` CHANGE  `opt_image`  `visualization_image` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'discontinued') == true) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` DROP  `discontinued`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'calcmodel_weight') == false) {
	$query = "ALTER TABLE  `#__configbox_xref_element_option` ADD  `calcmodel_weight` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'inctaxdelivery') == true) {
	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `inctaxdelivery`  `inctaxdelivery` FLOAT NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'deliveryprice') == true) {
	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `deliveryprice`  `deliveryprice` FLOAT NOT NULL";
	$db->setQuery($query);
	$db->query();
	
}


if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_paymentoptions', 'percentage') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_paymentoptions`
		ADD  `percentage` FLOAT NOT NULL ,
		ADD  `price_min` FLOAT NOT NULL ,
		ADD  `price_max` FLOAT NOT NULL";
		
		$db->setQuery($query);
		$db->query();
	}
}

if (KenedoPlatform::getName() == 'joomla') {
	if (KenedoPlatform::p()->getVersionShort() == '1.5') {
		// Doesn't matter anymore, latest CB does not use the jquery includer plugin.
	}
	else {

		$query = "SELECT * FROM `#__extensions` WHERE `folder` = 'system' AND `element` = 'jquery' LIMIT 1";
		$db->setQuery($query);
		$jQueryPlugin = $db->loadObject();

		if ($jQueryPlugin) {

			$params = new KStorage($jQueryPlugin->params);
			$version = $params->get('version','1');

			if (version_compare($version,'1.7.2','l')) {
				$params->set('version','1.7.2');
				$newString = $params->toString();
				$query = "UPDATE `#__extensions` SET `params` = '".$db->getEscaped($newString)."' WHERE `extension_id` = ".(int)$jQueryPlugin->extension_id;
				$db->setQuery($query);
				$db->query();
			}
		}

	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_bundles') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_bundles` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`product_id` mediumint(8) unsigned NOT NULL,
	`published` tinyint(1) unsigned NOT NULL DEFAULT '1',
	`ordering` mediumint(9) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_bundle_items') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__configbox_bundle_items` (
	  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	  `bundle_id` mediumint(8) unsigned NOT NULL,
	  `element_id` mediumint(8) unsigned NOT NULL,
	  `element_option_xref_id` mediumint(8) unsigned NOT NULL,
	  `text` mediumtext NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `bundle_id` (`bundle_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_states') == false && ConfigboxUpdateHelper::tableExists('#__configbox_states') == false) {
	$query = "CREATE TABLE `#__cbcheckout_states` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`country_id` mediumint(8) unsigned NOT NULL,
	`name` varchar(50) NOT NULL,
	`code` varchar(50) NOT NULL DEFAULT '',
	`custom_1` text NOT NULL DEFAULT '',
	`custom_2` text NOT NULL DEFAULT '',
	`custom_3` text NOT NULL DEFAULT '',
	`custom_4` text NOT NULL DEFAULT '',
	`ordering` mediumint(9) NOT NULL,
	`published` tinyint(1) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `country_id` (`country_id`),
	KEY `ordering` (`ordering`,`published`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8";
	
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'state') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_users` ADD  `state` MEDIUMINT UNSIGNED NOT NULL , ADD  `billingstate` MEDIUMINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'state') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD  `state` MEDIUMINT UNSIGNED NOT NULL , ADD  `billingstate` MEDIUMINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {
	
	$query = "SELECT `id` FROM `#__cbcheckout_userfields` WHERE `field_name` = 'state'";
	$db->setQuery($query);
	$hasState = $db->loadResult();
	
	if (!$hasState) {
		$query = "INSERT INTO `#__cbcheckout_userfields` (`field_name`, `show_checkout`, `require_checkout`, `show_quotation`, `require_quotation`, `show_assistance`, `require_assistance`, `validation_browser`, `validation_server`, `group_id`) VALUES
		('state', 0, 0, 0, 0, 0, 0, '', '', 1),('billingstate', 0, 0, 0, 0, 0, 0, '', '', 1);
		";
		$db->setQuery($query);
		$db->query();
	}
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'custom_1') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orders`
	ADD  `custom_1` TEXT NOT NULL DEFAULT '' ,
	ADD  `custom_2` TEXT NOT NULL DEFAULT '',
	ADD  `custom_3` TEXT NOT NULL DEFAULT '',
	ADD  `custom_4` TEXT NOT NULL DEFAULT ''
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'custom_1') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress`
	ADD  `custom_1` TEXT NOT NULL DEFAULT '' ,
	ADD  `custom_2` TEXT NOT NULL DEFAULT '',
	ADD  `custom_3` TEXT NOT NULL DEFAULT '',
	ADD  `custom_4` TEXT NOT NULL DEFAULT ''
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'custom_1') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_users`
	ADD  `custom_1` TEXT NOT NULL DEFAULT '' ,
	ADD  `custom_2` TEXT NOT NULL DEFAULT '',
	ADD  `custom_3` TEXT NOT NULL DEFAULT '',
	ADD  `custom_4` TEXT NOT NULL DEFAULT ''
	";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'delivery_id') == false) {

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `delivery`  `delivery_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `payment`  `payment_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `inctaxorder`  `order_tax` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `price`  `order_net` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `price_recurring`  `order_recurring_net` FLOAT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `inctaxorder_recurring`  `order_recurring_tax` FLOAT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `deliveryprice`  `delivery_net` FLOAT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `paymentprice`  `payment_net` FLOAT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `inctaxdelivery`  `delivery_tax` FLOAT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__cbcheckout_orders` CHANGE  `inctaxpayment`  `payment_tax` FLOAT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__cbcheckout_orders` SET `order_net` = `order_net` - `order_tax`;";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__cbcheckout_orders` SET `order_recurring_net` = `order_recurring_net` - `order_recurring_tax`;";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__cbcheckout_orders` SET `delivery_net` = `delivery_net` - `delivery_tax`;";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__cbcheckout_orders` SET `payment_net` = `payment_net` - `payment_tax`;";
	$db->setQuery($query);
	$db->query();
}
