<?php
defined('CB_VALID_ENTRY') or die();

if (KenedoPlatform::getName() == 'magento') {
	$oldStoreFolder = Mage::getBaseDir('media').'/elovaris/configbox/store_data';
	$oldCustomerFolder = Mage::getBaseDir('media').'/elovaris/configbox/customer_data';
	$oldSettingsFolder = Mage::getBaseUrl('media').'/elovaris/configbox/settings';
}
else {
	$oldStoreFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data';
	$oldCustomerFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data';
	$oldSettingsFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/data/settings';
}

/* MOVE OUT ORDER MANAGEMENT DATA FOLDER STUFF - START */
$oldDataFolder = KenedoPlatform::p()->getComponentDir('com_cbcheckout').'/data';

$folderRenamings = array(
	$oldDataFolder.'/invoices' 							=> $oldCustomerFolder.'/invoices',
	$oldDataFolder.'/order_files' 						=> $oldCustomerFolder.'/order_files',
	$oldDataFolder.'/position_images' 					=> $oldCustomerFolder.'/position_images',
	$oldDataFolder.'/quotations' 							=> $oldCustomerFolder.'/quotations',
	$oldDataFolder.'/shoplogo' 							=> $oldStoreFolder.'/shoplogo',
	$oldDataFolder.'/notification_attachments' 			=> KenedoPlatform::p()->getOldDirCustomization().'/notification_attachments',
	$oldDataFolder.'/notification_templates' 				=> KenedoPlatform::p()->getOldDirCustomization().'/notification_templates',
	$oldDataFolder.'/notification_elements' 				=> KenedoPlatform::p()->getOldDirCustomization().'/notification_elements',
	KenedoPlatform::p()->getOldDirCustomization().'/notification_elements' 	=> KenedoPlatform::p()->getOldDirCustomization().'/notification_snippets',
);

foreach ($folderRenamings as $old=>$new) {
	if (is_dir($old) && !is_dir($new)) {
		rename($old, $new);
	}
}
/* MOVE OUT ORDER MANAGEMENT DATA FOLDER STUFF - END */

/* MERGE JOOMLA ACCESS RULES AND REMOVE ORDER MANAGEMENT RULES AFTERWARDS - START */

// Go on only if we're on joomla actually
if (KenedoPlatform::getName() == 'joomla' && KenedoPlatform::p()->getVersionShort() != '1.5') {
	// Little safeguard in case this table gets removed in the future
	if (ConfigboxUpdateHelper::tableExists('#__assets')) {
		// Get both rulesets
		$query = "SELECT * FROM `#__assets` WHERE `name` = 'com_cbcheckout' OR `name` = 'com_configbox'";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$rules = $db->loadObjectList('name');
		// Go on if the om rules are actually there and if they're not just an empty object
		if (!empty($rules['com_cbcheckout']->rules) && $rules['com_cbcheckout']->rules != '{}') {

			$omRules = json_decode($rules['com_cbcheckout']->rules);
			$cbRules = json_decode($rules['com_configbox']->rules);

			foreach ($omRules as $key=>$rules) {
				// Skip core.manage, keep using cb rule on that one
				if ($key == 'core.manage') continue;
				// Copy over the rule
				$cbRules->$key = $rules;
			}
			// Prepare the merged rules
			$rulesJson = json_encode($cbRules);

			// Remove the om rules
			$query = "UPDATE `#__assets` SET `rules` = '{}' WHERE `name` = 'com_cbcheckout'";
			$db->setQuery($query);
			$success = $db->query();

			// Get the merged rules into cb
			$query = "UPDATE `#__assets` SET `rules` = '".$db->getEscaped($rulesJson)."' WHERE `name` = 'com_configbox'";
			$db->setQuery($query);
			$success = $db->query();

		}
	}
}
/* MERGE JOOMLA ACCESS RULES AND REMOVE ORDER MANAGEMENT RULES AFTERWARDS - END */

