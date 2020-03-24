<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerIPN extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
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

	function processIpn() {
		$responseString = $this->processTransactionFeedback();
		$levels = ob_get_level();
		for ($i = $levels; $i > 1; $i--) {
			ob_end_clean();
		}
		die($responseString);
	}
	
	function processTransactionFeedback() {
		
		try {
			
			$connectorName = KRequest::getString('connector_name','');
			
			$ipnObject = ConfigboxPspHelper::getIpnObject($connectorName);
			
			if (!$ipnObject) {
				KLog::log('Could not get a payment gateway','error');
				return false;
			}
			
			// Get the order id
			$orderId = $ipnObject->getOrderId();
			
			// Get order
			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
			$orderRecord = $orderModel->getOrderRecord( $orderId );
			
			// Abort if order is not found
			if (!$orderRecord) {
				KLog::log('No order found for order id "'.$orderId.'".','error');
				KLog::log('No order found for order id "'.$orderId.'".','payment');
				return false;
			}
			
			// Send the payment methods settings
			$ipnObject->setPaymentOptionSettings($orderRecord->payment->params);
			
			// Check if the order id valid
			$response = $ipnObject->isValidRequest();
			if ($response !== true) {
				KLog::log('IPN request is not valid. Error is "'.$response.'"','payment');				
				return false;
			}
			
			$transactionId = $ipnObject->getTransactionId();
			
			// Store the transaction id
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__cbcheckout_order_records` SET `transaction_id` = '".$db->getEscaped($transactionId)."' WHERE `id` = ".intval($orderId);
			$db->setQuery($query);
			$db->query();
			
			KLog::log('Transaction ID is "'.$transactionId.'"','payment');
			
			// Check if payment was successful
			$paymentSucceeded = $ipnObject->getPaymentSuccess();
			
			if (!$paymentSucceeded) {
				KLog::log('Payment was not successful for order "'.$orderId.'". Gateway message "'.$ipnObject->getErrorMessage().'"','payment');
				if (method_exists($ipnObject, 'getResponseString')) {
					return $ipnObject->getResponseString();
				}
				else {
					return false;
				}
			}
			else {
				KLog::log('Payment was successful for order "'.$orderId.'".','payment');
			}
			
			if (method_exists($ipnObject, 'requiresAmountCheck') && $ipnObject->requiresAmountCheck() == true) { 
				
				// Get paid amount and currency id
				$paidAmount = $ipnObject->getPaidAmount();
				KLog::log('Paid amount in buyer\'s currency is "'.$paidAmount.'".','payment');
				
				$currencyId = $ipnObject->getCurrencyId();
				KLog::log('Currency ID is "'.$currencyId.'".','payment');
				// Check if currency is supported
				if (!$currencyId) {
					KLog::log(' Unsupported currency id ("'.$currencyId.'") for "'.$orderId.'".','payment');

					$status = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('payed in unsupported currency'), true);

					$orderModel->setStatus($status,$orderId);
					
					KLog::log('Currency for order "'.$orderId.'" not supported.','payment');
					if (method_exists($ipnObject, 'getResponseString')) {
						return $ipnObject->getResponseString();
					}
				}
				
				// Get the currency used in payment
				$currency = ConfigboxCurrencyHelper::getCurrencyById($currencyId);
				
				// Multiply expected amount with currency multiplier
				$checkAmount = round($orderRecord->basePayableAmount * $currency->multiplicator,2);
				
				KLog::log('Expecting "'.round($orderRecord->basePayableAmount,2).'" in base currency as full payment for order "'.$orderId.'".','payment');
				KLog::log('Expecting "'.$checkAmount.'" in "'.$currency->code.'" as full payment (Multiplicator is '.$currency->multiplicator.').','payment');
				
				// Log expected and payed amount
				KLog::log('Paid amount in "'.$currency->code.'" is "'.$paidAmount.'".','payment');
				
				// Check for correct amount full payment
				if ($checkAmount == $paidAmount) {
					$amountCorrect = true;
				}
				else {
					$amountCorrect = false;
				}
			}
			else {
				KLog::log('PSP does not require amount checks, payment is regarded full.','payment');
				$amountCorrect = true;
			}
			
			// If amount is correct set status to paid
			if ($amountCorrect) {

				$status = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('paid'), true);;
				
				// Set Status
				KLog::log('Amount correct, setting order status to "'.$status.'".','payment');
				$orderModel->setStatus($status,$orderId);
				
			}
			else {
				$status = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('incorrect amount paid'), true);
				KLog::log('Amount NOT correct, setting order status to "'.$status.'".','payment');
				$orderModel->setStatus($status,$orderId);
			}
			
			if (method_exists($ipnObject, 'getResponseString')) {
				return $ipnObject->getResponseString();
			}
		
		} catch (Exception $e) {
			KLog::log('PHP message: '.$e->getMessage(), 'payment');
		}

		return false;

	}

	function getUrlSegments(&$queryParameters) {

		// Just in case we get bad data, log it and leave it alone
		if ($queryParameters['task'] != 'processipn') {
			KLog::log('Unknown task for IPN controller detected. Task was "'.$queryParameters['task'].'"', 'error');
			return array();
		}

		$segments[] = 'ipn';
		$segments[] = 'processipn';
		$segments[] = $queryParameters['connector_name'];

		unset($queryParameters['controller'], $queryParameters['task'], $queryParameters['connector_name'], $queryParameters['Itemid']);

		return $segments;
	}

}
