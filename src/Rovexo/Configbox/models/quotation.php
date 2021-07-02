<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelQuotation {
	
	function getQuotation($orderId) {
		
		$db = KenedoPlatform::getDb();
		$query = "
		SELECT q.*, o.user_id
		FROM `#__cbcheckout_order_quotations` AS q
		LEFT JOIN `#__cbcheckout_order_records` AS o ON o.id = q.order_id
		WHERE q.order_id = ".(int)$orderId;
		$db->setQuery($query);
		$quotation = $db->loadObject();
		
		if (!$quotation) {
			return NULL;
		}
		else {
			// Append full path and download URL
			$quotation->path = $this->getQuotationsDir().'/'.$quotation->file;
			$quotation->url = KLink::getRoute('index.php?option=com_configbox&view=quotation&order_id='.$orderId);;
			return $quotation;
		}
		
	}

	function getQuotationsDir() {
		return KenedoPlatform::p()->getDirDataCustomer().'/private/quotations';
	}
	
	function isQuotationFileRemovable($orderId) {
		$quotation = $this->getQuotation($orderId);
		
		if (!$quotation) {
			return true;
		}
		else {
			if (is_file($this->getQuotationsDir().'/'.$quotation->file) && !is_writable($this->getQuotationsDir().'/'.$quotation->file)) {
				return false;
			}
			else {
				return true;
			}
		}
	}
	
	function removeQuotation($orderId) {
		
		$quotation = $this->getQuotation($orderId);
		
		if ($quotation) {

			$filePath = $this->getQuotationsDir().'/'.$quotation->file;

			if (is_file($filePath)) {
				$success = unlink($filePath);
				if (!$success) {
					KLog::log('Could not remove quotation file for order id "'.$orderId.'" in "'.$filePath.'". Check folder permissions.','error','Could not remove quotation file.');
					return false;
				}
			}
			
			$db = KenedoPlatform::getDb();
			$query = "DELETE FROM `#__cbcheckout_order_quotations` WHERE `order_id` = ".(int)$orderId;
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				KLog::log('Could not remove quotation record for order id "'.$orderId.'" because of a SQL error: "'.$db->getErrorMsg().'".','error','Could not remove quotation record.');
				return false;
			}
			else {
				return true;
			}
		
		}
		
		return true;
	}

	/**
	 *
	 * It looks if there is a quote generated already (meaning a record in the quotations table and a PDF file with the quote).
	 * If it's there, this just returns quote data (and does not create the PDF again)
	 * If not, it creates the quote data and the PDF.
	 *
	 * @param int $orderId Order record ID
	 * @return object Quotation data (see method getQuotation)
	 * @throws Exception If anything goes wrong (order does not exist, PDF rendering fails, storing the PDF fails)
	 */
	function createQuotation($orderId) {
		
		$quotation = $this->getQuotation($orderId);
		
		if ($quotation) {
			return $quotation;
		}

		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderRecord = $orderModel->getOrderRecord($orderId);

		if (!$orderRecord) {
			throw new Exception('createQuote called with an order ID that does not exist');
		}

		// Get the order record view's HTML
		$view = KenedoView::getView('ConfigboxViewRecord');
		$view->orderRecord = $orderRecord;
		$view->prepareTemplateVars();
		$view->showIn = 'quotation';
		$view->showChangeLinks = false;
		$view->showProductDetails = false;
		$orderRecordHtml = $view->getViewOutput();

		// Get the quotation view's HTML
		$quotationView = KenedoView::getView('ConfigboxViewQuotation');
		$quotationView->orderId = $orderId;
		$quotationView->prepareTemplateVars();
		$quotationView->orderRecordHtml = $orderRecordHtml;
		$quotationHtml = $quotationView->getViewOutput('default');
		
		// Prepare the file name
		$file = __FILE__;
		while(is_file($file)) {
			$file = $this->getQuotationsDir().'/'.$orderId.'_'.uniqid().'.pdf';
		}
		
		// Store the quotation HTML for debug
		if (KenedoPlatform::p()->getDebug()) {
			$logPath = KenedoPlatform::p()->getLogPath();
			$folder = $logPath.'/configbox/quotations_html';
			if (!is_dir($folder)) {
				mkdir($folder,0777,true);
			}
			file_put_contents($folder.'/'.basename($file).'.html', $quotationHtml);
		}
		
		//TODO: Replace the permission lookup with something more specific
		if (ConfigboxPermissionHelper::canChangeInvoices() && KRequest::getString('debug',0) == 1) {
			die($quotationHtml);
		}

		$domPdf = ConfigboxDomPdfHelper::getDomPdfObject();
		$domPdf->loadHtml($quotationHtml, 'UTF-8');
		$domPdf->render();
		
		// Create quotation folder if not there already
		if (!is_dir($this->getQuotationsDir())) {
			mkdir($this->getQuotationsDir(),0777,true);
			file_put_contents( $this->getQuotationsDir().'/.htaccess' , "deny from all");
		}
		
		// Write the file to the filesystem
		$succ = file_put_contents($file, $domPdf->output());
		
		if ($succ === false) {
			KLog::log('Could not store quotation file for order id "'.$orderId.'" in "'.$file.'". Check folder permissions.','error','Could not store quotation file.');
			throw new Exception('Could not store quotation file for order id "'.$orderId.'" in "'.$file.'". Check folder permissions.');
		}

		$record = new stdClass();
		$record->order_id = $orderId;
		$record->created_on = KenedoTimeHelper::getFormattedOnly('NOW','datetime');
		$record->created_by = KenedoPlatform::p()->getUserId();
		$record->file = basename($file);

		try {
			KenedoPlatform::getDb()->insertObject('#__cbcheckout_order_quotations', $record, 'order_id');
		}
		catch (Exception $e) {
			KLog::log('Could not store quotation record for order id "'.$orderId.'" because of a SQL error: "'.$e->getMessage().'".','error','Could not store quotation record.');
			throw $e;
		}

		return $this->getQuotation($orderId);

	}
	
}