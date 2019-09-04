<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Check for a FK constraint on order_records.user_id
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
