<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_reviews', 'option_id') == true) {

	$query = "ALTER TABLE `#__configbox_reviews` CHANGE `option_id` `option_id` INT(10) UNSIGNED NULL;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_reviews', 'product_id') == false) {

	$query = "ALTER TABLE `#__configbox_reviews` CHANGE `product_id` `product_id` INT(10) UNSIGNED NULL;";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_reviews` ADD INDEX `product_id` (`product_id`);";
	$db->setQuery($query);
	$db->query();

	// Add foreign key
	$query = "ALTER TABLE `#__configbox_reviews` ADD CONSTRAINT FOREIGN KEY (`product_id`) REFERENCES `#__configbox_products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";
	$db->setQuery($query);
	$db->query();

}