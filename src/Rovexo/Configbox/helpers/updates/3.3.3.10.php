<?php
defined('CB_VALID_ENTRY') or die();
$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'baseprice_recurring')) {
	$query = "
	ALTER TABLE `#__configbox_products`
	    MODIFY `baseprice_recurring` decimal(20, 4) unsigned default 0.0000 not null,
	    MODIFY `baseweight` decimal(20, 3) unsigned default 0.000  not null

	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_pages', 'css_classes')) {
	$query = "
	ALTER TABLE `#__configbox_pages`
	    MODIFY `css_classes` varchar(255) default ''  not null AFTER `ordering`
	";
	$db->setQuery($query);
	$db->query();
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_calculation_matrices', 'multiplicator')) {
	$query = "
	ALTER TABLE `#__configbox_calculation_matrices`
	    MODIFY `multiplicator`  decimal(20, 5) default 0.00000 not null
	";
	$db->setQuery($query);
	$db->query();
}