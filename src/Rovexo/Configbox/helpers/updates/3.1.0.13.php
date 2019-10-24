<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'use_recurring_pricing') == false) {

	$query = "ALTER TABLE `#__configbox_products` ADD `use_recurring_pricing` ENUM('0', '1') NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

	// Find out what products use recurring pricing

	// Get recurring option prices and recurring calculations in answers
	$query = "
	SELECT DISTINCT `answers`.`element_id`
	FROM `#__configbox_xref_element_option` AS `answers`
	LEFT JOIN `#__configbox_options` AS `options` ON `options`.`id` = `answers`.`option_id`
	WHERE `options`.`price_recurring` != 0 OR `answers`.calcmodel_recurring IS NOT NULL
	";
	$db->setQuery($query);
	$questionIds = $db->loadResultList();

	// Get question calculations recurring
	$query = "
	SELECT `id`
	FROM `#__configbox_elements`
	WHERE calcmodel_recurring IS NOT NULL
	";
	$db->setQuery($query);

	// Have all question ids together
	$questionIds = array_merge($questionIds, $db->loadResultList());

	// Get the product ids for those questions
	$productIds = array();

	foreach ($questionIds as $questionId) {
		$assignments = ConfigboxCacheHelper::getAssignments();
		$productIds[] = $assignments['element_to_product'][$questionId];
	}

	// Get the distinct product ids
	$productIds = array_unique($productIds);

	if (count($productIds)) {
		// Now set recurring pricing on for those
		$query = "UPDATE `#__configbox_products` SET `use_recurring_pricing` = '1' WHERE `id` IN (".implode(',', $productIds).")";
		$db->setQuery($query);
		$db->query();
	}

}