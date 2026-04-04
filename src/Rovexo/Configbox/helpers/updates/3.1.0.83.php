<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

$query = "SET FOREIGN_KEY_CHECKS=0";
$db->setQuery($query);
$db->query();

$query = "DELETE FROM #__configbox_countries WHERE `id` = 0";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_shopdata', 'country_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_shopdata DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_shopdata MODIFY country_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_config', 'default_country_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_config DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_config MODIFY default_country_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_tax_class_rates', 'country_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_tax_class_rates DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_tax_class_rates MODIFY country_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_xref_country_payment_method', 'country_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_xref_country_payment_method DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_xref_country_payment_method MODIFY country_id INT UNSIGNED NOT NULL;";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_xref_country_zone', 'country_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_xref_country_zone DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_xref_country_zone MODIFY country_id INT UNSIGNED NOT NULL;";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_shopdata', 'state_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_shopdata DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_shopdata MODIFY state_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();


$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_counties', 'state_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_counties DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_counties MODIFY state_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();


$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_tax_class_rates', 'state_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_tax_class_rates DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_tax_class_rates MODIFY state_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();

$fkName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_states', 'country_id');
if ($fkName) {
	$query = "ALTER TABLE #__configbox_states DROP FOREIGN KEY $fkName";
	$db->setQuery($query);
	$db->query();
}

$query = "ALTER TABLE #__configbox_states MODIFY country_id INT UNSIGNED NULL;";
$db->setQuery($query);
$db->query();

$query = "ALTER TABLE #__configbox_countries MODIFY id INT UNSIGNED AUTO_INCREMENT NOT NULL;";
$db->setQuery($query);
$db->query();

$query = "ALTER TABLE #__configbox_states MODIFY id INT UNSIGNED AUTO_INCREMENT NOT NULL;";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_xref_country_payment_method ADD FOREIGN KEY (country_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE CASCADE
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_xref_country_zone ADD FOREIGN KEY (country_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE CASCADE
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_shopdata ADD FOREIGN KEY (country_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE SET NULL
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_config ADD FOREIGN KEY (default_country_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE SET NULL
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_tax_class_rates ADD FOREIGN KEY (country_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE SET NULL
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_counties ADD FOREIGN KEY (state_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE RESTRICT 
";
$db->setQuery($query);
$db->query();


$query = "
ALTER TABLE #__configbox_shopdata ADD FOREIGN KEY (state_id) 
REFERENCES #__configbox_states (id)
ON UPDATE CASCADE ON DELETE SET NULL
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_tax_class_rates ADD FOREIGN KEY (state_id) 
REFERENCES #__configbox_states (id)
ON UPDATE CASCADE ON DELETE SET NULL
";
$db->setQuery($query);
$db->query();

$query = "
ALTER TABLE #__configbox_states ADD FOREIGN KEY (country_id) 
REFERENCES #__configbox_countries (id)
ON UPDATE CASCADE ON DELETE SET NULL
";
$db->setQuery($query);
$db->query();

$query = "SET FOREIGN_KEY_CHECKS=1";
$db->setQuery($query);
$db->query();