<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Indices in user table for speedy cleanup
if (ConfigboxUpdateHelper::tableExists('#__configbox_users') == true) {

	$query = "SHOW INDEX FROM `#__configbox_users`";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	$gotTemporaryIndex = $gotCreatedIndex = false;

	foreach ($indices as $index) {
		if ($index['Column_name'] == 'is_temporary' && $index['Key_name'] == 'is_temporary') {
			$gotTemporaryIndex = true;
		}
		if ($index['Column_name'] == 'created' && $index['Key_name'] == 'created') {
			$gotCreatedIndex = true;
		}
	}

	if ($gotTemporaryIndex == false && ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'is_temporary') == true) {
		$query = "ALTER TABLE `#__configbox_users` ADD INDEX (`is_temporary`)";
		$db->setQuery($query);
		$db->query();
	}

	if ($gotCreatedIndex == false && ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'created') == true) {
		$query = "ALTER TABLE `#__configbox_users` ADD INDEX (`created`)";
		$db->setQuery($query);
		$db->query();
	}

}

// Add ordering column to matrix data to enable col/row sorting
if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_matrices_data', 'ordering') == false) {
	$query = "ALTER TABLE `#__configbox_calculation_matrices_data` ADD `ordering` SMALLINT UNSIGNED NOT NULL, ADD INDEX (`ordering`)";
	$db->setQuery($query);
	$db->query();
}