/* MERGE STRING TABLES AND UPDATE ORDER_STRINGS WITH NEW TYPE IDS - START */

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_strings') == true) {

	$reindexing = array(
		array('old'=>1, 	'new'=>44),
		array('old'=>3, 	'new'=>45),
		array('old'=>5, 	'new'=>46),
		array('old'=>6, 	'new'=>47),
		array('old'=>20, 	'new'=>38),
		array('old'=>30, 	'new'=>32),
		array('old'=>31, 	'new'=>39),
		array('old'=>50, 	'new'=>80),
		array('old'=>51, 	'new'=>81),
		array('old'=>52, 	'new'=>82),
		array('old'=>53, 	'new'=>83),
		array('old'=>54, 	'new'=>84),
		array('old'=>60, 	'new'=>85),
		array('old'=>61, 	'new'=>86),
		array('old'=>62, 	'new'=>87),
		array('old'=>63, 	'new'=>88),
		array('old'=>64, 	'new'=>89),
	);

	// Get any old types, then replace the records into configbox_strings with the new type ID (replace because we might have 'orphans' with that type ID in the table)
	// Looks like it can be done easier by just removing orphans by deleting anything with the new type ids then update,
	// but this script may run multiple times, deleting the actual new type records

	// Order management entities could theoretically have custom language types, but that was definitely not done at this time, so no need to deal with it

	foreach ($reindexing as $item) {

		$query = "SELECT * FROM `#__cbcheckout_strings` WHERE `type` = ".intval($item['old']);
		$db->setQuery($query);
		$records = $db->loadObjectList();
		if ($records) {
			foreach ($records as $record) {
				// Change the type
				$record->type = $item['new'];
				// Replace the record
				$success = $db->replaceObject('#__cbcheckout_strings', $record);
			}
		}

	}

	$currentValidOmTypes = array ( 0 => '42', 1 => '43', 2 => '32', 3 => '39', 4 => '36', 5 => '37', 6 => '46', 7 => '47', 8 => '55', 9 => '44', 10 => '45', 11 => '34', 12 => '38', 13 => '33', 14 => '35', 15 => '80', 16 => '81', 17 => '82', 18 => '83', 19 => '84', 20 => '85', 21 => '86', 22 => '87', 23 => '88', 24 => '89', );

	// Now copy over all wanted texts into configbox_strings (overwriting any orphans in configbox_strings)
	$query = "SELECT * FROM `#__cbcheckout_strings` WHERE `type` IN (".implode(',', $currentValidOmTypes).")";
	$db->setQuery($query);
	$records = $db->loadObjectList();

	if ($records) {

		foreach ($records as $record) {
			// Remember the record id
			$id = $record->id;
			// Set the record ID to NULL, configbox_strings got a primary key field
			$record->id = NULL;

			$success = $db->replaceObject('#__configbox_strings', $record);

			if ($success) {
				$query = "DELETE FROM `#__cbcheckout_strings` WHERE `id` = ".intval($id);
				$db->setQuery($query);
				$db->query();
			}

		}

	}

	// Dealing with the order_strings table. Here we do not have to worry about orphans, a simple update is enough and probably better because that table can be huge
	foreach ($reindexing as $item) {
		$query = "UPDATE `#__cbcheckout_order_strings` SET `type` = ".intval($item['new']).", `table` = 'configbox_strings' WHERE `type` = ".intval($item['old']) . " AND `table` = 'cbcheckout_strings'";
		$db->setQuery($query);
		$db->query();
	}

}
/* MERGE STRING TABLES AND UPDATE ORDER_STRINGS WITH NEW TYPE IDS - END */

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

// Remove old product listings table if still around
$query = "DROP TABLE IF EXISTS `#__configbox_pcategories`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableExists('#__configbox_grandorders') == true && ConfigboxUpdateHelper::tableExists('#__configbox_carts') == false) {
	$query = "RENAME TABLE #__configbox_grandorders TO #__configbox_carts";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_grandorders`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableExists('#__configbox_orders') == true && ConfigboxUpdateHelper::tableExists('#__configbox_cart_positions') == false) {

	$query = "RENAME TABLE `#__configbox_orders` TO `#__configbox_cart_positions`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_positions` DROP INDEX `grandorder_id`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_positions` CHANGE `grandorder_id` `cart_id` BIGINT( 8 ) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_positions` ADD INDEX (`cart_id`)";
	$db->setQuery($query);
	$db->query();

}

// Remove the renamed table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_orders`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableExists('#__configbox_orderitems') == true && ConfigboxUpdateHelper::tableExists('#__configbox_cart_position_configurations') == false) {

	$query = "RENAME TABLE `#__configbox_orderitems` TO `#__configbox_cart_position_configurations`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_position_configurations` DROP INDEX `order_id/element_id`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_position_configurations` DROP INDEX `order_id`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_position_configurations` CHANGE `order_id` `cart_position_id` BIGINT(20) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_position_configurations` ADD UNIQUE KEY `cart_position_id-element_id` (`cart_position_id`,`element_id`)";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_cart_position_configurations` ADD INDEX (`cart_position_id`)";
	$db->setQuery($query);
	$db->query();

}

// Remove the renamed table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_orderitems`";
$db->setQuery($query);
$db->query();

// Change fk grandorder_id to cart_id, adapt indices
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_records') == true && ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'grandorder_id') == true) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` DROP INDEX `grandorder_id`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_records` CHANGE `grandorder_id` `cart_id` BIGINT(20) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_records` ADD INDEX (`cart_id`)";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_categories') == true && ConfigboxUpdateHelper::tableExists('#__configbox_pages') == false) {

	$query = "RENAME TABLE #__configbox_categories TO #__configbox_pages";
	$db->setQuery($query);
	$db->query();

	$colsToRemove = array(
		'section_id',
		'layout_type',
		'width_left_column',
		'width_middle_column',
		'width_right_column',
		'content_left_column',
		'width_middle_column',
		'width_right_column',
		'content_left_column',
		'content_upper_row',
		'content_middle_column',
		'content_right_column',
		'content_lower_row',
		'checked_out',
		'checked_out_time',
	);

	foreach ($colsToRemove as $col) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_pages', $col) == true) {
			$query = "ALTER TABLE `#__configbox_pages` DROP `".$db->getEscaped($col)."`";
			$db->setQuery($query);
			$db->query();
		}
	}

}

