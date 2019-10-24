<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_files') == true) {

	$query = "DROP TABLE `#__cbcheckout_order_files`";
	$db->setQuery($query);
	$db->query();

}
