<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$query = "ALTER TABLE `#__configbox_elements` ROW_FORMAT=DYNAMIC;";
$db->setQuery($query);
$db->query();

$query = "ALTER TABLE `#__configbox_products` ROW_FORMAT=DYNAMIC;";
$db->setQuery($query);
$db->query();

$query = "ALTER TABLE `#__configbox_xref_element_option` ROW_FORMAT=DYNAMIC;";
$db->setQuery($query);
$db->query();