// Remove the renamed table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_categories`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements','cat_id') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements','page_id') == false) {

	$query = "ALTER TABLE `#__configbox_elements` DROP INDEX cat_id";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_elements` CHANGE `cat_id` `page_id` MEDIUMINT(8) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_elements` ADD INDEX `page_id-ordering` (  `page_id` ,  `ordering` )";
	$db->setQuery($query);
	$db->query();

}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_sections`";
$db->setQuery($query);
$db->query();

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_languages`";
$db->setQuery($query);
$db->query();

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_config`";
$db->setQuery($query);
$db->query();


/* UPDATE JOOMLA CONFIGURATOR PAGE AND PRODUCT LISTING LINKS  - START */
if (KenedoPlatform::getName() == 'joomla') {

	$db = KenedoPlatform::getDb();
	$query = "SELECT * FROM `#__menu` WHERE `link` LIKE '%option=com_configbox%'";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	foreach ($items as $item) {
		$link = $item->link;

		if (strstr($link,'view=category')) {
			$link = str_replace('view=category', 'view=configuratorpage', $link);
		}

		if (strstr($link,'view=configuratorpage')) {
			$link = str_replace('cat_id=', 'page_id=', $link);
		}

		if (strstr($link,'view=productlisting') && strstr($link,'&pcat_id=')) {
			$link = str_replace('&pcat_id=', '&listing_id=', $link);
		}

		if ($link != $item->link) {
			$query = "UPDATE `#__menu` SET `link` = '".$db->getEscaped($link)."' WHERE `id` = ".intval($item->id);
			$db->setQuery($query);
			$db->query();
		}
	}

	if (KenedoPlatform::p()->getVersionShort() == '1.5') {
		$query = "SELECT `id` FROM `#__components` WHERE `option` = 'com_cbcheckout'";
		$db->setQuery($query);
		$idCbcheckout = $db->loadResult();

		$query = "SELECT `id` FROM `#__components` WHERE `option` = 'com_configbox'";
		$db->setQuery($query);
		$idConfigbox = $db->loadResult();

		if ($idCbcheckout && $idConfigbox) {

			$query = "SELECT * FROM `#__menu` WHERE `componentid` = ".intval($idCbcheckout);
			$db->setQuery($query);
			$items = $db->loadObjectList();

			foreach ($items as $item) {
				$link = str_replace('com_cbcheckout','com_configbox',$item->link);
				$query = "UPDATE `#__menu` SET `link` = '".$db->getEscaped($link)."', `componentid` = ".intval($idConfigbox)." WHERE `id` = ".intval($item->id);
				$db->setQuery($query);
				$db->query();
			}
		}

	}
	else {
		$query = "SELECT `extension_id` FROM `#__extensions` WHERE `name` = 'cbcheckout'";
		$db->setQuery($query);
		$idCbcheckout = $db->loadResult();

		$query = "SELECT `extension_id` FROM `#__extensions` WHERE `name` = 'configbox'";
		$db->setQuery($query);
		$idConfigbox = $db->loadResult();

		if ($idCbcheckout && $idConfigbox) {

			$query = "SELECT * FROM `#__menu` WHERE `client_id` = 0 AND `component_id` = ".intval($idCbcheckout);
			$db->setQuery($query);
			$items = $db->loadObjectList();

			foreach ($items as $item) {
				$link = str_replace('com_cbcheckout','com_configbox',$item->link);
				$query = "UPDATE `#__menu` SET `link` = '".$db->getEscaped($link)."', `component_id` = ".intval($idConfigbox)." WHERE `id` = ".intval($item->id);
				$db->setQuery($query);
				$db->query();
			}
		}
	}



	$query = "SELECT * FROM `#__menu` WHERE `link` LIKE '%option=com_cbcheckout%' AND `client_id` = 0";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	foreach ($items as $item) {
		$link = $item->link;

		$link = str_replace('com_cbcheckout', 'com_configbox', $link);

		$query = "UPDATE `#__menu` SET `link` = '".$db->getEscaped($link)."' WHERE `id` = ".intval($item->id);
		$db->setQuery($query);
		$db->query();
	}

}
/* UPDATE JOOMLA CONFIGURATOR PAGE LINKS - END */



if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_pcategory_product') == true && ConfigboxUpdateHelper::tableExists('#__configbox_xref_listing_product') == false) {

	$query = "RENAME TABLE `#__configbox_xref_pcategory_product` TO `#__configbox_xref_listing_product`";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_xref_listing_product` CHANGE `pcategory_id` `listing_id` MEDIUMINT(8) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_xref_listing_product` ADD UNIQUE `listing_id-product_id` (  `listing_id` ,  `product_id` )";
	$db->setQuery($query);
	$db->query();

}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_xref_pcategory_product`";
$db->setQuery($query);
$db->query();

// Drop the pcat_id field from products
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products','pcat_id') == true){
	$query = "ALTER TABLE `#__configbox_products` DROP `pcat_id`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_availablein`";
