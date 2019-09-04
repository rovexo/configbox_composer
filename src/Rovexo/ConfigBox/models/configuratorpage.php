<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelConfiguratorPage extends KenedoModelLight {

	protected $memoPages = array();
	protected $memoProductPages = array();

	/**
	 * @param int $pageId
	 * @return ConfigboxPageData
	 * @throws Exception
	 */
	function getPage($pageId) {

		if ($pageId == 0 || intval($pageId) != $pageId) {
			$msgPublic = 'Called getPage without a valid page id. Supplied $pageId was '.var_export($pageId, true).'.';
			KLog::log($msgPublic.' Backtrace:'."\n".var_export(debug_backtrace(false), true), 'error');
			throw new Exception($msgPublic, 500);
		}

		if (empty($this->memoPages[$pageId])) {

			$model = KenedoModel::getModel('ConfigboxModelAdminpages');
			$page = $model->getRecord($pageId);

			$this->augmentPage($page);

			$this->memoPages[$pageId] = $page;
			
		}
		
		return $this->memoPages[$pageId];
	}

	/**
	 * Returns an array or pages (same structure as getPage)
	 * @param int $productId
	 *
	 * @return ConfigboxPageData[]
	 * @throws Exception if $productId is zeroish or otherwise invalid
	 */
	function getPages($productId) {

		if ($productId == 0 || intval($productId) != $productId) {
			$msgPublic = 'Called getPages without a valid product id. Supplied $productId was '.var_export($productId, true).'.';
			KLog::log($msgPublic.' Backtrace:'."\n".var_export(debug_backtrace(false), true), 'error');
			throw new Exception($msgPublic, 500);
		}

		if (empty($this->memoProductPages[$productId])) {

			$model = KenedoModel::getModel('ConfigboxModelAdminpages');
			$filters = array(
				'adminpages.product_id' => $productId,
				'adminpages.published'  => 1,
			);
			$ordering = array('propertyName'=>'ordering', 'direction'=>'ASC');
			$pages = $model->getRecords($filters, array(), $ordering);

			foreach($pages as $page) {
				$this->augmentPage($page);
			}

			$this->memoProductPages[$productId] = $pages;

		}

		return $this->memoProductPages[$productId];

	}

	/**
	 * @param ConfigboxPageData|object $page
	 */
	protected function augmentPage(&$page) {

		if ($page) {

			if (KenedoPlatform::getName() == 'magento') {
				$currentUrl = Mage::helper('core/url')->getCurrentUrl();
				$currentUrl = preg_replace('/page_id=(\d+)/i', '', $currentUrl);
				$currentUrl = rtrim($currentUrl,'&?');
				$page->url = $currentUrl . ( (strstr($currentUrl, '?')) ? '&':'?') . 'page_id='.$page->id;
			} elseif(KenedoPlatform::getName() == 'magento2') {
			    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $currentUrl = $objectManager->get('Magento\Framework\UrlInterface')->getCurrentUrl();
                $currentUrl = preg_replace('/page_id=(\d+)/i', '', $currentUrl);
                $currentUrl = rtrim($currentUrl,'&?');
                $page->url = $currentUrl . ( (strstr($currentUrl, '?')) ? '&':'?') . 'page_id='.$page->id;
            } else {
				$page->url = KLink::getRoute('index.php?option=com_configbox&view=configuratorpage&prod_id='.intval($page->product_id).'&page_id='.intval($page->id));
			}

		}

	}

	/**
	 * @param int $pageId
	 * @return ConfigboxQuestion[] $questions Array of ConfigboxQuestion objects (be aware -> may be sub classes)
	 * @deprecated Dropped in CB 4.0
	 */
	function getElements($pageId) {
		
		$assignments = ConfigboxCacheHelper::getAssignments();
		$questionIds = (!empty($assignments['page_to_element'][$pageId])) ? $assignments['page_to_element'][$pageId] : array();

		if (!$questionIds) {
			$questionIds = array();
		}

		$questions = array();
		foreach ($questionIds as $questionId) {
			$questions[$questionId] = ConfigboxQuestion::getQuestion($questionId);
		}
		return $questions;

	}

	/**
	 * @param int $productId
	 * @param int $pageId
	 * @return null|StdClass
	 * @deprecated Dropped in CB 4.0
	 */
	function getNextAndPrevPageId($productId, $pageId) {
		
		$assignments = ConfigboxCacheHelper::getAssignments();
		$pages = (!empty($assignments['product_to_page'][$productId])) ? $assignments['product_to_page'][$productId] : array();
		
		if (!count($pages)) {
			return NULL;
		}
		
		$sortedPages = array();
		foreach ($pages as $page) {
			$sortedPages[] = $page;
		}
		
		$i = 0;
		$current = 0;
		foreach ($sortedPages as $sortedPageId) {
			if ($sortedPageId == $pageId) {
				$current = $i;
				break;
			}
			$i++;
		}
		
		$return = new stdClass();
		$return->prevPageId = ( isset($sortedPages[$current-1]) ) ? $sortedPages[$current-1] : NULL;
		$return->nextPageId = ( isset($sortedPages[$current+1]) ) ? $sortedPages[$current+1] : NULL;
		
		return $return;
	
	}

	/**
	 * Checks if there is a temporary user, an unordered cart and a matching and unfinished position for the given product.
	 * Creates any of those if necessary.
	 * @param int $productId
	 * @return int $positionId
	 */
	function ensureProperCartEnvironment($productId) {

		// Make sure we got a user
		if (ConfigboxUserHelper::getUserId() == 0) {
			$userId = ConfigboxUserHelper::createNewUser();
			ConfigboxUserHelper::setUserId($userId);
		}

		// Make sure we have a cart id
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		if (!$cartModel->getSessionCartId()) {
			KLog::log('No cart is set, creating new one.');
			$cartId = $cartModel->createCart();
			$cartModel->setSessionCartId($cartId);
		}

		$cartId = $cartModel->getSessionCartId();

		// In case we got a cart id from session, but cleanup removed it already
		if ($cartModel->cartExists($cartId) == false) {
			KLog::log('Cart ID must have been cleaned up, creating new one.');
			$cartId = $cartModel->createCart();
			$cartModel->setSessionCartId($cartId);
		}

		$cart = $cartModel->getCartData($cartId);

		// Create a new cart in case the current cart cannot be added to anymore (when cart is checked out or similar)
		if (ConfigboxPermissionHelper::isPermittedAction('addToOrder', $cart) == false) {
			KLog::log('Adding to this cart not allowed, creating new one.');
			$cartId = $cartModel->createCart();
			$cartModel->setSessionCartId($cartId);
		}

		// Create a new position in case there is none yet or the current position is finished or isn't for the product
		// we deal with now.
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$positionId = $positionModel->getId();
		// If there is no position id, create a new position
		if (!$positionId) {
			KLog::log('No position is set, creating new one.');
			$positionId = $positionModel->createPosition($cartId, $productId);
		}
		$position = $positionModel->getPosition($positionId);

		// If the position is for a different product, create a new position
		if ($position->prod_id != $productId or $position->finished) {
			if ($position->finished) {
				KLog::log('Currently active position is finished, creating new one.');
			}
			else {
				KLog::log('Currently active position has a product diffent from the requested product. Creating new position.');
			}
			$positionId = $positionModel->createPosition($cartId, $productId);
		}

		return $positionId;

	}
	
}