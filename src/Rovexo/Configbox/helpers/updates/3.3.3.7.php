<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'language_tag')) {
	$query = "
	ALTER TABLE `#__configbox_config` MODIFY `language_tag` varchar(5) NULL
	";
	$db->setQuery($query);
	$db->query();
}

$keys = ConfigboxUpdateHelper::getKeyNames('#__configbox_xref_country_payment_method', 'payment_id');
foreach ($keys as $key) {
	if ($key !== 'PRIMARY') {
		$query = "
		ALTER TABLE #__configbox_xref_country_payment_method RENAME INDEX `".$key."` TO `payment_id`";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_shopdata')) {

	$query = "
	SELECT CONSTRAINT_NAME
	FROM information_schema.KEY_COLUMN_USAGE
	WHERE 
    TABLE_SCHEMA = DATABASE() AND 
    TABLE_NAME = '#__configbox_shopdata' AND 
    `REFERENCED_COLUMN_NAME` IS NOT NULL
	";
	$db->setQuery($query);
	$db->query();
	$constraintNames = $db->loadResultList();

	foreach ($constraintNames as $constraintName) {
		$query = "
		ALTER TABLE `#__configbox_shopdata` DROP FOREIGN KEY `".$constraintName."`
		";
		$db->setQuery($query);
		$db->query();
	}

	$query = "
	ALTER TABLE `#__configbox_shopdata`
	
	add constraint #__configbox_shopdata_ibfk_1
			foreign key (country_id) references #__configbox_countries (id)
				on update cascade on delete set null,
	add constraint #__configbox_shopdata_ibfk_2
			foreign key (state_id) references #__configbox_states (id)
				on update cascade on delete set null

	";
	$db->setQuery($query);
	$db->query();

}




if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_country_zone')) {

	$query = "
	SELECT CONSTRAINT_NAME
	FROM information_schema.KEY_COLUMN_USAGE
	WHERE 
    TABLE_SCHEMA = DATABASE() AND 
    TABLE_NAME = '#__configbox_xref_country_zone' AND 
    `REFERENCED_COLUMN_NAME` IS NOT NULL
	";
	$db->setQuery($query);
	$db->query();
	$constraintNames = $db->loadResultList();

	foreach ($constraintNames as $constraintName) {
		$query = "
		ALTER TABLE `#__configbox_xref_country_zone` DROP FOREIGN KEY `".$constraintName."`
		";
		$db->setQuery($query);
		$db->query();
	}

	$query = "
	ALTER TABLE `#__configbox_xref_country_zone`
	add constraint #__configbox_xref_country_zone_ibfk_1
        foreign key (zone_id) references #__configbox_zones (id)
            on update cascade on delete cascade,
    add constraint #__configbox_xref_country_zone_ibfk_2
        foreign key (country_id) references #__configbox_countries (id)
            on update cascade on delete cascade
	";
	$db->setQuery($query);
	$db->query();

}