$db->setQuery($query);
$db->query();

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_country_zone`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_emails') == true && ConfigboxUpdateHelper::tableExists('#__configbox_notifications') == false) {
	$query = "RENAME TABLE `#__cbcheckout_emails` TO `#__configbox_notifications`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_emails`";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_users') == true && ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_users','is_temporary') == false) {
	$query = "ALTER TABLE `#__cbcheckout_users` ADD  `is_temporary` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_users') == true && ConfigboxUpdateHelper::tableExists('#__configbox_users') == true) {

	// Remove the table in case it got re-created by a update rerun
	$query = "DROP TABLE IF EXISTS `#__configbox_users`";
	$db->setQuery($query);
	$db->query();

	$query = "RENAME TABLE `#__cbcheckout_users` TO `#__configbox_users`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_users`";
$db->setQuery($query);
$db->query();

// Rename the calculation data table
if (ConfigboxUpdateHelper::tableExists('#__configbox_tables') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculation_table_data') == false) {
	$query = "RENAME TABLE `#__configbox_tables` TO `#__configbox_calculation_table_data`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_tables`";
$db->setQuery($query);
$db->query();

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_strings`";
$db->setQuery($query);
$db->query();

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_orders`";
$db->setQuery($query);
$db->query();

// Rename table orderaddress and make it the same way as other order_ tables
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orderaddress') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_users') == false) {

	$query = "RENAME TABLE #__cbcheckout_orderaddress TO #__cbcheckout_order_users";
	$db->setQuery($query);
	$db->query();

	// Drop the AI
	$query = "ALTER TABLE `#__cbcheckout_order_users` CHANGE  `id`  `id` BIGINT(20) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	// Drop the primary key
	$query = "ALTER TABLE `#__cbcheckout_order_users` DROP PRIMARY KEY";
	$db->setQuery($query);
	$db->query();

	// Now drop the id column completely
	$query = "ALTER TABLE `#__cbcheckout_order_users` DROP `id`";
	$db->setQuery($query);
	$db->query();

	// Make the user_id field the new id
	$query = "ALTER TABLE `#__cbcheckout_order_users` CHANGE `user_id` `id` BIGINT(20) UNSIGNED NOT NULL";
	$db->setQuery($query);
	$db->query();

	// Set the primary key over id and order_id like the other order_ tables
	$query = "ALTER TABLE `#__cbcheckout_order_users` ADD PRIMARY KEY (  `id` ,  `order_id` )";
	$db->setQuery($query);
	$db->query();

	// Drop the password column
	$query = "ALTER TABLE `#__cbcheckout_order_users` DROP `password`";
	$db->setQuery($query);
	$db->query();

	// Drop the language_id column
	$query = "ALTER TABLE `#__cbcheckout_order_users` DROP `language_id`";
	$db->setQuery($query);
	$db->query();

}

// Rename order files table
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_orderfiles') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_files') == false) {
	$query = "RENAME TABLE `#__cbcheckout_orderfiles` TO `#__cbcheckout_order_files`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_orderfiles`";
$db->setQuery($query);
$db->query();


// Rename quotations table
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_quotations') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_quotations') == false) {
	$query = "RENAME TABLE `#__cbcheckout_quotations` TO `#__cbcheckout_order_quotations`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_quotations`";
$db->setQuery($query);
$db->query();


// Rename invoices table
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_invoices') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_invoices') == false) {
	$query = "RENAME TABLE `#__cbcheckout_invoices` TO `#__cbcheckout_order_invoices`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_invoices`";
$db->setQuery($query);
$db->query();


// Rename payment trackings table
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_payment_trackings') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_payment_trackings') == false) {
	$query = "RENAME TABLE `#__cbcheckout_payment_trackings` TO `#__cbcheckout_order_payment_trackings`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_payment_trackings`";
$db->setQuery($query);
$db->query();

// Rename user fields table
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_userfields') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_user_field_definitions') == false) {
	$query = "RENAME TABLE `#__cbcheckout_userfields` TO `#__cbcheckout_user_field_definitions`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_userfields`";
$db->setQuery($query);
$db->query();

// Rename payment options table
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_paymentoptions') == true && ConfigboxUpdateHelper::tableExists('#__configbox_payment_methods') == false) {
	$query = "RENAME TABLE `#__cbcheckout_paymentoptions` TO `#__configbox_payment_methods`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__cbcheckout_paymentoptions`";
$db->setQuery($query);
$db->query();


// Rename calculations table
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculationmodels') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculations') == false) {
	$query = "RENAME TABLE `#__configbox_calculationmodels` TO `#__configbox_calculations`";
	$db->setQuery($query);
	$db->query();
}

// Remove the table in case it got re-created by a update rerun
$query = "DROP TABLE IF EXISTS `#__configbox_calculationmodels`";
$db->setQuery($query);
$db->query();

