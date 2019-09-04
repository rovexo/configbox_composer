<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__configbox_products') == true && ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'magento_product_id') == false) {
	$query = "ALTER TABLE  `#__configbox_products` ADD  `magento_product_id` INT(10) UNSIGNED NULL DEFAULT NULL , ADD INDEX (  `magento_product_id` )";
	$db->setQuery($query);
	$db->query();
}