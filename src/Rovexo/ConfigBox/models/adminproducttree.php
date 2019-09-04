<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminproducttree extends KenedoModel {

	/**
	 * @param bool 		$inclAnswers	True if you want assigned answers in the tree as well. Defaults to false
	 * @param int|null 	$productId 		Normally you get a full tree, Pprovide a product id if you
	 * 									want only this product's branch.
	 * @param int|null 	$listingId 		Supply a listing ID if you want to get products of a particular  listing only.
	 * @return array	$tree			Nested array with products/pages/questions. dump() it to see details.
	 */
	public function getTree($inclAnswers = true, $productId = NULL, $listingId = NULL) {
	
		$db = KenedoPlatform::getDb();

		$query = "
        SELECT p.`id`, p.`published` 
        FROM `#__configbox_products` AS p
        LEFT JOIN `#__configbox_xref_listing_product` AS xreflist ON p.id = xreflist.product_id
        ";

		$wheres = array();

		if ($productId) {
			$wheres[] = "p.id = ".intval($productId);
		}

		if ($listingId) {
			$wheres[] = "xreflist.listing_id = ".intval($listingId);
		}

		if (count($wheres)) {
			$query .= "WHERE ".implode(' AND ', $wheres);
		}

		$query .= " GROUP BY p.id";

		$db->setQuery($query);
		$products = $db->loadAssocList('id');
		
		if (!$products) {
			$products = array();
		}
		
		foreach ($products as &$product) {
			$product['title'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 1, $product['id']);
			$product['pages'] = array();
			$query = 'index.php?option=com_configbox&controller=adminproducts&task=ajaxDelete&id='.intval($product['id']).'&format=raw&lang='.KText::getLanguageCode();
			$product['url_delete'] = KLink::getRoute($query);
		}
	
		$query = "SELECT `id`, `product_id`, `published` FROM `#__configbox_pages`";
		if ($productId) {
			$query .= " WHERE `product_id` = ".intval($productId);
		}
		$query .= " ORDER BY `ordering`";
		$db->setQuery($query);
		$pages = $db->loadAssocList('id');
	
		foreach ($pages as &$page) {
			$page['title'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 3, $page['id']);
			$page['questions'] = array();
			$query = 'index.php?option=com_configbox&controller=adminpages&task=ajaxDelete&id='.intval($page['id']).'&format=raw&lang='.KText::getLanguageCode();
			$page['url_delete'] = KLink::getRoute($query);
		}
		
		
		$query = "SELECT `id`, `page_id`, `internal_name`, `published` FROM `#__configbox_elements`";
		if ($productId) {
			$keys = array_keys($pages);
			if (!count($keys)) {
				$keys = array(0);
			}
			$query .= " WHERE `page_id` IN (".implode(',',$keys).") ORDER BY `ordering`";
			
		}
		else {
			$query .= " ORDER BY `ordering`";
		}

		$db->setQuery($query);
		$questions = $db->loadAssocList('id');
	
		foreach ($questions as &$question) {
			$question['title'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $question['id']);
			$question['answers'] = array();
			$query = 'index.php?option=com_configbox&controller=adminelements&task=ajaxDelete&id='.intval($question['id']).'&format=raw&lang='.KText::getLanguageCode();
			$question['url_delete'] = KLink::getRoute($query);
		}
		
		if ($inclAnswers) {
			$query = "SELECT `id`, `element_id`, `option_id`, `published` FROM `#__configbox_xref_element_option` ORDER BY `ordering`";
			
			if ($productId) {
				$keys = array_keys($questions);
				if (!count($keys)) {
					$keys = array(0);
				}
				$query .= "WHERE `element_id` IN (".implode(',',$keys).")";
					
			}
			
			$db->setQuery($query);
			$answers = $db->loadAssocList('id');
			
			foreach ($answers as &$answer) {
				$answer['title'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $answer['option_id']);

				if (!isset($questions[$answer['element_id']])) {
					continue;
				}
				$questions[$answer['question_id']]['answers'][$answer['id']] = $answer;
			}
			
		}
		
		foreach ($questions as &$question) {
			if (!isset($pages[$question['page_id']])) {
				continue;
			}
			$pages[$question['page_id']]['questions'][$question['id']] = $question;
		}
		
		foreach ($pages as &$page) {
			if (!isset($products[$page['product_id']])) {
				continue;
			}
			$products[$page['product_id']]['pages'][$page['id']] = $page;
		}
		
		usort($products, array("ConfigboxModelAdminproducttree", "sortByTitle"));
		
		return $products;
	
	}

	function sortByTitle($a, $b) {
		return strcmp($a["title"], $b["title"]);
	}
	
}