<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

// Change CHARS to fitting VARCHAR columns (runs again due to possible problem with file 3.1.72)
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

	if ($col->COLUMN_DEFAULT !== NULL) {
		$settings[] = "DEFAULT '".$col->COLUMN_DEFAULT."'";
	}

	$query = "ALTER TABLE `".$col->TABLE_NAME."` MODIFY `".$col->COLUMN_NAME."` VARCHAR(".$col->CHARACTER_MAXIMUM_LENGTH.") ".implode(' ', $settings);
	$db->setQuery($query);
	$db->query();

}