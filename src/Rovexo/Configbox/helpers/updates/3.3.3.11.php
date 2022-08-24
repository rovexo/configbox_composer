<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

// Changes any date or datetime cols to being NULLABLE and correcting any zero date values to NULL
$query = "
(SELECT *
FROM information_schema.COLUMNS
WHERE 
TABLE_SCHEMA=DATABASE() and (DATA_TYPE='date' or DATA_TYPE='datetime') 
and (TABLE_NAME LIKE '%configbox_%' OR TABLE_NAME LIKE '%cbcheckout_%'))
";
$db->setQuery($query);
$cols = $db->loadAssocList();

$query = "SHOW VARIABLES LIKE 'sql_mode'";
$db->setQuery($query);
$var = $db->loadAssoc();
$mode = $var['Value'];

$query = "SET sql_mode=''";
$db->setQuery($query);
$db->query();

foreach ($cols as $col) {

	$TABLE_NAME = $col['TABLE_NAME'];
	$COLUMN_NAME = $col['COLUMN_NAME'];
	$COLUMN_TYPE = $col['COLUMN_TYPE'];

	$query = "
	ALTER TABLE `$TABLE_NAME` MODIFY `$COLUMN_NAME` $COLUMN_TYPE NULL DEFAULT NULL COMMENT 'UTC'
	";
	$db->setQuery($query);
	$db->query();

	$query = "
	UPDATE `$TABLE_NAME` SET `$COLUMN_NAME` = NULL
	WHERE `$COLUMN_NAME` LIKE '0000-00-00%'
	";
	$db->setQuery($query);
	$db->query();

}

$query = "SET sql_mode='".$mode."'";
$db->setQuery($query);
$db->query();
