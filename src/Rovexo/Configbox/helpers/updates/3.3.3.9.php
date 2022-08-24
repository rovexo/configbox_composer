<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_currencies', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_currencies` 
	    MODIFY `order_id` int unsigned not null,
	    MODIFY `multiplicator` decimal(10, 5) unsigned default 1.00000 not null
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_countries', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_countries` 
	    MODIFY `order_id` int unsigned not null
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_counties', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_counties` 
	    MODIFY `order_id` int unsigned not null
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_cities', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_cities` 
	    MODIFY `order_id` int unsigned not null
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_payment_methods', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_payment_methods` 
	    MODIFY `order_id` int unsigned not null,
	     MODIFY `id` int unsigned not null
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_salutations', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_salutations` 
	    MODIFY `order_id` int unsigned not null";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_shipping_methods', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_shipping_methods` 
	    MODIFY `order_id` int unsigned not null,
	     MODIFY `id` int unsigned not null
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_states', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_states` 
	    MODIFY `order_id` int unsigned not null	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_strings', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_strings`
	    MODIFY `order_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_user_groups', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_user_groups`
	    MODIFY `order_id` INT UNSIGNED NOT NULL,
	    MODIFY `group_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'order_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_users`
	    MODIFY `order_id` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'state')) {
	$keys = ConfigboxUpdateHelper::getKeyNames('#__cbcheckout_order_users', 'state');
	if (!in_array('state', $keys)) {
		$query = "
		create index `state` on #__cbcheckout_order_users (state);
		";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'billingstate')) {
	$keys = ConfigboxUpdateHelper::getKeyNames('#__cbcheckout_order_users', 'billingstate');
	if (!in_array('billingstate', $keys)) {
		$query = "
		create index `billingstate` on #__cbcheckout_order_users (billingstate);
		";
		$db->setQuery($query);
		$db->query();
	}

}
