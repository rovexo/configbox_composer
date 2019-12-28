<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (KenedoPlatform::getName() == 'magento') {
	$oldStoreFolder = Mage::getBaseDir('media').DS.'elovaris'.DS.'configbox'.DS.'store_data';
	$oldCustomerFolder = Mage::getBaseDir('media').DS.'elovaris'.DS.'configbox'.DS.'customer_data';
	$oldSettingsFolder = Mage::getBaseUrl('media').DS.'elovaris'.DS.'configbox'.DS.'settings';
}
else {
	$oldStoreFolder = KenedoPlatform::p()->getComponentDir('com_configbox').DS.'data';
	$oldCustomerFolder = KenedoPlatform::p()->getComponentDir('com_configbox').DS.'data';
	$oldSettingsFolder = KenedoPlatform::p()->getComponentDir('com_configbox').DS.'data'.DS.'settings';
}

/* UPGRADE CONFIGBOX STRINGS TABLE TO LANGUAGE TAGS - START */
	
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_strings', 'language_tag') == false) {
	
	$query = "ALTER TABLE `#__configbox_strings` ADD `language_tag` CHAR( 5 ) NOT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "SELECT * FROM `#__configbox_languages`";
	$db->setQuery($query);
	$languages = $db->loadObjectList();
	foreach ($languages as $lang) {
		$query = "UPDATE `#__configbox_strings` SET `language_tag` = '".$db->getEscaped($lang->tag)."' WHERE `lang_id` = ".intval($lang->id);
		$db->setQuery($query);
		$db->query();
	}
	
	$query = "ALTER TABLE `#__configbox_strings` DROP PRIMARY KEY";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_strings` CHANGE  `id`  `id` BIGINT( 20 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__configbox_strings` DROP INDEX `id`";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__configbox_strings` ADD PRIMARY KEY ( `id` )";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_strings` CHANGE  `id`  `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_strings` ADD UNIQUE  `uniqe_strings` (  `type` ,  `key` ,  `language_tag` )";
	$db->setQuery($query);
	$db->query();
	
}

/* UPGRADE CONFIGBOX STRINGS TABLE TO LANGUAGE TAGS - END */


/* UPGRADE ORDER MANAGEMENT STRINGS TABLE TO LANGUAGE TAGS - START */

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_strings', 'language_tag') == false) {
	$query = "ALTER TABLE `#__cbcheckout_strings` ADD `language_tag` CHAR( 5 ) NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_languages`";
	$db->setQuery($query);
	$languages = $db->loadObjectList();
	foreach ($languages as $lang) {
		$query = "UPDATE `#__cbcheckout_strings` SET `language_tag` = '".$db->getEscaped($lang->tag)."' WHERE `lang_id` = ".intval($lang->id);
		$db->setQuery($query);
		$db->query();
	}
	
	$query = "ALTER TABLE `#__cbcheckout_strings` DROP INDEX `type/key/lang_id`";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_strings` ADD UNIQUE `uniqe_strings` ( `type` , `key`, `language_tag` )";
	$db->setQuery($query);
	$db->query();
	
}
/* UPGRADE ORDER MANAGEMENT STRINGS TABLE TO LANGUAGE TAGS - END */


/* UPGRADE OLD LABELS TABLE TO LANGUAGE TAGS - START */
$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_oldlabels');
if (!isset($fields['language_tag'])) {
	$query = "ALTER TABLE `#__configbox_oldlabels` ADD `language_tag` CHAR( 5 ) NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_languages`";
	$db->setQuery($query);
	$languages = $db->loadObjectList();
	foreach ($languages as $lang) {
		$query = "UPDATE `#__configbox_oldlabels` SET `language_tag` = '".$db->getEscaped($lang->tag)."' WHERE `lang_id` = ".intval($lang->id);
		$db->setQuery($query);
		$db->query();
	}

	$query = "ALTER TABLE `#__configbox_oldlabels` DROP INDEX `type-key-lang_id`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_oldlabels` ADD UNIQUE `uniqe_strings` ( `type` , `key`, `language_tag` )";
	$db->setQuery($query);
	$db->query();
	
}
/* UPGRADE OLD LABELS TABLE TO LANGUAGE TAGS - END */



/* ADD THE ADMIN DEFAULT LANGUAGE IN CONFIG AND SET IT - START */
$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_config');
if (!isset($fields['language_tag'])) {
	
	$query = "ALTER TABLE `#__configbox_config` ADD  `language_tag` CHAR( 5 ) NOT NULL DEFAULT  'en-GB'";
	$db->setQuery($query);
	$db->query();
	
	// If the configbox_languages table is still there, use it. Otherwise get the current platform language
	if (ConfigboxUpdateHelper::tableExists('#__configbox_languages') == true) {
		$query = "SELECT `tag` FROM `#__configbox_languages` WHERE `default` = '1'";
		$db->setQuery($query);
		$default = $db->loadResult();
	}
	else {
		$default = '';
	}
	
	if (!$default) {
		$default = KenedoPlatform::p()->getLanguageTag(); 
	}
	$query = "UPDATE `#__configbox_config` SET `language_tag` = '".$db->getEscaped($default)."'";
	$db->setQuery($query);
	$db->query();
	
}
/* ADD THE ADMIN DEFAULT LANGUAGE IN CONFIG AND SET IT - START */


/* SET THE ACTIVE LANGUAGES TABLE - START */
$tables = ConfigboxUpdateHelper::getTableList();
if (!in_array($db->getPrefix().'configbox_active_languages', $tables)) {

	$query = "
	CREATE TABLE `#__configbox_active_languages` (
	 `tag` char(5) NOT NULL,
	 PRIMARY KEY (`tag`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8
	";
	$db->setQuery($query);
	$db->query();
	
	if (in_array($db->getPrefix().'configbox_languages', $tables)) {
		$query = "SELECT `tag` FROM `#__configbox_languages`";
		$db->setQuery($query);
		$tags = $db->loadResultList();
	}
	else {
		$tags = array();
	}

	if (!$tags) {
		$tags = KenedoPlatform::p()->getLanguages();
		$tags = array_keys($tags);
	}
	
	foreach ($tags as $tag) {
		$query = "INSERT INTO `#__configbox_active_languages` (`tag`) VALUES ('".$db->getEscaped($tag)."')";
		$db->setQuery($query);
		$db->query();
	}
	
}
/* SET THE ACTIVE LANGUAGES TABLE - START */


/* UPGRADE ELEMENT POPUP PICKER TO USE ITS OWN HTML - START */
$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_elements');
if (isset($fields['article'])) {
	
	// For Joomla, take the existing articles and copy them to configox_strings
	if (KenedoPlatform::getName() == 'joomla') {
		$query = "SELECT `id`, `article` FROM `#__configbox_elements` WHERE `article` != 0";
		$db->setQuery($query);
		$elements = $db->loadAssocList();
		
		if ($elements) {
			$query = "SELECT `tag` FROM `#__configbox_active_languages`";
			$db->setQuery($query);
			$tags = $db->loadResultList();
	
			foreach ($elements as $element) {
				$query = "SELECT `introtext`, `fulltext` FROM `#__content` WHERE `id` = ".(int)$element['article'];
				$db->setQuery($query);
				$article = $db->loadAssoc();
				$text = $article['introtext'] . $article['fulltext'];
				foreach ($tags as $tag) {
					$query = "REPLACE INTO `#__configbox_strings` (`type`, `key`, `lang_id`, `language_tag`, `text`) VALUES ( 62, ".(int)$element['id'].", 0,'".$db->getEscaped($tag)."','".$db->getEscaped($text)."' )";
					$db->setQuery($query);
					$succ = $db->query();
				}
			}
	
			ConfigboxCacheHelper::purgeCache();
		}
	}
	
	$query = "ALTER TABLE `#__configbox_elements` DROP `article`";
	$db->setQuery($query);
	$succ = $db->query();
}
/* UPGRADE ELEMENT POPUP PICKER TO USE ITS OWN HTML - END */

/* REMOVE THE OLD CART VIEW FOLDER AND UPDATE JOOMLA MENU ITEM LINKS - START */
$oldView = KPATH_DIR_CB.DS.'views'.DS.'grandorder';
$newView = KPATH_DIR_CB.DS.'views'.DS.'cart';

if (is_dir($newView) && is_dir($oldView)) {
	KenedoFileHelper::deleteFolder($oldView);
}

if (KenedoPlatform::getName() == 'joomla') {
	$query = "UPDATE `#__menu` SET `link` = 'index.php?option=com_configbox&view=cart' WHERE `link` = 'index.php?option=com_configbox&view=grandorder' ";
	$db->setQuery($query);
	$db->query();
}
/* REMOVE THE OLD CART VIEW FOLDER AND UPDATE JOOMLA MENU ITEM LINKS - END */

if (ConfigboxUpdateHelper::tableExists('#__configbox_categories') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_categories', 'css_classes') == false) {
	$query = "
	ALTER TABLE `#__configbox_categories`
	ADD  `css_classes` VARCHAR( 255 ) NOT NULL DEFAULT  '',
	ADD  `layout_type` SMALLINT NOT NULL DEFAULT  '1',
	ADD  `width_left_column` VARCHAR( 16 ) NOT NULL DEFAULT  '',
	ADD  `width_middle_column` VARCHAR( 16 ) NOT NULL DEFAULT  '',
	ADD  `width_right_column` VARCHAR( 16 ) NOT NULL DEFAULT  '',
	ADD  `content_left_column` VARCHAR( 255 ) NOT NULL DEFAULT  '',
	ADD  `content_middle_column` VARCHAR( 255 ) NOT NULL DEFAULT  '',
	ADD  `content_right_column` VARCHAR( 255 ) NOT NULL DEFAULT  '',
	ADD  `content_lower_row` VARCHAR( 255 ) NOT NULL DEFAULT  ''
	";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__configbox_categories` ADD  `content_upper_row` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `content_left_column`";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_config');
if (!isset($fields['show_conversion_table'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD `show_conversion_table` SMALLINT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['show_nav_as_tabs'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD `show_nav_as_tabs` SMALLINT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');
if (!isset($fields['show_nav_as_tabs'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `show_nav_as_tabs` SMALLINT NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_elements');

if (!isset($fields['choices'])) {
	$query = "ALTER TABLE `#__configbox_elements` ADD  `choices` TEXT NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['desc_display_method'])) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `desc_display_method` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}



/* UNPACK DOMPDF - START */
$folder  = KPATH_DIR_CB.DS.'external'.DS.'dompdf';
$zipFile = KPATH_DIR_CB.DS.'external'.DS.'dompdf.zip';
if (!is_dir($folder) && is_file($zipFile)) {
	KenedoFileHelper::extractZip($zipFile, $folder);
	unlink($zipFile);
}
/* UNPACK DOMPDF - END */


/* UNPACK HTMLPURIFIER - START */
$folder  = KPATH_DIR_CB.DS.'external'.DS.'kenedo'.DS.'external'.DS.'htmlpurifier';
$zipFile = KPATH_DIR_CB.DS.'external'.DS.'kenedo'.DS.'external'.DS.'htmlpurifier.zip';
if (!is_dir($folder) && is_file($zipFile)) {
	KenedoFileHelper::extractZip($zipFile, $folder);
	unlink($zipFile);
}
/* UNPACK HTMLPURIFIER - END */

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_countries')) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_countries', 'custom_1') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_countries` ADD  `custom_1` TEXT NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE  `#__cbcheckout_countries` ADD  `custom_2` TEXT NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE  `#__cbcheckout_countries` ADD  `custom_3` TEXT NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE  `#__cbcheckout_countries` ADD  `custom_4` TEXT NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippingrates')) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shippingrates', 'external_id') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_shippingrates` ADD  `external_id` VARCHAR( 100 ) NOT NULL DEFAULT  '', ADD INDEX (  `external_id` )";
		$db->setQuery($query);
		$db->query();
	}
}

/* ADD ORDERING TO SHIPPING RATES AND REORDER RATES AND PAYMENT OPTIONS - START */
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippingrates')) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shippingrates', 'ordering') == false) {

		$query = "ALTER TABLE `#__cbcheckout_shippingrates` ADD  `ordering` MEDIUMINT NOT NULL DEFAULT '0', ADD INDEX ( `ordering` )";
		$db->setQuery($query);
		$db->query();

		$query = "SELECT * FROM `#__cbcheckout_shippingrates` ORDER BY `id`";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if ($items) {
			$i = 10;
			foreach ($items as $item) {
				$query = "UPDATE `#__cbcheckout_shippingrates` SET `ordering` = ".intval($i)." WHERE `id` = ".intval($item->id);
				$db->setQuery($query);
				$db->query();
				$i += 10;
			}
		}

	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions')) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_paymentoptions', 'ordering') == false) {
		$query = "SELECT * FROM `#__cbcheckout_paymentoptions` ORDER BY `ordering`, `id`";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if ($items) {
			$i = 10;
			foreach ($items as $item) {
				$query = "UPDATE `#__cbcheckout_paymentoptions` SET `ordering` = ".intval($i)." WHERE `id` = ".intval($item->id);
				$db->setQuery($query);
				$db->query();
				$i += 10;
			}
		}
	}
}

/* ADD ORDERING TO SHIPPING RATES AND REORDER RATES AND PAYMENT OPTIONS - END */

$tables = ConfigboxUpdateHelper::getTableList();

if (!in_array($db->getPrefix().'configbox_product_images', $tables)) {
	
	$query = "
CREATE TABLE IF NOT EXISTS `#__configbox_product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `filename` varchar(30) NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
	$db->setQuery($query);
	$db->query();
}

if (!in_array($db->getPrefix().'configbox_tax_classes', $tables)) {

	$query = "CREATE TABLE IF NOT EXISTS `#__configbox_tax_classes` (
					`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
					`title` varchar(255) NOT NULL,
					`default_tax_rate` decimal(4,2) unsigned NOT NULL,
					PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	$db->setQuery($query);
	$db->query();
	
	$query = "INSERT INTO  `#__configbox_tax_classes` (
				`id` ,
				`title` ,
				`default_tax_rate`
				)
				VALUES (
				NULL ,  'Default Tax Class',  '19'
				);";
	
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_tax_class_rates') == false) {

	$query = "
CREATE TABLE `#__cbcheckout_tax_class_rates` (
 `tax_class_id` mediumint(8) unsigned NOT NULL,
 `zone_id` mediumint(8) unsigned NOT NULL,
 `state_id` mediumint(8) unsigned NOT NULL,
 `country_id` int(10) unsigned NOT NULL,
 `tax_rate` decimal(4,2) unsigned NOT NULL,
 UNIQUE KEY `unq_class_zone_state_country` (`tax_class_id`,`zone_id`,`state_id`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
	";
	
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');

if (isset($fields['show_totals_only'])) {
	$query = "
ALTER TABLE `#__configbox_products`
  DROP `show_totals_only`,
  DROP `show_totals_only_recurring`,
  DROP `show_recurring`,
  DROP `show_elements`,
  DROP `show_options`,
  DROP `show_calculation_elements`,
  DROP `show_vattext`,
  DROP `show_element_prices`,
  DROP `expand_categories`;
	";
	$db->setQuery($query);
	$db->query();
}

/* ADD TAX CLASS IDS TO PRODUCTS - START */
$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');

if (!isset($fields['taxclass_id'])) {
	$query = "ALTER TABLE  `#__configbox_products` ADD  `taxclass_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL AFTER  `taxrate`";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_products` ADD  `taxclass_recurring_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL AFTER  `taxclass_id`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE  `#__configbox_products` ADD  `taxrate_dec` DECIMAL( 4,2 ) UNSIGNED NOT NULL AFTER  `taxrate`";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_products` SET taxrate_dec = taxrate";
	$db->setQuery($query);
	$db->query();
	
	$query = "SELECT DISTINCT(p.taxrate_dec) AS taxrate FROM `#__configbox_products` AS p";
	$db->setQuery($query);
	$tax_rates = $db->loadAssocList();
	
	foreach ($tax_rates AS $tax_rate)
	{
		$query = "INSERT INTO  `#__configbox_tax_classes` (
					`id` ,
					`title` ,
					`default_tax_rate`
					)
					VALUES (
					NULL ,  '".$tax_rate['taxrate']."%',  '".$tax_rate['taxrate']."'
					);";
		
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__configbox_products` SET taxclass_id = ".intval($db->insertid()).", taxclass_recurring_id = ".intval($db->insertid())." WHERE taxrate_dec = ".$tax_rate['taxrate'];
		$db->setQuery($query);
		$db->query();
	}
}

/* ADD TAX CLASS IDS TO PRODUCTS - END */

/* ADD TAX CLASS IDS TO PAYMENT OPTIONS - START */

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions') == true) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_paymentoptions', 'taxclass_id') == false) {
		
		$query = "ALTER TABLE  `#__cbcheckout_paymentoptions` ADD  `taxrate_dec` DECIMAL( 4,2 ) UNSIGNED NOT NULL AFTER  `taxrate`";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_paymentoptions` ADD  `taxclass_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL AFTER  `taxrate_dec`";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_paymentoptions` SET `taxrate_dec` = `taxrate`";
		$db->setQuery($query);
		$db->query();
		
		
		$query = "SELECT DISTINCT(p.taxrate_dec) AS `taxrate` FROM `#__cbcheckout_paymentoptions` AS p";
		$db->setQuery($query);
		$tax_rates = $db->loadAssocList();
		if ($tax_rates) {
			// Find tax classes with the same tax rate, if there are none, create a new one. Afterwards set the tax class id
			foreach ($tax_rates as $tax_rate) {
			
				$query = "SELECT `id` FROM `#__configbox_tax_classes` WHERE `default_tax_rate` = ".floatval($tax_rate['taxrate'])." LIMIT 1";
				$db->setQuery($query);
				$row = $db->loadAssoc();
				if ($row) {
					$taxClassId = $row['id'];
				}
				else {
					$query = "INSERT INTO  `#__configbox_tax_classes` (
					`id` ,
					`title` ,
					`default_tax_rate`
					)
					VALUES (
					NULL ,  '".$tax_rate['taxrate']."%',  '".$tax_rate['taxrate']."'
					);";
					$db->setQuery($query);
					$db->query();
					$taxClassId = $db->insertid();
				}
			
				$query = "UPDATE `#__cbcheckout_paymentoptions` SET `taxclass_id` = ".intval($taxClassId)." WHERE `taxrate_dec` = ".$tax_rate['taxrate'];
				$db->setQuery($query);
				$db->query();
			}
			
		}
		
	}
}
/* ADD TAX CLASS IDS TO PAYMENT OPTIONS - END */


/* ADD TAX CLASS IDS TO SHIPPING RATES - START */
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippingrates') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_shippingrates', 'taxclass_id') == false) {
		
		$query = "ALTER TABLE  `#__cbcheckout_shippingrates` ADD  `taxrate_dec` DECIMAL( 4,2 ) UNSIGNED NOT NULL AFTER  `taxrate`";
		$db->setQuery($query);
		$db->query();
	
		$query = "ALTER TABLE  `#__cbcheckout_shippingrates` ADD  `taxclass_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL AFTER  `taxrate_dec`";
		$db->setQuery($query);
		$db->query();
	
		$query = "UPDATE `#__cbcheckout_shippingrates` SET `taxrate_dec` = `taxrate`";
		$db->setQuery($query);
		$db->query();
	
	
		$query = "SELECT DISTINCT(p.taxrate_dec) AS `taxrate` FROM `#__cbcheckout_shippingrates` AS p";
		$db->setQuery($query);
		$tax_rates = $db->loadAssocList();
		if ($tax_rates) {
			// Find tax classes with the same tax rate, if there are none, create a new one. Afterwards set the tax class id
			foreach ($tax_rates as $tax_rate) {
		
				$query = "SELECT `id` FROM `#__configbox_tax_classes` WHERE `default_tax_rate` = ".floatval($tax_rate['taxrate'])." LIMIT 1";
				$db->setQuery($query);
				$row = $db->loadAssoc();
				if ($row) {
					$taxClassId = $row['id'];
				}
				else {
					$query = "INSERT INTO  `#__configbox_tax_classes` (
					`id` ,
					`title` ,
					`default_tax_rate`
					)
					VALUES (
					NULL ,  '".$tax_rate['taxrate']."%',  '".$tax_rate['taxrate']."'
					);";
					$db->setQuery($query);
					$db->query();
					$taxClassId = $db->insertid();
				}
		
				$query = "UPDATE `#__cbcheckout_shippingrates` SET `taxclass_id` = ".intval($taxClassId)." WHERE `taxrate_dec` = ".$tax_rate['taxrate'];
				$db->setQuery($query);
				$db->query();
			}
			
		}
	}
}

