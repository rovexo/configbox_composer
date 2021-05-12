<?php
defined('CB_VALID_ENTRY') or die();

class ObserverOrders {
	
	public $checkedOutStatus = 1;
	public $savedStatus = 8;
	public $quoteRequestedStatus = 11;
	public $orderConfirmedStatus = 4;

	/**
	 * @return string[]
	 */
	function onConfigBoxGetStatusCodes() {
		
		return array(
			0 => KText::_('Not ordered'),
			1 => KText::_('In Checkout'),
			2 => KText::_('Ordered'),
			3 => KText::_('Paid'),
			4 => KText::_('Confirmed'),
			5 => KText::_('Shipped'),
			6 => KText::_('Cancelled'),
			7 => KText::_('Refunded'),
			8 => KText::_('Saved'),
			9 => KText::_('Incorrect amount paid'),
			10 => KText::_('Payed in unsupported currency'),
			11 => KText::_('Quotation sent'),
			14 => KText::_('Quotation requested'),
			);	
	}

	/**
	 * @param string $type
	 * @return int|null
	 */
	function onConfigBoxGetStatusCodeForType($type) {
		
		switch ($type) {
			
			case 'refunded': 						return 7;
			case 'ordered': 						return 2;
			case 'paid': 							return 3;
			case 'shipped': 						return 5;
			case 'checked out': 					return $this->checkedOutStatus;
			case 'incorrect amount paid': 			return 9;
			case 'payed in unsupported currency': 	return 10;
			case 'confirmed': 						return $this->orderConfirmedStatus;
			case 'quote requested': 				return $this->quoteRequestedStatus;

			default: return null;
		}
		
	}

	/**
	 * @param string $action
	 * @param ConfigboxCartData $cartDetails
	 * @return bool|null
	 */
	function onConfigBoxGetActionPermission($action,$cartDetails) {

		// Legacy, remove in 2.7
		if ($action == 'removeGrandorder') {
			KLog::logLegacyCall('Change the action permission from removeGrandorder to removeCart.');
			$action = 'removeCart';
		}
		
		switch ($action) {
			
			case 'editOrder': 			$codes = array(0,8,11,12,14)	; break;
			case 'removeOrderRecord':	$codes = array(0,1,8,11,12,14)	; break;
			case 'saveOrder': 			$codes = array(0,1,8,11,12,14)	; break;
			case 'checkoutOrder': 		$codes = array(0,1,8,11,12,14)	; break;
			case 'cancelOrder': 		$codes = array(2)				; break;
			case 'removeCart': 			$codes = array(0,8,11,12,14)	; break;
			case 'goBackToCart': 		$codes = array(0,1, 8)			; break;
			case 'addToOrder': 			$codes = array(0,1,8,11,12,14)	; break;
			case 'cleanOrder': 			$codes = array(0,1)				; break;
			case 'placeOrder': 			$codes = array(0,1,8,11,14)		; break;
			case 'getInvoice': 			$codes = array(3,4,5)			; break;
			case 'requestQuote': 		$codes = array(0,1,8,11,12,14)	; break;
			case 'downloadProducts': 	$codes = array(3)				; break;
			
			default:
				KLog::log('No permissions set for action "'.$action.'".','error',KText::_('A system error occured.'));
				return false;
				break;
			
		}
		
		// Get the order status
		if (!isset($cartDetails->status) || $cartDetails->status === null) {
			KenedoObserver::triggerEvent('onConfigBoxGetStatus',array(&$cartDetails));
		}
		
		$status = (isset($cartDetails->status)) ? intval($cartDetails->status) : 0;
		
		// If it's a status unknown to this connector, don't get involved.
		// Other statuses can be added by connectors and have their own way of checking permissions
		$allStati = $this->onConfigBoxGetStatusCodes();
		
		if (!isset($allStati[$status])) {
			return NULL;
		}
		
		if (in_array($status, $codes)) {
			$result =  true;
		}
		else {
			$result = false;
		}
		
		KLog::log('Permission for action "'.$action.'" and status "'.$cartDetails->status.'" requested. Was '.(($result)?'GRANTED':'DENIED'),'debug');
		return $result;
		
	}

	/**
	 * @param int $orderId Order record ID
	 * @param int $status
	 * @return bool
	 */
	function onConfigBoxSetStatus( $orderId, $status ) {
		
		$query = "UPDATE `#__cbcheckout_order_records` SET `status` = ".(int)$status." WHERE `id` = ".(int)$orderId;
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$succ = $db->query();
		
		if ($succ) {
			KLog::log('Set status of order record with ID "'.$orderId.'" to "'.$status.'" ','debug');
			return true;
		}
		else {
			KLog::log('Could not set status of record with ID "'.$orderId.'" to "'.$status.'". Error was "'.$db->getErrorMsg().'"','error',KText::_('A system error occured.'));
			return false;	
		}
		
	}

