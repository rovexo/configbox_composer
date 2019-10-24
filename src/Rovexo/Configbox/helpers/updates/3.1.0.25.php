<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enable_file_uploads') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `enable_file_uploads`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allowed_file_extensions') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `allowed_file_extensions`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allowed_file_mimetypes') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `allowed_file_mimetypes`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'allowed_file_size') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `allowed_file_size`";
	$db->setQuery($query);
	$db->query();

}