/* ADD TAX CLASS IDS TO SHIPPING RATES - END */

if (ConfigboxUpdateHelper::tableExists('#__configbox_groups') == true) {
	$query = "DROP TABLE `#__configbox_groups`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_groups') == false) {

	if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_groups') == false) {

		$query = "CREATE TABLE `#__cbcheckout_user_groups` (
			  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL,
			  `discount_start_1` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_factor_1` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_start_2` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_factor_2` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_start_3` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_factor_3` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_start_4` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_factor_4` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_start_5` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
			  `discount_factor_5` decimal(4,2) unsigned NOT NULL DEFAULT '0.00',
			  `custom_1` text NOT NULL,
			  `custom_2` text NOT NULL,
			  `custom_3` text NOT NULL,
			  `custom_4` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		$db->setQuery($query);
		$db->query();

		// Populate the table with one default group
		$query = "INSERT INTO  `#__cbcheckout_user_groups` (`title`) VALUES ('Default Group');";
		$db->setQuery($query);
		$db->query();

	}

}


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'user_group')) {
	$query = "ALTER TABLE  `#__cbcheckout_config` CHANGE  `user_group`  `joomla_user_group_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'joomla_user_group_id')) {
	$query = "ALTER TABLE `#__cbcheckout_config` DROP  `joomla_user_group_id`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'default_customer_group') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'default_user_group_id') == false) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'default_customer_group_id') == false) {
			$query = "ALTER TABLE  `#__cbcheckout_config` CHANGE  `default_customer_group`  `default_user_group_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
			$db->setQuery($query);
			$db->query();
		}
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calcmodel_id_min_val') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `calcmodel_id_min_val` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calcmodel_id_max_val') == false) {
	$query = "ALTER TABLE  `#__configbox_elements` ADD  `calcmodel_id_max_val` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' ";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'autoselect_any') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'behavior_on_activation') == false) {
		$query = "ALTER TABLE  `#__configbox_elements` ADD  `autoselect_any`  TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_categories') == true && ConfigboxUpdateHelper::tableExists('#__configbox_sections') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_categories', 'product_id') == false) {
		
		$query = "ALTER TABLE `#__configbox_categories` ADD  `product_id` MEDIUMINT UNSIGNED NULL DEFAULT NULL , ADD INDEX (  `product_id` )";
		$db->setQuery($query);
		$db->query();
		
		// REMOVE SECTIONS, CONNECT PAGES WITH PRODUCTS - START
		$query = "
		SELECT s.prod_id AS product_id, s.ordering AS section_ordering, c.ordering AS category_ordering, c.id AS category_id
		FROM `#__configbox_categories` AS c
		LEFT JOIN `#__configbox_sections` AS s ON s.id = c.section_id";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Set the product ID for each configurator page
		foreach ($items as $item) {
			$query = "UPDATE `#__configbox_categories` SET `product_id` = ".(int)$item->product_id.", `ordering` = ". ( $item->section_ordering * 1000 + $item->category_ordering)." WHERE `id` = ".(int)$item->category_id." LIMIT 1";
			$db->setQuery($query);
			$db->query();
		}

		// Get the ordering of pages right (since sections are gone, numbering got to work as if there were sections)
		$query = "
		SELECT c.id, c.product_id, c.ordering
		FROM `#__configbox_categories` AS c
		ORDER BY c.product_id, c.ordering";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		
		$product_id = 0;
		foreach ($items as $item) {
			if ($item->product_id != $product_id) {
				$i = 10;
			}
		
			$query = "UPDATE `#__configbox_categories` SET `ordering` = ". (int)$i." WHERE `id` = ".(int)$item->id;
			$db->setQuery($query);
			$db->query();
		
			$i = $i + 10;
			$product_id = $item->product_id;
		}
		// REMOVE SECTIONS, CONNECT PAGES WITH PRODUCTS - END
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'enable_reviews') == false) {
	$query = "ALTER TABLE `#__configbox_options` ADD `enable_reviews` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '2', ADD `external_reviews_id` VARCHAR( 200 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enable_reviews_options') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `enable_reviews_options` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'calcmodel_id_x') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'column_calc_id') == false) {
		$query = "ALTER TABLE `#__configbox_calculation_tables` ADD `calcmodel_id_x` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'calcmodel_id_y') == false) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'row_calc_id') == false) {
		$query = "ALTER TABLE  `#__configbox_calculation_tables` ADD `calcmodel_id_y` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();
	}
}

// Table renamed in 3.0.0 to configbox_calculation_codes (col formula renamed to code)
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_formulas', 'formula') == true) {
	$query = "ALTER TABLE  `#__configbox_calculation_formulas` CHANGE  `formula`  `formula` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_groups') == false) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'enable_checkout_order') == false) {
		$query = "
	ALTER TABLE  `#__cbcheckout_user_groups`
	ADD  `enable_checkout_order` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1',
	ADD  `enable_see_pricing` TINYINT UNSIGNED NOT NULL DEFAULT  '1',
	ADD  `enable_save_order` TINYINT UNSIGNED NOT NULL DEFAULT  '1',
	ADD  `enable_request_quotation` TINYINT UNSIGNED NOT NULL DEFAULT  '1',
	ADD  `enable_request_assistance` TINYINT UNSIGNED NOT NULL DEFAULT  '0',
	ADD  `enable_recommendation` TINYINT UNSIGNED NOT NULL DEFAULT  '0',
	ADD  `b2b_mode` TINYINT UNSIGNED NOT NULL DEFAULT '0',
	ADD  `joomla_user_group_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'
	";
		$db->setQuery($query);
		$db->query();
	}

}


$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_config');
if (!isset($fields['enable_geolocation'])) {
	$query = "ALTER TABLE  `#__configbox_config` ADD  `enable_geolocation` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}