	/**
	 * @param ConfigboxCartData $cartDetails
	 * @return bool
	 */
	function onConfigBoxGetStatus(&$cartDetails) {
		
		// Legacy, on 2.7 have the id coming from ->id only
		if (!empty($cartDetails->grandorder_id)) {
			KLog::logLegacyCall('onConfigBoxGetStatus called and it provided a grandorder_id, check the source of the call and make sure ->id gets the cart id.');
			$cartId = $cartDetails->grandorder_id;
		}
		elseif (!empty($cartDetails->cart_id)) {
			KLog::logLegacyCall('onConfigBoxGetStatus called. Looks like an orderrecord was provided instead of a cart details record.');
			$cartId = $cartDetails->cart_id;
		}
		else {
			$cartId = $cartDetails->id;
		}
		
		$query = "SELECT `status` FROM `#__cbcheckout_order_records` WHERE `cart_id` = ".intval($cartId)." LIMIT 1";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		
		$codeStrings = $this->onConfigBoxGetStatusCodes();
				
		$cartDetails->status = (int) $db->loadResult();
		$cartDetails->statusString = (!empty($codeStrings[$cartDetails->status])) ? $codeStrings[$cartDetails->status] : $cartDetails->status;
		
		return $cartDetails->status;
		
	}

	/**
	 * @param ConfigboxCartData $cartDetails
	 * @return bool
	 */
	function onConfigBoxAddToCart(&$cartDetails) {

		// Go through all positions..
		foreach ($cartDetails->positions as $position) {

			$configuration = ConfigboxConfiguration::getInstance($position->id);

			// ..store selections from finished positions in the DB..
			if ($position->finished) {
				$configuration->storeSelectionsInDb();
			}

			// ..then clear the selections from session (including unfinished ones) - just for keeping session data small
			$configuration->deleteSelectionsFromSession();

		}

		$cartDetails->redirectURL = 'index.php?option=com_configbox&view=cart';
	
		return true;
	}

	/**
	 * @param ConfigboxCartData $cartDetails Cart details from ConfigboxModelCart->getCartDetails();
	 */
	function onConfigBoxGetCbOrderId(&$cartDetails) {
		
		$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `cart_id` = ".intval($cartDetails->id)." LIMIT 1";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$id = $db->loadResult();
		if ($id !== NULL) $cartDetails->cbOrderId = $id;
		else $cartDetails->cbOrderId = null;
		
	}

	/**
	 * @param ConfigboxCartData $cartDetails Cart details (use it to get the order address in case we got a connected order record)
	 * @param int $userId ConfigBox user id
	 * @return ConfigboxUserData $userData (see ConfigboxUserHelper::getUser()
	 *
	 * @throws Exception If no user can be found
	 */
	function onConfigBoxGetUserData($cartDetails = NULL, $userId = NULL) {

		// If we got cart details, take the order address in case we got a matching order record
		if ($cartDetails) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `cart_id` = ".intval($cartDetails->id)." LIMIT 1";
			$db->setQuery($query);
			$orderRecordId = $db->loadResult();
			
			if ($orderRecordId) {
				$user = ConfigboxUserHelper::getOrderAddress($orderRecordId);
			}
			else {
				$user = ConfigboxUserHelper::getUser($cartDetails->user_id);
			}

			return $user;

		}
		elseif ($userId) {
			$user = ConfigboxUserHelper::getUser($userId);
			return $user;
		}
		else {
			$userId = ConfigboxUserHelper::getUserId();
			if (!$userId) {
				throw new Exception('No user ground for cart details');
			}
			else {
				$user = ConfigboxUserHelper::getUser($userId);
			}
			
			return $user;
		}

	}

	/**
	 * @param float $taxRate
	 * @param int $taxRateId
	 * @param int|null $userId
	 * @return float
	 */
	function onConfigboxGetTaxRate( &$taxRate, $taxRateId, $userId = NULL ) {
		
		if (!$userId) {
			$userId = ConfigboxUserHelper::getUserId();
		}
		$userData = ConfigboxUserHelper::getUser($userId);
		
		$newRate = ConfigboxUserHelper::getTaxRate($taxRateId, $userData);
		
		if ($newRate !== NULL) {
			$taxRate = $newRate;
		}
		
		return $newRate;
	}

	/**
	 * @param ConfigboxUserData $customerData
	 * @return bool
	 */
	function onConfigBoxIsVatFree($customerData) {
		return ConfigboxUserHelper::isVatFree($customerData);
	}

	/**
	 * @param int $cartId
	 * @param ConfigboxUserData $customerData
	 * @param float $weight
	 * @param array $maxDimensions
	 * @param bool $cheapestOnly
	 * @return ConfigboxDeliveryMethodData[]
	 */
	function onConfigboxGetDeliveryOptions($cartId, $customerData, $weight, $maxDimensions, $cheapestOnly = false) {
		$deliveryModel = KenedoModel::getModel('ConfigboxModelDelivery');
		return $deliveryModel->getDeliveryOptions($cartId, $customerData, $weight, $maxDimensions, $cheapestOnly);
	}

	/**
	 * @param ConfigboxUserData $customerData
	 * @param float $baseTotal
	 * @return ConfigboxPaymentmethodData[]
	 */
	function onConfigboxGetPaymentOptions($customerData, $baseTotal) {
		$paymentModel = KenedoModel::getModel('ConfigboxModelPayment');
		return $paymentModel->getPaymentOptions($customerData, $baseTotal);
	}
	
}