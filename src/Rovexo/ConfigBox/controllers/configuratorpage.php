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

	/**
	 * Renders JSON data about with missing selections for the current cart position.
	 *
	 * @throws Exception
	 */
	function getMissingSelections() {
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$missingSelections = $positionModel->getMissingSelections();
		echo json_encode($missingSelections);
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

		ob_start();

		header('Content-Type: application/json', true);
		echo json_encode($response);

		// Deal with compression of the response
		$data = ob_get_clean();

		if (defined('CONFIGBOX_CONFIGURATOR_NO_COMPRESSSION')) {
			echo $data;
			exit();
		}

        if ((KenedoPlatform::getName() == 'magento') || (KenedoPlatform::getName() == 'magento2')) {
			echo $data;
			exit();
		}

		// Here we just deal with encoding
		if (ini_get('zlib.output_compression')) {
			echo $data;
		}
		elseif (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			echo $data;
		}
		elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			header('Content-Encoding: gzip');
			echo gzencode($data, 2);
		}
		elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
			header('Content-Encoding: x-gzip');
			echo gzencode($data, 2);
		}
		else {
			echo $data;
		}

		// Do a blunt exit to avoid any after-render processing of the platform
		exit();

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