<?php
defined('CB_VALID_ENTRY') or die();

class ObserverNotifications {

	protected $originalLanguageTag;
	protected $doNotSend;
	protected $lastStatusChange;

	function onConfigBoxSetStatus($orderId, $status) {

		// Measure to bypass notification if a status update was triggered without actually changing the status
		if (isset($this->lastStatusChange[$orderId]) && $this->lastStatusChange[$orderId] == $status) {
			KLog::log('Status setting without status change detected, notification was skipped.', 'debug');
			return true;
		}
		$this->lastStatusChange[$orderId] = $status;

		// Input validation
		if ((int)$orderId === 0) {
			KLog::log('Set status was called without providing an order id. Order ID passed was: '.var_export($orderId,true),'error');
			return false;
		}

		// Get the order record for the order ID
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderRecord = $orderModel->getOrderRecord($orderId);

		// Check if orderRecord actually exists
		if (!$orderRecord) {
			KLog::log('Set status was called for a non existent order record. Order ID passed was: '.var_export($orderId,true).', getOrderRecord response was '.var_export($orderRecord,true),'error');
			return false;
		}

		// Get the appropriate notifications directly from DB for easier handling and better performance
		$db = KenedoPlatform::getDb();
		$query = 'SELECT * FROM `#__configbox_notifications` WHERE `statuscode` = '.(int)$status;
		$db->setQuery($query);
		$notifications = $db->loadObjectList();

		// Skip further processing if no notifications exist
		if (!$notifications) {
			return true;
		}

		// Get the store information
		$shopData 	= ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);

		// Remember original language tag for later (to reset the language in case it has changed)
		$this->originalLanguageTag = KText::getLanguageTag();

		// Set up the recipient data
		$notificationRecipients = array(
			'customer'=> array(
				'recipientType'		=> 'customer',
				'languageTag'		=> $orderRecord->orderAddress->language_tag,
				'toEmail'			=> $orderRecord->orderAddress->billingemail,
				'fromName'			=> $shopData->shopname,
				'fromEmail'			=> $shopData->shopemailsales,
				'keySubject'		=> 'subject',
				'keyBody'			=> 'body',
				'quotationEmail'	=> $orderRecord->groupData->quotation_email,
			),
			'manager'=> array(
				'recipientType'		=> 'manager',
				'languageTag'		=> CbSettings::getInstance()->get('language_tag'),
				'toEmail'			=> $shopData->shopemailsales,
				'fromName'			=> $shopData->shopname,
				'fromEmail'			=> $shopData->shopemailsales,
				'keySubject'		=> 'subjectmanager',
				'keyBody'			=> 'bodymanager',
				'quotationEmail'	=> 1,
			),
		);

		$notificationModel 	= KenedoModel::getModel('ConfigboxModelAdminnotifications');

		$numberErrors = 0;