if (!isset($fields['maxmind_license_key'])) {
	$query = "ALTER TABLE  `#__configbox_config` ADD  `maxmind_license_key` VARCHAR( 200 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_states') == false) {

	$query = 'DROP TABLE IF EXISTS `#__cbcheckout_states`';
	$db->setQuery($query);
	$db->query();

	$query = "
	CREATE TABLE `#__cbcheckout_states` (
	`id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`country_id` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(50) NOT NULL DEFAULT '',
	`iso_code` VARCHAR(50) NOT NULL DEFAULT '',
	`fips_number` VARCHAR(5) NOT NULL DEFAULT '',
	`custom_1` TEXT NOT NULL DEFAULT '',
	`custom_2` TEXT NOT NULL DEFAULT '',
	`custom_3` TEXT NOT NULL DEFAULT '',
	`custom_4` TEXT NOT NULL DEFAULT '',
	`ordering` MEDIUMINT(9) NOT NULL DEFAULT '0',
	`published` TINYINT(1) UNSIGNED NOT NULL  DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY `country_id` (`country_id`),
	KEY `ordering` (`ordering`,`published`),
	KEY `iso_fips` (`iso_code`,`fips_number`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8
	";

	$db->setQuery($query);
	$db->query();


	$query = "SELECT `id` FROM `#__cbcheckout_states` LIMIT 1";
	$db->setQuery($query);
	$hasStates = $db->loadResult();

	if (!$hasStates) {
		$file =  __DIR__ .'/complete/import_states.sql';

		if (file_exists($file)) {
			$queries = $db->splitSql(file_get_contents($file));
			foreach ($queries as $query) {
				if (trim($query)) {
					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}

}
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_users') == true && ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users','is_temporary') == false) {
	$query = "ALTER TABLE `#__cbcheckout_users` ADD  `is_temporary` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_orderaddress');
if (!isset($fields['user_id'])) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD  `user_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `id` , ADD  `order_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `user_id`";
	$db->setQuery($query);
	$db->query();
	
	$query = "SELECT user_id, id AS order_id FROM `#__cbcheckout_orders`";
	$db->setQuery($query);
	$addresses = $db->loadObjectList();
	
	foreach ($addresses as $address) {
		$query = "UPDATE `#__cbcheckout_orderaddress` SET `user_id` = ".(int)$address->user_id.", `order_id` = ".(int)$address->order_id." WHERE `id` = ".(int)$address->order_id." LIMIT 1";
		$db->setQuery($query);
		$db->query();
	}
	
	$query = "DELETE FROM `#__cbcheckout_orderaddress` WHERE `user_id` = 0 OR `order_id` = 0";
	$db->setQuery($query);
	$db->query();
	
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');

if (!isset($fields['quantity_element_id'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `quantity_element_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['alt_quantity_element_id'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `alt_quantity_element_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['quantity_multiplies'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `quantity_multiplies` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['quantity_in_cart'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `quantity_in_cart` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['show_buy_button'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `show_buy_button` ENUM('0','1') NOT NULL DEFAULT '1'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `pm_show_quotation_button` ENUM('0','1','2') NOT NULL DEFAULT '2'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_show_delivery_options'])) {
	
	$query = "ALTER TABLE  `#__configbox_products` 
				ADD  `pm_show_delivery_options` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '2',
				ADD  `pm_show_payment_options` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '2',
				ADD  `pm_show_cart_button` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '2',
				ADD  `is_tangible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1',
				ADD  `product_custom_1` TEXT NOT NULL DEFAULT  '',
				ADD  `product_custom_2` TEXT NOT NULL DEFAULT  '',
				ADD  `product_custom_3` TEXT NOT NULL DEFAULT  '',
				ADD  `product_custom_4` TEXT NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_config` 
				ADD  `pm_show_delivery_options` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
				ADD  `pm_show_payment_options` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
				ADD  `pm_show_cart_button` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
	
}

if (!isset($fields['pm_show_net_in_b2c'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_show_net_in_b2c` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_regular_show_taxes'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_regular_show_taxes` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_regular_show_cart_button'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_regular_show_cart_button` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_regular_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_regular_show_quotation_button` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_recurring_show_taxes'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_recurring_show_taxes` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_recurring_show_cart_button'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_recurring_show_cart_button` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_recurring_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD  `pm_recurring_show_quotation_button` ENUM(  '0',  '1', '2' ) NOT NULL DEFAULT  '2'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');

if (isset($fields['pm_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `pm_show_quotation_button`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['pm_show_cart_button'])) {
	$query = "ALTER TABLE `#__configbox_products` DROP `pm_show_cart_button`";
	$db->setQuery($query);
	$db->query();
}


$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_config');

if (!isset($fields['pm_regular_show_taxes'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_regular_show_taxes` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_regular_show_cart_button'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_regular_show_cart_button` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_regular_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_regular_show_quotation_button` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_recurring_show_taxes'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_recurring_show_taxes` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_recurring_show_cart_button'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_recurring_show_cart_button` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_recurring_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_recurring_show_quotation_button` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['pm_show_net_in_b2c'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `pm_show_net_in_b2c` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['pm_show_cart_button'])) {
	$query = "ALTER TABLE `#__configbox_config` DROP `pm_show_cart_button`";
	$db->setQuery($query);
	$db->query();
}

if (isset($fields['pm_show_quotation_button'])) {
	$query = "ALTER TABLE `#__configbox_config` DROP `pm_show_quotation_button`";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['label_product_custom_1'])) {
	
	$query = "ALTER TABLE  `#__configbox_config` 
	ADD  `label_product_custom_1` VARCHAR( 200 ) NOT NULL DEFAULT  '',
	ADD  `label_product_custom_2` VARCHAR( 200 ) NOT NULL DEFAULT  '',
	ADD  `label_product_custom_3` VARCHAR( 200 ) NOT NULL DEFAULT  '',
	ADD  `label_product_custom_4` VARCHAR( 200 ) NOT NULL DEFAULT  '',
	ADD  `label_product_custom_5` VARCHAR( 200 ) NOT NULL DEFAULT  '',
	ADD  `label_product_custom_6` VARCHAR( 200 ) NOT NULL DEFAULT  ''";
	
	$db->setQuery($query);
	$db->query();
}

/* USER AND ORDER ADDRESS: PUT BILLING INFO TO EMPTY DELIVERY INFO - START */
$query = "SELECT `id` FROM `#__cbcheckout_users` WHERE (`billingemail` = '' OR `billingemail` IS NULL) AND `is_temporary` = '0' LIMIT 1";
$db->setQuery($query);
$normalizeUserdata = $db->loadResult();

if ($normalizeUserdata) {
	
	$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_users');
	if (isset($fields['billinglanguage'])) {
		$query = "UPDATE `#__cbcheckout_users` SET `billinglanguage` = `language` WHERE `samedelivery` = '1' AND (`billingemail` = '' OR `billingemail` IS NULL) ";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_users` SET `language` = `billinglanguage` WHERE `samedelivery` = '1' AND (`email` = '' OR `email` IS NULL) ";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_orderaddress` SET `billinglanguage` = `language` WHERE `samedelivery` = '1' AND (`billingemail` = '' OR `billingemail` IS NULL) ";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_orderaddress` SET `language` = `billinglanguage` WHERE `samedelivery` = '1' AND (`email` = '' OR `email` IS NULL) ";
		$db->setQuery($query);
		$db->query();
	}
	
	$query = "	UPDATE `#__cbcheckout_users`
	SET
	`billingcompanyname` = `companyname`,
	`billingfirstname` = `firstname`,
	`billinglastname` = `lastname`,
	`billinggender` = `gender`,
	`billingaddress1` = `address1`,
	`billingaddress2` = `address2`,
	`billingzipcode` = `zipcode`,
	`billingcity` = `city`,
	`billingcountry` = `country`,
	`billingemail` = `email`,
	`billingphone` = `phone`,
	`billingstate` = `state`
		
	WHERE `samedelivery` = '1' AND (`billingemail` = '' OR `billingemail` IS NULL) ";
	
	$db->setQuery($query);
	$db->query();
	
	$query = "	UPDATE `#__cbcheckout_users`
	SET
	`companyname` = `billingcompanyname`,
	`firstname` = `billingfirstname`,
	`lastname` = `billinglastname`,
	`gender` = `billinggender`,
	`address1` = `billingaddress1`,
	`address2` = `billingaddress2`,
	`zipcode` = `billingzipcode`,
	`city` = `billingcity`,
	`country` = `billingcountry`,
	`email` = `billingemail`,
	`phone` = `billingphone`,
	`state` = `billingstate`
	
	WHERE `samedelivery` = '1' AND (`email` = '' OR `email` IS NULL) ";
	
	$db->setQuery($query);
	$db->query();
	
	
	$query = "	UPDATE `#__cbcheckout_orderaddress`
	SET
	`billingcompanyname` = `companyname`,
	`billingfirstname` = `firstname`,
	`billinglastname` = `lastname`,
	`billinggender` = `gender`,
	`billingaddress1` = `address1`,
	`billingaddress2` = `address2`,
	`billingzipcode` = `zipcode`,
	`billingcity` = `city`,
	`billingcountry` = `country`,
	`billingemail` = `email`,
	`billingphone` = `phone`,
	`billingstate` = `state`
	
	WHERE `samedelivery` = '1' AND (`billingemail` = '' OR `billingemail` IS NULL) ";
	
	$db->setQuery($query);
	$db->query();
	
	$query = "	UPDATE `#__cbcheckout_orderaddress`
	SET
	`companyname` = `billingcompanyname`,
	`firstname` = `billingfirstname`,
	`lastname` = `billinglastname`,
	`gender` = `billinggender`,
	`address1` = `billingaddress1`,
	`address2` = `billingaddress2`,
	`zipcode` = `billingzipcode`,
	`city` = `billingcity`,
	`country` = `billingcountry`,
	`email` = `billingemail`,
	`phone` = `billingphone`,
	`state` = `billingstate`
		
	WHERE `samedelivery` = '1' AND (`email` = '' OR `email` IS NULL) ";
	
	$db->setQuery($query);
	$db->query();
	
}
/* USER AND ORDER ADDRESS: PUT BILLING INFO TO EMPTY DELIVERY INFO - END */


/* ADD INDEXES TO XREFS - START */
$query = "SHOW INDEX FROM `#__configbox_xref_element_option`";
$db->setQuery($query);
$indices = $db->loadAssocList('Key_name');
if (!isset($indices['element_id'])) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD INDEX  `element_id` (  `element_id` )";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD INDEX  `option_id` (  `option_id` )";
	$db->setQuery($query);
	$db->query();
	
}
/* ADD INDEXES TO XREFS - END */


$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_calculation_tables');
if (isset($fields['nexthigher'])) {
	$query = "ALTER TABLE `#__configbox_calculation_tables` CHANGE  `nexthigher`  `lookup_value` TINYINT( 2 ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_orders');
if (!isset($fields['order_unreduced_net']) && !isset($fields['products_unreduced_net'])) {

	$query = "ALTER TABLE  `#__cbcheckout_orders`
		ADD  `order_unreduced_net` DECIMAL( 20, 2 ) UNSIGNED NOT NULL AFTER  `order_recurring_tax` ,
		ADD  `order_unreduced_tax` DECIMAL( 20, 2 ) UNSIGNED NOT NULL AFTER  `order_unreduced_net` ,
		ADD  `order_unreduced_recurring_net` DECIMAL( 20, 2 ) UNSIGNED NOT NULL AFTER  `order_unreduced_tax` ,
		ADD  `order_unreduced_recurring_tax` DECIMAL( 20, 2 ) UNSIGNED NOT NULL AFTER  `order_unreduced_recurring_net`";

	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__cbcheckout_orders` SET order_unreduced_net = order_net, order_unreduced_tax = order_tax, order_unreduced_recurring_net = order_recurring_net, order_unreduced_recurring_tax = order_recurring_tax";
	
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_groups') == false) {

	$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_user_groups');

	if (isset($fields['discount_start_1']) && $fields['discount_start_1']->Type == 'decimal(10,2) unsigned') {
		$query = "ALTER TABLE  `#__cbcheckout_user_groups`
	CHANGE  `discount_start_1`  `discount_start_1` DECIMAL( 20, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_start_2`  `discount_start_2` DECIMAL( 20, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_start_3`  `discount_start_3` DECIMAL( 20, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_start_4`  `discount_start_4` DECIMAL( 20, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_start_5`  `discount_start_5` DECIMAL( 20, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00',
		
	CHANGE  `discount_factor_1`  `discount_factor_1` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_factor_2`  `discount_factor_2` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_factor_3`  `discount_factor_3` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_factor_4`  `discount_factor_4` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0.00',
	CHANGE  `discount_factor_5`  `discount_factor_5` DECIMAL( 20, 2 ) NOT NULL DEFAULT  '0.00'

	";

		$db->setQuery($query);
		$db->query();
	}

}

$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_config');

if (!isset($fields['default_country_id'])) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD  `default_country_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_orders');
if (!isset($fields['store_id'])) {
	$query = "ALTER TABLE  `#__cbcheckout_orders` ADD  `store_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orderaddress') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'joomlaid') == false) {

		$query = "ALTER TABLE `#__cbcheckout_orderaddress` ADD `joomlaid` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'";
		$db->setQuery($query);
		$db->query();

		$query = "SELECT `id`, `joomlaid` FROM `#__cbcheckout_users` WHERE `joomlaid` != 0";
		$db->setQuery($query);
		$list = $db->loadAssocList('id');

		foreach ($list as $userId=>$user) {
			$query = "UPDATE `#__cbcheckout_orderaddress` SET `joomlaid` = ".(int)$user['joomlaid']." WHERE `user_id` = ".(int)$userId;
			$db->setQuery($query);
			$db->query();
		}

	}
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_tax_classes');
if (!isset($fields['id_external'])) {
	$query = "ALTER TABLE  `#__configbox_tax_classes` ADD  `id_external` VARCHAR(100) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
}


$query = "CREATE TABLE IF NOT EXISTS `#__configbox_connectors` (
 `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(100) NOT NULL,
 `ordering` mediumint(9) NOT NULL,
 `published` tinyint(3) UNSIGNED NOT NULL DEFAULT  '1',
 `after_system` TINYINT(3) UNSIGNED NOT NULL DEFAULT  '1',
 `file` VARCHAR( 500 ) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_config');
if (!isset($fields['enable_reviews_products'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `enable_reviews_products` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');
if (!isset($fields['enable_reviews'])) {
	$query = "ALTER TABLE `#__configbox_products` ADD `enable_reviews` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '2', ADD `external_reviews_id` VARCHAR( 200 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_options');
if (!isset($fields['option_image'])) {
	$query = "ALTER TABLE `#__configbox_options` ADD  `option_image` VARCHAR( 200 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_config');
if (!isset($fields['continue_listing_id'])) {
	$query = "ALTER TABLE `#__configbox_config` ADD  `continue_listing_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

$fields = ConfigboxUpdateHelper::getTableFields('#__configbox_products');
if (!isset($fields['charge_deposit'])) {	
	$query = "ALTER TABLE  `#__configbox_products` 
	ADD  `charge_deposit` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
	ADD  `deposit_percentage` DECIMAL( 6, 3 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (!isset($fields['dispatch_time'])) {
	$query = "ALTER TABLE  `#__configbox_products` ADD  `dispatch_time` TINYINT UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
}

/* CONVERT ORDER MANAGEMENT ORDER FIELDS FROM FLOAT TO DECIMAL - START */
$fields = ConfigboxUpdateHelper::getTableFields('#__cbcheckout_orders');
if (!isset($fields['products_open_unreduced_net'])) {
	
	$query = "ALTER TABLE `#__cbcheckout_orders` 
CHANGE  `order_net`  `order_net` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `order_tax`  `order_tax` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `order_recurring_net`  `order_recurring_net` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `order_recurring_tax`  `order_recurring_tax` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `delivery_net`  `delivery_net` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `payment_net`  `payment_net` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `delivery_tax`  `delivery_tax` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `payment_tax`  `payment_tax` VARCHAR( 255 ) NOT NULL DEFAULT  '0',
CHANGE  `weight`  `weight` VARCHAR( 255 ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_orders` 
CHANGE  `order_net`  `order_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `order_tax`  `order_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `order_recurring_net`  `order_recurring_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `order_recurring_tax`  `order_recurring_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `delivery_net`  `delivery_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `payment_net`  `payment_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `delivery_tax`  `delivery_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `payment_tax`  `payment_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `weight`  `weight` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_orders` 
CHANGE  `order_net`  `products_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `order_tax`  `products_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `order_recurring_net`  `products_recurring_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `order_recurring_tax`  `products_recurring_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `order_unreduced_net`  `products_unreduced_net` DECIMAL( 20, 2 ) UNSIGNED NOT NULL ,
CHANGE  `order_unreduced_tax`  `products_unreduced_tax` DECIMAL( 20, 2 ) UNSIGNED NOT NULL ,
CHANGE  `order_unreduced_recurring_net`  `products_unreduced_recurring_net` DECIMAL( 20, 2 ) UNSIGNED NOT NULL ,
CHANGE  `order_unreduced_recurring_tax`  `products_unreduced_recurring_tax` DECIMAL( 20, 2 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_orders` 
ADD  `products_open_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `products_tax` ,
ADD  `products_open_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `products_open_net`";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_orders` 
ADD  `products_open_unreduced_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `products_open_tax` ,
ADD  `products_open_unreduced_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `products_open_unreduced_net`";
	$db->setQuery($query);
	$db->query();
	
	$query ="ALTER TABLE  `#__cbcheckout_orders` 
CHANGE  `products_unreduced_net`  `products_unreduced_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `products_unreduced_tax`  `products_unreduced_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `products_unreduced_recurring_net`  `products_unreduced_recurring_net` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000',
CHANGE  `products_unreduced_recurring_tax`  `products_unreduced_recurring_tax` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0.000'";
	$db->setQuery($query);
	$db->query();
}
/* CONVERT ORDER MANAGEMENT ORDER FIELDS FROM FLOAT TO DECIMAL - END */


$query = "CREATE TABLE IF NOT EXISTS `#__configbox_session` (
 `id` varchar(128) NOT NULL,
 `user_agent` varchar(200) NOT NULL DEFAULT '',
 `ip_address` varchar(100) NOT NULL DEFAULT '',
 `data` text NOT NULL,
 `updated` bigint(20) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM";
$db->setQuery($query);
$db->query();

/* ADD INDEX ON FIELD UPDATED TO SESSION TABLE - START */
$query = "SHOW INDEX FROM `#__configbox_session` WHERE `Key_name` = 'updated'";
$db->setQuery($query);
$indices = $db->loadAssocList();

if (!$indices) {
	$query = "ALTER TABLE `#__configbox_session` ADD INDEX (  `updated` )";
	$db->setQuery($query);
	$db->query();
	
}
/* ADD INDEX ON FIELD UPDATED TO SESSION TABLE - END */


/* ADD PRIMARY KEY FIELD ID TO CONFIGBOX CONFIG TABLE - START */
$query = "SHOW INDEX FROM `#__configbox_config` WHERE `Key_name` = 'PRIMARY'";
$db->setQuery($query);
$indices = $db->loadAssocList();

if (count($indices) == 0) {
	$query = "ALTER TABLE `#__configbox_config` ADD PRIMARY KEY (`id`)";
	$db->setQuery($query);
	$db->query();
	
}
/* ADD PRIMARY KEY FIELD ID TO CONFIGBOX CONFIG TABLE - END */


/* ADD PRIMARY KEY FIELD ID TO ORDER MANAGEMENT CONFIG TABLE - START */
$query = "SHOW INDEX FROM `#__cbcheckout_config` WHERE `Key_name` = 'PRIMARY'";
$db->setQuery($query);
$indices = $db->loadAssocList();

if (count($indices) == 0) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD PRIMARY KEY (`id`)";
	$db->setQuery($query);
	$db->query();
}
/* ADD PRIMARY KEY FIELD ID TO ORDER MANAGEMENT CONFIG TABLE - END */



/* ADD INDICES TO ORDER MANAGEMENT TAX CLASS RATES - START */
$query = "SHOW INDEX FROM `#__cbcheckout_tax_class_rates` WHERE `Key_name` = 'unq_tax_class_rate_country'";
$db->setQuery($query);
$indices = $db->loadAssocList();
if ($indices) {
	
	$query = "ALTER TABLE `#__cbcheckout_tax_class_rates` DROP INDEX unq_tax_class_rate_state";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_tax_class_rates` DROP INDEX unq_tax_class_rate_country";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_tax_class_rates` DROP INDEX unq_tax_class_rate_zone";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_tax_class_rates` ADD UNIQUE `unq_class_zone_state_country` (  `tax_class_id` ,  `zone_id` ,  `state_id` ,  `country_id` )";
	$db->setQuery($query);
	$db->query();
	
}
/* ADD INDICES TO ORDER MANAGEMENT TAX CLASS RATES - END */


/* ADD INDICES TO CONFIGBOX ORDERS TABLE - START */
if (ConfigboxUpdateHelper::tableExists('#__configbox_orders') == true) {
	$query = "SHOW INDEX FROM `#__configbox_orders` WHERE `Key_name` = 'grandorder_id'";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	
	if (!$indices) {
		$query = "ALTER TABLE `#__configbox_orders` ADD INDEX (  `grandorder_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE `#__configbox_orders` ADD INDEX (  `prod_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE `#__configbox_orders` ADD INDEX (  `finished` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE `#__configbox_orders` ADD INDEX (  `created` )";
		$db->setQuery($query);
		$db->query();
		
	}
}
/* ADD INDICES TO CONFIGBOX ORDERS TABLE - END */


/* CHANGE STORAGE ENGINE TO INNODB - START  */
$query = "SHOW TABLES LIKE '%configbox%'";
$db->setQuery($query);
$tables = $db->loadRowList(0);

foreach ($tables as $item) {
	$query = "ALTER TABLE `".$item[0]."` ENGINE = INNODB";
	$db->setQuery($query);
	$db->query();
}

$query = "SHOW TABLES LIKE '%cbcheckout%'";
$db->setQuery($query);
$tables = $db->loadRowList(0);

foreach ($tables as $item) {
	$query = "ALTER TABLE `".$item[0]."` ENGINE = INNODB";
	$db->setQuery($query);
	$db->query();
}
/* CHANGE STORAGE ENGINE TO INNODB - END  */

if (ConfigboxUpdateHelper::tableExists('#__configbox_orderitems') == true) {
	$query = "SHOW INDEX FROM `#__configbox_orderitems` WHERE `Key_name` = 'order_id'";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	if (!$indices) {
		$query = "ALTER TABLE `#__configbox_orderitems` ADD INDEX ( `order_id` )";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_config') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'enable_invoicing') == false ) {
		
		$query = "
		ALTER TABLE  `#__cbcheckout_config`
		ADD  `enable_invoicing` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '1',
		ADD  `invoice_generation` ENUM(  '0',  '1',  '2' ) NOT NULL DEFAULT  '0',
		ADD  `invoice_number_prefix` VARCHAR( 10 ) NOT NULL DEFAULT  '',
		ADD  `invoice_number_start` INT UNSIGNED NOT NULL DEFAULT  '1'";
		$db->setQuery($query);
		$db->query();
		
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orders') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orders', 'invoice_released') == false ) {
		$query = "ALTER TABLE `#__cbcheckout_orders` ADD  `invoice_released` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_invoices') == false) {
	$query = "
	CREATE TABLE `#__cbcheckout_invoices` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`invoice_number_prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`invoice_number_serial` int(10) unsigned NOT NULL,
	`order_id` int(10) unsigned NOT NULL,
	`file` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	`released_by` int(11) NOT NULL DEFAULT '0',
	`released_on` datetime NOT NULL COMMENT 'UTC Timing',
	`changed` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
	`original_file` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`changed_by` int(10) unsigned NOT NULL DEFAULT '0',
	`changed_on` datetime DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `order_id` (`order_id`),
	UNIQUE KEY `unique_prefix_serial` (`invoice_number_prefix`,`invoice_number_serial`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_config') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'enable_file_uploads') == false ) {
		
		$query = "ALTER TABLE `#__cbcheckout_config`
		ADD  `enable_file_uploads` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
		ADD  `allowed_file_extensions` VARCHAR( 255 ) NOT NULL DEFAULT  '',
		ADD  `allowed_file_mimetypes` VARCHAR( 255 ) NOT NULL DEFAULT  '',
		ADD  `allowed_file_size` SMALLINT UNSIGNED NOT NULL DEFAULT  '10' COMMENT  'MB'";
		$db->setQuery($query);
		$db->query();
		
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orderfiles') == false) {
	$query = "CREATE TABLE `#__cbcheckout_orderfiles` (
			 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			 `order_id` int(10) unsigned NOT NULL,
			 `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			 `comment` text COLLATE utf8_unicode_ci NOT NULL,
			 `created_on` datetime NOT NULL,
			 PRIMARY KEY (`id`),
			 KEY `order_id` (`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_users') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'salutation_id') == false ) {
	
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD  `salutation_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` CHANGE  `gender`  `gender` ENUM( '1',  '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` CHANGE  `group_id`  `group_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` CHANGE  `newsletter`  `newsletter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD  `billingsalutation_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` CHANGE  `country`  `country` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` CHANGE  `billingcountry`  `billingcountry` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `salutation_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `billingsalutation_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `group_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `country` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `state` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `billingcountry` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_users` ADD INDEX (  `billingstate` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_users` SET `salutation_id` = `gender`, `billingsalutation_id` = `billinggender`";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orderaddress') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'salutation_id') == false ) {
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD  `salutation_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD  `billingsalutation_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` CHANGE  `gender`  `gender` ENUM(  '1',  '2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` CHANGE  `group_id`  `group_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` CHANGE  `newsletter`  `newsletter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` CHANGE  `country`  `country` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` CHANGE  `billingcountry`  `billingcountry` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `salutation_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `billingsalutation_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `group_id` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `country` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `state` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `billingcountry` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD INDEX (  `billingstate` )";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_orderaddress` SET `salutation_id` = `gender`, `billingsalutation_id` = `billinggender`";
		$db->setQuery($query);
		$db->query();
		
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {

	$query = "SELECT `id` FROM `#__cbcheckout_userfields` WHERE `field_name` = 'salutation_id'";
	$db->setQuery($query);
	$hasField = $db->loadResult();

	if (!$hasField) {
		$query = "UPDATE `#__cbcheckout_userfields` SET  `field_name` =  'salutation_id' WHERE  `field_name` = 'gender' LIMIT 1";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_userfields` SET  `field_name` =  'billingsalutation_id' WHERE  `field_name` = 'billinggender' LIMIT 1";
		$db->setQuery($query);
		$db->query();
	}

}


/* CHANGE FLOATS TO DECIMALS - START */

function tableFieldNeedsDecimalChange($table, $field) {

	if (ConfigboxUpdateHelper::tableExists($table) == false) {
		return false;
	}

	if (ConfigboxUpdateHelper::tableFieldExists($table, $field) == false) {
		return false;
	}

	$fields = ConfigboxUpdateHelper::getTableFields($table);
	if (isset($fields[$field]) && stripos($fields[$field]->Type, 'decimal')) {
		return true;
	}
	else {
		return false;
	}

}

if (tableFieldNeedsDecimalChange('#__configbox_options','price')) {

	$query = "
	ALTER TABLE  `#__configbox_options`
	CHANGE  `price`  `price` VARCHAR( 50 ) NOT NULL ,
	CHANGE  `price_recurring`  `price_recurring` VARCHAR( 50 ) NOT NULL ,
	CHANGE  `weight`  `weight` VARCHAR( 50 ) NOT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "
	ALTER TABLE  `#__configbox_options`
	CHANGE  `price`  `price` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0',
	CHANGE  `price_recurring`  `price_recurring` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0',
	CHANGE  `weight`  `weight` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0'
	";
	$db->setQuery($query);
	$db->query();
	
}

if (tableFieldNeedsDecimalChange('#__configbox_calculation_tables','multiplicator')) {
	$query = "ALTER TABLE  `#__configbox_calculation_tables` CHANGE  `multiplicator`  `multiplicator` VARCHAR( 50 ) NOT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_calculation_tables` CHANGE  `multiplicator`  `multiplicator` DECIMAL( 20, 5 ) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (tableFieldNeedsDecimalChange('#__configbox_elements','multiplicator')) {
	
	$query = "
	ALTER TABLE  `#__configbox_elements`
	CHANGE  `multiplicator`  `multiplicator` VARCHAR( 50 ) NOT NULL ,
	CHANGE  `upload_size_mb`  `upload_size_mb` VARCHAR( 50 ) NOT NULL DEFAULT  '1',
	CHANGE  `slider_steps`  `slider_steps` VARCHAR( 50 ) NOT NULL DEFAULT  '1'
	";
	$db->setQuery($query);
	$db->query();
	
	$query = "
	ALTER TABLE  `#__configbox_elements`
	CHANGE  `multiplicator`  `multiplicator` DECIMAL( 20, 5 ) NOT NULL ,
	CHANGE  `upload_size_mb`  `upload_size_mb` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT  '1',
	CHANGE  `slider_steps`  `slider_steps` DECIMAL( 20, 5 ) UNSIGNED NOT NULL DEFAULT  '1'
	";
	$db->setQuery($query);
	$db->query();
	
}

if (tableFieldNeedsDecimalChange('#__configbox_products','baseprice')) {
	$query = "
	ALTER TABLE  `#__configbox_products`
	CHANGE  `baseprice`  `baseprice` VARCHAR( 50 ) NOT NULL ,
	CHANGE  `baseprice_recurring`  `baseprice_recurring` VARCHAR( 50 ) NOT NULL ,
	CHANGE  `baseweight`  `baseweight` VARCHAR( 50 ) NOT NULL
	";
	$db->setQuery($query);
	$db->query();
	
	$query = "
	ALTER TABLE  `#__configbox_products`
	CHANGE  `baseprice`  `baseprice` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
	CHANGE  `baseprice_recurring`  `baseprice_recurring` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0',
	CHANGE  `baseweight`  `baseweight` DECIMAL( 20, 3 ) UNSIGNED NOT NULL DEFAULT  '0'
	";
	$db->setQuery($query);
	$db->query();
}

if (tableFieldNeedsDecimalChange('#__configbox_tables','value')) {
	$query = "ALTER TABLE  `#__configbox_tables` CHANGE  `value`  `value` VARCHAR( 50 ) NOT NULL";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_tables` CHANGE  `value`  `value` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippingrates') == true) {

	if (tableFieldNeedsDecimalChange('#__cbcheckout_shippingrates','price')) {
		$query = "
		ALTER TABLE  `#__cbcheckout_shippingrates`
		CHANGE  `minweight`  `minweight` VARCHAR( 50 ) NOT NULL ,
		CHANGE  `maxweight`  `maxweight` VARCHAR( 50 ) NOT NULL ,
		CHANGE  `price`  `price` VARCHAR( 50 ) NOT NULL";
		$db->setQuery($query);
		$db->query();

		$query = "
		ALTER TABLE  `#__cbcheckout_shippingrates`
		CHANGE  `minweight`  `minweight` DECIMAL( 20, 4 ) UNSIGNED NOT NULL DEFAULT  '0',
		CHANGE  `maxweight`  `maxweight` DECIMAL( 20, 4 ) UNSIGNED NOT NULL DEFAULT  '0',
		CHANGE  `price`  `price` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0'
		";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions') == true) {

	if (tableFieldNeedsDecimalChange('#__cbcheckout_paymentoptions', 'price')) {
		$query = "
		ALTER TABLE  `#__cbcheckout_paymentoptions` CHANGE  `price`  `price` VARCHAR( 50 ) NOT NULL ,
		CHANGE  `percentage`  `percentage` VARCHAR( 50 ) NOT NULL ,
		CHANGE  `price_min`  `price_min` VARCHAR( 50 ) NOT NULL ,
		CHANGE  `price_max`  `price_max` VARCHAR( 50 ) NOT NULL
		";
		$db->setQuery($query);
		$db->query();

		$query = "
		ALTER TABLE  `#__cbcheckout_paymentoptions`
		CHANGE  `price`  `price` DECIMAL( 20, 3 ) NOT NULL ,
		CHANGE  `percentage`  `percentage` DECIMAL( 10, 3 ) NOT NULL ,
		CHANGE  `price_min`  `price_min` DECIMAL( 20, 3 ) UNSIGNED NOT NULL ,
		CHANGE  `price_max`  `price_max` DECIMAL( 20, 3 ) UNSIGNED NOT NULL
		";
		$db->setQuery($query);
		$db->query();
	}

}

/* CHANGE FLOATS TO DECIMAL - END */

if (ConfigboxUpdateHelper::tableExists('#__configbox_groups') == false) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_user_groups', 'quotation_download') == false) {

		$query = "
	ALTER TABLE  `#__cbcheckout_user_groups` 
	ADD  `quotation_download` 	ENUM(  '0',  '1' ) NOT NULL DEFAULT  '1',
	ADD  `quotation_email` 		ENUM(  '0',  '1' ) NOT NULL DEFAULT  '1'";
		$db->setQuery($query);
		$db->query();

		if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_config') && ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'autoquote')) {
			$query = "SELECT `autoquote` FROM `#__cbcheckout_config` WHERE `id` = 1";
			$db->setQuery($query);
			$value = $db->loadResult();
		}
		else {
			$value = 1;
		}

		$query = "UPDATE `#__cbcheckout_user_groups` SET `quotation_download` = '".intval($value)."', `quotation_email` = '1' ";
		$db->setQuery($query);
		$db->query();

	}

}


if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','autoquote') == true) {
	$query = "ALTER TABLE `#__cbcheckout_config` DROP `autoquote`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','quotewithpricing') == true) {
	$query = "ALTER TABLE `#__cbcheckout_config` DROP `quotewithpricing`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_quotations') == false) {

	$query = "
	CREATE TABLE `#__cbcheckout_quotations` (
	 `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	 `created_on` datetime DEFAULT NULL COMMENT 'UTC',
	 `created_by` int(10) unsigned NOT NULL DEFAULT '0',
	 `file` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	 PRIMARY KEY (`order_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_salutations') == false && ConfigboxUpdateHelper::tableExists('#__configbox_salutations') == false) {
	
	$query = "CREATE TABLE `#__cbcheckout_salutations` (
	`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`gender` enum('1','2') NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$db->setQuery($query);
	$db->query();
	
	$query = "INSERT INTO `#__cbcheckout_salutations` (`gender`) VALUES ('1'), ('2')";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_active_languages`";
	$db->setQuery($query);
	$tags = $db->loadResultList();

	$query = "INSERT INTO `#__cbcheckout_strings` (`type`, `key`, `lang_id`, `language_tag`, `text`) VALUES ";
	$values = array();
	
	foreach ($tags as $tag) {
		$values[] = "( 55, 1, 0, '".$db->getEscaped($tag)."', 'Mr.' ), ( 55, 2, 0, '".$db->getEscaped($tag)."', 'Mrs.' )";
	}
	
	if (count($values)) {
		$query .= implode(",\n",$values);
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_currencies') == false) {
	
	$query = "ALTER TABLE  `#__configbox_currencies` CHANGE  `multiplicator`  `multiplicator` VARCHAR( 100 ) NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE  `#__configbox_currencies` CHANGE  `multiplicator`  `multiplicator` DECIMAL( 10, 5 ) UNSIGNED NOT NULL DEFAULT  '1'";
	$db->setQuery($query);
	$db->query();
	
	$query = "
	CREATE TABLE `#__cbcheckout_order_currencies` (
			`id` mediumint(8) unsigned NOT NULL,
			`order_id` int(10) unsigned NOT NULL,
			`base` tinyint(1) NOT NULL DEFAULT '0',
			`multiplicator` decimal(10,5) unsigned NOT NULL DEFAULT '1.00000',
			`symbol` varchar(10) NOT NULL DEFAULT '',
			`code` varchar(10) NOT NULL DEFAULT '',
			`default` tinyint(1) NOT NULL DEFAULT '0',
			`ordering` mediumint(9) NOT NULL DEFAULT '0',
			`published` tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`,`order_id`),
			KEY `code` (`code`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
	";
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'language_id') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD  `language_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0', ADD INDEX (  `language_id` )";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'language_id') == false) {
	$query = "ALTER TABLE  `#__cbcheckout_users` ADD  `language_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0', ADD INDEX (  `language_id` )";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'billinglanguage') == true) {
	
	$query = "UPDATE `#__cbcheckout_users` SET `language_id` = `billinglanguage`";
	$db->setQuery($query);
	$reponses[] = $db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_users` DROP `billinglanguage`, DROP `language`";
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'billinglanguage') == true) {
	
	$query = "UPDATE `#__cbcheckout_orderaddress` SET `language_id` = `billinglanguage`";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_orderaddress` DROP `billinglanguage`, DROP `language`";
	$db->setQuery($query);
	$db->query();
	
}

// Use only language_id for user fields, get settings from billinglanguage, drop delivery language
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {
	$query = "UPDATE `#__cbcheckout_userfields` SET `field_name` = 'language_id' WHERE `field_name` = 'billinglanguage' LIMIT 1";
	$db->setQuery($query);
	$db->query();
	
	$query = "DELETE FROM `#__cbcheckout_userfields` WHERE `field_name` = 'language' LIMIT 1";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_availablein') == true) {
	
	// Set unique index to cb strings
	$query = "SHOW INDEX FROM `#__cbcheckout_availablein`";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	if ($indices) {
		foreach ($indices as $index) {
			if ($index['Key_name'] == 'shippers/countries') {
				$query = "ALTER TABLE `#__cbcheckout_availablein` DROP INDEX  `shippers/countries` , ADD PRIMARY KEY ( `country_id`, `payment_id` )";
				$db->setQuery($query);
				$db->query();
				break;
			}
		}
	}
	
	
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_xref_country_payment_option') == false) {
	$query = "RENAME TABLE `#__cbcheckout_availablein` TO `#__cbcheckout_xref_country_payment_option`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_country_zone') == true) {
	
	// Set unique index to cb strings
	$query = "SHOW INDEX FROM `#__cbcheckout_country_zone`";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	if ($indices) {
		foreach ($indices as $index) {
			if ($index['Key_name'] == 'zone-country') {
				$query = "ALTER TABLE `#__cbcheckout_country_zone` DROP INDEX  `zone-country` , ADD PRIMARY KEY (  `country_id`, `zone_id` )";
				$db->setQuery($query);
				$db->query();
				break;
			}
		}
	}
	
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_country_zone') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_xref_country_zone') == false) {
	$query = "ALTER TABLE `#__cbcheckout_country_zone` RENAME `#__cbcheckout_xref_country_zone`";
	$db->setQuery($query);
	$db->query();
}

$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_configurations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` int(10) unsigned NOT NULL DEFAULT '0',
  `price_net` decimal(20,3) NOT NULL DEFAULT '0.000',
  `price_recurring_net` decimal(20,3) NOT NULL DEFAULT '0.000',
  `element_id` int(10) unsigned NOT NULL DEFAULT '0',
  `element_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `weight` decimal(20,3) NOT NULL DEFAULT '0.000',
  `xref_id` int(10) unsigned NOT NULL DEFAULT '0',
  `option_id` int(10) unsigned NOT NULL DEFAULT '0',
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `output_value` text COLLATE utf8_unicode_ci NOT NULL,
  `element_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `option_sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `option_image` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `show_in_overviews` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `element_custom_1` text COLLATE utf8_unicode_ci NOT NULL,
  `element_custom_2` text COLLATE utf8_unicode_ci NOT NULL,
  `element_custom_3` text COLLATE utf8_unicode_ci NOT NULL,
  `element_custom_4` text COLLATE utf8_unicode_ci NOT NULL,
  `assignment_custom_1` text COLLATE utf8_unicode_ci NOT NULL,
  `assignment_custom_2` text COLLATE utf8_unicode_ci NOT NULL,
  `assignment_custom_3` text COLLATE utf8_unicode_ci NOT NULL,
  `assignment_custom_4` text COLLATE utf8_unicode_ci NOT NULL,
  `option_custom_1` text COLLATE utf8_unicode_ci NOT NULL,
  `option_custom_2` text COLLATE utf8_unicode_ci NOT NULL,
  `option_custom_3` text COLLATE utf8_unicode_ci NOT NULL,
  `option_custom_4` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `element_id` (`element_id`),
  KEY `xref_id` (`xref_id`),
  KEY `position_id` (`position_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
$db->setQuery($query);
$db->query();
	
$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_currencies` (
  `id` mediumint(8) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `multiplicator` decimal(10,5) unsigned NOT NULL DEFAULT '1.00000',
  `symbol` varchar(10) NOT NULL DEFAULT '',
  `code` varchar(10) NOT NULL DEFAULT '',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` mediumint(9) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`order_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_delivery_options') == false && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_shipping_methods') == false) {
	$query = "
	CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_delivery_options` (
	  `order_id` int(10) unsigned NOT NULL,
	  `id` mediumint(8) unsigned NOT NULL,
	  `shipper_id` mediumint(8) unsigned NOT NULL,
	  `zone_id` mediumint(8) unsigned NOT NULL,
	  `minweight` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000',
	  `maxweight` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000',
	  `deliverytime` mediumint(9) NOT NULL,
	  `price` decimal(20,3) NOT NULL DEFAULT '0.000',
	  `taxclass_id` mediumint(8) unsigned NOT NULL,
	  PRIMARY KEY (`order_id`,`id`),
	  KEY `minweight` (`minweight`),
	  KEY `maxweight` (`maxweight`),
	  KEY `price` (`price`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$db->setQuery($query);
	$db->query();
}
	
$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_payment_options` (
  `order_id` int(11) NOT NULL DEFAULT '0',
  `id` mediumint(8) unsigned NOT NULL,
  `class` varchar(100) NOT NULL DEFAULT '',
  `price` decimal(20,3) NOT NULL DEFAULT '0.000',
  `taxclass_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `params` varchar(255) NOT NULL DEFAULT '',
  `ordering` mediumint(9) NOT NULL DEFAULT '0',
  `percentage` decimal(20,3) NOT NULL DEFAULT '0.000',
  `price_min` decimal(20,3) NOT NULL DEFAULT '0.000',
  `price_max` decimal(20,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`order_id`,`id`),
  KEY `index_price` (`price`),
  KEY `index_price_max` (`price_max`),
  KEY `index_price_min` (`price_min`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();

$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_positions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `product_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `product_image` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quantity` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `weight` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `taxclass_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `taxclass_recurring_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `product_base_price_net` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `product_base_price_recurring_net` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `price_net` decimal(20,3) NOT NULL DEFAULT '0.000',
  `price_recurring_net` decimal(20,3) NOT NULL DEFAULT '0.000',
  `open_amount_net` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT 'the amount left after deposit payment',
  `using_deposit` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `dispatch_time` TINYINT UNSIGNED NOT NULL DEFAULT  '0',
  `product_custom_1` text COLLATE utf8_unicode_ci NOT NULL,
  `product_custom_2` text COLLATE utf8_unicode_ci NOT NULL,
  `product_custom_3` text COLLATE utf8_unicode_ci NOT NULL,
  `product_custom_4` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
$db->setQuery($query);
$db->query();

$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `delivery_id` int(10) unsigned NOT NULL DEFAULT '0',
  `payment_id` int(10) unsigned NOT NULL DEFAULT '0',
  `grandorder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime DEFAULT NULL COMMENT 'UTC',
  `paid` tinyint(4) NOT NULL DEFAULT '0',
  `paid_on` datetime DEFAULT NULL,
  `status` smallint(5) unsigned NOT NULL DEFAULT '0',
  `invoice_released` ENUM(  '0',  '1' ) NOT NULL DEFAULT '0',
  `comment` TEXT NOT NULL DEFAULT '',
  `custom_1` TEXT NOT NULL ,
  `custom_2` TEXT NOT NULL ,
  `custom_3` TEXT NOT NULL ,
  `custom_4` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grandorder_id` (`grandorder_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
$db->setQuery($query);
$db->query();
	
$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_strings` (
  `order_id` int(10) unsigned NOT NULL DEFAULT '0',
  `table` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` int(5) unsigned NOT NULL,
  `key` bigint(20) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`order_id`,`table`,`type`,`key`,`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();
	
$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_tax_class_rates` (
  `order_id` int(10) unsigned NOT NULL,
  `tax_class_id` mediumint(8) unsigned NOT NULL,
  `zone_id` mediumint(8) unsigned NOT NULL,
  `state_id` mediumint(8) unsigned NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `tax_rate` decimal(4,2) unsigned NOT NULL,
  `default_tax_rate` decimal(10,3) unsigned NOT NULL,
  UNIQUE KEY `unq_class_zone_state_country` (`order_id`,`tax_class_id`,`zone_id`,`state_id`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();

$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_user_groups` (
  `order_id` int(11) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `discount_start_1` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `discount_start_2` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `discount_start_3` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `discount_start_4` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `discount_start_5` decimal(20,3) unsigned NOT NULL DEFAULT '0.000',
  `discount_factor_1` decimal(20,3) NOT NULL DEFAULT '0.000',
  `discount_factor_2` decimal(20,3) NOT NULL DEFAULT '0.000',
  `discount_factor_3` decimal(20,3) NOT NULL DEFAULT '0.000',
  `discount_factor_4` decimal(20,3) NOT NULL DEFAULT '0.000',
  `discount_factor_5` decimal(20,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`order_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
$db->setQuery($query);
$db->query();

$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_xref_country_payment_option` (
  `order_id` int(10) unsigned NOT NULL,
  `payment_id` mediumint(8) unsigned NOT NULL,
  `country_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`order_id`,`country_id`,`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();

$query = "
CREATE TABLE IF NOT EXISTS `#__cbcheckout_order_xref_country_zone` (
  `order_id` int(10) unsigned NOT NULL,
  `zone_id` mediumint(8) unsigned NOT NULL,
  `country_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`order_id`,`country_id`,`zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$db->setQuery($query);
$db->query();


$query = "DROP TABLE IF EXISTS `#__cbcheckout_statistics`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_strings', 'language_tag') == false) {

	$query = "ALTER TABLE `#__cbcheckout_order_strings` ADD `language_tag` CHAR( 5 ) NOT NULL DEFAULT  '' AFTER  `lang_id`";
	$db->setQuery($query);
	$db->query();
	
	$query = "SELECT COUNT(*) FROM `#__cbcheckout_order_strings`";
	$db->setQuery($query);
	$count = $db->loadResult();
	
	if ($count) {
		$query = "SELECT * FROM `#__configbox_languages`";
		$db->setQuery($query);
		$languages = $db->loadObjectList();
		foreach ($languages as $lang) {
			$query = "UPDATE `#__cbcheckout_order_strings` SET `language_tag` = '".$db->getEscaped($lang->tag)."' WHERE `lang_id` = ".intval($lang->id);
			$db->setQuery($query);
			$db->query();
		}
	}
	
	$query = "ALTER TABLE `#__cbcheckout_order_strings` DROP PRIMARY KEY";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_order_strings` ADD PRIMARY KEY (  `order_id` ,  `table` ,  `type` ,  `key` ,  `language_tag` )";
	$db->setQuery($query);
	$db->query();
	
	$query = "ALTER TABLE `#__cbcheckout_order_strings` DROP `lang_id`";
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enable_performance_tracking') == false) {
	$query = "ALTER TABLE  `#__configbox_config` ADD  `enable_performance_tracking` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

/* CREATE DATA SUBFOLDERS - START */
$dataFolders = array(
		'data'.DS.'cache',
		'data'.DS.'customization',
		'data'.DS.'default_images',
		'data'.DS.'downloads',
		'data'.DS.'element_images',
		'data'.DS.'file_uploads',
		'data'.DS.'opt_images',
		'data'.DS.'option_images',
		'data'.DS.'option_picker_images',
		'data'.DS.'prod_baseimages',
		'data'.DS.'prod_images',
);

foreach ($dataFolders as $dataFolder) {
	$dataFolder = KPATH_DIR_CB.DS.$dataFolder;
	if (!is_dir($dataFolder)) {
		mkdir($dataFolder,0777,true);
	}
}
/* CREATE DATA SUBFOLDERS - END */

/* COPY THE DEFAULT PRODUCT IMAGE IF NOT THERE YET - START */
$defaultImage =  KenedoPlatform::p()->getComponentDir('com_configbox').'/data/default_images/default_prod_image.jpg';
$srcFile = KPATH_DIR_ASSETS.DS.'images'.DS.'default_prod_image.jpg';

if (is_dir(dirname($defaultImage)) == false) {
	mkdir( dirname($defaultImage), 0777, true );
}

if (!is_file($defaultImage) && is_file($defaultImage)) {
	copy($srcFile, $defaultImage);
}
/* COPY THE DEFAULT PRODUCT IMAGE IF MISSING - END */

/* REPAIR INCORRECTLY CREATED LANGUAGE OVERRIDE FILES (NO .INI EXTENSION) - START */
$baseLangFolder = KenedoPlatform::p()->getDirCustomization().DS.'language_overrides';


if( is_file($baseLangFolder.DS.'frontend'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox')) {
	rename($baseLangFolder.DS.'frontend'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox', $baseLangFolder.DS.'frontend'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox.ini');
}

if( is_file($baseLangFolder.DS.'backend'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox')) {
	rename($baseLangFolder.DS.'backend'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox', $baseLangFolder.DS.'backend'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox.ini');
}

if( is_file($baseLangFolder.DS.'mod_configboxcurrencies'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox')) {
	rename($baseLangFolder.DS.'mod_configboxcurrencies'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox', $baseLangFolder.DS.'mod_configboxcurrencies'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox.ini');
}

if( is_file($baseLangFolder.DS.'mod_configboxprices'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox')) {
	rename($baseLangFolder.DS.'mod_configboxprices'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox', $baseLangFolder.DS.'mod_configboxprices'.DS.'language'.DS.'de-DE'.DS.'de-DE.com_configbox.ini');
}
/* REPAIR INCORRECTLY CREATED LANGUAGE OVERRIDE FILES (NO .INI EXTENSION) - END */

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_delivery_options')) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_delivery_options', 'external_id') == false) {
		$query = "ALTER TABLE `#__cbcheckout_order_delivery_options` ADD  `external_id` VARCHAR( 100 ) NOT NULL DEFAULT  ''";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_delivery_options', 'ordering') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_order_delivery_options` ADD `ordering` MEDIUMINT NOT NULL DEFAULT  '0', ADD INDEX (  `ordering` )";
		$db->setQuery($query);
		$db->query();
	}
}

/* CREATE THE NEW USERFIELDS COLUMNS - START */

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_userfields', 'show_saveorder') == false) {
	
		$query = "ALTER TABLE  `#__cbcheckout_userfields` 
		ADD  `show_saveorder` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0' AFTER  `require_assistance` ,
		ADD  `require_saveorder` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0' AFTER  `show_saveorder` ,
		ADD  `show_profile` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0' AFTER  `require_saveorder` ,
		ADD  `require_profile` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0' AFTER  `show_profile`";
		$db->setQuery($query);
		$db->query();
		
		
		$query = "ALTER TABLE `#__cbcheckout_userfields`
		CHANGE  `show_checkout`  `show_checkout` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
		CHANGE  `require_checkout`  `require_checkout` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
		CHANGE  `show_quotation`  `show_quotation` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
		CHANGE  `require_quotation`  `require_quotation` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
		CHANGE  `show_assistance`  `show_assistance` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
		CHANGE  `require_assistance`  `require_assistance` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE `#__cbcheckout_userfields` 
		CHANGE  `validation_browser`  `validation_browser` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '',
		CHANGE  `validation_server`    `validation_server` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  ''";
		$db->setQuery($query);
		$db->query();
		
		
		$query = "UPDATE `#__cbcheckout_userfields` SET `show_saveorder` = `show_checkout`, `require_saveorder` = `require_checkout`,  `show_profile` = `show_checkout`, `require_profile` = `require_checkout` ";
		$db->setQuery($query);
		$db->query();
		
	}

}
/* CREATE THE NEW USERFIELDS COLUMNS - START */

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'as_textarea') == false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD `as_textarea` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'was_price') == false) {
	
	$query = "
	ALTER TABLE  `#__configbox_options` 
	ADD  `was_price` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0',
	ADD  `was_price_recurring` DECIMAL( 20, 3 ) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'was_price') == false) {
	$query = "
	ALTER TABLE  `#__configbox_products`
	ADD  `was_price` DECIMAL( 20, 3 ) NOT NULL DEFAULT  '0',
	ADD  `was_price_recurring` DECIMAL( 20, 3 ) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_option_custom_5') == false) {
	$query = "
	ALTER TABLE `#__configbox_config` 
	ADD  `label_option_custom_5` VARCHAR( 100 ) NOT NULL DEFAULT  '',
	ADD  `label_option_custom_6` VARCHAR( 100 ) NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'disable_non_available') == false) {
	$query = "ALTER TABLE  `#__configbox_options` ADD  `disable_non_available` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_tables', 'calcmodel_id_multi') == false) {
	$query = "ALTER TABLE `#__configbox_calculation_tables` ADD  `calcmodel_id_multi` INT UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orders')) {

$query = "SELECT * FROM `#__cbcheckout_orders`";
$db->setQuery($query);
$orders = $db->loadObjectList('id');

$query = "SELECT `id` FROM `#__cbcheckout_order_records` LIMIT 1";
$db->setQuery($query);
$orderRecords = $db->loadResult();

if (count($orders) && !$orderRecords) {
	
	// We need that because we had serialized data with objects of that class
	if (!class_exists('ConfigBoxGrandorder')) {
		class ConfigBoxGrandorder {

		}
	}

	set_time_limit(0);
	ini_set('memory_limit',-1);

	$query = "START TRANSACTION";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_config` WHERE `id` = 1";
	$db->setQuery($query);
	$configs = $db->loadAssoc();

	if (is_array($configs)) {
		foreach ($configs as $key=>&$value) {
			if (!defined('CONFIGBOX_'.strtoupper($key))) define('CONFIGBOX_'.strtoupper($key),$value);
			// MERGELECACY Legacy configuration constants - REMOVE WITH CB 4.0
			if (!defined('CBCHECKOUT_'.strtoupper($key))) define('CBCHECKOUT_'.strtoupper($key),$value);
		}
	}

	// If no language tag was supplied, fall back to system language
	$languageTag = KenedoPlatform::p()->getLanguageTag();

	// Get active languages (from CB settings)
	$activeLanguages = KenedoLanguageHelper::getActiveLanguageTags();

	// If the requested language isn't an active CB language (see settings), then fall back to default language
	if (in_array($languageTag, $activeLanguages) == false) {
		$languageTag = CbSettings::getInstance()->get('language_tag');
	}

	// Have KText receive the language
	KText::setLanguage($languageTag);

	$cartModel = KenedoModel::getModel('ConfigboxModelCart');

	foreach ($orders as $orderId=>$order) {

		$originalOrderTotal = $order->products_net;
		$originalOrderTotalRecurring = $order->products_recurring_net;

		$orderRecord = new stdClass();
		$orderRecord->id = $order->id;
		$orderRecord->user_id = $order->user_id;
		$orderRecord->delivery_id = $order->delivery_id;
		$orderRecord->payment_id = $order->payment_id;
		$orderRecord->grandorder_id = $order->grandorder_id;
		$orderRecord->store_id = $order->store_id;
		$orderRecord->created_on = $order->sent_time;
		$orderRecord->paid = (in_array($order->status, array( 3 )));
		$orderRecord->paid_on = ($orderRecord->paid) ? $orderRecord->created_on : NULL;
		$orderRecord->status = $order->status;
			
		$succ = $db->insertObject('#__cbcheckout_order_records',$orderRecord,'id');
			
		if (!$succ) {
			$errorMsg = $db->getErrorMsg();
			$query = "ROLLBACK";
			$db->setQuery($query);
			$db->query();
			KLog::log('Error inserting order record. SQL error is "'.$errorMsg.'"','error','Error on updating order records. See log for more info');
		}
			
		/* COPY OVER THE COUNTRY ZONE XREFS - START */
		if (!isset($zoneCountries)) {
			$query = "SELECT * FROM `#__cbcheckout_xref_country_zone`";
			$db->setQuery($query);
			$zoneCountries = $db->loadObjectList();
		}
		$query = "
		INSERT INTO `#__cbcheckout_order_xref_country_zone`
		(`order_id`, `zone_id`, `country_id`)
		VALUES
		";
		$values = array();
		foreach ($zoneCountries as $item) {
			$values[] = "('".intval($orderRecord->id)."', '".intval($item->zone_id)."', '".intval($item->country_id)."')";
		}
		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting order country/zone records. SQL error is "'.$errorMsg.'"','error','Error inserting order country/zone records. See log for more info');
			}
		}
		/* COPY OVER THE COUNTRY ZONE XREFS - END */

		/* COPY OVER THE PAYMENT OPTION - COUNTRY XREFS - START */
		if (!isset($paymentCountries)) {
			$query = "
			SELECT xref.* FROM `#__cbcheckout_xref_country_payment_option` AS xref
			LEFT JOIN `#__cbcheckout_paymentoptions` AS p ON p.id = xref.payment_id
			WHERE p.published = '1'
			";
			$db->setQuery($query);
			$paymentCountries = $db->loadObjectList();
		}
		$query = "
		INSERT INTO `#__cbcheckout_order_xref_country_payment_option`
		(`order_id`, `payment_id`, `country_id`)
		VALUES
		";
		$values = array();
		foreach ($paymentCountries as $item) {
			$values[] = "('".intval($orderRecord->id)."', '".intval($item->payment_id)."', '".intval($item->country_id)."')";
		}
		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting order country/payment records. SQL error is "'.$errorMsg.'"','error','Error inserting order country/payment records. See log for more info');
			}
		}
		/* COPY OVER THE PAYMENT OPTION - COUNTRY XREFS - END */


		/* COPY OVER THE CURRENCY RATE INFORMATION - START */
			
		if (!isset($currencies)) {
			$query = "SELECT * FROM `#__configbox_currencies` WHERE `published` = '1' ORDER BY `ordering`";
			$db->setQuery($query);
			$currencies = $db->loadObjectList();
		}
			
		$query = "
		INSERT INTO `#__cbcheckout_order_currencies`
		(`order_id`, `id`, `base`, `multiplicator`, `symbol`, `code`, `default`, `ordering`, `published`)
		VALUES
		";
			
		$values = array();
		foreach ($currencies as $item) {
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 6, $item->id);
			$values[] = "('".$orderRecord->id."', '".$item->id."', '".intval($item->base)."', '".floatval($item->multiplicator)."', '".$db->getEscaped($item->symbol)."', '".$db->getEscaped($item->code)."', '".intval($item->default)."', '".intval($item->ordering)."', '".intval($item->ordering)."' )";
		}
			
		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting order currency records. SQL error is "'.$errorMsg.'"','error','Error inserting order currency records. See log for more info');
			}
		}
		/* COPY OVER THE CURRENCY RATE INFORMATION - END */


		/* COPY OVER THE TAX RATE INFORMATION - START */
			
		if (!isset($currentTaxClasses)) {
			$query = "
			SELECT tc.default_tax_rate, tcr.*, tc.id AS tax_class_id
			FROM `#__configbox_tax_classes` AS tc
			LEFT JOIN `#__cbcheckout_tax_class_rates` AS tcr ON tc.id = tcr.tax_class_id";
			$db->setQuery($query);
			$currentTaxClasses = $db->loadObjectList();

			if ($db->getErrorMsg()) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error loading tax class rates for order insert. SQL error is "'.$errorMsg.'"','error','Error loading tax class rates for order insert. See log for more info');
			}
		}
			
		$query = "
		INSERT INTO `#__cbcheckout_order_tax_class_rates`
		(`order_id`, `tax_class_id`, `zone_id`, `state_id`, `country_id`, `tax_rate`, `default_tax_rate`)
		VALUES
		";
			
		$values = array();
		foreach ($currentTaxClasses as $taxClass) {
			$values[] = "('".intval($orderRecord->id)."', '".intval($taxClass->tax_class_id)."', '".floatval($taxClass->zone_id)."', '".floatval($taxClass->state_id)."', '".floatval($taxClass->country_id)."', '".floatval($taxClass->tax_rate)."', '".floatval($taxClass->default_tax_rate)."' )";
		}
			
		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting order tax rate records. SQL error is "'.$errorMsg.'"','error','Error inserting order tax rate records. See log for more info');
			}
		}
		/* COPY OVER THE TAX RATE INFORMATION - END */


		/* GET THE ORDER DETAILS - START */
		if ($order->description) {
			$orderDetails = unserialize($order->description);
		}
		else {
			$orderDetails = $cartModel->getCartDetails($order->grandorder_id);
		}
		/* GET THE ORDER DETAILS - END */

		/* COPY OVER THE USER GROUP INFORMATION - START */
		if (!isset($allGroups)) {
			$query = "SELECT * FROM `#__cbcheckout_user_groups`";
			$db->setQuery($query);
			$allGroups = $db->loadObjectList();
		}

		$query = "
		INSERT INTO `#__cbcheckout_order_user_groups`
		(`order_id`, `group_id`, `discount_start_1`, `discount_start_2`, `discount_start_3`, `discount_start_4`, `discount_start_5`, `discount_factor_1`, `discount_factor_2`, `discount_factor_3`, `discount_factor_4`, `discount_factor_5`)
		VALUES
		";
		$values = array();
		foreach ($allGroups as $group) {
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 50, $group->id);
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 51, $group->id);
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 52, $group->id);
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 53, $group->id);
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 54, $group->id);

			$values[] = "('".intval($orderRecord->id)."', '".intval($group->id)."', '".floatval($group->discount_start_1)."', '".floatval($group->discount_start_2)."', '".floatval($group->discount_start_3)."', '".floatval($group->discount_start_4)."', '".floatval($group->discount_start_5)."', '".floatval($group->discount_factor_1)."', '".floatval($group->discount_factor_2)."', '".floatval($group->discount_factor_3)."', '".floatval($group->discount_factor_4)."', '".floatval($group->discount_factor_5)."')";
		}

		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting order discount records. SQL error is "'.$errorMsg.'"','error','Error inserting order discount records. See log for more info');
			}
		}
		/* COPY OVER THE USER GROUP INFORMATION - END */


		/* IN CASE THE DISCOUNT PERCENTAGE IS OUTDATED, OVERWRITE IT WITH THE ONE IN THE ORDER DETAILS - START */
		if (isset($orderDetails->discount->user_group)) {
			$query = "
			REPLACE INTO `#__cbcheckout_order_user_groups`
			(`order_id`, `group_id`, `discount_start_1`, `discount_start_2`, `discount_start_3`, `discount_start_4`, `discount_start_5`, `discount_factor_1`, `discount_factor_2`, `discount_factor_3`, `discount_factor_4`, `discount_factor_5`)
			VALUES
			('".intval($orderRecord->id)."', '".intval($orderDetails->discount->user_group->id)."', '".floatval($orderDetails->discount->user_group->discount_start_1)."', '".floatval($orderDetails->discount->user_group->discount_start_2)."', '".floatval($orderDetails->discount->user_group->discount_start_3)."', '".floatval($orderDetails->discount->user_group->discount_start_4)."', '".floatval($orderDetails->discount->user_group->discount_start_5)."', '".floatval($orderDetails->discount->user_group->discount_factor_1)."', '".floatval($orderDetails->discount->user_group->discount_factor_2)."', '".floatval($orderDetails->discount->user_group->discount_factor_3)."', '".floatval($orderDetails->discount->user_group->discount_factor_4)."', '".floatval($orderDetails->discount->user_group->discount_factor_5)."' )
			";
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error replacing current user group record. SQL error is "'.$errorMsg.'"','error','Error replacing current user group record. See log for more info');
			}
		}
		/* IN CASE THE DISCOUNT PERCENTAGE IS OUTDATED, OVERWRITE IT WITH THE ONE IN THE ORDER DETAILS - END */


		/* COPY OVER THE DELIVERY RECORDS - START */
		if (!isset($shippingRates)) {
			$query = "SELECT * FROM `#__cbcheckout_shippingrates` WHERE `published` = '1'";
			$db->setQuery($query);
			$shippingRates = $db->loadObjectList();
		}
		$query = "
		INSERT INTO `#__cbcheckout_order_delivery_options`
		(`order_id`, `id`, `shipper_id`, `zone_id`, `minweight`, `maxweight`, `deliverytime`, `price`, `taxclass_id`,`ordering`)
		VALUES
		";
		$values = array();

		foreach ($shippingRates as $item) {

			$succ = ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 3, $item->id);

			$values[] = "('".intval($orderRecord->id)."', '".intval($item->id)."', '".intval($item->shipper)."', '".intval($item->zone)."', '".floatval($item->minweight)."', '".floatval($item->maxweight)."', '".intval($item->deliverytime)."', '".floatval($item->price)."', '".intval($item->taxclass_id)."', '".intval($item->ordering)."')";
		}

		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting shipping rate records. SQL error is "'.$errorMsg.'"','error','Error inserting shipping rate records. See log for more info');
			}
		}
		/* COPY OVER THE DELIVERY RECORDS - END */


		/* COPY OVER THE PAYMENT RECORDS - START */
		if (!isset($paymentOptions)) {
			$query = "SELECT * FROM `#__cbcheckout_paymentoptions` WHERE `published` = '1'";
			$db->setQuery($query);
			$paymentOptions = $db->loadObjectList();
		}
		$query = "
		INSERT INTO `#__cbcheckout_order_payment_options`
		(`order_id`, `id`, `class`, `price`, `taxclass_id`, `params`, `ordering`, `percentage`, `price_min`, `price_max`)
		VALUES
		";

		$values = array();
		foreach ($paymentOptions as $item) {
			$succ = ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 5, $item->id);
			$succ = ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__cbcheckout_strings', 6, $item->id);
			$values[] = "('".intval($orderRecord->id)."', '".intval($item->id)."', '".$db->getEscaped($item->class)."', '".floatval($item->price)."', '".intval($item->taxclass_id)."', '".$db->getEscaped($item->params)."', '".intval($item->ordering)."', '".floatval($item->percentage)."', '".floatval($item->price_min)."', '".floatval($item->price_max)."' )";
		}

		if (count($values)) {
			$query .= implode(",\n",$values);
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting payment method records. SQL error is "'.$errorMsg.'"','error','Error inserting payment method records. See log for more info');
			}
		}
		/* COPY OVER THE PAYMENT RECORDS - END */


		if (!isset($orderDetails->orders) || !is_array($orderDetails->orders)) {
			$orderDetails->orders = array();
		}

		$orderTotalNet = 0;
		$orderTotalRecurringNet = 0;

		foreach ($orderDetails->orders as $gorder) {

			/* INSERT EACH POSITION - START */
			$position = new stdClass();
			$position->order_id 		= $orderRecord->id;
			$position->product_id 		= $gorder->productData->id;
			$position->product_sku 		= $gorder->productData->sku;
			$position->product_image 	= '';
			$position->quantity 		= $gorder->quantity;
			$position->weight 			= $gorder->weight;
			$position->dispatch_time 	= !empty($gorder->productData->dispatch_time) ? $gorder->productData->dispatch_time : 0;

			if (empty($gorder->productData->taxclass_id)) {

				$query = "SELECT taxrate, taxclass_id, taxclass_recurring_id FROM `#__configbox_products` WHERE `id` = ".(int)$gorder->productData->id;
				$db->setQuery($query);
				$values = $db->loadObject();

				$position->taxclass_id 				= $values->taxclass_id;
				$position->taxclass_recurring_id 	= $values->taxclass_recurring_id;
			}
			else {
				$position->taxclass_id 				= $gorder->productData->taxclass_id;
				$position->taxclass_recurring_id 	= $gorder->productData->taxclass_recurring_id;
			}

			$position->product_base_price_net 			= $gorder->productData->basePriceNet;
			$position->product_base_price_recurring_net = $gorder->productData->basePriceRecurringNet;

			if (!empty($gorder->baseDepositNet)) {
				$position->price_net = $gorder->basePayableNet;
				$position->price_recurring_net = $gorder->baseTotalRecurringNet;
				$position->open_amount_net = $gorder->baseTotalNet - $gorder->baseDepositNet;
				$position->using_deposit = ($position->open_amount_net) ? 1 : 0;
			}
			else {
				$position->price_net = $gorder->baseTotalNet;
				$position->price_recurring_net = $gorder->baseTotalRecurringNet;
				$position->open_amount_net = 0;
				$position->using_deposit = 0;
			}

			if (isset($gorder->productData->product_custom_1)) {
				$position->product_custom_1 = $gorder->productData->product_custom_1;
				$position->product_custom_2 = $gorder->productData->product_custom_2;
				$position->product_custom_3 = $gorder->productData->product_custom_3;
				$position->product_custom_4 = $gorder->productData->product_custom_4;
			}
			else {
				$position->product_custom_1 = '';
				$position->product_custom_2 = '';
				$position->product_custom_3 = '';
				$position->product_custom_4 = '';
			}

			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings',  1, $gorder->productData->id); // Product Title
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 11, $gorder->productData->id); // Product Description
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 25, $gorder->productData->id); // Interval
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 26, $gorder->productData->id); // Price Label
			ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 31, $gorder->productData->id); // Price Recurring Label

			$succ = $db->insertObject('#__cbcheckout_order_positions',$position,'id');

			if (!$succ) {
				$errorMsg = $db->getErrorMsg();
				$query = "ROLLBACK";
				$db->setQuery($query);
				$db->query();
				KLog::log('Error inserting order position record. SQL error is "'.$errorMsg.'"','error','Error on inserting order position record. See log for more info');
			}

			/* INSERT EACH POSITION - END */

			/* COPY THE PRODUCT IMAGE - START */
			$productImage = $oldStoreFolder .DS. 'prod_images' .DS. $gorder->productData->prod_image;

			if (!empty($gorder->productData->prod_image) && is_file( $productImage )) {
				// Written out without use of constants because at this point we do not have those constants ready (and it does not matter because the folder wasn't flexible at this point)

				$folder = $oldCustomerFolder.DS.'position_images';
				if (!is_dir($folder)) {
					$succ = mkdir($folder,0755,true);
					if (!$succ) {
						$query = "ROLLBACK";
						$db->setQuery($query);
						$db->query();
						KLog::log('Error copying product image','error','Error copying product image. Make sure the folder "'.$folder.'" is writable');
					}
				}
				$fileName = $orderRecord->id.'-'.$position->id.'.'.pathinfo($productImage, PATHINFO_EXTENSION);
				$succ = copy($productImage,$folder.DS.$fileName);
				if (!$succ) {
					$query = "ROLLBACK";
					$db->setQuery($query);
					$db->query();
					KLog::log('Error copying product image','error','Error copying product image. Make sure the folder "'.$folder.'" is writable');
				}
				else {
					$position->product_image = $fileName;
					$succ = $db->updateObject('#__cbcheckout_order_positions',$position,'id');
				}
			}
			/* COPY THE PRODUCT IMAGE - END */

			if (isset($gorder->elements) && is_array($gorder->elements)) {

				/* INSERT EACH ELEMENT - START */
				foreach ($gorder->elements as $element) {

					$configuration = new stdClass();
					$configuration->position_id = $position->id;

					if (isset($element->selection->basePriceNet)) {
						$configuration->price_net 			= $element->selection->basePriceNet 			* $position->quantity;
						$configuration->price_recurring_net = $element->selection->basePriceRecurringNet 	* $position->quantity;
					}
					elseif( isset($element->basePriceNet) ) {
						$configuration->price_net = $element->basePriceNet 						* $position->quantity;
						$configuration->price_recurring_net = $element->basePriceRecurringNet 	* $position->quantity;
					}
					else {
						$configuration->price_net = 0;
						$configuration->price_recurring_net = 0;
					}
					
					$configuration->element_id = $element->id;
					$configuration->element_type = $element->type;
					$configuration->value = $element->selection->value;
					$configuration->output_value = $element->selection->outputValue;
					$configuration->element_code = '';

					if (isset($element->weight)) {
						$configuration->weight = $element->weight;
					}
					else {
						$configuration->weight = 0;
					}

					ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings',  4, $configuration->element_id); // Element Title
					ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 14, $configuration->element_id); // Element Description
					ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 50, $configuration->element_id); // Element Translatable 1
					ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 51, $configuration->element_id); // Element Translatable 2

					if ($element->selection->isXref) {

						$configuration->xref_id = $element->selection->value;
						$configuration->option_id = $element->selection->option->option_id;
						$configuration->option_sku = $element->selection->option->sku;
						$configuration->option_image = (!empty($element->selection->option->option_image)) ? $element->selection->option->option_image : '';

						$configuration->option_custom_1 = $element->selection->option->option_custom_1;
						$configuration->option_custom_2 = $element->selection->option->option_custom_2;
						$configuration->option_custom_3 = $element->selection->option->option_custom_3;
						$configuration->option_custom_4 = $element->selection->option->option_custom_4;

						$configuration->assignment_custom_1 = $element->selection->option->assignment_custom_1;
						$configuration->assignment_custom_2 = $element->selection->option->assignment_custom_2;
						$configuration->assignment_custom_3 = $element->selection->option->assignment_custom_3;
						$configuration->assignment_custom_4 = $element->selection->option->assignment_custom_4;

						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings',  5, $configuration->option_id); // Option Title
						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 15, $configuration->option_id); // Option Description
						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 28, $configuration->option_id); // Option Contract

					}
					else {
						$configuration->xref_id = 0;
						$configuration->option_id = 0;
						$configuration->option_sku = '';
						$configuration->option_image = '';

						$configuration->option_custom_1 = '';
						$configuration->option_custom_2 = '';
						$configuration->option_custom_3 = '';
						$configuration->option_custom_4 = '';

						$configuration->assignment_custom_1 = '';
						$configuration->assignment_custom_2 = '';
						$configuration->assignment_custom_3 = '';
						$configuration->assignment_custom_4 = '';
					}

					$configuration->element_custom_1 = $element->element_custom_1;
					$configuration->element_custom_2 = $element->element_custom_2;
					$configuration->element_custom_3 = $element->element_custom_3;
					$configuration->element_custom_4 = $element->element_custom_4;

					$succ = $db->insertObject('#__cbcheckout_order_configurations',$configuration,'id');

					if (!$succ) {
						$errorMsg = $db->getErrorMsg();
						$query = "ROLLBACK";
						$db->setQuery($query);
						$db->query();
						KLog::log('Error inserting configuration item record. SQL error is "'.$errorMsg.'"','error','Error on inserting configuration item. See log for more info');
					}

				}
				/* INSERT EACH ELEMENT - END */
					
			}

			$orderTotalNet 			+= $position->price_net;
			$orderTotalRecurringNet += $position->price_recurring_net;

		}
	
	}

	$query = "COMMIT";
	$db->setQuery($query);
	$db->query();
}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users', 'language_tag') == false) {

	$query = "ALTER TABLE  `#__cbcheckout_users` ADD  `language_tag` CHAR( 5 ) NOT NULL , ADD INDEX (  `language_tag` )";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_languages`";
	$db->setQuery($query);
	$languages = $db->loadObjectList();
	foreach ($languages as $lang) {
		$query = "UPDATE `#__cbcheckout_users` SET `language_tag` = '".$db->getEscaped($lang->tag)."' WHERE `language_id` = ".intval($lang->id);
		$db->setQuery($query);
		$db->query();
	}
	
	$query = "UPDATE `#__cbcheckout_users` SET `language_tag` = '".$db->getEscaped($languages[0]->tag)."' WHERE `language_id` = 0";
	$db->setQuery($query);
	$db->query();
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_orderaddress', 'language_tag') == false) {

	$query = "ALTER TABLE  `#__cbcheckout_orderaddress` ADD  `language_tag` CHAR( 5 ) NOT NULL , ADD INDEX (  `language_tag` )";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_languages`";
	$db->setQuery($query);
	$languages = $db->loadObjectList();
	
	foreach ($languages as $lang) {
		$query = "UPDATE `#__cbcheckout_orderaddress` SET `language_tag` = '".$db->getEscaped($lang->tag)."' WHERE `language_id` = ".intval($lang->id);
		$db->setQuery($query);
		$db->query();
	}

	$query = "UPDATE `#__cbcheckout_orderaddress` SET `language_tag` = '".$db->getEscaped($languages[0]->tag)."' WHERE `language_id` = 0";
	$db->setQuery($query);
	$db->query();
	
}

