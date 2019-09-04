<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerUser extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewUser
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewUser');
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

	function removeOrder() {
		
		// Get and sanitize record ids
		$cids = KRequest::getArray('cid');
		foreach ($cids as &$cid) {
			$cid = (int)$cid;
		}
		
		// Bounce if no ids where found
		if (count($cids) == 0) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('No orders chosen for removal.'));
			return false;
		}
		
		// Get the store ID
		$storeId = ConfigboxStoreHelper::getStoreId();
		
		// Find the records that the user should be able to reach
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id`, `user_id`, `status` FROM `#__cbcheckout_order_records` WHERE `id` IN (".implode(',',$cids).")";
		if ($storeId != 1) {
			$query .= " AND store_id = ".(int)$storeId;
		}
		$db->setQuery($query);
		$removalItems = $db->loadObjectList('id');
		
		// Bounce if nothing is there
		if (!$removalItems) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('No orders chosen for removal.'));
			return false;
		}
		
		// Get the user's id
		$userId = ConfigboxUserHelper::getUserId();
		
		// Loop through the orders and see if the user can actually remove them
		foreach ($removalItems as $item) {
			if ($item->user_id != $userId) {
				$platformUserId = KenedoPlatform::p()->getUserId();
				if (ConfigboxPermissionHelper::canEditOrders($platformUserId) == false) {
					KenedoPlatform::p()->sendSystemMessage(KText::_('You cannot remove orders of other customers.'));
					return false;
				}
			}
			if (ConfigboxPermissionHelper::isPermittedAction('removeOrderRecord', $item) == false) {
				$platformUserId = KenedoPlatform::p()->getUserId();
				if (ConfigboxPermissionHelper::canEditOrders($platformUserId) == false) {
					KenedoPlatform::p()->sendSystemMessage(KText::sprintf('You cannot remove order %s because if its status.',$item->id));
					return false;
				}
			}
				
		}
		
		// Get the ids of the order records
		$orderIds = array_keys($removalItems);
		
		// Get the model and try to remove them
		$model = KenedoModel::getModel('ConfigboxModelAdminorders');
		$success = $model->delete($orderIds);
		
		// Send a message if removal did not work
		if ($success == false) {
			$errors = $model->getErrors();
			foreach ($errors as $error) {
				KenedoPlatform::p()->sendSystemMessage($error);
			}
		}
		else {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Order removed.'));
		}
		
		// Redirect to the customer account page
		$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=user',false));
		$this->redirect();

		return true;
	}

	function store() {

		// Get the default model
		$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');

		// Make a normalized data object from HTTP request data
		$data = $model->getDataFromRequest();

		// Prepare the data
		$model->prepareForStorage($data);

		// See what kind of customer form we deal with
		$formType = KRequest::getKeyword('form_type', 'profile');

		// Check if the data validates
		$checkResult = $model->validateData($data, $formType);

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
		$success = $model->store($data);

		// Abort and send feedback if storage fails
		if ($success === false) {
			$response = new stdClass();
			$response->success = false;
			$response->errors = $model->getErrors();
			echo json_encode($response);
			return;
		}
		else {
			$response = new stdClass();
			$response->success = true;
			$response->message = KText::_('Record saved.');
			$response->errors = array();
			echo json_encode($response);
			return;
		}

	}

	function loginUser() {
		
		// Store the old user id for later moving of orders and carts
		$oldUserId = ConfigboxUserHelper::getUserId();
		
		$email = KRequest::getString('username','');
		$password = KRequest::getString('password','');
		
		// Either email or username can be used
		if (!$email) {
			$email = KRequest::getString('email','');
		}

		$userId = ConfigboxUserHelper::getUserIdByEmail($email);

		// Authenticate the user
		$authenticated = ConfigboxUserHelper::authenticateUser($userId, $password);

		if ($authenticated == true) {
			// Login the user
			$response = ConfigboxUserHelper::loginUser($userId);
		}
		else {
			// Or put response to false
			$response = false;
		}

		if ($response == true) {
			
			// Set the order address as well
			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
			$orderId = $orderModel->getId();
			if ($orderId) {
				ConfigboxUserHelper::setOrderAddress($orderId);
			}

			// Get the new user id
			$newUserId = ConfigboxUserHelper::getUserId();
			
			// Move the orders
			ConfigboxUserHelper::moveUserOrders($oldUserId, $newUserId);
			
			// Get the success redirection URL
			$url = urldecode(KRequest::getString('return_success'));
				
		}
		else {
			
			// Get the failure redirection URL
			$url = urldecode(KRequest::getString('return_failure'));
			
		}
		
		if (in_array(KRequest::getString('format', ''), array('json', 'raw'))) {
			$jsonResponse = new stdClass();
			$jsonResponse->success = $response;
			if ($response == false) {
				$jsonResponse->errorMessage = KText::_('Login failed. Please check your email address and password.');
			}
			echo json_encode($jsonResponse);
		}
		else {

			// Send feedback
			if ($response == true) {
				$message = 'You have been successfully logged in.';
			}
			else {
				$message = 'Login failed. Please check your email address and password.';
			}

			KenedoPlatform::p()->sendSystemMessage(KText::_($message));

			// Redirect
			$this->setRedirect($url);
			$this->redirect();
			
		}
	
	
	}

	/**
	 * Logs out the current user from the platform (and ConfigBox)
	 */
	function logoutUser() {

		KenedoPlatform::p()->logout();

		echo json_encode(array('success'=>true));

	}
	
	function registerUser() {
		
		$userId = ConfigboxUserHelper::getUserId();
		if ($userId == 0) {
			$userId = ConfigboxUserHelper::createNewUser();
			ConfigboxUserHelper::setUserId($userId);
		}
		
		$userData = ConfigboxUserHelper::getUser($userId);
		
		$userData->billingemail = KRequest::getString('email','');
		$userData->billingfirstname = KRequest::getString('firstname','');
		$userData->billinglastname = KRequest::getString('lastname','');
		
		$password 	= KRequest::getString('password');
		$password2 	= KRequest::getString('passwordconf');
		
		if ($password != $password2) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('The password confirmation does not match the password.'));
			$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=user&layout=register',false));
			$this->redirect();
		}
		
		$response = ConfigboxUserHelper::registerPlatformUser($userData, $password);
		
		if ($response == false) {
			$errorMessage = ConfigboxUserHelper::$error;
			KenedoPlatform::p()->sendSystemMessage($errorMessage);
			$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=user&layout=register',false));
			$this->redirect();
			return false;
		}
		else {
			
			$loginResponse = KenedoPlatform::p()->login($response->username);

			// Login the user
			if ($loginResponse == true) {
				KenedoPlatform::p()->sendSystemMessage(KText::_('Thank you for registering. You are now registered and logged in.'));
				$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=user', false, CbSettings::getInstance()->get('securecheckout')));
				$this->redirect();
				
			}
			else {
				KenedoPlatform::p()->sendSystemMessage(KText::_('Could not login after registration.'));
				$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&view=user&layout=login', false, CbSettings::getInstance()->get('securecheckout')));
				$this->redirect();
			}
			
		}

		return true;
		
	}
	
	function sendPasswordChangeVerificationCode() {
		
		$emailAddress = KRequest::getString('email','');
		$verificationCode = ConfigboxUserHelper::getPassword(16);
		
		$userId = ConfigboxUserHelper::getUserIdByEmail($emailAddress);
		
		if (!$userId) {
			$jsonResponse = new stdClass();
			$jsonResponse->success = false;
			$jsonResponse->errorMessage = KText::_('There is no customer account with that email address.');
			echo json_encode($jsonResponse);
			return;
		}
		
		KSession::set('passwordChangeUserId', $userId);
		KSession::set('passwordChangeVerificationCode', $verificationCode);
		
		$shopData = ConfigboxStoreHelper::getStoreRecord();
		
		$emailSubject = KText::sprintf('PASSWORD_CHANGE_VERIFICATION_CODE_EMAIL_SUBJECT', $shopData->shopname);
		$emailBody = KText::sprintf('PASSWORD_CHANGE_VERIFICATION_CODE_EMAIL_TEXT', $verificationCode);
		
		$emailView = KenedoView::getView('ConfigboxViewEmailtemplate');
		$emailView->prepareTemplateVars();
		$emailView->assign('emailContent', $emailBody);
		$emailBody = $emailView->getViewOutput('default');

		// Use shop data email sales (and fall back to platform's mailer address
		$fromEmail = $shopData->shopemailsales;

		if (empty($fromEmail)) {
			$fromEmail = KenedoPlatform::p()->getMailerFromEmail();
		}

		// Use shop data company name (and fall back to platform's mailer name
		$fromName = $shopData->shopname;

		if (empty($fromName)) {
			$fromName = KenedoPlatform::p()->getMailerFromName();
		}

		$email = new stdClass();
		$email->toEmail		= $emailAddress;
		$email->fromEmail	= $fromEmail;
		$email->fromName	= $fromName;
		$email->subject		= $emailSubject;
		$email->body 		= $emailBody;
		$email->attachments	= array();
		$email->cc			= NULL;
		$email->bcc			= NULL;
		
		$dispatchResponse = KenedoPlatform::p()->sendEmail($email->fromEmail, $email->fromName, $email->toEmail, $email->subject, $email->body, true, $email->cc, $email->bcc, $email->attachments);
		
		$jsonResponse = new stdClass();
		
		if ($dispatchResponse !== true) {
			
			$jsonResponse->success = false;
			$jsonResponse->errorMessage = KText::_('We could not send you a verification code because the email dispatch failed. Please contact us to solve this issue.');
			
			// Log error message to errors
			KLog::log('Password change verification code email could not be sent. Recipient was "'.$email->toEmail.'". Sender email was "'.$email->fromEmail.'". Email body char count was "'.mb_strlen($email->body).'".', 'error');
		}
		else {
			$jsonResponse->success = true;
		}
		
		echo json_encode($jsonResponse);
		
	}
	
	function changePasswordWithCode() {
		
		// Prepare the response object
		$jsonResponse = new stdClass();
		$jsonResponse->errors = array();
		
		// Get the input
		$verificationCode = trim(KRequest::getString('code'));
		$password = trim(KRequest::getVar('password'));

		// Check if the verification code was supplied
		if ($verificationCode == '') {
			$jsonResponse->success = false;
			$jsonResponse->errors[] = array(
				'fieldName'=>'verification_code',
				'code'=>'no_code',
				'message'=>KText::_('Please enter the verification code you received by email.'),
			);
		}
		// Check if the codes match
		elseif ($verificationCode != KSession::get('passwordChangeVerificationCode')) {
			$jsonResponse->success = false;

			$jsonResponse->errors[] = array(
				'fieldName'=>'verification_code',
				'code'=>'code_mismatch',
				'message'=>KText::_('The verification code is not correct. Please check and try again. You can also request a new code.'),
			);

		}

		// Check if the new password was supplied
		if ($password == '') {
			$jsonResponse->success = false;
			$jsonResponse->errors[] = array(
				'fieldName'=>'new_password',
				'code'=>'no_password',
				'message'=>KText::_('Please enter a new password.'),
			);
		}
		// Check if the new password is ok
		elseif( KenedoPlatform::p()->passwordMeetsStandards($password) == false ) {
			$jsonResponse->success = false;
			$jsonResponse->errors[] = array(
					'fieldName'=>'new_password',
					'code'=>'bad_password',
					'message'=>KenedoPlatform::p()->getPasswordStandardsText(),
			);
		}

		// Stop if we got problems so far
		if (count($jsonResponse->errors)) {
			echo json_encode($jsonResponse);
			return;
		}
		
		// Get the user ID from the verification info
		$userId = KSession::get('passwordChangeUserId');
		
		// Change the password
		$success = ConfigboxUserHelper::changeUserPassword($userId, $password);
		
		// Do the final setup of the response
		if ($success == true) {

			// Log the user in if requested
			if (KRequest::getInt('login')) {				

				$isAuthenticated = ConfigboxUserHelper::authenticateUser($userId, $password);

				if ($isAuthenticated) {
					ConfigboxUserHelper::loginUser($userId);
				}
			}
			
			$jsonResponse->success = true;
			
			KSession::delete('passwordChangeUserId');
			KSession::delete('passwordChangeVerificationCode');
			
		}
		else {
			$jsonResponse->success = false;
			$jsonResponse->errors[] = array(
					'fieldName'=>'',
					'code'=>'change_password_failure',
					'message'=>ConfigboxUserHelper::$error,
			);
		}
		
		// Do the response
		echo json_encode($jsonResponse);
		return;
		
	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		// Except: We still got an editprofile screen that shows as alternative layout, we check for a menu item
		if (!empty($queryParameters['layout']) && $queryParameters['layout'] == 'editprofile') {
			$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=user&layout=editprofile', $langTag);
			if ($id) {
				unset($queryParameters['layout']);
				return array();
			}
			else {
				unset($queryParameters['layout'], $queryParameters['view']);
				return array('edit');
			}
		}

		// Menu item check, if there is none, we add view name
		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=user', $langTag);

		if ($id) {
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view']);
			return array();
		}
		else {
			unset($queryParameters['view']);
			return array('user');
		}

	}

}
