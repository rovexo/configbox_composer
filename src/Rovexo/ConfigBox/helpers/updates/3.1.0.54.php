<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'ga_behavior_offline_psps') == false) {

	$query = "ALTER TABLE `#__configbox_config` ADD `ga_behavior_offline_psps` ENUM('conversion_when_ordered', 'conversion_when_paid') DEFAULT 'conversion_when_ordered'";
	$db->setQuery($query);
	$db->query();

}
