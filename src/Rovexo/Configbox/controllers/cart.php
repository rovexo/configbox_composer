<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCart extends KenedoController {

	/**
	 * @return ConfigboxModelCart
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelCart');
	}

	/**
	 * @return ConfigboxViewCart
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewCart');
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
	 * Outputs the cart view
	 */
	function display() {
		$this->getDefaultView()->display();
	}

	/**
	 * Typically requested from a configurator page with a configuration that needs to be finished.
	 * Task is used to set the underlying position of the config to finished making the position 'visible' in the cart.
	 * Redirects the visitor to the cart.
	 * @throws Exception
	 */
	function finishConfiguration() {

		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

		$positionId = KRequest::getInt('cart_position_id');

		if (!$positionId) {
			KenedoPlatform::p()->sendSystemMessage('No cart position id provided.');
			return;
		}

		if ($positionModel->userOwnsPosition($positionId) == false) {
			throw new Exception('Cart position does not exist or does not belong to your user account.');
		}

		$position = $positionModel->getPosition($positionId);
		if (!$position) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Configuration not found.'));
			return;
		}

		// Check for missing elements
		$missingSelections = $positionModel->getMissingSelections();

		if ($missingSelections) {
			$this->reportMissingElementsAndRedirect($missingSelections, $positionId);
			return;
		}

		// Set position's flag to finished
		$positionModel->editPosition($positionId, array('finished'=>1));

		// Fire the 'add to cart' event
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartDetails = $cartModel->getCartDetails($position->cart_id);

		KenedoObserver::triggerEvent('onConfigBoxAddToCart', array(&$cartDetails));

		// The 'stay' GET/POST parameter makes the customer stay on the configurator page
		if (KRequest::getInt('stay')) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Product added to the cart.'),'notice');
			$this->copyCartPosition();
		}
		else {
			$url = KLink::getRoute($cartDetails->redirectURL, false);
			$this->setRedirect($url);
		}

	}

	protected function reportMissingElementsAndRedirect($missingElements, $cartPositionId) {

		// Prepare the text for the feedback
		$text = '<div>'.KText::_('Before finishing the configuration you have to make a choice for these required elements:').'</div>';
		$text .= '<ul>';
		foreach ($missingElements as $missingElement) {
			$text .= '<li>'.$missingElement['title'].'</li>';
		}
		$text .= '</ul>';

		// Send the feedback
		KenedoPlatform::p()->sendSystemMessage($text);

		// Get the info for the first missing element
		$firstMissing = $missingElements[0];

		// Create the URL and redirect
		$url = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=editCartPosition&cart_position_id='.intval($cartPositionId).'&prod_id='.$firstMissing['productId'].'&page_id='.$firstMissing['pageId'], false);
		$this->setRedirect($url);

	}

	/**
	 * Takes in cartId (or cart_id for legacy) and creates a order record.
	 * You get JSON back (with checkoutViewUrl to the checkout page view)
	 * On issues, you get an error array in the JSON.
	 *
	 * @see ObserverOrders::onConfigBoxCheckout
	 * @throws Exception
	 */
	function checkoutCart() {

		// Get the cart details
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartId = KRequest::getInt('cart_id');
		if (empty($cartId)) {
			$cartId = KRequest::getInt('cartId');
		}

		// Check if the user owns the cart
		if ($cartModel->cartBelongsToUser($cartId) == false) {

			$response = ConfigboxJsonResponse::makeOne();
			$response->setSuccess(false);
			$response->setErrors(array(KText::_('This cart does not belong to your customer account.')));
			echo $response->toJson();
			return;

		}

		// Get the cart details
		$cartDetails = $cartModel->getCartDetails($cartId);

		// See if checkout is permitted
		if (ConfigboxPermissionHelper::isPermittedAction('checkoutOrder', $cartDetails) == false) {

			$response = ConfigboxJsonResponse::makeOne();
			$response->setSuccess(false);
			$response->setErrors(array(KText::_('You cannot checkout this cart anymore.')));
			echo $response->toJson();
			return;

		}

		KenedoObserver::triggerEvent('onConfigBoxCheckout', array(&$cartDetails));

		$cartModel->forgetMemoizedData();

		$checkoutViewUrl = KLink::getRoute('index.php?option=com_configbox&view=checkout&format=raw', false, CbSettings::getInstance()->get('securecheckout'));


		echo json_encode(array(
			'success' => true,
			'errors' => array(),
			'checkoutViewUrl' => $checkoutViewUrl,
		));

	}

	function removeCartPosition() {

		// Get the position id from request
		$positionId = KRequest::getInt('cart_position_id');

		// Get the position model
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

		// Check if the user owns this position
		if ($positionModel->userOwnsPosition($positionId) == false) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('You cannot remove this position.'));
			$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=cart', false));
			return;
		}

		// Try removing the position
		$success = $positionModel->removePosition($positionId);

		// Get the right feedback text
		$message = ($success) ? KText::_('Product removed from order') : KText::_('Could not remove product from order');

		KenedoPlatform::p()->sendSystemMessage($message);
		$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=cart', false));

	}

	function editCartPosition() {

		// Get the position id from request
		$positionId = KRequest::getInt('cart_position_id');

		// Get the position model
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

		// Check if the user owns this position
		if ($positionModel->userOwnsPosition($positionId) == false) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Order not found.'));
			$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=cart', false));
			return;
		}

		// Set the id, get position data
		$positionModel->setId($positionId);
		$positionData = $positionModel->getPosition($positionId);

		// Edit the position
		$positionModel->editPosition($positionId, array('finished' => 0));

		// The user can request a specific configurator page to get to, if not, go to the first one

		$productId = $positionData->prod_id;
		$pageId = KRequest::getInt('page_id', 0);

		if (!$pageId) {
			$productModel = KenedoModel::getModel('ConfigboxModelProduct');
			$product = $productModel->getProduct($positionData->prod_id);
			$pageId = $product->firstPageId;
		}

		$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=configuratorpage&prod_id=' . intval($productId) . '&page_id=' . intval($pageId), false));
		$this->redirect();

	}

	function setCartPositionQuantity() {

		$errors = array();

		// Get the position id and quantity from request
		$positionId = KRequest::getInt('cart_position_id');
		$quantity = KRequest::getInt('quantity',0);

		// Get the position model
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

		// Check if the user owns this position
		if ($positionModel->userOwnsPosition($positionId) == false) {
			$errors[] = 'Invalid position id';
		}

		// Check if the position is valid
		if ($quantity < 1) {
			$errors[] = KText::_('Invalid quantity');
		}

		// Set the position id, get basic position data
		$positionModel->setId($positionId);
		$positionData = $positionModel->getPosition($positionId);

		// Get the cart details for permission check
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartDetails = $cartModel->getCartDetails($positionData->cart_id);

		// Check if editing the cart is permitted
		if (ConfigboxPermissionHelper::isPermittedAction('editOrder', $cartDetails) == false) {
			$errors[] = KText::_('You cannot edit this order anymore.');
		}

		if (count($errors)) {

			echo json_encode(array(
				'success' => false,
				'errors' => $errors,
			));
			return;

		}

		// Update the quantity
		$success = $positionModel->updateQuantity($positionId, $quantity);

		echo json_encode(array(
			'success' => $success,
			'errors' => $errors,
		));

	}

	function copyCartPosition() {

		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$positionId = KRequest::getInt('cart_position_id');
		$positionId = $positionModel->copyPosition($positionId);
		$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&controller=cart&task=editCartPosition&cart_position_id='.intval($positionId),false));

	}

	function addProductToCart() {

		if (ConfigboxUserHelper::getUserId() == 0) {
			$userId = ConfigboxUserHelper::createNewUser();
			ConfigboxUserHelper::setUserId($userId);
		}

		$productId = KRequest::getInt('prod_id',0);
		if (!$productId) {
			return;
		}

		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

		// Create a cart if we got none yet
		if (!$cartModel->getSessionCartId()) {
			$cartId = $cartModel->createCart();
			$cartModel->setSessionCartId($cartId);
		}

		$cartId = $cartModel->getSessionCartId();

		// Set up the new position
		$positionId = $positionModel->createPosition($cartId, $productId);

		// Check for missing elements
		$missingSelections = $positionModel->getMissingSelections();
		if ($missingSelections) {
			$this->reportMissingElementsAndRedirect($missingSelections, $positionId);
			return;
		}

		$positionModel->editPosition($positionId, array('finished'=>1));
		$cartDetails = $cartModel->getCartDetails($cartId);
		KenedoObserver::triggerEvent('onConfigBoxAddToCart',array(&$cartDetails));

		if (KRequest::getKeyword('format','html') == 'json') {
			// Set the old position id
			if ($positionId) {
				$positionModel->setId($positionId);
			}
			KRequest::setVar('layout','component');
			$response = new stdClass();
			$response->success = 1;
			$response->message = KText::_('Product added to the cart.');
			echo json_encode($response);
		}
		else {
			if (KRequest::getInt('stay')) {
				// Set the old position id
				if ($positionId) {
					$positionModel->setId($positionId);
				}
				KenedoPlatform::p()->sendSystemMessage(KText::_('Product added to the cart.'),'notice');
				$this->setRedirect($_SERVER['HTTP_REFERER']);
			}
			else {
				$this->setRedirect( KLink::getRoute($cartDetails->redirectURL,false) );
			}
		}
		return;

	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=cart', $langTag);

		if ($id) {
			unset($queryParameters['view']);
			$queryParameters['Itemid'] = $id;
			return array();
		}
		return array();

	}

	function reloadCartSummary() {
		$view = $this->getDefaultView();
		$view->prepareTemplateVars();
		$view->renderView('summary');
	}

}