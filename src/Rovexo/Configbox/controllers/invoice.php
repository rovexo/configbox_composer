<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerInvoice extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewInvoice
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewInvoice');
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
		
		$orderId = KRequest::getInt('order_id');
		$currentUserId = ConfigboxUserHelper::getUserId();
		
		$query = "SELECT `user_id` FROM `#__cbcheckout_order_records` WHERE `id` = ".(int)$orderId;
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$orderUserId = $db->loadResult();
		
		if ($orderUserId != $currentUserId) {
			$platformUserId = KenedoPlatform::p()->getUserId();
			if (ConfigboxPermissionHelper::canDownloadInvoices($platformUserId) !== true) {
				KLog::log('Customer with ID "'.$currentUserId.'" tried to download invoice for order ID "'.$orderId.'" of user "'.$orderUserId.'".','permissions','Invoice not found.');
				return false;
			}
		}
		
		$model = KenedoModel::getModel('ConfigboxModelInvoice');
		$invoiceData = $model->getInvoiceData($orderId);
		
		$file = KenedoPlatform::p()->getDirDataCustomer().'/private/invoices/'.$invoiceData->file;
		
		$filename = KText::sprintf('Invoice_%s',$invoiceData->invoice_number). '.pdf';
		
		if (is_file($file)) {
			header("Cache-Control: private");
			header("Content-Type: application/pdf");
			header("Content-Disposition: attachment; filename=\"$filename\"");
			readfile($file);
		}
		else {
			echo 'filenotfound';
		}
		die();
	}
	
}
