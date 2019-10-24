<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'orderedtime') == true) {

	$query = "ALTER TABLE `#__configbox_config` DROP `orderedtime`";
	$db->setQuery($query);
	$db->query();

}