/* COPY OVER ALL MISSING GROUP DATA TO ORDER_USER_GROUPS - START */

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'custom_1') == false) {

	$query = "ALTER TABLE  `#__cbcheckout_order_user_groups`
		ADD `title` varchar(255) NOT NULL DEFAULT '',
		ADD `custom_1` text NOT NULL,
		ADD `custom_2` text NOT NULL,
		ADD `custom_3` text NOT NULL,
		ADD `custom_4` text NOT NULL,
		ADD `enable_checkout_order` tinyint(1) unsigned NOT NULL DEFAULT '1',
		ADD `enable_see_pricing` tinyint(3) unsigned NOT NULL DEFAULT '1',
		ADD `enable_save_order` tinyint(3) unsigned NOT NULL DEFAULT '1',
		ADD `enable_request_quotation` tinyint(3) unsigned NOT NULL DEFAULT '1',
		ADD `enable_request_assistance` tinyint(3) unsigned NOT NULL DEFAULT '0',
		ADD `enable_recommendation` tinyint(3) unsigned NOT NULL DEFAULT '0',
		ADD `b2b_mode` tinyint(3) unsigned NOT NULL DEFAULT '0',
		ADD `joomla_user_group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
		ADD `quotation_download` enum('0','1') NOT NULL DEFAULT '1',
		ADD `quotation_email` enum('0','1') NOT NULL DEFAULT '1'
	";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__cbcheckout_user_groups`";
	$db->setQuery($query);
	$dataAllGroups = $db->loadObjectList('id');

	foreach ($dataAllGroups as $groupId=>$groupData) {
		$query = "
		UPDATE `#__cbcheckout_order_user_groups` SET

		`title` = '".$db->getEscaped($groupData->title)."',

		`custom_1` = '".$db->getEscaped($groupData->custom_1)."',
		`custom_2` = '".$db->getEscaped($groupData->custom_2)."',
		`custom_3` = '".$db->getEscaped($groupData->custom_3)."',
		`custom_4` = '".$db->getEscaped($groupData->custom_4)."',

		`enable_checkout_order` 	= '".$db->getEscaped($groupData->enable_checkout_order)."',
		`enable_see_pricing` 		= '".$db->getEscaped($groupData->enable_see_pricing)."',
		`enable_save_order` 		= '".$db->getEscaped($groupData->enable_save_order)."',
		`enable_request_quotation` 	= '".$db->getEscaped($groupData->enable_request_quotation)."',
		`enable_request_assistance` = '".$db->getEscaped($groupData->enable_request_assistance)."',
		`enable_recommendation` 	= '".$db->getEscaped($groupData->enable_recommendation)."',

		`b2b_mode` 				= '".$db->getEscaped($groupData->b2b_mode)."',
		`joomla_user_group_id` 	= '".$db->getEscaped($groupData->joomla_user_group_id)."',
		`quotation_download` 	= '".$db->getEscaped($groupData->quotation_download)."',
		`quotation_email` 		= '".$db->getEscaped($groupData->quotation_email)."'

		WHERE `group_id` = ".intval($groupId);

		$db->setQuery($query);
		$db->query();

	}


}

/* COPY OVER ALL MISSING GROUP DATA TO ORDER_USER_GROUPS - END */

// Change countries table from cbcheckout to configbox
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_countries') == true && ConfigboxUpdateHelper::tableExists('#__configbox_countries') == false ) {
	$query = "RENAME TABLE #__cbcheckout_countries TO #__configbox_countries";
	$db->setQuery($query);
	$db->query();
}

// Change counties table from cbcheckout to configbox
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_counties') == true && ConfigboxUpdateHelper::tableExists('#__configbox_counties') == false ) {
	$query = "RENAME TABLE #__cbcheckout_counties TO #__configbox_counties";
	$db->setQuery($query);
	$db->query();
}

// Change states table from cbcheckout to configbox
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_states') == true && ConfigboxUpdateHelper::tableExists('#__configbox_states') == false ) {
	$query = "RENAME TABLE #__cbcheckout_states TO #__configbox_states";
	$db->setQuery($query);
	$db->query();
}

// Change city table from cbcheckout to configbox
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_cities') == true && ConfigboxUpdateHelper::tableExists('#__configbox_cities') == false ) {
	$query = "RENAME TABLE #__cbcheckout_cities TO #__configbox_cities";
	$db->setQuery($query);
	$db->query();
}

