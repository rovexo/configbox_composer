<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_countries', 'in_eu_vat_area') == false) {

	$query = "ALTER TABLE `#__configbox_countries` ADD COLUMN `in_eu_vat_area` ENUM('0', '1') DEFAULT '0' AFTER `vat_free_with_vatin`";
	$db->setQuery($query);
	$db->query();

	$query = "CREATE INDEX in_eu_vat_area ON #__configbox_countries (in_eu_vat_area);";
	$db->setQuery($query);
	$db->query();

	$euCodes = array(
		'DE',
		'AT',
		'BE',
		'BG',
		'CY',
		'CZ',
		'DK',
		'EE',
		'FI',
		'FR',
		'HU',
		'IE',
		'IT',
		'LV',
		'LT',
		'LU',
		'MT',
		'NL',
		'PL',
		'PT',
		'RO',
		'SK',
		'SI',
		'ES',
		'SE',
	);

	array_walk($euCodes, function(&$code) {
		$code = "'".$code."'";
	});

	$query = "UPDATE `#__configbox_countries` SET `in_eu_vat_area` = '1' WHERE `country_2_code` IN (".implode(', ', $euCodes).")";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_countries` DROP COLUMN `vat_free_with_vatin`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_countries', 'in_eu_vat_area') == false) {

	$query = "ALTER TABLE `#__cbcheckout_order_countries` ADD COLUMN `in_eu_vat_area` ENUM('0', '1') DEFAULT '0' AFTER `vat_free_with_vatin`";
	$db->setQuery($query);
	$db->query();

	$query = "CREATE INDEX in_eu_vat_area ON #__cbcheckout_order_countries (in_eu_vat_area);";
	$db->setQuery($query);
	$db->query();

	$euCodes = array(
		'DE',
		'AT',
		'BE',
		'BG',
		'CY',
		'CZ',
		'DK',
		'EE',
		'FI',
		'FR',
		'HU',
		'IE',
		'IT',
		'LV',
		'LT',
		'LU',
		'MT',
		'NL',
		'PL',
		'PT',
		'RO',
		'SK',
		'SI',
		'ES',
		'SE',
	);

	array_walk($euCodes, function(&$code) {
		$code = "'".$code."'";
	});

	$query = "UPDATE `#__cbcheckout_order_countries` SET `in_eu_vat_area` = '1' WHERE `country_2_code` IN (".implode(', ', $euCodes).")";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_countries` DROP COLUMN `vat_free_with_vatin`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shopdata', 'country_id') == false) {

	$query = "ALTER TABLE `#__configbox_shopdata` ADD COLUMN `country_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL AFTER `shopcountry`";
	$db->setQuery($query);
	$db->query();

	$query = "CREATE INDEX `country_id` ON `#__configbox_shopdata` (`country_id`);";
	$db->setQuery($query);
	$db->query();

	$query = "
	ALTER TABLE `#__configbox_shopdata` 
	ADD FOREIGN KEY (country_id) REFERENCES #__configbox_countries (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL";
	$db->setQuery($query);
	$db->query();

	// Try to set the country_id using the country name that may be already entered
	$query = "SELECT `shopcountry` FROM `#__configbox_shopdata` WHERE `id` = 1";
	$db->setQuery($query);
	$name = $db->loadResult();

	if ($name) {

		$query = "SELECT `id` FROM `#__configbox_countries` WHERE LOWER(`country_name`) = '".$db->getEscaped(strtolower($name))."'";
		$db->setQuery($query);
		$id = $db->loadResult();

		if ($id) {
			$query = "UPDATE `#__configbox_shopdata` SET `country_id` = '".intval($id)."'";
			$db->setQuery($query);
			$db->query();
		}

	}

	$query = "ALTER TABLE `#__configbox_shopdata` DROP COLUMN `shopcountry`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shopdata', 'state_id') == false) {

	$query = "ALTER TABLE `#__configbox_shopdata` ADD COLUMN `state_id` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL AFTER `country_id`";
	$db->setQuery($query);
	$db->query();

	$query = "CREATE INDEX `state_id` ON `#__configbox_shopdata` (`state_id`);";
	$db->setQuery($query);
	$db->query();

	$query = "
	ALTER TABLE `#__configbox_shopdata` 
	ADD FOREIGN KEY (state_id) REFERENCES #__configbox_states (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL";
	$db->setQuery($query);
	$db->query();

}