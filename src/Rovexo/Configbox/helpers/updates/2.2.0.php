<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_countries')) {

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_countries', 'ordering') == false) {
		$query = "ALTER TABLE  `#__cbcheckout_countries` ADD  `ordering` MEDIUMINT NOT NULL";
		$db->setQuery($query);
		$db->query();
	}

	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_countries', 'zone_id') == true) {
		$query = "ALTER TABLE `#__cbcheckout_countries` DROP `zone_id`";
		$db->setQuery($query);
		$db->query();
	}

}