// Change table cbcheckout_salutations to configbox_salutations
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_salutations') == true && ConfigboxUpdateHelper::tableExists('#__configbox_salutations') == false ) {
	$query = "RENAME TABLE #__cbcheckout_salutations TO #__configbox_salutations";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_countries') == false) {

	$query = "

	CREATE TABLE `#__cbcheckout_order_countries` (
  `id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `country_name` varchar(64) DEFAULT NULL,
  `country_3_code` char(3) DEFAULT NULL,
  `country_2_code` char(2) DEFAULT NULL,
  `vat_free` tinyint(1) NOT NULL DEFAULT '1',
  `vat_free_with_vatin` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` mediumint(9) NOT NULL,
  `custom_1` text NOT NULL,
  `custom_2` text NOT NULL,
  `custom_3` text NOT NULL,
  `custom_4` text NOT NULL,
  PRIMARY KEY (`id`,`order_id`),
  KEY `idx_country_name` (`country_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$db->setQuery($query);
	$db->query();

	$query = "
	CREATE TABLE `#__cbcheckout_order_states` (
  `id` mediumint(8) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `country_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `iso_code` varchar(50) NOT NULL DEFAULT '',
  `fips_number` varchar(5) NOT NULL DEFAULT '',
  `custom_1` text NOT NULL,
  `custom_2` text NOT NULL,
  `custom_3` text NOT NULL,
  `custom_4` text NOT NULL,
  `ordering` mediumint(9) NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`, `order_id`),
  KEY `country_id` (`country_id`),
  KEY `ordering` (`ordering`,`published`),
  KEY `iso_fips` (`iso_code`,`fips_number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();

	$query = "
	CREATE TABLE `#__cbcheckout_order_counties` (
  `id` int(11) NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `county_name` varchar(200) NOT NULL DEFAULT '',
  `state_id` int(10) unsigned NOT NULL,
  `custom_1` text NOT NULL,
  `custom_2` text NOT NULL,
  `custom_3` text NOT NULL,
  `custom_4` text NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `published` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`order_id`),
  KEY `state_id` (`state_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();

	// We saw this table already in place once on the demo site. We delete it just in case prior fresh creation
	$query = "DROP TABLE IF EXISTS `#__cbcheckout_order_cities`";
	$db->setQuery($query);
	$db->query();

	$query = "
	CREATE TABLE `#__cbcheckout_order_cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL,
  `city_name` varchar(200) NOT NULL DEFAULT '',
  `county_id` int(10) unsigned NOT NULL,
  `custom_1` text NOT NULL,
  `custom_2` text NOT NULL,
  `custom_3` text NOT NULL,
  `custom_4` text NOT NULL,
  `ordering` mediumint(9) NOT NULL,
  `published` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`order_id`),
  KEY `state_id` (`county_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";
	$db->setQuery($query);
	$db->query();

	$query = "
	CREATE TABLE `#__cbcheckout_order_salutations` (
  `id` smallint(5) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `gender` enum('1','2') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`,`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
	";
	$db->setQuery($query);
	$db->query();


	$query = "SELECT * FROM `#__configbox_countries`";
	$db->setQuery($query);
	$countries = $db->loadObjectList('id');

	$query = "SELECT * FROM `#__configbox_states`";
	$db->setQuery($query);
	$states = $db->loadObjectList('id');

	$query = "SELECT * FROM `#__configbox_counties`";
	$db->setQuery($query);
	$counties = $db->loadObjectList('id');

	$query = "SELECT * FROM `#__configbox_cities`";
	$db->setQuery($query);
	$cities = $db->loadObjectList('id');

	$query = "SELECT * FROM `#__configbox_salutations`";
	$db->setQuery($query);
	$salutations = $db->loadObjectList('id');

	$query = "SELECT `key`, `language_tag`, `text` FROM `#__configbox_strings` WHERE `type` = 55";
	$db->setQuery($query);
	$stringData = $db->loadObjectList();

	$salutationStrings = array();
	foreach ($stringData as $string) {
		$salutationStrings[$string->key][$string->language_tag] = $string->text;
	}

	$query = "SELECT `order_id`, `country`, `billingcountry`, `state`, `billingstate`, `county_id`, `billingcounty_id`, `city_id`, `billingcity_id`, `salutation_id`, `billingsalutation_id` FROM `#__cbcheckout_order_users`";
	$db->setQuery($query);
	/**
	 * @var ConfigboxUserData[] $users
	 */
	$users = $db->loadObjectList();

	foreach ($users as $user) {
		// Country
		if ($user->country && !empty($countries[$user->country])) {
			$countries[$user->country]->order_id = $user->order_id;
			$db->replaceObject('#__cbcheckout_order_countries', $countries[$user->country]);
		}
		// Billing Country
		if ($user->billingcountry && !empty($countries[$user->billingcountry])) {
			if ($user->country != $user->billingcountry) {
				$countries[$user->billingcountry]->order_id = $user->order_id;
				$db->replaceObject('#__cbcheckout_order_countries', $countries[$user->billingcountry]);
			}
		}

		// State
		if ($user->state && !empty($states[$user->state])) {
			$states[$user->state]->order_id = $user->order_id;
			$db->replaceObject('#__cbcheckout_order_states', $states[$user->state]);
		}
		// Billing State
		if ($user->billingstate && !empty($states[$user->billingstate])) {
			if ($user->state != $user->billingstate) {
				$states[$user->billingstate]->order_id = $user->order_id;
				$db->replaceObject('#__cbcheckout_order_states', $states[$user->billingstate]);
			}
		}

		// Shortcut to avoid a bit of processing
		if (count($counties)) {

			// County
			if ($user->county_id && !empty($counties[$user->county_id])) {
				$counties[$user->county_id]->order_id = $user->order_id;
				$db->replaceObject('#__cbcheckout_order_counties', $counties[$user->county_id]);
			}
			// Billing County
			if ($user->billingcounty_id && !empty($counties[$user->billingcounty_id])) {
				if ($user->county_id != $user->billingcounty_id) {
					$counties[$user->billingcounty_id]->order_id = $user->order_id;
					$db->replaceObject('#__cbcheckout_order_counties', $counties[$user->billingcounty_id]);
				}
			}

		}

		// Shortcut to avoid a bit of processing
		if (count($cities)) {

			// City
			if ($user->city_id && !empty($cities[$user->city_id])) {
				$cities[$user->city_id]->order_id = $user->order_id;
				$db->replaceObject('#__cbcheckout_order_cities', $cities[$user->city_id]);
			}
			// Billing City
			if ($user->billingcity_id && !empty($cities[$user->billingcity_id])) {
				if ($user->city_id != $user->billingcity_id) {
					$cities[$user->billingcity_id]->order_id = $user->order_id;
					$db->replaceObject('#__cbcheckout_order_cities', $cities[$user->billingcity_id]);
				}
			}

		}


		// Salutation
		if ($user->salutation_id && !empty($salutations[$user->salutation_id])) {
			$salutations[$user->salutation_id]->order_id = $user->order_id;
			$db->replaceObject('#__cbcheckout_order_salutations', $salutations[$user->salutation_id]);
			// Get the translations for the salutation
			if (!empty($salutationStrings[$user->salutation_id])) {
				foreach ($salutationStrings[$user->salutation_id] as $languageTag=>$translation) {
					$orderString = new stdClass();
					$orderString->order_id = $user->order_id;
					$orderString->table = 'configbox_strings';
					$orderString->type = 55;
					$orderString->key = $user->salutation_id;
					$orderString->language_tag = $languageTag;
					$orderString->text = $translation;
					$db->replaceObject('#__cbcheckout_order_strings', $orderString);
				}
			}

		}
		// Billing Salutation
		if ($user->billingsalutation_id && !empty($salutations[$user->billingsalutation_id])) {
			if ($user->salutation_id != $user->billingsalutation_id) {
				$salutations[$user->billingsalutation_id]->order_id = $user->order_id;
				$db->replaceObject('#__cbcheckout_order_salutations', $salutations[$user->billingsalutation_id]);
				// Get the translations for the salutation
				if (!empty($salutationStrings[$user->billingsalutation_id])) {
					foreach ($salutationStrings[$user->billingsalutation_id] as $languageTag=>$translation) {
						$orderString = new stdClass();
						$orderString->order_id = $user->order_id;
						$orderString->table = 'configbox_strings';
						$orderString->type = 55;
						$orderString->key = $user->billingsalutation_id;
						$orderString->language_tag = $languageTag;
						$orderString->text = $translation;
						$db->replaceObject('#__cbcheckout_order_strings', $orderString);
					}
				}

			}
		}

	}

}

$base = KenedoPlatform::p()->getOldDirCustomization().'/templates';

if (is_dir($base)) {

	$folderRenamings = array(
		'block_pricing' => 'blockpricing',
		'block_visualization' => 'blockvisualization',
		'block_navigation' => 'blocknavigation',
		'block_currencies' => 'blockcurrencies',
		'block_cart' => 'blockcart',
	);

	foreach ($folderRenamings as $old=>$new) {
		if (is_dir($base.'/'.$old) && !is_dir($base.'/'.$new)) {
			rename($base.'/'.$old, $base.'/'.$new);
		}
	}

}


if (ConfigboxUpdateHelper::tableExists('#__configbox_elements') == true) {

	// Correct and set indices for rules columns
	$query = "SHOW INDEX FROM `#__configbox_elements`";
	$db->setQuery($query);
	$indices = $db->loadAssocList('Key_name');

	if (!isset($indices['calcmodel_id_min_val']) && ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calcmodel_id_min_val')) {
		$query = "ALTER TABLE `#__configbox_elements` ADD INDEX ( `calcmodel_id_min_val` )";
		$db->setQuery($query);
		$db->query();
	}

	if (!isset($indices['calcmodel_id_max_val']) && ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'calcmodel_id_max_val')) {
		$query = "ALTER TABLE `#__configbox_elements` ADD INDEX ( `calcmodel_id_max_val` )";
		$db->setQuery($query);
		$db->query();
	}

}

// Calculations: Change type naming - editor -> formula, formula -> code
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculations') == true) {

	// Making sure that in case the script accidently runs twice the new 'formula' types (from 'editor') won't become 'code'
	$query = "SELECT `id` FROM `#__configbox_calculations` WHERE `type` = 'code' LIMIT 1";
	$db->setQuery($query);
	$codeTypeExists = (boolean) $db->loadResult();

	if ($codeTypeExists == false) {
		$query = "UPDATE `#__configbox_calculations` SET `type` = 'code' WHERE `type` = 'formula'";
		$db->setQuery($query);
		$db->query();
	}

	$query = "UPDATE `#__configbox_calculations` SET `type` = 'formula' WHERE `type` = 'editor'";
	$db->setQuery($query);
	$db->query();

}

