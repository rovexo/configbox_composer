<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'showrefundpolicy') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `showrefundpolicy`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'showrefundpolicyinline') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `showrefundpolicyinline`";
	$db->setQuery($query);
	$db->query();

}