<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'product_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_positions`
	MODIFY `product_id` INT UNSIGNED DEFAULT NULL,
	MODIFY `taxclass_id` INT UNSIGNED DEFAULT NULL,
	MODIFY `taxclass_recurring_id` INT UNSIGNED DEFAULT NULL,
	MODIFY `price_net` decimal(20, 4) unsigned default 0.0000 not null,
	MODIFY `price_recurring_net` decimal(20, 4) unsigned default 0.0000 not null
	
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'open_amount_net')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_positions`
		DROP `open_amount_net`
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_positions', 'using_deposit')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_positions`
	    DROP `using_deposit`
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_strings', 'type')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_strings`
	    MODIFY `type` INT UNSIGNED NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'platform_user_id')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_users`
	    MODIFY `platform_user_id` INT UNSIGNED NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_order_users', 'language_tag')) {
	$query = "
	ALTER TABLE `#__cbcheckout_order_users`
	    MODIFY `language_tag` varchar(5) NULL
	";
	$db->setQuery($query);
	$db->query();
}

$keys = ConfigboxUpdateHelper::getKeyNames('#__configbox_countries', 'ordering');
if (!$keys) {

	$query = "
	CREATE INDEX `ordering` ON #__configbox_countries (`ordering`);
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableExists('#__configbox_customers')) {
	$query = "DROP TABLE IF EXISTS `#__configbox_customers`";
	$db->setQuery($query);
	$db->query();
}

$constraintName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_config', 'default_country_id');
if ($constraintName) {
	$query = "
	ALTER TABLE `#__configbox_config` DROP FOREIGN KEY `".$constraintName."`
	";
	$db->setQuery($query);
	$db->query();
}

$query = "
	ALTER TABLE `#__configbox_config`
	    ADD CONSTRAINT `".$constraintName."` 
	    FOREIGN KEY (default_country_id) 
	    REFERENCES #__configbox_countries (id)
            ON UPDATE CASCADE 
	        ON DELETE SET NULL
	";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_oldlabels', 'type')) {
	$query = "
	ALTER TABLE `#__configbox_oldlabels`
	    MODIFY `type` INT UNSIGNED NOT NULL,
	    MODIFY `language_tag` VARCHAR(5) NOT NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'magento_product_id')) {
	$query = "ALTER TABLE `#__configbox_products` DROP magento_product_id";
	$db->setQuery($query);
	$db->query();
}


$constraintName = ConfigboxUpdateHelper::getFkConstraintName('#__configbox_calculations', 'product_id');
if ($constraintName) {
	$query = "
	ALTER TABLE `#__configbox_calculations` DROP FOREIGN KEY `".$constraintName."`
	";
	$db->setQuery($query);
	$db->query();

}

$query = "
	ALTER TABLE `#__configbox_calculations`
	    ADD CONSTRAINT `".$constraintName."`
	    FOREIGN KEY (product_id) REFERENCES #__configbox_products (id)
            ON UPDATE CASCADE 
            ON DELETE SET NULL
	";
