<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'position_id') == true) {

	$query = "
	ALTER TABLE #__cbcheckout_order_configurations MODIFY `position_id` INT(10) UNSIGNED DEFAULT NULL;
	";
	$db->setQuery($query);
	$db->query();

}
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'element_id') == true) {

	$query = "
	ALTER TABLE #__cbcheckout_order_configurations MODIFY element_id INT(10) UNSIGNED DEFAULT NULL;
	";
	$db->setQuery($query);
	$db->query();

}
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'xref_id') == true) {

	$query = "
	ALTER TABLE #__cbcheckout_order_configurations MODIFY xref_id INT(10) UNSIGNED DEFAULT NULL;
	";
	$db->setQuery($query);
	$db->query();

}
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_configurations', 'option_id') == true) {

	$query = "
	ALTER TABLE #__cbcheckout_order_configurations MODIFY option_id INT(10) UNSIGNED DEFAULT NULL;
	";
	$db->setQuery($query);
	$db->query();

}