		foreach ($notifications as $notification) {

			$notificationData = $notificationModel->getRecord($notification->id);

			foreach ($notificationRecipients as $recipientData) {

				// Skip customer if notification is set not to send or quotation email is deactivated for the customer's customer group
				if ($recipientData['recipientType'] == 'customer' && ($notificationData->send_customer == 0 || ($recipientData['quotationEmail'] == 0 && $status == 14) )) {
					continue;
				}

				// Skip manager if notification is set to
				if ($recipientData['recipientType'] == 'manager' && $notificationData->send_manager == 0) {
					continue;
				}

				// Change language and reload data if recipient language differs from current language
				if ($recipientData['languageTag'] != KText::getLanguageTag()) {

					KText::setLanguage($recipientData['languageTag']);

					$notificationModel->forgetRecords();
					$notificationData = $notificationModel->getRecord($notification->id);

					ConfigboxStoreHelper::forgetStoreRecords();
					$shopData = ConfigboxStoreHelper::getStoreRecord();

					$orderModel->unsetOrderRecord($orderId);
					$orderRecord = $orderModel->getOrderRecord($orderId);

				}

				// Prepare the email data
				$email = new stdClass();
				$email->toEmail		= $recipientData['toEmail'];
				$email->fromEmail	= $recipientData['fromEmail'];
				$email->fromName	= $recipientData['fromName'];
				$email->subject		= $notificationData->{$recipientData['keySubject']};
				$email->body 		= $notificationData->{$recipientData['keyBody']};
				$email->attachments	= array();
				$email->cc			= NULL;
				$email->bcc			= NULL;

				// Process the email
				$this->processNotificationTemplates(	$email, $shopData, $orderRecord, $recipientData, $status);
				$this->processNotificationSnippets(		$email, $shopData, $orderRecord, $recipientData, $status);
				$this->processNotificationAttachments(	$email, $shopData, $orderRecord, $recipientData, $status);
				$this->processNotificationPlaceholders(	$email, $shopData, $orderRecord, $recipientData, $status);
				$this->processRelativeUrls($email);

				// Validate the email data
				$validationResponse = $this->validateEmailData($email);

				// Log errors if email is not valid
				if ($validationResponse !== true) {
					KLog::log('Email data was not valid. Error messages follow. Recipient data was '.var_export($recipientData,true).'. Notification data was '.var_export($notificationData,true).'. Email data was '.var_export($email,true),'error');
					foreach ($validationResponse as $errorMsg) {
						KLog::log($errorMsg,'error');
					}
					KLog::log('End of notification email error messages.','error');
					$numberErrors++;
				}
				// Dispatch otherwise
				else {

					if (!isset($email->doNotWrap) || $email->doNotWrap == false) {
						$emailView = KenedoView::getView('ConfigboxViewEmailtemplate');
						$emailView->prepareTemplateVars();
						$emailView->assign('emailContent', $email->body);
						$email->body = $emailView->getViewOutput('default');
					}

					if (!isset($email->doNotNormalize) || $email->doNotNormalize == false) {
						// Normalize HTML
						$this->normalizeEmailBodyHtml($email);
					}

					$profilerKeyword = 'email_dispatch_'.rand(0,1000);

					KLog::start($profilerKeyword);

					// Dispatch the email
					$dispatchResponse = KenedoPlatform::p()->sendEmail($email->fromEmail, $email->fromName, $email->toEmail, $email->subject, $email->body, true, $email->cc, $email->bcc, $email->attachments);

					$time = KLog::stop($profilerKeyword);

					// Log if dispatch did not work
					//TODO: Clean and future-proof way to log the actual dispatch error
					if ($dispatchResponse !== true) {

						// Increment error count
						$numberErrors++;

						// Log error message to errors
						KLog::log('Notification email could not be sent. Recipient data was '.var_export($recipientData,true).'. Notification data was '.var_export($notificationData,true).'. Email data was '.var_export($email,true),'error');

						// Log error message to debug as well
						KLog::log('Notification email could not be sent. Recipient data was '.var_export($recipientData,true).'. Notification data was '.var_export($notificationData,true).'. Email data was '.var_export($email,true),'debug');
					}
					else {
						// Log message in debug mode
						KLog::log('Notification email was sent. Email dispatch took '.$time.'ms. Recipient data was '.var_export($recipientData,true).'. Notification data was '.var_export($notificationData,true).'. Email data was '.var_export($email,true),'debug');
					}
				}

			}

		}

		// Restore the language (method checks by itself whether it is necessary)
		$this->restoreOriginalLanguage();

