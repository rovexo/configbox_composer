<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allow_recommendations') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `allow_recommendations`";
	$db->setQuery($query);
	$db->query();

}
