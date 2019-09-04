<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'platform_user_id') === false) {
	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `platform_user_id` (`platform_user_id`);";
	$db->setQuery($query);
	$db->query();
}
