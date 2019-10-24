<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerProduct extends KenedoController {

	/**
	 * @return ConfigboxModelProduct
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelProduct');
	}

	/**
	 * @return ConfigboxViewProduct
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewProduct');
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	/**
	 * Outputs the product view
	 */
	function display() {

		// Fix labels (in case of language change with JoomFish or when labels have changed)
		if (KRequest::getInt('prod_id', 0) == 0 && !empty($GLOBALS['productLabel'])) {
			KLog::log('No prod id, but label found. Trying to fix label (prodlabel: "'.$GLOBALS['productLabel'].'", prod_id:"'.KRequest::getInt('prod_id',0).'"', 'debug');
			KenedoModel::getModel('ConfigboxModelProduct')->fixLabels();
		}

		$this->getDefaultView()->setProductId(KRequest::getInt('prod_id', 0))->display();
	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		$prodId = (!empty($queryParameters['prod_id'])) ? intval($queryParameters['prod_id']) : 0;

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=product&prod_id='.$prodId);

		// Perfect match, thank you very much. Return the option and menu item id and good bye.
		if ($id) {
			$queryParameters['option'] = 'com_configbox';
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
			return array();
		}

		// Get product listings the product belongs to
		$listingIds = KenedoRouterHelper::getListingIds($prodId);

		// See if we currently are on a product listing page
		$currentListingId = KRequest::getInt('listing_id');

		// If so use that listing if the product belongs to it
		if ($currentListingId && in_array($currentListingId, $listingIds)) {
			$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=productlisting&listing_id='.$currentListingId);
			if ($id) {
				$queryParameters['option'] = 'com_configbox';
				$queryParameters['Itemid'] = $id;
				unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id'], $queryParameters['listing_id']);
				$prodLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId, $langTag);
				return array($prodLabel);
			}
		}

		// Check for product listing menu items where the product belongs to
		if ($listingIds) {
			foreach ($listingIds as $listingId) {
				$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=productlisting&listing_id='.$listingId);
				if ($id) {
					$queryParameters['option'] = 'com_configbox';
					$queryParameters['Itemid'] = $id;
					unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id'], $queryParameters['listing_id']);
					$prodLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId, $langTag);
					return array($prodLabel);
				}
			}
		}

		// We're getting desperate to find something good here, let's try product listing links without a set listing id
		$searchLinks = array(
			'index.php?option=com_configbox&view=productlisting&listing_id=0',
			'index.php?option=com_configbox&view=productlisting',
		);

		foreach ($searchLinks as $searchLink) {
			// Check for a matching menu item
			$id = KenedoRouterHelper::getItemIdByLink($searchLink);
			if ($id) {
				$queryParameters['option'] = 'com_configbox';
				$queryParameters['Itemid'] = $id;
				unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id'], $queryParameters['listing_id']);
				$prodLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId, $langTag);
				return array($prodLabel);
			}
		}

		$prodLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId, $langTag);

		if ($prodLabel) {
			// This is sad, that poor little product with nowhere to go. We wish it all the best and return the product label.
			unset ($queryParameters['view'], $queryParameters['prod_id']);
			return array($prodLabel);
		}
		else {
			// I'm hearth-broken now. Not even a product label to send the little product off! Where has the world come to?
			return array();
		}

	}

}