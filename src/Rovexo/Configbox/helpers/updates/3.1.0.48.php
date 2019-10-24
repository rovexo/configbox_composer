<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$query = "
ALTER TABLE `#__configbox_config`
  MODIFY `lastcleanup` BIGINT UNSIGNED DEFAULT 0,
  MODIFY `usertime` MEDIUMINT UNSIGNED DEFAULT 24,
  MODIFY `unorderedtime` MEDIUMINT UNSIGNED DEFAULT 24,
  MODIFY `intervals` MEDIUMINT UNSIGNED DEFAULT 12,
  MODIFY `labelexpiry` MEDIUMINT UNSIGNED DEFAULT 28,
  MODIFY `weightunits` VARCHAR(16) DEFAULT '',
  MODIFY `product_key` VARCHAR(64) DEFAULT '',
  MODIFY `default_customer_group_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
  MODIFY `default_country_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
  MODIFY `maxmind_user_id` VARCHAR(32) DEFAULT '',
  MODIFY `defaultprodimage` VARCHAR(32) DEFAULT '',
  MODIFY `securecheckout` ENUM('0', '1') DEFAULT '0',
  MODIFY disable_delivery ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY newsletter_preset ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY alternate_shipping_preset ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY show_recurring_login_cart ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY explicit_agreement_terms ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY explicit_agreement_rp ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY enable_geolocation ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY use_internal_element_names ENUM('0', '1') NOT NULL DEFAULT '0',

  MODIFY pm_regular_show_overview ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_regular_show_prices ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_regular_show_categories ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_regular_show_elements ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_regular_show_elementprices ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_regular_expand_categories ENUM('0', '1', '2') NOT NULL DEFAULT '2',
  MODIFY pm_recurring_show_overview ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_recurring_show_prices ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_recurring_show_categories ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_recurring_show_elements ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_recurring_show_elementprices ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY pm_recurring_expand_categories ENUM('0', '1', '2') NOT NULL DEFAULT '2',
  MODIFY show_conversion_table ENUM('0', '1') NOT NULL DEFAULT '0',
  MODIFY pm_show_regular_first ENUM('0', '1') NOT NULL DEFAULT '1',
  MODIFY sku_in_order_record ENUM('0', '1') NOT NULL DEFAULT '0'
";
$db->setQuery($query);
$db->query();

$keys = ConfigboxUpdateHelper::getKeyNames('#__configbox_elements', 'rules');

if (count($keys)) {
	foreach ($keys as $key) {
		$query = "DROP INDEX `".$key."` ON #__configbox_elements;";
		$db->setQuery($query);
		$db->query();
	}
}

$keys = ConfigboxUpdateHelper::getKeyNames('#__configbox_xref_element_option', 'rules');

if (count($keys)) {
	foreach ($keys as $key) {
		$query = "DROP INDEX `".$key."` ON #__configbox_xref_element_option;";
		$db->setQuery($query);
		$db->query();
	}
}
