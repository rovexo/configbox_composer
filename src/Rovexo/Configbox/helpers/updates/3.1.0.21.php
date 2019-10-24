<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'use_captcha_recommendation') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `use_captcha_recommendation`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'recaptcha_public_key') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `recaptcha_public_key`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'recaptcha_private_key') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `recaptcha_private_key`";
	$db->setQuery($query);
	$db->query();

}