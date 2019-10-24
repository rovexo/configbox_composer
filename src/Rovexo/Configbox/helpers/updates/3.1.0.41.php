<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::keyExists('#__configbox_connectors', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_connectors` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_connectors', 'ordering') === false) {
	$query = "ALTER TABLE `#__configbox_connectors` ADD INDEX `ordering` (`ordering`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_currencies', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_currencies` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_currencies', 'ordering') === false) {
	$query = "ALTER TABLE `#__configbox_currencies` ADD INDEX `ordering` (`ordering`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_groups', 'joomla_user_group_id') === false) {
	$query = "ALTER TABLE `#__configbox_groups` ADD INDEX `joomla_user_group_id` (`joomla_user_group_id`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_listings', 'ordering') === true) {
	$query = "ALTER TABLE `#__configbox_listings` DROP `ordering`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_notifications', 'statuscode') === false) {
	$query = "ALTER TABLE `#__configbox_notifications` ADD INDEX `statuscode` (`statuscode`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_payment_methods', 'published') === false) {
	$query = "ALTER TABLE `#__configbox_payment_methods` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_payment_methods', 'ordering') === false) {
	$query = "ALTER TABLE `#__configbox_payment_methods` ADD INDEX `ordering` (`ordering`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_product_detail_panes', 'ordering') === false) {
	$query = "ALTER TABLE `#__configbox_product_detail_panes` ADD INDEX `ordering` (`ordering`);";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_product_images') === true) {
	$query = "DELETE FROM `#__configbox_product_images`";
	$db->setQuery($query);
	$db->query();

	$query = "DROP TABLE `#__configbox_product_images`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::keyExists('#__configbox_reviews', 'date_created') === false) {

	$query = "ALTER TABLE `#__configbox_reviews` ADD INDEX `date_created` (`date_created`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_reviews', 'review_type') === true) {

	$query = "ALTER TABLE `#__configbox_reviews` DROP `review_type`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_shippers', 'published') === false) {

	$query = "ALTER TABLE `#__configbox_shippers` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_shippers` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_shipping_methods', 'published') === false) {

	$query = "ALTER TABLE `#__configbox_shipping_methods` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_shipping_methods` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'ordering') === true) {

	$query = "ALTER TABLE `#__configbox_shipping_methods` MODIFY `ordering` SMALLINT NOT NULL DEFAULT '0';";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_shipping_methods', 'deliverytime') === true) {

	$query = "ALTER TABLE `#__configbox_shipping_methods` MODIFY `deliverytime` TINYINT UNSIGNED NOT NULL DEFAULT '0';";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_states', 'published') === false) {

	$query = "ALTER TABLE `#__configbox_states` ADD INDEX `published` (`published`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_states', 'custom_1') === true) {

	$query = "ALTER TABLE `#__configbox_states` MODIFY `custom_1` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_states` MODIFY `custom_2` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_states` MODIFY `custom_3` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_states` MODIFY `custom_4` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_states', 'ordering') === true) {

	$query = "ALTER TABLE `#__configbox_states` MODIFY `ordering` SMALLINT NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'language_id') === true) {

	$query = "ALTER TABLE `#__configbox_users` DROP `language_id`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'platform_user_id') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `platform_user_id` (`platform_user_id`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'state') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `state` (`state`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'billingstate') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `billingstate` (`billingstate`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'county_id') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `county_id` (`county_id`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'billingcounty_id') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `billingcounty_id` (`billingcounty_id`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'country') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `country` (`country`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'billingcountry') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `billingcountry` (`billingcountry`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'city_id') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `city_id` (`city_id`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_users', 'billingcity_id') === false) {

	$query = "ALTER TABLE `#__configbox_users` ADD INDEX `billingcity_id` (`billingcity_id`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'custom_1') === true) {

	$query = "ALTER TABLE `#__configbox_users` MODIFY `custom_1` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_users` MODIFY `custom_2` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_users` MODIFY `custom_3` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_users` MODIFY `custom_4` VARCHAR(255) DEFAULT ''";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_xref_listing_product', 'ordering') === false) {

	$query = "ALTER TABLE `#__configbox_xref_listing_product` MODIFY `ordering` SMALLINT NOT NULL DEFAULT 0;";
	$db->setQuery($query);
	$db->query();

	$query = "ALTER TABLE `#__configbox_xref_listing_product` ADD INDEX `ordering` (`ordering`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'published') === true) {

	$query = "ALTER TABLE `#__configbox_products` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_reviews', 'published') === true) {

	$query = "ALTER TABLE `#__configbox_reviews` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_payment_methods', 'published') === true) {

	$query = "ALTER TABLE `#__configbox_payment_methods` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_payment_methods', 'ordering') === true) {

	$query = "ALTER TABLE `#__configbox_payment_methods` MODIFY `ordering` SMALLINT NOT NULL DEFAULT 0";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_pages', 'published') === true) {

	$query = "ALTER TABLE `#__configbox_pages` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_pages', 'ordering') === true) {

	$query = "ALTER TABLE `#__configbox_pages` MODIFY `ordering` SMALLINT NOT NULL DEFAULT 0";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_options', 'sku') === false) {

	$query = "ALTER TABLE `#__configbox_options` ADD INDEX `sku` (`sku`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'available') === true) {

	$query = "ALTER TABLE `#__configbox_options` MODIFY `available` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_options', 'availibility_date') === true) {

	$query = "ALTER TABLE `#__configbox_options` ALTER COLUMN `availibility_date` SET DEFAULT NULL;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_oldlabels', 'lang_id') === true) {

	$query = "ALTER TABLE `#__configbox_oldlabels` DROP `lang_id`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_oldlabels', 'last_visit') === true) {

	$query = "ALTER TABLE `#__configbox_oldlabels` DROP `last_visit`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_oldlabels', 'created') === false) {

	$query = "ALTER TABLE `#__configbox_oldlabels` ADD INDEX `created` (`created`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::keyExists('#__configbox_oldlabels', 'prod_id') === false) {

	$query = "ALTER TABLE `#__configbox_oldlabels` ADD INDEX `prod_id` (`prod_id`);";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_listings', 'published') === true) {

	$query = "ALTER TABLE `#__configbox_listings` MODIFY `published` ENUM ('0', '1') NOT NULL DEFAULT 1;";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_listings', 'product_sorting') === true) {

	$query = "ALTER TABLE `#__configbox_listings` MODIFY `product_sorting` ENUM ('0', '1') NOT NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

}