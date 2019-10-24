<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();
if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_records') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'custom_5') == false) {
		$query = "
ALTER TABLE  `#__cbcheckout_order_records`
ADD  `custom_5` TEXT NOT NULL DEFAULT  '',
ADD  `custom_6` TEXT NOT NULL DEFAULT  '',
ADD  `custom_7` TEXT NOT NULL DEFAULT  '',
ADD  `custom_8` TEXT NOT NULL DEFAULT  '',
ADD  `custom_9` TEXT NOT NULL DEFAULT  '',
ADD  `custom_10` TEXT NOT NULL DEFAULT  ''";
		$db->setQuery($query);
		$db->query();
	}
}