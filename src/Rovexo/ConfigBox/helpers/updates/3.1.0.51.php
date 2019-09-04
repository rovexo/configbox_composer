<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shopdata', 'state_id') == true) {

	$query = "ALTER TABLE `#__configbox_shopdata` MODIFY `state_id` MEDIUMINT(8) UNSIGNED NULL";
	$db->setQuery($query);
	$db->query();

}