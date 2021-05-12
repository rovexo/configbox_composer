<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCheckout extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewCheckout
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewCheckout');
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

	function display() {

		header('Access-Control-Allow-Origin: *', true);

		$orderId = KRequest::getInt('order_id');

		if (!$orderId) {
			echo 'No order ID provided';
			return;
		}

		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$belongs = $orderModel->orderBelongsToUser($orderId);

		if (!$belongs) {
			echo 'This order does not belong to your user account.';
			return;
		}

		// Many things still depend on the session order ID, so we set it now
		$orderModel->setSessionOrderId($orderId);

		$view = KenedoView::getView('ConfigboxViewCheckout');
		$view->setOrderId($orderId);
		echo $view->getHtml();

	}

	/**
	 * Takes in gaClientId (Google Analytics Client ID), takes the order ID from session data and stores it in the
	 * order record.
	 *
	 * @throws Exception If the session user does not own the order record
	 */
	function storeGaClientId() {

		$clientId = KRequest::getString('gaClientId');

		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();

		if ($orderModel->orderBelongsToUser($orderId) == false) {
			throw new Exception('Trying to set GA client ID for an order that does not belong your account.');
		}

		$orderModel->storeGaClientId($orderId, $clientId);

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}
	
	function backToCart() {
	
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		$orderRecord = $orderModel->getOrderRecord($orderId);
		
		// See if the current state of affairs permits going back
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartDetails = $cartModel->getCartDetails($orderRecord->cart_id);
		$canGoBack 	= ConfigboxPermissionHelper::isPermittedAction('goBackToCart', $cartDetails);
		
		if ($canGoBack) {
			$adminOrderModel = KenedoModel::getModel('ConfigboxModelAdminorders');
			$adminOrderModel->delete(array($orderId));
			$orderModel->unsetOrderRecord($orderId);
			$cartModel->setSessionCartId($orderRecord->cart_id);
			$url = KLink::getRoute('index.php?option=com_configbox&view=cart',false);
			$this->setRedirect($url);
		}
		else {
			$url = KLink::getRoute('index.php?option=com_configbox&view=checkout',false);
			$this->setRedirect($url);
		}
		
	}
	
	function storeOrderAddress() {

		// Get the default model
		$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');

		// Make a normalized customer data object from HTTP request data
		$data = $model->getDataFromRequest();

		// Set user ID and platform user ID to current user's to avoid unauthorized user data changes
		$currentUser = ConfigboxUserHelper::getUser();
		$data->id = $currentUser->id;
		$data->platform_user_id = $currentUser->platform_user_id;

		// Prepare the data (auto-fill data like empty URL segment fields and similar)
		$model->prepareForStorage($data);

		// Check if the data validates
		$checkResult = $model->validateData($data, 'checkout');

		// Abort and send feedback if validation fails
		if ($checkResult === false) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = array();
			$response->validationIssues = $model->getValidationIssues();
			echo json_encode($response);
			return;
		}

		// Get the customer data stored. Order address data gets stored later
		$success = $model->store($data);

		// Abort and send feedback if storage fails
		if ($success === false) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = $model->getErrors();
			echo json_encode($response);
			return;
		}

		// Get the order record ID
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();

		// Get the new customer data
		ConfigboxUserHelper::resetUserCache();
		$user = ConfigboxUserHelper::getUser();

		// Store the order address
		$success = ConfigboxUserHelper::setOrderAddress($orderId, $user);

		// Abort if a problem occurred
		if ($success == false) {
			KLog::log('Could not set order address.', 'error');

			$response = new stdClass();
			$response->success = false;
			$response->errors = array(KText::_('System error: Could not set order address.'));
			echo json_encode($response);
			return;
		}

		// Register the user if not done already
		if ($user->platform_user_id == 0) {
			$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');
			$model->registerPlatformUser($user->id);
		}

		// Login the user if not done already
		if (KenedoPlatform::p()->isLoggedIn() == false) {
			KenedoPlatform::p()->login($user->billingemail);
		}

		// Get the order and check if delivery method is still valid
		$orderModel->unsetOrderRecord($orderId);
		$orderRecord = $orderModel->getOrderRecord($orderId);

		$noneSelected = ($orderRecord->delivery_id == 0);
		$nowInvalid = ($orderRecord->delivery_id && $orderModel->isValidDeliveryOption($orderRecord, $orderRecord->delivery_id) == false);

		if ($noneSelected || $nowInvalid) {

			$possibleOptions = $orderModel->getOrderRecordDeliveryOptions($orderRecord);
			if ($possibleOptions) {
				$orderModel->storeOrderRecordDeliveryOption($orderId, $possibleOptions[0]->id);
			}
			else {
				$orderModel->storeOrderRecordDeliveryOption($orderId, 0);
			}

		}

		// Now the same for the payment method
		$noneSelected = ($orderRecord->payment_id == 0);
		$nowInvalid = ($orderRecord->payment_id && $orderModel->isValidPaymentOption($orderRecord, $orderRecord->payment_id) == false);

		if ($noneSelected || $nowInvalid) {

			$orderModel->storeOrderRecordPaymentOption($orderId, 0);

			$possibleOptions = $orderModel->getOrderRecordPaymentOptions($orderRecord);
			if ($possibleOptions) {
				$orderModel->storeOrderRecordPaymentOption($orderId, $possibleOptions[0]->id);
			}
			else {
				$orderModel->storeOrderRecordPaymentOption($orderId, 0);
			}

		}

		// Respond with the success JSON data
		$response = new stdClass();
		$response->success = true;
		$response->errors = array();
		echo json_encode($response);
		
	}
	
	function storeDeliveryOption() {
		
		$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $model->getId();
		$deliveryId = KRequest::getInt('id');
		
		if ($deliveryId == 0) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = array(KText::_('Please choose a delivery method.'));
			echo json_encode($response);
		}
		else {
			
			$success = $model->storeOrderRecordDeliveryOption($orderId, $deliveryId);
			
			$response = new stdClass();
			
			if ($success) {
				$response->success = true;
				$response->errors = array();
			}
			else {
				$response->success = false;
				$response->errors = $model->getErrors();
			}
			
			echo json_encode($response);
			
		}
		
	}
	
	function storePaymentOption() {
		
		$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $model->getId();
		$paymentId = KRequest::getInt('id');
		
		if ($paymentId == 0) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = array(KText::_('Please choose a payment method.'));
			echo json_encode($response);
		}
		else {
			
			$success = $model->storeOrderRecordPaymentOption($orderId, $paymentId);
			
			$response = new stdClass();
			
			if ($success) {
				$response->success = true;
				$response->errors = array();
			}
			else {
				$response->success = false;
				$response->errors = $model->getErrors();
			}
			
			echo json_encode($response);
			
		}
		
	}
	
	function placeOrder() {
		
		$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $model->getId();
		$orderRecord = $model->getOrderRecord($orderId);
		
		$response = new stdClass();
		$response->errors = array();
		
		$orderAddressComplete = ConfigboxUserHelper::orderAddressComplete($orderRecord->orderAddress);
				
		if (!$orderAddressComplete) {
			$response->errors[] = KText::_('Please complete your order address.');
		}
		
		if ($orderRecord->payment_id == 0) {
			$response->errors[] = KText::_('Please choose a payment method.');
		}
		
		if (CbSettings::getInstance()->get('disable_delivery') == 0 && $orderRecord->delivery_id == 0) {
			$response->errors[] = KText::_('Please choose a delivery method.');
		}
		
		if (count($response->errors) == 0) {
			$response->success = true;
			$status = 2;			
			$model->setStatus($status, $orderId);

			// Unset the cart data
			$cartModel = KenedoModel::getModel('ConfigboxModelCart');
			$cartModel->resetCart();

		}
		else {
			$response->success = false;
		}

		echo json_encode($response);
	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=checkout', $langTag);
		if ($id) {
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view']);
			return array();
		}
		else {
			unset($queryParameters['view'], $queryParameters['Itemid']);
			return array('checkout');
		}

	}

}