<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

/* Remove helper columns taxrate and taxrate_dec from products, shipping and payment - START */

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_element_custom_translatable_1') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_element_custom_translatable_1` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `label_element_custom_4`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'label_element_custom_translatable_2') == false) {
	$query = "ALTER TABLE `#__configbox_config` ADD `label_element_custom_translatable_2` VARCHAR( 255 ) NOT NULL DEFAULT  '' AFTER  `label_element_custom_translatable_1`";
	$db->setQuery($query);
	$db->query();
}
