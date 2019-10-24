<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'page_nav_block_on_missing_selections') == false) {

	$query = "ALTER TABLE `#__configbox_products` ADD `page_nav_block_on_missing_selections` ENUM('0','1','2') NOT NULL DEFAULT '2';";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT * FROM `#__configbox_pages`";
	$db->setQuery($query);
	$pageData = $db->loadAssocList();

	$settings = array();
	foreach ($pageData as $page) {
		if ($page['lock_on_required'] == 1 || $page['lock_on_required'] == 0) {
			$settings[$pageData['product_id']] = $page['lock_on_required'];
		}
	}

	foreach ($settings as $productId => $setting) {
		$query = "UPDATE `#__configbox_products` SET `page_nav_block_on_missing_selections` = '".$setting."' WHERE `id` = ".intval($productId);
		$db->setQuery($query);
		$db->query();
	}

	$query = "ALTER TABLE `#__configbox_pages` DROP `lock_on_required`";
	$db->setQuery($query);
	$db->query();

}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_config', 'page_nav_block_on_missing_selections') == false) {

	$query = "ALTER TABLE `#__configbox_config` CHANGE `lock_on_required` `page_nav_block_on_missing_selections` ENUM('0','1') NOT NULL DEFAULT '0';";
	$db->setQuery($query);
	$db->query();

}