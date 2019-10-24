<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_reviews', 'option_id') == true) {

	$query = "ALTER TABLE `#__configbox_reviews` DROP `option_id`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'enable_reviews_options') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `enable_reviews_options`";
	$db->setQuery($query);
	$db->query();

}