<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'visualization_type') == false) {

	$query = "ALTER TABLE `#__configbox_products` ADD `visualization_type` ENUM('none', 'composite', 'shapediver') NOT NULL DEFAULT 'none'";
	$db->setQuery($query);
	$db->query();

	// Find out what products use visualization

	// Get recurring option prices and recurring calculations in answers
	$query = "
	SELECT DISTINCT `pages`.`product_id`
	FROM `#__configbox_xref_element_option` AS `answers`
	LEFT JOIN `#__configbox_elements` AS `questions` ON `questions`.`id` = `answers`.`element_id`
	LEFT JOIN `#__configbox_pages` AS `pages` ON `pages`.`id` = `questions`.`page_id`
	WHERE `answers`.`visualization_image` != ''
	";
	$db->setQuery($query);
	$productIds = $db->loadResultList();

	// Get question calculations recurring
	$query = "
	SELECT `id`
	FROM `#__configbox_products`
	WHERE `baseimage` != ''
	";
	$db->setQuery($query);

	// Have all product ids together
	$productIds = array_merge($productIds, $db->loadResultList());

	// Get the distinct product ids
	$productIds = array_unique($productIds);

	// Set visualization type for those products
	if (count($productIds)) {
		// Now set recurring pricing on for those
		$query = "UPDATE `#__configbox_products` SET `visualization_type` = 'composite' WHERE `id` IN (".implode(',', $productIds).")";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'shapediver_model_data') == false) {

	$query = "ALTER TABLE `#__configbox_products` ADD `shapediver_model_data` TEXT NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'is_shapediver_control') == false) {

	$query = "ALTER TABLE `#__configbox_elements` ADD `is_shapediver_control` ENUM('0','1') NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_elements` ADD INDEX (`is_shapediver_control`)";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'shapediver_parameter_id') == false) {

	$query = "ALTER TABLE `#__configbox_elements` ADD `shapediver_parameter_id` VARCHAR(255) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'shapediver_choice_value') == false) {

	$query = "ALTER TABLE `#__configbox_xref_element_option` ADD `shapediver_choice_value` VARCHAR(512) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();

}