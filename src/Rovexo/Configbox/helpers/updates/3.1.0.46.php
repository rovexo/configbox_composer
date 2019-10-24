<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$orderRelTables = array(
	'#__cbcheckout_order_users',
	'#__cbcheckout_order_cities',
	'#__cbcheckout_order_counties',
	'#__cbcheckout_order_states',
	'#__cbcheckout_order_countries',
	'#__cbcheckout_order_currencies',
	'#__cbcheckout_order_invoices',
	'#__cbcheckout_order_payment_methods',
	'#__cbcheckout_order_payment_trackings',
	'#__cbcheckout_order_positions',
	'#__cbcheckout_order_quotations',
	'#__cbcheckout_order_salutations',
	'#__cbcheckout_order_shipping_methods',
	'#__cbcheckout_order_strings',
	'#__cbcheckout_order_tax_class_rates',
	'#__cbcheckout_order_user_groups',
);

$tablesOrderIdIsPrimary = array(
	'#__cbcheckout_order_quotations',
);

foreach ($orderRelTables as $table) {
	if (ConfigboxUpdateHelper::tableExists($table) == false) {
		throw new Exception('Tried to clean up and set foreign keys for table '.$table.', but it did not exsit.', 'error');
	}
}

foreach ($orderRelTables as $table) {

	// Remove orphans
	$query = "DELETE FROM `".$table."` WHERE `order_id` NOT IN (SELECT `id` FROM `#__cbcheckout_order_records`)";
	$db->setQuery($query);
	$db->query();


	// Make sure we got type INT(10) UNSIGNED (AND NOT NULL AUTO_INCREMENT for those that have `order_id` as primary key)
	if (in_array($table, $tablesOrderIdIsPrimary)) {
		$query = "ALTER TABLE `".$table."` MODIFY COLUMN `order_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
		$db->setQuery($query);
		$db->query();

	}
	else {
		$query = "ALTER TABLE `".$table."` MODIFY COLUMN `order_id` INT(10) UNSIGNED";
		$db->setQuery($query);
		$db->query();
	}

	$name = ConfigboxUpdateHelper::getFkConstraintName($table, 'order_id');

	if ($name) {
		$query = "ALTER TABLE `".$table."` DROP FOREIGN KEY ".$name;
		$db->setQuery($query);
		$db->query();
	}

	// Add the foreign key (restricting both update and delete)
	$query = "
	ALTER TABLE `".$table."` 
	ADD FOREIGN KEY (`order_id`) REFERENCES `#__cbcheckout_order_records` (id) 
	ON UPDATE CASCADE;";
	$db->setQuery($query);
	$db->query();

}

$query = "DELETE FROM `#__cbcheckout_order_configurations` WHERE `position_id` NOT IN (SELECT `id` FROM `#__cbcheckout_order_positions`)";
$db->setQuery($query);
$db->query();

$query = "ALTER TABLE `#__cbcheckout_order_configurations` MODIFY COLUMN `position_id` INT(10) UNSIGNED";
$db->setQuery($query);
$db->query();

$query = "
	ALTER TABLE `#__cbcheckout_order_configurations`
	ADD FOREIGN KEY (`position_id`) REFERENCES `#__cbcheckout_order_positions` (id) 
	ON UPDATE CASCADE;";
$db->setQuery($query);
$db->query();