// Change table calculation_formulas to calculation_codes (with some safeguards for overwriting because calculation_formulas will be the new calculation_editor)
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_formulas') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculation_editor') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculation_codes') == false) {
	$query = "RENAME TABLE #__configbox_calculation_formulas TO #__configbox_calculation_codes";
	$db->setQuery($query);
	$db->query();
}

// Change table calculation_editor to calculation_formulas
if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_editor') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculation_formulas') == false ) {
	$query = "RENAME TABLE #__configbox_calculation_editor TO #__configbox_calculation_formulas";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_tables') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculation_matrices') == false) {
	$query = "RENAME TABLE #__configbox_calculation_tables TO #__configbox_calculation_matrices";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_calculation_table_data') == true && ConfigboxUpdateHelper::tableExists('#__configbox_calculation_matrices_data') == false) {
	$query = "RENAME TABLE #__configbox_calculation_table_data TO #__configbox_calculation_matrices_data";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_products') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'magento_product_id') == false) {
	$query = "ALTER TABLE  `#__configbox_products` ADD  `magento_product_id` INT UNSIGNED NULL DEFAULT NULL , ADD INDEX (  `magento_product_id` )";
	$db->setQuery($query);
	$db->query();
}

// Change table cbcheckout_reviews to configbox_reviews
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_reviews') == true && ConfigboxUpdateHelper::tableExists('#__configbox_reviews') == false ) {
	$query = "RENAME TABLE #__cbcheckout_reviews TO #__configbox_reviews";
	$db->setQuery($query);
	$db->query();
}

