<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'page_nav_cart_button_last_page_only') == false) {

	$query = "ALTER TABLE `#__configbox_products` ADD `page_nav_cart_button_last_page_only` ENUM('0','1','2') NOT NULL DEFAULT '2';";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_pages`";
	$db->setQuery($query);
	$pageData = $db->loadAssocList();

	$settings = array();
	foreach ($pageData as $page) {
		if ($page['finish_last_page_only'] == 1 || $page['finish_last_page_only'] == 0) {
			$settings[$page['product_id']] = $page['finish_last_page_only'];
		}
	}

	foreach ($settings as $productId => $setting) {
		$query = "UPDATE `#__configbox_products` SET `page_nav_cart_button_last_page_only` = '".$setting."' WHERE `id` = ".intval($productId);
		$db->setQuery($query);
		$db->query();
	}

	$query = "ALTER TABLE `#__configbox_pages` DROP `finish_last_page_only`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'page_nav_cart_button_last_page_only') == false) {

	$query = "ALTER TABLE `#__configbox_config` CHANGE `finish_last_page_only` `page_nav_cart_button_last_page_only` ENUM('0','1') NOT NULL DEFAULT '0';";
	$db->setQuery($query);
	$db->query();

}