$db->setQuery($query);
$db->query();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'integer_only')) {
	$query = "ALTER TABLE `#__configbox_elements` DROP `integer_only`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'upload_mime_types')) {
	$query = "ALTER TABLE `#__configbox_elements` MODIFY `upload_mime_types` VARCHAR(255) DEFAULT '' NOT NULL";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_elements')) {

	$query = "
	SELECT CONSTRAINT_NAME
	FROM information_schema.KEY_COLUMN_USAGE
	WHERE 
    TABLE_SCHEMA = DATABASE() AND 
    TABLE_NAME = '#__configbox_elements' AND 
    `REFERENCED_COLUMN_NAME` IS NOT NULL
	";
	$db->setQuery($query);
	$db->query();
	$constraintNames = $db->loadResultList();

	foreach ($constraintNames as $constraintName) {
		$query = "
		ALTER TABLE `#__configbox_elements` DROP FOREIGN KEY `".$constraintName."`
		";
		$db->setQuery($query);
		$db->query();
	}

	$query = "
	ALTER TABLE `#__configbox_elements`
	ADD CONSTRAINT #__configbox_elements_ibfk_1
		FOREIGN KEY (page_id) REFERENCES #__configbox_pages (id),
	ADD CONSTRAINT #__configbox_elements_ibfk_2
		FOREIGN KEY (calcmodel_id_min_val) REFERENCES #__configbox_calculations (id)
		ON UPDATE CASCADE ON DELETE SET NULL,
	ADD CONSTRAINT #__configbox_elements_ibfk_3
		FOREIGN KEY (calcmodel_id_max_val) REFERENCES #__configbox_calculations (id)
		 ON UPDATE CASCADE ON DELETE SET NULL,
	ADD CONSTRAINT #__configbox_elements_ibfk_4
		FOREIGN KEY (calcmodel) REFERENCES #__configbox_calculations (id)
		ON UPDATE CASCADE ON DELETE SET NULL,
	ADD CONSTRAINT #__configbox_elements_ibfk_5
		FOREIGN KEY (calcmodel_recurring) REFERENCES #__configbox_calculations (id)
		ON UPDATE CASCADE ON DELETE SET NULL,
	ADD CONSTRAINT #__configbox_elements_ibfk_6
		FOREIGN KEY (calcmodel_weight) REFERENCES #__configbox_calculations (id)
		ON UPDATE CASCADE ON DELETE SET NULL
	";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_tax_class_rates', 'zone_id')) {
	$query = "ALTER TABLE `#__configbox_tax_class_rates` DROP `zone_id`";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_users', 'language_tag')) {
	$query = "
	ALTER TABLE `#__configbox_users`
	    MODIFY `language_tag` varchar(5) NULL
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_xref_element_option', 'default')) {
	$query = "
	ALTER TABLE `#__configbox_xref_element_option` 
	    MODIFY `default` INT UNSIGNED NOT NULL,
	    MODIFY `published` INT UNSIGNED NOT NULL
	    ";
	$db->setQuery($query);
	$db->query();
}



if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_element_option')) {

	$query = "
	SELECT CONSTRAINT_NAME
	FROM information_schema.KEY_COLUMN_USAGE
	WHERE 
    TABLE_SCHEMA = DATABASE() AND 
    TABLE_NAME = '#__configbox_xref_element_option' AND 
    `REFERENCED_COLUMN_NAME` IS NOT NULL
	";
	$db->setQuery($query);
	$db->query();
	$constraintNames = $db->loadResultList();

	foreach ($constraintNames as $constraintName) {
		$query = "
		ALTER TABLE `#__configbox_xref_element_option` DROP FOREIGN KEY `".$constraintName."`
		";
		$db->setQuery($query);
		$db->query();
	}

	$query = "
	ALTER TABLE `#__configbox_xref_element_option`
	ADD constraint #__configbox_xref_element_option_ibfk_1
		foreign key (element_id) references #__configbox_elements (id),
	ADD constraint #__configbox_xref_element_option_ibfk_2
		foreign key (option_id) references #__configbox_options (id),
	ADD constraint #__configbox_xref_element_option_ibfk_3
		foreign key (calcmodel) references #__configbox_calculations (id)
	    ON UPDATE CASCADE ON DELETE SET NULL,
	ADD constraint #__configbox_xref_element_option_ibfk_4
		foreign key (calcmodel_recurring) references #__configbox_calculations (id)
	    ON UPDATE CASCADE ON DELETE SET NULL,
	ADD constraint #__configbox_xref_element_option_ibfk_5
		foreign key (calcmodel_weight) references #__configbox_calculations (id)
		ON UPDATE CASCADE ON DELETE SET NULL
	";
	$db->setQuery($query);
	$db->query();

}
