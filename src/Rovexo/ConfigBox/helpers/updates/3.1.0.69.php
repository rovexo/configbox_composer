<?php
defined('CB_VALID_ENTRY') or die();

if (KenedoPlatform::getName() == 'magento') {

	if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_products', 'magento_product_id') == true) {

		$query = "
	    INSERT INTO `#__configbox_magento_xref_mprod_cbprod` (`cb_product_id`, `magento_product_id`) 
		SELECT `#__configbox_products`.`id`, `#__configbox_products`.`magento_product_id` 
		FROM `#__configbox_products` WHERE (magento_product_id IS NOT NULL AND magento_product_id > 0) 
	    ON DUPLICATE KEY UPDATE `cb_product_id` = VALUES(`cb_product_id`), `magento_product_id` = VALUES(`magento_product_id`)";
		$db->setQuery($query);
		$db->query();

		$query = "ALTER TABLE #__configbox_products DROP COLUMN `magento_product_id`";
		$db->setQuery($query);
		$db->query();

	}

}
