<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'password')) {
	$query = "ALTER TABLE `#__configbox_users` MODIFY `password` VARCHAR(255) NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_records', 'comment')) {
	$db = KenedoPlatform::getDb();
	$query = "
	ALTER TABLE #__cbcheckout_order_records 
	    MODIFY `comment` TEXT NULL,
	    MODIFY `transaction_id` TEXT NULL,
	    MODIFY `transaction_data` TEXT NULL
	";
	$db->setQuery($query);
	$db->query();
}
