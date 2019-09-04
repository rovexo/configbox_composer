<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_matrices', 'row_type') == true) {

	$query = "UPDATE `#__configbox_calculation_matrices` SET `row_type` = 'question' WHERE `row_type` = 'element'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_calculation_matrices` MODIFY `row_type` ENUM('none', 'question', 'calculation') NOT NULL DEFAULT 'none'";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_matrices', 'column_type') == true) {

	$query = "UPDATE `#__configbox_calculation_matrices` SET `column_type` = 'question' WHERE `column_type` = 'element'";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_calculation_matrices` MODIFY `column_type` ENUM('none', 'question', 'calculation') NOT NULL DEFAULT 'none'";
	$db->setQuery($query);
	$db->query();

}

