<?php
defined('CB_VALID_ENTRY') or die();

class ObserverInvoices {
	
	/**
	 * Generates the invoice and flags the order record as invoice_released
	 * @param int $orderId
	 * @param int $status
	 */
	function onConfigBoxSetStatus( $orderId, $status) {

		$paidStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('paid'),true);

		if ($status != $paidStatus) {
			return;
		}

		$settings = CbSettings::getInstance();

		if ($settings->get('enable_invoicing') && $settings->get('invoice_generation') == 0) {

			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
			$orderRecord = $orderModel->getOrderRecord($orderId);

			if ($orderRecord->invoice_released == 1) {
				return;
			}

			// On fully automatic generation, release the invoice
			$db = KenedoPlatform::getDb();
			$query = "
			UPDATE `#__cbcheckout_order_records` 
			SET `invoice_released` = '1' 
			WHERE `id` = ".(int)$orderId;
			$db->setQuery($query);
			$db->query();

			$invoiceModel = KenedoModel::getModel('ConfigboxModelInvoice');
			$invoiceModel->generateInvoice($orderId, 0);
			
		}
				
	}
	
}