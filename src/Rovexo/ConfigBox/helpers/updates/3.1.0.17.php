<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'enable_reviews') == true) {

	$query = "ALTER TABLE `#__configbox_options` DROP `enable_reviews`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'external_reviews_id') == true) {

	$query = "ALTER TABLE `#__configbox_options` DROP `external_reviews_id`";
	$db->setQuery($query);
	$db->query();

}