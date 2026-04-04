<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'platform_user_id') == true) {

	$db = KenedoPlatform::getDb();

	$query = "
	ALTER TABLE #__configbox_users
	MODIFY COLUMN `platform_user_id` INT UNSIGNED NULL DEFAULT NULL
	";
	$db->setQuery($query);
	$db->query();

}
