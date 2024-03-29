<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Changing all country/state/county/city_id/salutation fields to allow NULL and making the default NULL
$tables = array(
	'#__configbox_users' => array('country', 'billingcountry', 'state', 'billingstate', 'county_id', 'billingcounty_id', 'city_id', 'billingcity_id', 'salutation_id', 'billingsalutation_id'),
	'#__cbcheckout_order_users' => array('country', 'billingcountry', 'state', 'billingstate', 'county_id', 'billingcounty_id', 'city_id', 'billingcity_id', 'salutation_id', 'billingsalutation_id'),
);

foreach ($tables as $tableName => $columnNames) {

	$alterPart = "ALTER TABLE `".$tableName."` ";

	$modifies = [];

	foreach ($columnNames as $columnName) {

		if (ConfigboxUpdateHelper::tableFieldExists($tableName, $columnName) == true) {
			$modifies[] = "MODIFY `".$columnName."` MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL";
		}

		if (count($modifies)) {
			$query = $alterPart . ' '.implode(', ', $modifies);
			$db->setQuery($query);
			$db->query();
		}


	}
}