// Rename the fieldname language_id in language_tag
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true) {
	$query = "UPDATE `#__cbcheckout_userfields` SET `field_name` = 'language_tag' WHERE `field_name` = 'language_id'";
	$db->setQuery($query);
	$db->query();
}

// Create the MaxMind GeoIP database folder
$maxMindGeoIpFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data/maxmind_geoip';
if (!is_dir($maxMindGeoIpFolder)) {
	mkdir($maxMindGeoIpFolder,0777,true);
}

$maxMindGeoIpFolderInfoFile = KenedoPlatform::p()->getComponentDir('com_configbox').'/data/maxmind_geoip/readme.txt';
if (!is_file($maxMindGeoIpFolderInfoFile)) {
	$text = 'Copy the uncompressed MaxMind binary database files in this folder. Filename for GeoIp City is "GeoIpCity.dat", for GeoLite City is "GeoLiteCity.dat"';
	file_put_contents($maxMindGeoIpFolderInfoFile,$text);
}

/* ADD ORDER PAYMENT TRACKING TABLE - START */

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_payment_trackings') == false) {
	
	$query = "CREATE TABLE `#__cbcheckout_payment_trackings` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` int(10) unsigned NOT NULL,
			`order_id` int(10) unsigned NOT NULL,
			`got_tracked` enum('0','1') NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`,`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	
	$db->setQuery($query);
	$db->query();
}

