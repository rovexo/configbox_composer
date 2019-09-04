<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'show_product_details_button') == false) {

	$query = "SELECT `key` FROM `#__configbox_strings` WHERE `type` = 24 AND `text` != ''";
	$db->setQuery($query);
	$ids = $db->loadResultList();

	$query = "ALTER TABLE `#__configbox_products` ADD `show_product_details_button` ENUM('0', '1') NULL DEFAULT '0'";
	$db->setQuery($query);
	$db->query();

	if ($ids) {
		$query = "
		UPDATE `#__configbox_products` 
		SET `show_product_details_button` = '1'
		WHERE `id` IN (".implode(',', $ids).")";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'product_details_page_type') == false) {

	$query = "SELECT `key` FROM `#__configbox_strings` WHERE `type` = 24 AND `text` != ''";
	$db->setQuery($query);
	$ids = $db->loadResultList();

	$query = "ALTER TABLE `#__configbox_products` ADD `product_details_page_type` ENUM('none', 'cms_page', 'configbox_page') NULL DEFAULT 'none'";
	$db->setQuery($query);
	$db->query();

	if ($ids) {
		$query = "
		UPDATE `#__configbox_products` 
		SET `product_details_page_type` = 'configbox_page'
		WHERE `id` IN (".implode(',', $ids).")";
		$db->setQuery($query);
		$db->query();
	}

}


