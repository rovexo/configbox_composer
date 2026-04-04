<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'add_requirejs_onload') == false) {
	$query = "
	ALTER TABLE `#__configbox_config`
	    ADD `add_requirejs_onload` VARCHAR(1) DEFAULT '1'
	";
	$db->setQuery($query);
	$db->query();
}
