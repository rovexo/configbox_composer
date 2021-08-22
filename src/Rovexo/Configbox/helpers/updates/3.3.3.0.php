<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_payment_methods', 'params') == true) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__configbox_payment_methods` MODIFY `params` TEXT;";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_payment_methods', 'params') == true) {
	$db = KenedoPlatform::getDb();
	$query = "ALTER TABLE `#__cbcheckout_order_payment_methods` MODIFY `params` TEXT;";
	$db->setQuery($query);
	$db->query();
}