/* ADD ORDER PAYMENT TRACKING TABLE - END */

/* INSERT NEW CONNECTOR NAME FIELD AND INSERT NAMES - START */
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions') == true) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_paymentoptions','connector_name') == false) {
		
		$query = "ALTER TABLE `#__cbcheckout_paymentoptions` ADD `connector_name` VARCHAR(50) NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE `#__cbcheckout_order_payment_options` ADD `connector_name` VARCHAR(50) NOT NULL DEFAULT ''";
		$db->setQuery($query);
		$db->query();
		
		$mappings = array(
				'authorizenet'=>'authorizenet_sim',
				'mpay24'=>'mpay24_classic',
				'paypal'=>'paypal_wpstandard',
				'sofortueberweisung'=>'sofort_ueberweisung_classic',
				'vrpay'=>'vrpay_virtuell',
				'qpay'=>'wirecard_qpay',
				);
		
		foreach ($mappings as $old=>$new) {
			$query = "UPDATE `#__cbcheckout_paymentoptions` SET `connector_name` = '".$db->getEscaped($new)."' WHERE `class` = '".$db->getEscaped($old)."'";
			$db->setQuery($query);
			$db->query();
		}
		
		foreach ($mappings as $old=>$new) {
			$query = "UPDATE `#__cbcheckout_order_payment_options` SET `connector_name` = '".$db->getEscaped($new)."' WHERE `class` = '".$db->getEscaped($old)."'";
			$db->setQuery($query);
			$db->query();
		}
		
		$query = "UPDATE `#__cbcheckout_paymentoptions` SET `connector_name` = `class` WHERE `connector_name` = ''";
		$db->setQuery($query);
		$db->query();
		
		$query = "UPDATE `#__cbcheckout_order_payment_options` SET `connector_name` = `class` WHERE `connector_name` = ''";
		$db->setQuery($query);
		$db->query();
		
		$query = "ALTER TABLE `#__cbcheckout_paymentoptions` DROP `class`";
		$db->setQuery($query);
		$db->query();
	
		$query = "ALTER TABLE `#__cbcheckout_order_payment_options` DROP `class`";
		$db->setQuery($query);
		$db->query();
		
	}
}
/* ADD ORDER PAYMENT TRACKING TABLE - END */

