<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'user_id') == true) {

	$query = "ALTER TABLE `#__cbcheckout_order_records` MODIFY user_id INT(10) UNSIGNED;";
	$db->setQuery($query);
	$db->query();

}