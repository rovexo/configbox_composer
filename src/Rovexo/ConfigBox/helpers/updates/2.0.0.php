<?php
defined('CB_VALID_ENTRY') or die();

// Only do these if the 2.6.0 updates were not applied already (they remove those columns)
if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'joomla_user_group_id') == false) {
	
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'user_group') == false) {
		$query = "ALTER TABLE `#__cbcheckout_config` ADD `user_group` MEDIUMINT UNSIGNED NOT NULL;";
		$db->setQuery($query);
		$db->query();
	}
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'showrefundpolicy') == false) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD `showrefundpolicy` tinyint(1) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_config', 'showrefundpolicyinline') == false) {
	$query = "ALTER TABLE `#__cbcheckout_config` ADD `showrefundpolicyinline` tinyint(1) NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();
}
