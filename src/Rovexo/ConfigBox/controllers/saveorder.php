<?php
class ConfigboxControllerSaveorder extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewSaveorder
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewSaveorder');
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

	function saveOrder() {

		// Store the user (may need to get registered and/or logged in) - got customer form data coming in

		// Get the default model
		$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');

		// Make a normalized data object from HTTP request data
		$customerData = $model->getDataFromRequest();

		$userId = ConfigboxUserHelper::getUserId();

		if ($userId != $customerData->id) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = array('User ID from the customer form does not match your user ID.');
			echo json_encode($response);
			return;
		}

		// Prepare the data
		$model->prepareForStorage($customerData);

		// See what kind of customer form we deal with
		$formType = KRequest::getKeyword('form_type', 'saveorder');

		// Check if the data validates
		$checkResult = $model->validateData($customerData, $formType);

		// Abort and send feedback if validation fails
		if ($checkResult === false) {

			$response = new stdClass();
			$response->success = false;
			$response->errors = $model->getErrors();
			$response->validationIssues = $model->getValidationIssues();
			echo json_encode($response);
			return;

		}

		// Get the data stored
		$storeResult = $model->store($customerData);

		// Abort and send feedback if storing fails
		if ($storeResult === false) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = $model->getErrors();
			echo json_encode($response);
			return;
		}

		// Register/Log in if requested
		$isLoggedIn = KenedoPlatform::p()->isLoggedIn();

		// Register and login if requested and not logged in yet
		if ($isLoggedIn == false && KRequest::getInt('register')) {

			$registerResponse = $model->registerPlatformUser($customerData->id);

			if ($registerResponse === false) {
				$response = new stdClass();
				$response->success = false;
				$response->errors = $model->getErrors();
				echo json_encode($response);
				return;
			}

			$loginResponse = ConfigboxUserHelper::loginUser($customerData->id);

			if ($loginResponse === false) {
				$response = new stdClass();
				$response->success = false;
				$response->errors = array(KText::_('Login failed.'));
				echo json_encode($response);
				return;
			}

		}

		// Create the order record, store the order address - got the cart id coming in
		$cartId = KRequest::getInt('cartId');

		// Get the models
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');

		// Check if the cart actually belongs to the user
		$cartBelongs = $cartModel->cartBelongsToUser($cartId);

		// If not, cancel and say the cart cannot be found
		if ($cartBelongs == false) {
			$platformUserId = KenedoPlatform::p()->getUserId();
			KLog::log('Platform user ID "'.$platformUserId.'" tried to request another customer\'s (customer with ID "'.ConfigboxUserHelper::getUserId().'") quotation.','permissions',KText::_('Quotation not found.'));
			$response = new stdClass();
			$response->success = false;
			$response->errors = array(KText::_('Cart data not found.'));
			echo json_encode($response);
			return;
		}

		// Get the cart details
		$cartDetails = $cartModel->getCartDetails($cartId);

		// If a cart position id is supplied, create the order record with only that position
		$cartPositionid = KRequest::getInt('cart_position_id');
		if ($cartPositionid) {
			// We loop through the cart data and remove any position that does not match
			foreach ($cartDetails->positions as $key=>$position) {
				if ($position->id != $cartPositionid) {
					unset($cartDetails->positions[$key]);
				}
			}
		}

		// Create the order record with status 8 (saved)
		$orderModel->createOrderRecord($cartDetails, 8);

		// Respond with the right URL
		$response = new stdClass();
		$response->success = true;
		$response->errors = array();
		$response->redirectUrl = KLink::getRoute('index.php?option=com_configbox&view=user', false);
		echo json_encode($response);

	}

}