		// Send response
		if ($numberErrors == 0) {
			return true;
		}
		else {
			//TODO: Find a way to take care of all common issues about bad data entered at notification data, then be strict with the return value.
			return true;
		}

	}

	/**
	 * Checks if email data contains all necessary data
	 *
	 * @param StdClass $email The email object holding all email dispatch data
	 * @return array|boolean true if valid, array of strings containing error messages if invalid (not KTexted)
	 */
	protected function validateEmailData($email) {

		$errors = array();

		if (empty($email->toEmail)) {
			$errors[] = 'No email recipient was specified';
		}
		if (empty($email->subject)) {
			$errors[] = 'No email subject was specified';
		}
		if (empty($email->body)) {
			$errors[] = 'No email body (means message text) was specified';
		}

		if (count($errors)) {
			return $errors;
		}
		else {
			return true;
		}
	}

	/**
	 * Makes sure email body is wrapped in html/body structure
	 *
	 * @param StdClass $email The email object holding all email dispatch data - referenced
	 */
	protected function normalizeEmailBodyHtml($email) {
		if (strstr($email->body,'<html>') == false && strstr($email->body,'<body>') == false ) {
			$email->body = '<html><body>'.$email->body.'</body></html>';
		}
	}


	/**
	 * Checks if there is a file template for the notification (depends on status and recipient type) and replaces the email body if found and not empty
	 *
	 * @param stdClass $email The email object holding all email dispatch data - referenced
	 * @param object $shopData store information object (see ConfigboxModelShopdata)
	 * @param ConfigboxOrderData $orderRecord Order record
	 * @param array $recipientData Array with data holding information about the recipient (see onConfigBoxSetData)
	 * @param int $status the numeric order status
	 * @return boolean true if a file template is used, false if not
	 * @see ObserverNotifications::onConfigBoxSetData
	 */
	protected function processNotificationTemplates($email, $shopData, $orderRecord, $recipientData, $status) {

		// Make paths for override and regular template
		$fileHtml = $recipientData['recipientType'].'_'. $status.'.html.php';

		$regularPath = dirname(__FILE__).DS.'notifications'.DS.'templates';

		$customFolder = KenedoPlatform::p()->getDirCustomization() .DS. 'notification_templates';

		if (file_exists($customFolder.DS.$fileHtml)) {
			ob_start();
			include($customFolder.DS.$fileHtml);
			$content = ob_get_clean();
			if (trim($content)) {
				$email->body = $content;
				return true;
			}
		}
		elseif (file_exists($regularPath.DS.$fileHtml)) {
			ob_start();
			include($regularPath.DS.$fileHtml);
			$content = ob_get_clean();
			if (trim($content)) {
				$email->body = $content;
				return true;
			}
		}

		return false;

	}

	/**
	 * Scans the email body for notification element placeholders, loads and inserts them in the email
	 *
	 * @param stdClass $email The email object holding all email dispatch data - referenced
	 * @param object $shopData store information object (see ConfigboxModelShopdata)
	 * @param ConfigboxOrderData $orderRecord Order record
	 * @param array $recipientData Array with data holding information about the recipient (see onConfigBoxSetData)
	 * @param int $status status of order record
	 */
	protected function processNotificationSnippets(&$email, $shopData, $orderRecord, $recipientData, $status) {

		$customBaseFolder = KenedoPlatform::p()->getDirCustomization().DS.'notification_snippets';

		// Deal with notification elements
		preg_match_all("/\{element_(.*)\}/", $email->body, $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $elementTemplate) {

				// Security measures to prevent unwanted file inclusion attempts
				$elementTemplate = str_replace('..','',$elementTemplate);
				$elementTemplate = str_replace('/','',$elementTemplate);
				$elementTemplate = str_replace("\\",'',$elementTemplate);

				// Make paths for override and regular template
				$fileName = $elementTemplate.'.html.php';

				// Prepare the file paths for regular and custom template
				$regularPath = dirname(__FILE__).DS.'notifications'.DS.'elements'.DS.$fileName;

				$customPath = $customBaseFolder.DS.$fileName;

				// Set up output buffering to get template output in a variable
				ob_start();

				if (file_exists($customPath)) {
					include($customPath);
				}
				elseif (file_exists($regularPath)) {
					include($regularPath);
				}
				else {
					echo 'Element "'.$elementTemplate.'" not found.';
				}

				$elementMarkup = ob_get_clean();

				$email->body = str_replace('{element_'.$elementTemplate.'}', $elementMarkup, $email->body);

			}
		}

	}

	/**
	 * Adds attachments to the email by scanning notification attachment files
	 *
	 * @param stdClass $email The email object holding all email dispatch data
	 * @param object $shopData store information object (see ConfigboxModelShopdata)
	 * @param ConfigboxOrderData $orderRecord Order record
	 * @param array $recipientData Array with data holding information about the recipient (see onConfigBoxSetData)
	 * @param int $status status of order record
	 *
	 * @return bool $success
	 */
	protected function processNotificationAttachments($email, $shopData, $orderRecord, $recipientData, $status) {

		$this->doNotSend = false;

		// Make paths for override and regular template
		$filePdfs = array();
		$filePdfs[] = $recipientData['recipientType'].'_'.$status.'.pdf.php';
		$filePdfs[] = $recipientData['recipientType'].'_'.$status.'_1.pdf.php';
		$filePdfs[] = $recipientData['recipientType'].'_'.$status.'_2.pdf.php';
		$filePdfs[] = $recipientData['recipientType'].'_'.$status.'_3.pdf.php';
		$filePdfs[] = $recipientData['recipientType'].'_'.$status.'_4.pdf.php';
		$filePdfs[] = $recipientData['recipientType'].'_'.$status.'_5.pdf.php';


		// Get the paths to regular and override attachment file
		$regularPath = dirname(__FILE__).DS.'notifications'.DS.'attachments';

		$customFolder = KenedoPlatform::p()->getDirCustomization().DS.'notification_attachments';

		foreach ($filePdfs as $filePdf) {

			$this->doNotSend = false;

			// Figure out which template to use
			if (is_file($customFolder.DS.$filePdf)) {
				$templatePath = $customFolder.DS.$filePdf;
			}
			elseif (is_file($regularPath.DS.$filePdf)) {
				$templatePath = $regularPath.DS.$filePdf;
			}
			else {
				$templatePath = NULL;
			}

			// Load the content
			if ($templatePath) {
				ob_start();
				include($templatePath);
				$content = ob_get_clean();
			}
			else {
				continue;
			}

			// If we got content, store the file and add it to $email->attachments
			if ($content) {

				$domPdf = ConfigboxDomPdfHelper::getDomPdfObject();
				$domPdf->loadHtml($content, 'UTF-8');
				$domPdf->render();

				$baseName = KText::_('NOTIFICATION_ATTACHMENT_'.$status, KText::_('Attachment'));

				$fileName = KenedoPlatform::p()->getTmpPath() .DS. $baseName.'-'.$orderRecord->id.'-'.$status.'-'.(count($email->attachments) + 1).'.pdf';

				// Write the PDF file
				file_put_contents($fileName,$domPdf->output());

				// Write the HTML file for debugging
				file_put_contents($fileName.'.html',$content);

				// Make a copy if the template has set something
				if (!empty($this->copyPath)) {
					file_put_contents($this->copyPath,$domPdf->output());
					$this->copyPath = '';
				}

				// If $this->doNotSend is set in template, ignore the attachment
				if ($this->doNotSend == false) {
					// Add the file to the attachments array for inclusion in the email
					$email->attachments[] = $fileName;
				}

			}
		}

		return true;
	}

	/**
	 * Replace all placeholders in curly brackets with values from orderRecord, shopData, orderAddress (except notification_snippets)
	 *
	 * @param stdClass $email The email object holding all email dispatch data - referenced
	 * @param object $shopData store information object (see ConfigboxModelShopdata)
	 * @param ConfigboxOrderData $orderRecord Order record
	 * @param array $recipientData Array with data holding information about the recipient (see onConfigBoxSetData)
	 * @param int $status status of order record
	 *
	 * @return bool $success;
	 */
	protected function processNotificationPlaceholders($email, $shopData, $orderRecord, $recipientData, $status) {

		// Set the email keys holding the values to alter
		$emailKeys = array('body','subject');

		// Loop through the keys
		foreach ($emailKeys as $emailKey) {

			// Set a reference to the email object member to alter
			$textToAlter =& $email->$emailKey;

			// Replace order_id and the legacy placeholder cb_order_id right away to save ourselves some trouble
			$textToAlter = str_replace('{order_id}', $orderRecord->id, $textToAlter);
			$textToAlter = str_replace('{cb_order_id}', $orderRecord->id, $textToAlter);
			$textToAlter = str_replace('{comment}', $orderRecord->comment, $textToAlter);

			// Process all orderAddress related placeholders
			if ($orderRecord->orderAddress) {
				// Replace all placeholders with real values
				foreach ($orderRecord->orderAddress as $key=>$value) {
					if (is_string($value)) {

						// Prepend order_ to custom fields so we don't overwrite user custom fields
						if (strpos($key, 'custom_') === 0) {
							$key = 'customer_'.$key;
						}

						$textToAlter = str_replace('{'.$key.'}', $value, $textToAlter);
					}
				}
			}

			// Process all shopData related placeholders
			foreach ($shopData as $key=>$value) {
				if (is_string($value)) {
					$textToAlter = str_replace('{'.$key.'}', $value, $textToAlter);
				}
			}

			// Process all orderRecord related placeholders
			foreach ($orderRecord as $key=>$value) {
				if (is_string($value)) {

					// Prepend order_ to custom fields so we don't overwrite user custom fields
					if (strpos($key, 'custom_') === 0) {
						$key = 'order_'.$key;
					}

					// Deal with the datetime field 'created_on' (all dates in CB are stored in UTC timezone)
					if ($key == 'created_on') {
						$replacement = KenedoTimeHelper::getFormatted($value);
					}
					else {
						$replacement = $value;
					}

					$textToAlter = str_replace('{'.$key.'}', $replacement, $textToAlter);

				}
			}

			// Process gender related words (1 is male, 2 is female, any other value counts as unknown and is regarded as male)
			if ($orderRecord->orderAddress) {

				// Normalize gender number in case it is unkown
				if (!$orderRecord->orderAddress->billinggender) {
					$orderRecord->orderAddress->billinggender = 1;
				}
				preg_match_all("/\{(.*)\}/", $textToAlter, $matches);

				if (isset($matches[0])) {
					foreach ($matches[0] as $match) {

						// Extract the content of the curly bracket in the most complicated way possible :)
						$genderedVariations = explode('|',substr($match, 1, mb_strlen($match) -2));

						// Identify the gender related placeholder by checking if there are 2 parts
						if ($genderedVariations && count($genderedVariations) == 2) {
							// The part before the pipe symbol is the male expression, the part after is female
							$key = ($orderRecord->orderAddress->billinggender == 1) ? 0 : 1;
							$textToAlter = str_replace($match, $genderedVariations[$key], $textToAlter);
						}
					}
				}
				unset($matches);

			}

		}

		return true;

	}

	/**
	 * Scans the email body for relative urls in src attributes and url() statements (like CSS styles) and
	 * makes them absolute URIs, based on KPATH_URL_BASE
	 *
	 * @param stdClass $email The email object holding all email dispatch data - referenced
	 * @return void
	 */
	protected function processRelativeUrls(&$email) {

		// Rewrite paths to images if necessary
		preg_match_all("/src=\"(.*)\"/Ui", $email->body, $images);
		preg_match_all("/url(.*)/Ui", $email->body, $backgrounds);

		//TODO: Rather low priority: Leave non URL src attributes alone to allow things like base64 encoded sources

		// Init replacement array
		$replacements = array();

		// Check sources for images
		if (isset($images[1])) {
			foreach ($images[1] as $imagePath) {
				if (strpos($imagePath,'http') !== 0) {
					$replacements[$imagePath] = $imagePath;
				}
			}
		}
		// Check sources for CSS backgrounds and similar
		if (isset($backgrounds[1])) {
			foreach ($backgrounds[1] as $imagePath) {
				if (strpos($imagePath,'http') !== 0) {
					$replacements[$imagePath] = $imagePath;
				}
			}
		}

		// Replace relative URLs with absolute ones
		foreach ($replacements as $replacement) {
			$email->body = str_replace($replacement, KPATH_URL_BASE .'/'. $replacement, $email->body);
		}

	}

	/**
	 * Restores the original language in case it has changed during notification processing.
	 * Relies on the original language being set before notification processing.
	 */
	protected function restoreOriginalLanguage() {
		// Reset language settings and data if language is not the same as at the start
		if (KText::getLanguageTag() != $this->originalLanguageTag) {
			KText::setLanguage($this->originalLanguageTag);

			$notificationModel = KenedoModel::getModel('ConfigboxModelAdminnotifications');
			$notificationModel->forgetRecords();

			ConfigboxStoreHelper::forgetStoreRecords();

		}

	}

}