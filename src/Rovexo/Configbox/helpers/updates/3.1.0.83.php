<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$query = "SET FOREIGN_KEY_CHECKS=0";
$db->setQuery($query);
$db->query();

$query = "ALTER TABLE #__configbox_countries MODIFY id INT UNSIGNED AUTO_INCREMENT NOT NULL;";
$db->setQuery($query);
$db->query();

$query = "SET FOREIGN_KEY_CHECKS=1";
$db->setQuery($query);
$db->query();