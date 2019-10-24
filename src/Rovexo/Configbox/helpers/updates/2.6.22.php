<?php
defined('CB_VALID_ENTRY') or die();

// Add product sorting column to product listing table
if (ConfigboxUpdateHelper::tableExists('#__configbox_listings') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_listings', 'product_sorting') == false) {
		$query = "ALTER TABLE  `#__configbox_listings` ADD  `product_sorting` SMALLINT NOT NULL DEFAULT  '0'";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_pcategory_product') == true) {
	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'ordering') == true) {

		$query = "SELECT `id`, `ordering` FROM `#__configbox_products`";
		$db->setQuery($query);
		$products = $db->loadObjectList('id');

		$query = "SELECT * FROM `#__configbox_xref_pcategory_product` GROUP BY `product_id`";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if ($items) {
			foreach ($items as $item) {
				$query = "UPDATE `#__configbox_xref_pcategory_product` SET `ordering` = ".intval($products[$item->product_id]->ordering)." WHERE `product_id` = ".intval($item->product_id);
				$db->setQuery($query);
				$db->query();
			}
		}

		$query = "ALTER TABLE `#__configbox_products` DROP `ordering`";
		$db->setQuery($query);
		$db->query();

	}
}