/* REMOVE OUTDATED EDITMETADATA SETTING IN CONFIG - START */
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config','editmetadata') == true) {
	$query = "ALTER TABLE `#__configbox_config` DROP `editmetadata`";
	$db->setQuery($query);
	$db->query();
}
/* REMOVE OUTDATED EDITMETADATA SETTING IN CONFIG - END */

/* PRELIMINARY COUPON DISCOUNT FIELD - START */
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records','coupon_discount_net') == false) {
	$query = "ALTER TABLE `#__cbcheckout_order_records` ADD  `coupon_discount_net` DECIMAL( 20, 3 ) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}
/* PRELIMINARY COUPON DISCOUNT FIELD - END */

/* PRODUCT DETAILS PANES - START */
if (ConfigboxUpdateHelper::tableExists('#__configbox_product_detail_panes') == false) {
	$query = "
CREATE TABLE IF NOT EXISTS `#__configbox_product_detail_panes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `heading_icon_filename` varchar(30) NOT NULL,
  `css_classes` varchar(255) NOT NULL DEFAULT '',
  `ordering` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	
	$db->setQuery($query);
	$db->query();
}
/* PRODUCT DETAILS PANES - END */

/* PRODUCT DETAILS PANE SETTINGS - START */
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products','product_detail_panes_method') == false) {
	$query = "
ALTER TABLE  `#__configbox_products`
ADD  `product_detail_panes_method` ENUM(  'accordion',  'tabs' ) NOT NULL DEFAULT  'tabs',
ADD  `product_detail_panes_in_listings` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0',
ADD  `product_detail_panes_in_product_pages` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '1',
ADD  `product_detail_panes_in_configurator_steps` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'
	";
	$db->setQuery($query);
	$db->query();
}
/* PRODUCT DETAILS PANE SETTINGS - END */

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_product_images','image')) {
	$query = "ALTER TABLE `#__configbox_product_images` CHANGE  `image`  `filename` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	$db->setQuery($query);
	$db->query();
}

