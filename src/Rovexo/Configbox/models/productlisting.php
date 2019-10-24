<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelProductlisting extends KenedoModelLight {

	/**
	 * @param int $listingId
	 * @return ConfigboxProductData[]
	 */
	function getProductsForListing($listingId) {

		$products = array();

		$ass = ConfigboxCacheHelper::getAssignments();
		$productIds = (isset($ass['listing_to_product'][$listingId])) ? $ass['listing_to_product'][$listingId] : array();

		$productModel = KenedoModel::getModel('ConfigboxModelProduct');

		foreach ($productIds as $productId) {
			$products[$productId] = $productModel->getProduct($productId);
		}
		
		return $products;
		
	}

	/**
	 * @param int $listingId
	 * @return ConfigboxListingData|object
	 */
	function getProductListing($listingId) {
		$model = KenedoModel::getModel('ConfigboxModelAdminlistings');
		$record = $model->getRecord($listingId);
		return $record;
	}

	static function sortProductsByTitle($a, $b) {
		return strcmp($a->title, $b->title);
	}

}
