<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::keyExists('#__configbox_countries', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_countries` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_countries', 'country_2_code') === false) {
	$query = "ALTER TABLE `#__configbox_countries` ADD INDEX `country_2_code` (`country_2_code`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_countries', 'vat_free') === false) {
	$query = "ALTER TABLE `#__configbox_countries` ADD INDEX `vat_free` (`vat_free`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_countries', 'vat_free_with_vatin') === false) {
	$query = "ALTER TABLE `#__configbox_countries` ADD INDEX `vat_free_with_vatin` (`vat_free_with_vatin`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_xref_element_option', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_elements', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_elements` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_pages', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_pages` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_pages', 'ordering') === false) {
	$query = "ALTER TABLE `#__configbox_pages` ADD INDEX `ordering` (`ordering`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_products', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_products` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_listings', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_listings` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}