/* CONVERT MPAY24 PARAMETERS - START */

// Instead of one password for both test and live system, there are separate password fields for more comfortable switching
// live was renamed in production

// Get all connectors
$query = "SELECT * FROM `#__cbcheckout_paymentoptions` WHERE `connector_name` = 'mpay24_classic'";
$db->setQuery($query);
$connectors = $db->loadObjectList();

if ($connectors) {
	foreach ($connectors as $connector) {
		
		// Get the old parameters
		$params = new KStorage($connector->params);
		
		// Check if the outdated password parameter is there
		if ($params->get('password')) {
			
			// If the connector was in test mode, put the existing password, in the test system password, otherwise the other way around
			if ($params->get('testmode')) {
				$params->set('password_test', $params->get('password'));
				$params->set('password_production', '');
			}
			else {
				$params->set('password_production', $params->get('password'));
				$params->set('password_test', '');
			}
			
			// Copy live merchant id over to production merchant id
			$params->set('merchant_id_production', $params->get('merchant_id_live'));
			
			// Remove old parameters
			$params->remove('password');
			$params->remove('merchant_id_live');
			
			// Get the new query string
			$newParamString =  $params->toString('ini');
			
			// Update the parameter field
			$query = "UPDATE `#__cbcheckout_paymentoptions` SET `params` = '".$db->getEscaped($newParamString)."' WHERE `id` = ".intval($connector->id);
			$db->setQuery($query);
			$success = $db->query();
			
		}
				
	}
}
/* CONVERT MPAY24 PARAMETERS - END */

