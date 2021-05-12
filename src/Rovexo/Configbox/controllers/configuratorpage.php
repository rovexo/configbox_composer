<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerConfiguratorpage extends KenedoController {

	/**
	 * @return ConfigboxModelConfiguratorpage $model
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelConfiguratorpage');
	}

	/**
	 * @return ConfigboxViewConfiguratorpage
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewConfiguratorpage');
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
	 * Outputs the configurator page view
	 */
	function display() {

		// Fix labels (in case of language change with JoomFish or when labels have changed)
		if ((KRequest::getInt('prod_id', 0) == 0 && !empty($GLOBALS['productLabel'])) || (KRequest::getInt('page_id', 0) == 0 && !empty($GLOBALS['pageLabel']) )) {
			KLog::log('No cat or prod id, but label found. Trying to fix label (prodlabel: "'.$GLOBALS['productLabel'].'", page label: "'.$GLOBALS['pageLabel'].'", prod_id:"'.KRequest::getInt('prod_id',0).'", page_id:"'.KRequest::getInt('page_id',0).'")','debug');
			KenedoModel::getModel('ConfigboxModelProduct')->fixLabels();
		}

		$this->getDefaultView()->setPageId(KRequest::getInt('page_id'))->display();
	}

	function getPageHtml() {
		$pageId = KRequest::getInt('pageId', 0);
		echo $this->getDefaultView()->setPageId($pageId)->getHtml();
	}

	/**
	 * Renders JSON data about with missing selections for the current cart position.
	 *
	 * @throws Exception
	 */
	function getMissingSelectionsProduct() {
		$positionId = KRequest::getInt('cartPositionId', null);
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$missingSelections = $positionModel->getMissingSelections(null, $positionId);
		echo json_encode($missingSelections);
	}

	/**
	 * Renders JSON data about with missing selections for the current cart position, but only for the given page.
	 *
	 * @throws Exception
	 */
	function getMissingSelectionsPage() {
		$positionId = KRequest::getInt('cartPositionId', null);
		$pageId = KRequest::getInt('pageId', null);
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$missingSelections = $positionModel->getMissingSelections($pageId, $positionId);
		echo json_encode($missingSelections);
	}

	/**
	 * @throws Exception
	 * @depecated Use getMissingSelectionsProduct() instead
	 */
	function getMissingSelections() {
		$this->getMissingSelectionsProduct();
	}

	/**
	 * Requested via XHR by JS: com_configbox.sendSelectionToServer()
	 * It handles a make a change in the configuration, and sends a JSON string with comprehensive info and instructions
	 * back. The data returned is handled in the browser with configbox/configurator.processServerResponse().
	 *
	 * @throws Exception
	 */
	function makeSelection() {

		KLog::start('99 total for ConfigboxControllerConfiguratorpage:makeSelection');

		KLog::start('01 prepare');

		// Set input variables

		// The element that gets a new selection
		$questionId = KRequest::getInt('questionId', false);
		$selection = KRequest::getString('selection', false);
		$pageId = KRequest::getInt('pageId', 0);
		$gotConfirmation = (KRequest::getInt('confirmed', 0) != 0);

		// We do this because the standalone CB does not deal with en-GB, en-US and alike yet
		$languageTag = KRequest::getString('languageTag');
		KText::setLanguage($languageTag);

		// Process input and prepare the response
		$response = ConfigboxConfiguratorHelper::getMakeSelectionResponse($questionId, $selection, $pageId, $gotConfirmation);

		// Finally send the response
		$this->sendResponse($response);

	}

	/**
	 * Helper method for self::makeSelection to render the JSON data.
	 * @param $response
	 */
	protected function sendResponse($response) {

		// Run postMakeSelection to send more info along
		if (function_exists('postMakeSelection')) {
			postMakeSelection($response);
		}

		if (defined('CONFIGBOX_ENABLE_PERFORMANCE_TRACKING') && CONFIGBOX_ENABLE_PERFORMANCE_TRACKING) {
			KLog::stop('99 total for ConfigboxControllerConfiguratorpage:makeSelection');
			$response['performance']['counts'] = KLog::getCounts();
			$response['performance']['counts']['queries'] = KenedoPlatform::getDb()->getQueryCount();
			$response['performance']['queries'] = KenedoPlatform::getDb()->getQueryList();
			$response['performance']['totalQueryTime'] = KenedoPlatform::getDb()->getTotalQueryTime();
			$response['performance']['timings'] = KLog::getTimings();
		}

		ob_clean();
		header('Cache-Control: no-cache, must-revalidate');

		KenedoPlatform::p()->setDocumentMimeType('application/json');
		echo json_encode($response);

	}

	/**
	 * Takes in cartPositionId from request data and adds product to the cart.
	 * Responds with object in JSON (success: true|false, redirectUrl to cart).
	 * @throws Exception
	 */
	function addConfigurationToCart() {

		try {
			$positionId = KRequest::getInt('cartPositionId');

			if (!$positionId) {
				throw new Exception('No cart position ID provided.');
			}

			$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

			if ($positionModel->userOwnsPosition($positionId) == false) {
				throw new Exception('Cart position does not exist or does not belong to your user account.');
			}

			$position = $positionModel->getPosition($positionId);
			if (!$position) {
				throw new Exception('Cart position does not exist or does not belong to your user account.');
			}

			// Check for missing elements
			$missingSelections = $positionModel->getMissingSelections();
			if (count($missingSelections) !== 0) {
				throw new Exception('Configuration is missing selections.');
			}

			// Set position's flag to finished
			$positionModel->editPosition($positionId, array('finished'=>1));

			// Fire the 'add to cart' event
			$cartModel = KenedoModel::getModel('ConfigboxModelCart');
			$cartDetails = $cartModel->getCartDetails($position->cart_id);

			KenedoObserver::triggerEvent('onConfigBoxAddToCart', array(&$cartDetails));

			$url = KLink::getRoute($cartDetails->redirectURL, false);

			echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->setCustomData('redirectUrl', $url)->toJson();
		}
		catch (Exception $e) {
			$msg = KText::_('FEEDBACK_ADD_TO_CART_FAILED');
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setFeedback($msg)->toJson();
		}

	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		// Get prod and page id
		$prodId = (!empty($queryParameters['prod_id'])) ? intval($queryParameters['prod_id']) : 0;
		$pageId = (!empty($queryParameters['page_id'])) ? intval($queryParameters['page_id']) : 0;

		// Case product id and page id is set
		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId.'&page_id='.$pageId, $langTag);
		if ($id) {
			$queryParameters['option'] = 'com_configbox';
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
			return array();
		}

		// Case product id is set and page id is not
		// Missing page id is fine, it will be figured out by finding the first page of the product
		if ($pageId == 0) {
			$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId.'&page_id=0', $langTag);
			if ($id) {
				$queryParameters['option'] = 'com_configbox';
				$queryParameters['Itemid'] = $id;
				unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
				return array();
			}
		}

		// Ok, from now on we may need the labels for product and page
		$prodLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 17, $prodId, $langTag);
		$pageLabel = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 18, $pageId, $langTag);

		// Find a matching product view menu item that is a parent menu item of the current menu item
		$id = KenedoRouterHelper::getParentProductMenuItemId($prodId, $langTag);
		if ($id) {
			$queryParameters['option'] = 'com_configbox';
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
			return array($pageLabel);
		}

		// Find a any matching product menu item
		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=product&prod_id='.$prodId, $langTag);
		if ($id) {
			$queryParameters['option'] = 'com_configbox';
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
			return array($pageLabel);
		}

		// Get product listings the product belongs to
		$listingIds = KenedoRouterHelper::getListingIds($prodId);

		// Check if there are any parent product listing menu items in the current path - and use the item id in case
		if ($listingIds) {

			$itemId = KenedoRouterHelper::getParentListingMenuItemId($listingIds, $langTag);

			if ($itemId) {
				$queryParameters['option'] = 'com_configbox';
				$queryParameters['Itemid'] = $itemId;
				unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
				return array($prodLabel,$pageLabel);
			}

		}

		// Check for product listing menu items where the product belongs to, no matter if in path or not
		foreach ($listingIds as $listingId) {
			$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=productlisting&listing_id='.$listingId, $langTag);
			if ($id) {
				$queryParameters['option'] = 'com_configbox';
				$queryParameters['Itemid'] = $id;
				unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
				return array($prodLabel, $pageLabel);
			}
		}

		// Find a product listing menu item with no specific product listing id in path
		$id = KenedoRouterHelper::getParentListingMenuItemId(array('0'));
		if ($id) {
			$queryParameters['option'] = 'com_configbox';
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
			return array($prodLabel, $pageLabel);
		}

		// Try with general product listings no matter if in path or not
		$searchLink = 'index.php?option=com_configbox&view=productlisting&listing_id=0';

		// Check for a matching menu item
		$id = KenedoRouterHelper::getItemIdByLink($searchLink);

		if ($id) {
			$queryParameters['option'] = 'com_configbox';
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);
			return array($prodLabel, $pageLabel);
		}

		// If no product listing link is found, try pages
		$searchLinks = array();
		// Item with both params set
		$searchLinks[1] = 'index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId.'&page_id='.$pageId;
		// Item with only prod_id param set
		$searchLinks[2] = 'index.php?option=com_configbox&view=configuratorpage&prod_id='.$prodId.'&page_id=0';
		// Item with both params set to zero
		$searchLinks[3] = 'index.php?option=com_configbox&view=configuratorpage&prod_id=0&page_id=0';
		// Item with no params, empty params were not set in earlier Joomla versions
		$searchLinks[4] = 'index.php?option=com_configbox&view=configuratorpage';

		foreach ($searchLinks as $key => $searchLink) {

			$id = KenedoRouterHelper::getItemIdByLink($searchLink, $langTag);

			if ($id) {
				$queryParameters['option'] = 'com_configbox';
				$queryParameters['Itemid'] = $id;
				unset($queryParameters['view'], $queryParameters['page_id'], $queryParameters['prod_id']);

				switch ($key) {

					case 1: return array();
					case 2:
						// Special case: if go for the first page in a prod/nopage menu item, do not add the page label (double content)
						// The missing page label is dealt with in the configurator page view
						if (KenedoRouterHelper::isFirstPage($prodId,$pageId)) {
							return array();
						}
						else {
							return array($pageLabel);
						}
					default: return array($prodLabel,$pageLabel);
				}

			}
		}

		// This should only happen, if no matching menu items are found anywhere
		$segments = array($prodLabel,$pageLabel);
		unset ($queryParameters['view'],$queryParameters['prod_id'],$queryParameters['page_id'],$queryParameters['Itemid']);
		return $segments;

	}

}