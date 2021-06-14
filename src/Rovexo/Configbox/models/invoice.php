<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelInvoice extends KenedoModel {

	function getInvoiceData($orderId) {
		
		$query = "
		SELECT *, CONCAT(`invoice_number_prefix`,`invoice_number_serial`) AS `invoice_number`
		FROM `#__cbcheckout_order_invoices`
		WHERE `order_id` = ".(int)$orderId;
		
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$invoiceData = $db->loadObject();
		return $invoiceData;
	}
	
	function insertInvoice($orderId, $userId, $customInvoicePath, $invoiceNumberPrefix, $invoiceNumberSerial) {
		
		$db = KenedoPlatform::getDb();
		
		// Check if file exists
		if (!is_file($customInvoicePath)) {
			$this->setError('Could not find custom invoice file in "'.$customInvoicePath.'".');
			return false;
		}

		if (!$invoiceNumberSerial) {
			$this->setError(KText::_('No invoice number serial entered.'));
			return false;
		}
			
		// Get a filename
		$file = __FILE__;
		while(is_file($file)) {
			$file = KenedoPlatform::p()->getDirDataCustomer().'/private/invoices/'.$invoiceNumberPrefix.$invoiceNumberSerial.'_'.uniqid().'.'.pathinfo($customInvoicePath, PATHINFO_EXTENSION);
		}
			
		// Move the file
		$succ = rename($customInvoicePath,$file);
		if (!$succ) {
			$this->setError(KText::_('Could not store invoice file to "'.$file.'".'));
			return false;
		}
		chmod($file,0755);
		
		$record = new stdClass();
		$record->invoice_number_prefix = $invoiceNumberPrefix;
		$record->invoice_number_serial = $invoiceNumberSerial;
		$record->order_id = $orderId;
		$record->file = basename($file);
		$record->original_file = $record->file;
		$record->changed = 0;
		$record->released_by = $userId;
		$record->released_on = KenedoTimeHelper::getFormattedOnly('NOW','datetime');
		$record->changed_by = 0;
		$record->changed_on = NULL;
		
		// Insert record
		$succ = $db->insertObject('#__cbcheckout_order_invoices',$record);
		if (!$succ) {
			KLog::log('Could not insert invoice data because of a SQL error: "'.$db->getErrorMsg().'"','error','System error: Could not update invoice data');
			return false;
		}
		return $record;
	}
	
	function changeInvoice($orderId, $userId, $customInvoicePath, $invoiceNumberPrefix, $invoiceNumberSerial) {
		
		$db = KenedoPlatform::getDb();
		
		// Check if file exists
		if (!is_file($customInvoicePath)) {
			$this->setError('Could not find custom invoice file in "'.$customInvoicePath.'".');
			return false;
		}
		
		if (!$invoiceNumberPrefix) {
			$this->setError(KText::_('No invoice number prefix entered.'));
			return false;
		}
		
		if (!$invoiceNumberSerial) {
			$this->setError(KText::_('No invoice number serial entered.'));
			return false;
		}
			
		// Get a filename
		$file = __FILE__;
		while(is_file($file)) {
			$file = KenedoPlatform::p()->getDirDataCustomer().'/private/invoices/'.$invoiceNumberPrefix.$invoiceNumberSerial.'_'.uniqid().'.'.pathinfo($customInvoicePath, PATHINFO_EXTENSION);
		}
			
		// Move the file
		$succ = rename($customInvoicePath,$file);
		if (!$succ) {
			$this->setError(KText::_('Could not store invoice file to "'.$file.'".'));
			return false;
		}
		chmod($file,0755);
		
		$record = new stdClass();
		$record->invoice_number_prefix = $invoiceNumberPrefix;
		$record->invoice_number_serial = $invoiceNumberSerial;
		$record->order_id = $orderId;
		$record->file = basename($file);
		$record->changed = 1;
		$record->changed_by = $userId;
		$record->changed_on = KenedoTimeHelper::getFormattedOnly('NOW','datetime');
		
		// Update record
		$succ = $db->updateObject('#__cbcheckout_order_invoices',$record,'id');
		if (!$succ) {
			KLog::log('Could not update invoice data because of a SQL error: "'.$db->getErrorMsg().'"','error','System error: Could not update invoice data');
			return false;
		}
		return $record;
	}
	
	function getInvoicePrefix($prefix = NULL) {
		if (!$prefix) {
			$prefix = CbSettings::getInstance()->get('invoice_number_prefix');
		}
		return $prefix;
	}
	
	function getNextInvoiceSerial() {
		$db = KenedoPlatform::getDb();
		
		// Get prefix
		$invoiceNumberPrefix = $this->getInvoicePrefix();
		
		// Get serial
		$query = "
		SELECT MAX(`invoice_number_serial`) AS `max_invoice_number_serial`
		FROM `#__cbcheckout_order_invoices`
		WHERE `invoice_number_prefix` = '".$invoiceNumberPrefix."'";
		$db->setQuery($query);
		$maxNumber = intval($db->loadResult());
		$invoiceNumberSerial = max(array($maxNumber + 1, CbSettings::getInstance()->get('invoice_number_start')));
		return $invoiceNumberSerial;
	}
	
	function generateInvoice($orderId, $userId = 0) {
		
		$db = KenedoPlatform::getDb();
		
		// Check for an existing invoice
		$invoiceData = $this->getInvoiceData($orderId);
		
		// Abort if invoice data exists and change flag is not set
		if ($invoiceData) {
			return $invoiceData;
		}
		
		// Get prefix
		$invoiceNumberPrefix = $this->getInvoicePrefix();
		
		// Get serial
		$query = "
		SELECT MAX(`invoice_number_serial`) AS `max_invoice_number_serial` 
		FROM `#__cbcheckout_order_invoices` 
		WHERE `invoice_number_prefix` = '".$invoiceNumberPrefix."'";
		$db->setQuery($query);
		$maxNumber = intval($db->loadResult());	
		$invoiceNumberSerial = max(array($maxNumber + 1, CbSettings::getInstance()->get('invoice_number_start')));
		
		// Get invoice file
		$file = $this->writeInvoice($orderId, $invoiceNumberPrefix.$invoiceNumberSerial);
		if (!$file) {
			KLog::log('Could not generate invoice for order ID "'.$orderId.'". Check if folder "'.KenedoPlatform::p()->getDirDataCustomer().'/private/invoices" is writable','error',KText::_('System error: Could not generate invoice file.'));
			return false;
		}

		// Prepare record
		$record = new stdClass();
		$record->invoice_number_prefix = $invoiceNumberPrefix;
		$record->invoice_number_serial = $invoiceNumberSerial;
		$record->order_id = $orderId;
		$record->file = basename($file);
		$record->original_file = $record->file;
		$record->changed = 0;
		$record->changed_by = 0;
		$record->changed_on = NULL;
		$record->released_by = $userId;
		$record->released_on = KenedoTimeHelper::getFormattedOnly('NOW','datetime');
		
		// Insert record
		$succ = $db->insertObject('#__cbcheckout_order_invoices',$record);
		
		// Throw an error on sql error
		if (!$succ) {
			KLog::log('Could not insert invoice data because of a SQL error: "'.$db->getErrorMsg().'"','error','System error: Could not insert invoice data');
			return false;
		}
		
		if (CbSettings::getInstance()->get('send_invoice')) {
			$this->sendInvoiceEmail($record);
		}
		
		return $record;
		
	}
	
	function sendInvoiceEmail($invoiceRecord) {

		// Get the models
		$shopModel  = KenedoModel::getModel('ConfigboxModelAdminshopdata');
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		
		// Get the order record
		$orderRecord = $orderModel->getOrderRecord($invoiceRecord->order_id);
		
		// Keep the current language, in case we need to change it
		$originalLanguageTag = KText::getLanguageTag();
		
		// Get the customer's preferred language
		$customerLanguage = $orderRecord->orderAddress->language_tag;
		
		// Change the language in case
		if ($customerLanguage != KText::getLanguageTag()) {
			KText::setLanguage($customerLanguage);
			$orderModel->unsetOrderRecord($invoiceRecord->order_id);
			$shopModel->forgetRecords();
			
			// Get the order record
			$orderRecord = $orderModel->getOrderRecord($invoiceRecord->order_id);
			
		}
		
		// Get the email invoice view
		$view = KenedoView::getView('ConfigboxViewEmailinvoice');
		$view->prepareTemplateVars();
		$view->assignRef('invoiceRecord', $invoiceRecord);
		$view->assignRef('orderRecord', $orderRecord);
		
		// Get the view's output
		$emailContent = $view->getViewOutput();
		
		// Get the email template view, assign the content and get the resulting output
		$emailView = KenedoView::getView('ConfigboxViewEmailtemplate');
		$emailView->prepareTemplateVars();
		$emailView->assign('emailContent', $emailContent);
		$emailBody = $emailView->getViewOutput('default');
		
		// Get the store information
		$shopData = ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);
		
		// Prepare the email data for dispatch
		$email = new stdClass();
		$email->toEmail		= $orderRecord->orderAddress->billingemail;
		$email->fromEmail	= $shopData->shopemailsales;
		$email->fromName	= $shopData->shopname;
		$email->subject		= KText::sprintf('EMAIL_INVOICE_SUBJECT',$orderRecord->id);
		$email->body 		= $emailBody;
		$email->attachments	= array( KenedoPlatform::p()->getDirDataCustomer().'/private/invoices/'.$invoiceRecord->file );
		
		// Send the email
		$dispatchResponse = KenedoPlatform::p()->sendEmail($email->fromEmail, $email->fromName, $email->toEmail, $email->subject, $email->body, true, NULL, NULL, $email->attachments);
		
		// Change the language back in case
		if ($originalLanguageTag != KText::getLanguageTag()) {
			KText::setLanguage($originalLanguageTag);
		}
		
		return $dispatchResponse;
	}
	
	function writeInvoice($orderId, $invoiceNumber) {
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderRecord = $orderModel->getOrderRecord($orderId);
		
		// Set the customer language
		if ($orderRecord->orderAddress->language_tag != KText::getLanguageTag()) {
			// Remember the original
			$originalLanguageTag = KText::getLanguageTag();
			KText::setLanguage($orderRecord->orderAddress->language_tag);
		}
		
		$invoiceView = KenedoView::getView('ConfigboxViewInvoice');
		$invoiceView->assign('orderId', $orderId);
		$invoiceView->assign('invoiceNumber', $invoiceNumber);
		$invoiceView->prepareTemplateVars();
		$invoiceHtml = $invoiceView->getViewOutput('default');

		$domPdf = ConfigboxDomPdfHelper::getDomPdfObject();
		$domPdf->loadHtml($invoiceHtml, 'UTF-8');
		$domPdf->render();
				
		$file = __FILE__;
		while(is_file($file)) {
			$file = KenedoPlatform::p()->getDirDataCustomer().'/private/invoices/'.$invoiceNumber.'_'.uniqid().'.pdf';
		}
		
		if (!is_dir(dirname($file))) {
			mkdir(dirname($file),0777,true);
		}
		
		$succ = file_put_contents($file, $domPdf->output());
		
		// Restore the original language
		if (!empty($originalLanguageTag)) {
			KText::setLanguage($originalLanguageTag);
		}
		
		if ($succ === false) {
			return false;
		}
		else {
			return $file;
		}
		
	}
	
	function getParsedInvoiceTemplate($invoiceNumber, $orderId) {

		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderRecord = $orderModel->getOrderRecord($orderId);

		$shopdata = ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);

		$invoice = $shopdata->invoice;
		
		$invoice = str_replace('{invoice_number}',$invoiceNumber,$invoice);
		
		$invoice = str_replace('{order_id}',$orderRecord->id,$invoice);
		$invoice = str_replace('{user_id}',$orderRecord->user_id,$invoice);
		$invoice = str_replace('{billingsalutation}',	$orderRecord->orderAddress->billingsalutation,$invoice);
		$invoice = str_replace('{salutation}',			$orderRecord->orderAddress->salutation,$invoice);
		$invoice = str_replace('{country}',$orderRecord->orderAddress->countryname,$invoice);
		$invoice = str_replace('{countryname}',$orderRecord->orderAddress->countryname,$invoice);
		$invoice = str_replace('{billingcountry}', $orderRecord->orderAddress->billingcountryname, $invoice);
		$invoice = str_replace('{billingcountryname}', $orderRecord->orderAddress->billingcountryname, $invoice);
		$invoice = str_replace('{state}',$orderRecord->orderAddress->statename,$invoice);
		$invoice = str_replace('{statename}',$orderRecord->orderAddress->statename,$invoice);
		$invoice = str_replace('{billingstate}',$orderRecord->orderAddress->billingstatename,$invoice);
		$invoice = str_replace('{billingstatename}',$orderRecord->orderAddress->billingstatename,$invoice);
		$invoice = str_replace('{sent_time}', KenedoTimeHelper::getFormatted('now'), $invoice);
		$invoice = str_replace('{invoice_date}', KenedoTimeHelper::getFormatted('now'), $invoice);
		
		foreach ($orderRecord->orderAddress as $key=>$value) {
			if (is_string($value)) $invoice = str_replace('{'.$key.'}',$value,$invoice);
			
		}
		foreach ($shopdata as $key=>$value) {
			if (is_string($value)) $invoice = str_replace('{'.$key.'}',$value,$invoice);
			
		}
		foreach ($orderRecord as $key=>$value) {
			if (is_string($value)) $invoice = str_replace('{'.$key.'}',$value,$invoice);
		}
		
		// Rewrite paths to images if necessary
		preg_match_all("/src=\"(.*)\"/Ui", $invoice, $images);
		preg_match_all("/url\((.*)\)/Ui", $invoice, $backgrounds);
		
		$replacements = array();
		
		if (isset($images[1])) {
			foreach ($images[1] as $imagePath) {
				if (strpos($imagePath,'http') !== 0) {
					$replacements[$imagePath] = $imagePath;
				}
			}
		}
		if (isset($backgrounds[1])) {
			foreach ($backgrounds[1] as $imagePath) {
				if (strpos($imagePath,'http') !== 0) {
					$replacements[$imagePath] = $imagePath;
				}
			}
		}
		
		foreach ($replacements as $replacement) {
			$invoice = str_replace($replacement, KenedoPlatform::p()->getUrlBase().'/'.$replacement, $invoice);
		}
		
		return $invoice;
	}
	
	
}
