<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__cbcheckout_order_user_groups')) {

	$cols = [
		'discount_recurring_factor_5',
		'discount_recurring_factor_4',
		'discount_recurring_factor_3',
		'discount_recurring_factor_2',
		'discount_recurring_factor_1',
		'discount_recurring_start_5',
		'discount_recurring_start_4',
		'discount_recurring_start_3',
		'discount_recurring_start_2',
		'discount_recurring_start_1',
	];

	foreach ($cols as $col) {
		$query = "
		ALTER TABLE `#__cbcheckout_order_user_groups` CHANGE `".$col."` `".$col."` DECIMAL(20, 4) DEFAULT 0.0000 NOT NULL AFTER `discount_type_5`
		";
		$db->setQuery($query);
		$db->query();
	}
}
