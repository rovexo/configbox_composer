<?php
defined('CB_VALID_ENTRY') or die();

/**
 * Changes all ENUMS to fitting VARCHARs
 * Changes all TINYINT, SMALLINT, MEDIUMINT cols to INT cols
 * Drops and recreates all FKs before and after
 */

$db = KenedoPlatform::getDb();

// Change ENUMs to fitting VARCHAR columns
$query = "
SELECT * 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA=DATABASE() and DATA_TYPE='enum' and (TABLE_NAME LIKE '%configbox_%' OR TABLE_NAME LIKE '%cbcheckout_%')
";
$db->setQuery($query);
$cols = $db->loadObjectList();

foreach ($cols as $col) {

	$length = $col->CHARACTER_MAXIMUM_LENGTH;

	if ($length == 1) {
		$usedLength = 1;
	}
	elseif ($length <= 16) {
		$usedLength = 16;
	}
	elseif ($length <= 32) {
		$usedLength = 32;
	}
	elseif ($length <= 64) {
		$usedLength = 64;
	}
	elseif ($length <= 128) {
		$usedLength = 128;
	}
	else {
		$usedLength = $length;
	}

	$defaultThing = ($col->COLUMN_DEFAULT !== NULL) ? "NOT NULL DEFAULT '".$col->COLUMN_DEFAULT."'" : 'NULL';
	$query = "ALTER TABLE `".$col->TABLE_NAME."` MODIFY `".$col->COLUMN_NAME."` VARCHAR(".$usedLength.") ".$defaultThing;
	$db->setQuery($query);
	$db->query();

}

// Get FK infos on all CB tables
$query = "
SELECT DISTINCT constraints.CONSTRAINT_NAME,
				constraints.TABLE_NAME,
				constraints.REFERENCED_TABLE_NAME,
				constraints.UPDATE_RULE,
				constraints.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS AS `constraints`
WHERE 	constraints.CONSTRAINT_SCHEMA=DATABASE() AND
		constraints.REFERENCED_TABLE_NAME IS NOT NULL AND
		(constraints.TABLE_NAME LIKE '%configbox_%' OR constraints.TABLE_NAME LIKE '%cbcheckout_%');
";
$db->setQuery($query);
$fks = $db->loadObjectList('CONSTRAINT_NAME');

$query = "
SELECT * 
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA=DATABASE() AND 
      (TABLE_NAME LIKE '%configbox_%' OR TABLE_NAME LIKE '%cbcheckout_%') AND 
      REFERENCED_TABLE_NAME IS NOT NULL;
";
$db->setQuery($query);
$colInfo = $db->loadObjectList('CONSTRAINT_NAME');

// Mix in column names (avoiding joins in info schema tables)
foreach ($fks as $fk) {
	$fk->COLUMN_NAME = $colInfo[$fk->CONSTRAINT_NAME]->COLUMN_NAME;
	$fk->REFERENCED_COLUMN_NAME = $colInfo[$fk->CONSTRAINT_NAME]->REFERENCED_COLUMN_NAME;
}
unset($fk);

// Prepare FK creation queries and drop any FK constraints
$fkCreationQueries = [];
foreach ($fks as $fk) {

	$fkCreationQueries[] = "
	ALTER TABLE `$fk->TABLE_NAME` ADD
	FOREIGN KEY($fk->COLUMN_NAME) REFERENCES $fk->REFERENCED_TABLE_NAME($fk->REFERENCED_COLUMN_NAME)
	ON UPDATE $fk->UPDATE_RULE ON DELETE $fk->DELETE_RULE
	";

	$query = "ALTER TABLE `$fk->TABLE_NAME` DROP FOREIGN KEY `$fk->CONSTRAINT_NAME`";
	$db->setQuery($query);
	$db->query();

}

// Get all TINY, SMALL AND MEDIUMINTs
$query = "
SELECT * FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA=DATABASE() and DATA_TYPE IN('TINYINT', 'SMALLINT', 'MEDIUMINT') and (TABLE_NAME LIKE '%configbox%' OR TABLE_NAME LIKE '%cbcheckout%')
";
$db->setQuery($query);
$cols = $db->loadObjectList();

// Change all to INTs
foreach ($cols as $col) {

	$settings = [];

	if (stristr($col->COLUMN_TYPE, 'unsigned')) {
		$settings[] = 'UNSIGNED';
	}

	if ($col->COLUMN_DEFAULT !== NULL) {
		$settings[] = "DEFAULT '".$col->COLUMN_DEFAULT."'";
	}

	if (strtolower($col->IS_NULLABLE) == 'yes') {
		$settings[] = 'NULL';
	}

	if (strtolower($col->EXTRA) == 'auto_increment') {
		$settings[] = 'AUTO_INCREMENT';
	}

	$query = "ALTER TABLE `".$col->TABLE_NAME."` MODIFY `".$col->COLUMN_NAME."` INT ".implode(' ', $settings);
	$db->setQuery($query);
	$db->query();
}

// Recreate the FK constraints
foreach ($fkCreationQueries as $query) {
	$db->setQuery($query);
	$db->query();
}