// Change table cbcheckout_shippers to configbox_shippers
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippers') == true && ConfigboxUpdateHelper::tableExists('#__configbox_shippers') == false ) {
	$query = "RENAME TABLE #__cbcheckout_shippers TO #__configbox_shippers";
	$db->setQuery($query);
	$db->query();
}

// Change table __cbcheckout_shippingrates to __configbox_shipping_methods
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_shippingrates') == true && ConfigboxUpdateHelper::tableExists('#__configbox_shipping_methods') == false ) {
	$query = "RENAME TABLE #__cbcheckout_shippingrates TO #__configbox_shipping_methods";
	$db->setQuery($query);
	$db->query();
}

// Change table cbcheckout_order_delivery_options to cbcheckout_order_shipping_methods
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_delivery_options') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_shipping_methods') == false ) {
	$query = "RENAME TABLE #__cbcheckout_order_delivery_options TO #__cbcheckout_order_shipping_methods";
	$db->setQuery($query);
	$db->query();
}

// Change table cbcheckout_order_delivery_options to cbcheckout_order_shipping_methods
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_payment_options') == true && ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_payment_methods') == false ) {
	$query = "RENAME TABLE #__cbcheckout_order_payment_options TO #__cbcheckout_order_payment_methods";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_config') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'default_user_group_id') == true) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'default_customer_group_id') == false) {
			$query = "ALTER TABLE `#__configbox_config` CHANGE `default_user_group_id` `default_customer_group_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT  '0'";
			$db->setQuery($query);
			$db->query();
		}
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_users') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'joomlaid') == true) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'platform_user_id') == false) {
			$query = "ALTER TABLE  `#__configbox_users` CHANGE  `joomlaid`  `platform_user_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
			$db->setQuery($query);
			$db->query();
		}
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_users') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'joomlaid') == true) {
		if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'platform_user_id') == false) {
			$query = "ALTER TABLE  `#__cbcheckout_order_users` CHANGE  `joomlaid`  `platform_user_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL";
			$db->setQuery($query);
			$db->query();
		}
	}
}

// Change table cbcheckout_order_delivery_options to cbcheckout_order_shipping_methods
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_zones') == true && ConfigboxUpdateHelper::tableExists('#__configbox_zones') == false ) {

	$query = "RENAME TABLE #__cbcheckout_zones TO #__configbox_zones";
	$db->setQuery($query);
	$db->query();

}

// Remove the selection block - show quote button setting - PRODUCT
if (ConfigboxUpdateHelper::tableExists('#__configbox_products') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'pm_regular_show_quotation_button') == true) {
		$query = "ALTER TABLE `#__configbox_products` DROP `pm_regular_show_quotation_button`";
		$db->setQuery($query);
		$db->query();
	}
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'pm_recurring_show_quotation_button') == true) {
		$query = "ALTER TABLE `#__configbox_products` DROP `pm_recurring_show_quotation_button`";
		$db->setQuery($query);
		$db->query();
	}
}

// Remove the selection block - show quote button setting - CONFIG
if (ConfigboxUpdateHelper::tableExists('#__configbox_config') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'pm_regular_show_quotation_button') == true) {
		$query = "ALTER TABLE `#__configbox_config` DROP `pm_regular_show_quotation_button`";
		$db->setQuery($query);
		$db->query();
	}
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'pm_recurring_show_quotation_button') == true) {
		$query = "ALTER TABLE `#__configbox_config` DROP `pm_recurring_show_quotation_button`";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_products') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'charge_deposit') == true) {
		$query = "ALTER TABLE `#__configbox_products` DROP `charge_deposit`";
		$db->setQuery($query);
		$db->query();
	}
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'deposit_percentage') == true) {
		$query = "ALTER TABLE `#__configbox_products` DROP `deposit_percentage`";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_positions') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'open_amount_net') == true) {
		$query = "ALTER TABLE `#__cbcheckout_order_positions` DROP `open_amount_net`";
		$db->setQuery($query);
		$db->query();
	}
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'using_deposit') == true) {
		$query = "ALTER TABLE `#__cbcheckout_order_positions` DROP `using_deposit`";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_one_page_checkout') == true) {
	$query = "ALTER TABLE `#__configbox_config` DROP `use_one_page_checkout`";
	$db->setQuery($query);
	$db->query();
}