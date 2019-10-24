<?php
defined('CB_VALID_ENTRY') or die();

if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_pcategory_product') == true) {
	
	/* Remove dead product assignments */
	$query = "SELECT GROUP_CONCAT(id SEPARATOR ', ') AS ids, (1) AS dummy FROM `#__configbox_products` GROUP BY `dummy`";
	$db->setQuery($query);
	$products = $db->loadAssoc();
	
	if (!empty($products['ids'])) {
		$query = "DELETE FROM `#__configbox_xref_pcategory_product` WHERE `product_id` NOT IN (".$products['ids'].")";
		$db->setQuery($query);
		$db->query();
	}
	
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_elements') == true) {
	
	// Correct and set indices for rules columns
	$query = "SHOW INDEX FROM `#__configbox_elements`";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	
	$properIndexSet = false;
	foreach ($indices as $index) {
		if ($index['Column_name'] == 'rules' && $index['Key_name'] != 'rules_index') {
			$query = "ALTER IGNORE TABLE `#__configbox_elements` DROP INDEX  `".$index['Key_name']."`";
			$db->setQuery($query);
			$db->query();
		}
		elseif ($index['Column_name'] == 'rules' && $index['Key_name'] == 'rules_index') {
			$properIndexSet = true;
		}
	}
	
	if ($properIndexSet == false) {
		$query = "ALTER TABLE `#__configbox_elements` ADD INDEX  `rules_index` (  `rules` ( 3 ) )";
		$db->setQuery($query);
		$db->query();
	}
}

if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_element_option') == true) {
	$query = "SHOW INDEX FROM `#__configbox_xref_element_option`";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	
	$properIndexSet = false;
	foreach ($indices as $index) {
		if ($index['Column_name'] == 'rules' && $index['Key_name'] != 'rules_index') {
			$query = "ALTER IGNORE TABLE `#__configbox_xref_element_option` DROP INDEX  `".$index['Key_name']."`";
			$db->setQuery($query);
			$db->query();
		}
		elseif ($index['Column_name'] == 'rules' && $index['Key_name'] == 'rules_index') {
			$properIndexSet = true;
		}
		
	}
	
	if (!$properIndexSet) {
		$query = "ALTER TABLE `#__configbox_xref_element_option` ADD INDEX  `rules_index` (  `rules` ( 3 ) )";
		$db->setQuery($query);
		$db->query();
	}

}

if (ConfigboxUpdateHelper::tableFieldExists('#__cbcheckout_strings', 'language_tag') == false) {
	
	// Set unique index to cb strings
	$query = "SHOW INDEX FROM `#__cbcheckout_strings`";
	$db->setQuery($query);
	$indices = $db->loadAssocList();
	if ($indices) {
		foreach ($indices as $index) {
			if ($index['Key_name'] == 'type/key/lang_id' && $index['Non_unique'] == '1') {
				$query = "ALTER IGNORE TABLE `#__cbcheckout_strings` DROP INDEX  `type/key/lang_id` ,
				ADD UNIQUE  `type/key/lang_id` (  `type` ,  `key` ,  `lang_id` )";
				$db->setQuery($query);
				$db->query();
				break;
			}
		}
	}
	
}

if (ConfigboxUpdateHelper::tableFieldExists('#__configbox_elements', 'element_css_classes') == false) {
	
	$query = "ALTER TABLE `#__configbox_elements` ADD `element_css_classes` VARCHAR(100) NOT NULL DEFAULT ''";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_elements` SET `element_css_classes` = 'left-half', `layoutname` = 'default' WHERE `layoutname` = 'column_left' ";
	$db->setQuery($query);
	$db->query();
	
	$query = "UPDATE `#__configbox_elements` SET `element_css_classes` = 'right-half', `layoutname` = 'default' WHERE `layoutname` = 'column_right' ";
	$db->setQuery($query);
	$db->query();
	
}
