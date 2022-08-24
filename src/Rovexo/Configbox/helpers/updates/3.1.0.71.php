<?php
defined('CB_VALID_ENTRY') or die();

/**
 * Changes all CHAR columns to fitting VARCHARs
 */

$db = KenedoPlatform::getDb();

// Change ENUMs to fitting VARCHAR columns
$query = "
SELECT * 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA=DATABASE() and DATA_TYPE='char' and (TABLE_NAME LIKE '%configbox_%' OR TABLE_NAME LIKE '%cbcheckout_%')
";
$db->setQuery($query);
$cols = $db->loadObjectList();

foreach ($cols as $col) {

	$settings = [];

	if (strtolower($col->IS_NULLABLE) == 'yes') {
		$settings[] = 'NULL';
	}

	// MariaDB quotes col default, MySQL doesn't
	if ($col->COLUMN_DEFAULT === NULL) {
		$a = 1;
	}
	elseif ($col->COLUMN_DEFAULT === 'NULL') {
		$settings[] = 'DEFAULT NULL';
	}
	elseif(strpos($col->COLUMN_DEFAULT, "'") === 0) {
		$settings[] = 'DEFAULT '. $col->COLUMN_DEFAULT;
	}
	else {
		$settings[] = "DEFAULT '".$col->COLUMN_DEFAULT."'";
	}

	$query = "ALTER TABLE `".$col->TABLE_NAME."` MODIFY `".$col->COLUMN_NAME."` VARCHAR(".$col->CHARACTER_MAXIMUM_LENGTH.") ".implode(' ', $settings);
	$db->setQuery($query);
	$db->query();

}
