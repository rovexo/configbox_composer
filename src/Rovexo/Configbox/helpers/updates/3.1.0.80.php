<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_users') == true) {
	$query = "ALTER TABLE `#__cbcheckout_order_users` ROW_FORMAT=DYNAMIC;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_users') == true) {
	$query = "ALTER TABLE `#__configbox_users` ROW_FORMAT=DYNAMIC;";
	$db->setQuery($query);
	$db->query();
}


if (ConfigboxUpdateHelper::tableExists('#__configbox_config') == true) {

	$query = "
	ALTER TABLE `#__configbox_config`

    MODIFY `label_element_custom_1`               TEXT NULL,
 	MODIFY `label_element_custom_2`               TEXT NULL,
    MODIFY `label_element_custom_3`               TEXT NULL,
    MODIFY `label_element_custom_4`               TEXT NULL,
    MODIFY `label_element_custom_translatable_1`  TEXT NULL,
    MODIFY `label_element_custom_translatable_2`  TEXT NULL,
    MODIFY `label_assignment_custom_1`            TEXT NULL,
    MODIFY `label_assignment_custom_2`            TEXT NULL,
    MODIFY `label_assignment_custom_3`            TEXT NULL,
    MODIFY `label_assignment_custom_4`            TEXT NULL,
    MODIFY `label_option_custom_1`                TEXT NULL,
    MODIFY `label_option_custom_2`                TEXT NULL,
    MODIFY `label_option_custom_3`                TEXT NULL,
    MODIFY `label_option_custom_4`                TEXT NULL,
	MODIFY `label_option_custom_5`                TEXT NULL,
    MODIFY `label_option_custom_6`                TEXT NULL,
	MODIFY `label_product_custom_1`               TEXT NULL,
    MODIFY `label_product_custom_2`               TEXT NULL,
    MODIFY `label_product_custom_3`               TEXT NULL,
    MODIFY `label_product_custom_4`               TEXT NULL,
    MODIFY `label_product_custom_5`               TEXT NULL,
    MODIFY `label_product_custom_6`               TEXT NULL,
	MODIFY `maxmind_license_key`				  VARCHAR(64) NOT NULL DEFAULT ''
	";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_config` ROW_FORMAT=DYNAMIC;";
	$db->setQuery($query);
	$db->query();
}