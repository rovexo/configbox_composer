<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Check for a FK contraint on order_records.user_id
$name = ConfigboxUpdateHelper::getFkConstraintName('#__cbcheckout_order_records', 'user_id');

if (!$name) {

	$query = "UPDATE `#__cbcheckout_order_records` SET `user_id` = NULL WHERE `user_id` NOT IN (SELECT `id` FROM `#__cbcheckout_order_users`)";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_records` MODIFY COLUMN `user_id` INT(10) UNSIGNED";
	$db->setQuery($query);
	$db->query();

	$query = "
	ALTER TABLE `#__cbcheckout_order_records`
	ADD FOREIGN KEY (`user_id`) REFERENCES `#__cbcheckout_order_users` (id) 
	ON UPDATE CASCADE ON DELETE SET NULL;";
	$db->setQuery($query);
	$db->query();

}

// Check for a FK contraint on order_records.delivery_id
$name = ConfigboxUpdateHelper::getFkConstraintName('#__cbcheckout_order_records', 'delivery_id');

if (!$name) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` MODIFY COLUMN `payment_id` MEDIUMINT(8) UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_payment_methods` MODIFY COLUMN `id` MEDIUMINT(8) UNSIGNED";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__cbcheckout_order_records` SET `payment_id` = NULL WHERE `payment_id` NOT IN (SELECT `id` FROM `#__cbcheckout_order_payment_methods`)";
	$db->setQuery($query);
	$db->query();

	// Somehow MySQL doesn't let me add the FK constraint. Probably somehow related to the fact that
	// the child column is part of a multi-column primary key. Deferring setting one to some time later.

//	$query = "
//	ALTER TABLE `#__cbcheckout_order_records`
//	ADD FOREIGN KEY (`payment_id`) REFERENCES `#__cbcheckout_order_payment_methods` (id)
//	ON UPDATE CASCADE ON DELETE CASCADE;";
//	$db->setQuery($query);
//	$db->query();

}

// Check for a FK contraint on order_records.payment_id
$name = ConfigboxUpdateHelper::getFkConstraintName('#__cbcheckout_order_records', 'delivery_id');

if (!$name) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` MODIFY COLUMN `delivery_id` MEDIUMINT(8) UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__cbcheckout_order_shipping_methods` MODIFY COLUMN `id` MEDIUMINT(8) UNSIGNED";
	$db->setQuery($query);
	$db->query();

	$query = "UPDATE `#__cbcheckout_order_records` SET `delivery_id` = NULL WHERE `delivery_id` NOT IN (SELECT `id` FROM `#__cbcheckout_order_shipping_methods`)";
	$db->setQuery($query);
	$db->query();

	// Somehow MySQL doesn't let me add the FK constraint. Probably somehow related to the fact that
	// the child column is part of a multi-column primary key. Deferring setting one to some time later.

//	$query = "
//	ALTER TABLE `#__cbcheckout_order_records`
//	ADD FOREIGN KEY (`delivery_id`) REFERENCES `#__cbcheckout_order_shipping_methods` (id)
//	ON UPDATE CASCADE ON DELETE CASCADE;";
//	$db->setQuery($query);
//	$db->query();


}
