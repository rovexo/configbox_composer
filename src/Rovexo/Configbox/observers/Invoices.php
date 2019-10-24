<?php
defined('CB_VALID_ENTRY') or die();

class ObserverInvoices {
	
	/**
	 * Generates the invoice and flags the order record as invoice_released
	 * @param int $orderId
	 * @param int $status
	 */
	function onConfigBoxSetStatus( $orderId, $status) {

		$settings = CbSettings::getInstance();

		if ($settings->get('enable_invoicing') && ( $settings->get('invoice_generation') == 0 || $settings->get('invoice_generation') == 1)) {
			
			$paidStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType',array('paid'),true);
			
			if ($status && $status == $paidStatus && $settings->get('invoice_generation') == 0) {
				
				// On fully automatic generation, release the invoice
				$db = KenedoPlatform::getDb();
				$query = "UPDATE `#__cbcheckout_order_records` SET `invoice_released` = '1' WHERE `id` = ".(int)$orderId;
				$db->setQuery($query);
				$db->query();
				
				$invoiceModel = KenedoModel::getModel('ConfigboxModelInvoice');
				$invoiceModel->generateInvoice($orderId, 0);
				
			}
			
		}
				
	}
	
}