// Add the newsletter preset field
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','newsletter_preset') == false) {
	$query="ALTER TABLE `#__cbcheckout_config` ADD  `newsletter_preset` TINYINT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

// Add the alternate shipping preset field
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','alternate_shipping_preset') == false) {
	$query="ALTER TABLE `#__cbcheckout_config` ADD  `alternate_shipping_preset` TINYINT NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

// Remove obsolete securecheckout field in ORDER MANAGEMENT config
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','securecheckout') == true) {
	$query="ALTER TABLE `#__cbcheckout_config` DROP  `securecheckout` ";
	$db->setQuery($query);
	$db->query();
}

// Remove obsolete securecheckout field in ORDER MANAGEMENT config
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','securecheckout') == true) {
	$query="ALTER TABLE `#__cbcheckout_config` DROP  `securecheckout` ";
	$db->setQuery($query);
	$db->query();
}

// Calculation formulars: Convert version 2.4 attribute ElementAttribute(x.selectionPrice) and ElementAttribute(x.selectionPriceRecurring) - START
function getReplacedFormulaSelectionPrice($formula) {

	$matches = array();
	preg_match_all("/ElementAttribute\(([0-9]*)\.selectionPrice\)/", $formula, $matches);

	if ($matches) {
		foreach ($matches[0] as $key=>$occurence) {
			$elementId = $matches[1][$key];
			$replacement = 'ElementPrice('.$elementId.')';
			$formula = str_replace($occurence, $replacement,$formula);
		}
	}

	$matches = array();
	preg_match_all("/ElementAttribute\(([0-9]*)\.selectionPriceRecurring\)/", $formula, $matches);

	if ($matches) {
		foreach ($matches[0] as $key=>$occurence) {
			$elementId = $matches[1][$key];
			$replacement = 'ElementPriceRecurring('.$elementId.')';
			$formula = str_replace($occurence, $replacement,$formula);
		}
	}
	
	return $formula;
}

// Table renamed in 3.0.0 to configbox_calculation_codes (col formula renamed to code)
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_formulas', 'formula') == true) {
	$query = "SELECT * FROM `#__configbox_calculation_formulas` WHERE `formula` LIKE '%.selectionPrice%'";
	$db->setQuery($query);
	$formulas = $db->loadObjectList();

	if ($formulas) {
		foreach ($formulas as $formula) {

			$newFormula = getReplacedFormulaSelectionPrice($formula->formula);

			$query = "UPDATE `#__configbox_calculation_formulas` SET `formula` = '".$db->getEscaped($newFormula)."' WHERE `id` = ".intval($formula->id);
			$db->setQuery($query);
			$db->query();

		}
	}
}

// Calculation formulas: Convert version 2.4 attribute ElementAttribute(x.selectionPrice) and ElementAttribute(x.selectionPriceRecurring) - END


// Add setting to show SKU in order record view
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config','sku_in_order_record') == false) {
	$query="ALTER TABLE  `#__cbcheckout_config` ADD  `sku_in_order_record` TINYINT NOT NULL DEFAULT  '1' ";
	$db->setQuery($query);
	$db->query();
}

// Update the product listing table name
if (ConfigboxUpdateHelper::tableExists('#__configbox_pcategories') == true && ConfigboxUpdateHelper::tableExists('#__configbox_listings') == false) {
	$query = "RENAME TABLE  `#__configbox_pcategories` TO `#__configbox_listings`";
	$db->setQuery($query);
	$db->query();
}

// Remove old category view
$oldDir = KPATH_DIR_CB.DS.'views'.DS.'category';
$newDir = KPATH_DIR_CB.DS.'views'.DS.'configuratorpage';
if (is_dir($newDir) && is_dir($oldDir)) {
	KenedoFileHelper::deleteFolder($oldDir);
}

// Remove old product listing view
$oldDir = KPATH_DIR_CB.DS.'views'.DS.'products';
$newDir = KPATH_DIR_CB.DS.'views'.DS.'productlisting';
if (is_dir($newDir) && is_dir($oldDir)) {
	KenedoFileHelper::deleteFolder($oldDir);
}

// Rename old template override folder for the configurator page
$oldDir = KenedoPlatform::p()->getDirCustomization().DS.'templates'.DS.'configuration_page';
$newDir = KenedoPlatform::p()->getDirCustomization().DS.'templates'.DS.'configuratorpage';
if (!is_dir($newDir) && is_dir($oldDir)) {
	rename($oldDir,$newDir);
}

// Rename old template override folder for the listing
$oldDir = KenedoPlatform::p()->getDirCustomization().DS.'templates'.DS.'product_listing';
$newDir = KenedoPlatform::p()->getDirCustomization().DS.'templates'.DS.'productlisting';
if (!is_dir($newDir) && is_dir($oldDir)) {
	rename($oldDir,$newDir);
}

// Update Joomla menu item links for configurator page, product listing types
if (KenedoPlatform::getName() == 'joomla') {
	
	// Avoid faulty sql, in case joomla changes it's table name
	if (ConfigboxUpdateHelper::tableExists('#__menu')) {
	
		$query = "SELECT * FROM `#__menu` WHERE `link` LIKE '%option=com_configbox%' AND `link` LIKE '%view=category%'";
		$db->setQuery($query);
		$menuItems = $db->loadObjectList();
		
		if ($menuItems) {
			foreach ($menuItems as $menuItem) {
				$link = str_replace('view=category','view=configuratorpage',$menuItem->link);
				$query = "UPDATE `#__menu` SET `link` = '".$db->getEscaped($link)."' WHERE `id` = ".intval($menuItem->id);
				$db->setQuery($query);
				$db->query();
			}
		}
		
		$query = "SELECT * FROM `#__menu` WHERE `link` LIKE '%option=com_configbox%' AND `link` LIKE '%view=products%'";
		$db->setQuery($query);
		$menuItems = $db->loadObjectList();
		
		if ($menuItems) {
			foreach ($menuItems as $menuItem) {
				$link = str_replace('view=products','view=productlisting',$menuItem->link);
				$query = "UPDATE `#__menu` SET `link` = '".$db->getEscaped($link)."' WHERE `id` = ".intval($menuItem->id);
				$db->setQuery($query);
				$db->query();
			}
		}
		
	}
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config','review_notification_email') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `review_notification_email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  ''";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config','review_notification_email') == true) {

	$query = "SELECT `review_notification_email` FROM `#__configbox_config` LIMIT 1";
	$db->setQuery($query);
	$notificationEmail = $db->loadResult();
	
	if (!$notificationEmail) {
		$email = KenedoPlatform::p()->getMailerFromEmail();
		if ($email) {
			$query = "UPDATE `#__configbox_config` SET `review_notification_email` = '".$db->getEscaped($email)."'";
			$db->setQuery($query);
			$db->query();
		}
	}

}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_reviews') == false) {
	$query="
	CREATE TABLE IF NOT EXISTS `#__cbcheckout_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `comment` text CHARACTER SET utf8 NOT NULL,
  `published` tinyint(3) unsigned NOT NULL,
  `language_tag` varchar(5) CHARACTER SET utf8 NOT NULL,
  `date_created` datetime NOT NULL COMMENT 'UTC',
  `review_type` enum('product','option') CHARACTER SET utf8 NOT NULL,
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `option_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `published` (`published`,`language_tag`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();
}
