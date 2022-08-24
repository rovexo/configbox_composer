<?php
class ConfigboxControllerRfq extends KenedoController {

	/**
	 * @return ConfigboxModelRfq $model
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelRfq');
	}

	/**
	 * @return ConfigboxViewRfq
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewRfq');
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
	 * Takes customer form data (from view rfq), checks it, prepares a quote from a provided cart id and sends back
	 * a JSON object. If all went well, it sends the URL to the RFQ thank you page. Other responses depending on what
	 * went wrong (see implementation for details).
	 *
	 * @throws Exception
	 */
	function processQuotationRequest() {

		// Store the user (may need to get registered and/or logged in) - got customer form data coming in

		// Get the default model
		$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');

		// Make a normalized data object from HTTP request data
		$customerData = $model->getDataFromRequest();

		$userId = ConfigboxUserHelper::getUserId();

		if ($userId != $customerData->id) {

			$response = ConfigboxJsonResponse::makeOne()
				->setSuccess(false)
				->setErrors(array('User ID from the customer form does not match your user ID.'))
				->toJson();

			echo $response;

			return;
		}

		// Prepare the data
		$model->prepareForStorage($customerData);

		// See what kind of customer form we deal with
		$formType = KRequest::getKeyword('form_type', 'quotation');

		// Check if the data validates
		$checkResult = $model->validateData($customerData, $formType);

		// Abort and send feedback if validation fails
		if ($checkResult === false) {

			$response = ConfigboxJsonResponse::makeOne()
				->setSuccess(false)
				->setErrors($model->getErrors())
				->setValidationIssues($model->getValidationIssues())
				->toJson();

			echo $response;
			return;

		}

		// Get the data stored
		$storeResult = $model->store($customerData);

		// Abort and send feedback if storing fails
		if ($storeResult === false) {

			$response = ConfigboxJsonResponse::makeOne()
				->setSuccess(false)
				->setErrors($model->getErrors())
				->toJson();

			echo $response;
			return;

		}

		ConfigboxUserHelper::resetUserCache($userId);

		// Register/Log in if requested
		$isLoggedIn = KenedoPlatform::p()->isLoggedIn();

		// Register and login if requested and not logged in yet
		if ($isLoggedIn == false && KRequest::getInt('register')) {

			$registerResponse = $model->registerPlatformUser($customerData->id);

			if ($registerResponse === false) {

				$response = ConfigboxJsonResponse::makeOne()
					->setSuccess(false)
					->setErrors($model->getErrors())
					->toJson();

				echo $response;
				return;

			}

			$loginResponse = ConfigboxUserHelper::loginUser($customerData->id);

			if ($loginResponse === false) {

				$response = ConfigboxJsonResponse::makeOne()
					->setSuccess(false)
					->setErrors(array(KText::_('Login failed.')))
					->toJson();

				echo $response;
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
			KLog::log('Customer ID "'.ConfigboxUserHelper::getUserId().'" tried to request another customer\'s quotation.','permissions');

			$response = ConfigboxJsonResponse::makeOne()
				->setSuccess(false)
				->setErrors(array(KText::_('Cart data not found.')))
				->toJson();

			echo $response;
			return;
		}

		// Get the cart details
		$cartDetails = $cartModel->getCartDetails($cartId);

		// If a cart position id is supplied, create the order record with only that position
		$cartPositionId = KRequest::getInt('cart_position_id');
		if ($cartPositionId) {
			// We loop through the cart data and remove any position that does not match
			foreach ($cartDetails->positions as $key=>$position) {
				if ($position->id != $cartPositionId) {
					unset($cartDetails->positions[$key]);
				}
			}
		}

		// Create the order record
		$orderId = $orderModel->createOrderRecord($cartDetails, 11);

		$comment = KRequest::getString('comment');
		if ($comment) {
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__cbcheckout_order_records` SET `comment` = '".$db->getEscaped($comment)."' WHERE `id` = ".intval($orderId);
			$db->setQuery($query);
			$db->query();
		}

		// Set order status to 'Quotation sent' - That will trigger the quote email dispatch if there is a notification
		$orderModel->setStatus(11, $orderId);

		// Respond with the right URL
		$urlThanksPage = KLink::getRoute('index.php?option=com_configbox&view=rfqthankyou&order_id='.intval($orderId), false);

		$response = ConfigboxJsonResponse::makeOne()
			->setSuccess(true)
			->setCustomData('redirectUrl', $urlThanksPage)
			->toJson();

		echo $response;

	}

}