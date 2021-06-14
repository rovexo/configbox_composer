<?php
defined('CB_VALID_ENTRY') or die();

class IpnAuthorizenet_sim {
	
	private $settings;
	
	function __construct() {
		KLog::log(__CLASS__ .' gateway selected.','payment');
	}
	
	function setPaymentOptionSettings($settings) {
		$this->settings = $settings;
	}
	
	function &getPaymentOptionSettings() {
		return $this->settings;
	}
	
	function isValidRequest() {
	
		if (!$this->getOrderId()) {
			return 'no order id';
		}
	
		if (!$this->getCurrencyId()) {
			return 'no currency id';
		}
	
		if (!$this->getPaidAmount()) {
			return 'no amount';
		}
	
		if (KRequest::getVar('x_response_code',NULL) === NULL) {
			return 'no payment status';
		}
	
		return true;
	}
	
	function getTransactionId() {
		
		$tix = KRequest::getString('x_trans_id',NULL,'POST');
	
		return $tix;
	}
	
	function getOrderId() {
	
		$orderId = KRequest::getInt('x_invoice_num',NULL,'POST');
	
		return $orderId;
	}
	
	function getCurrencyId() {
		
		$currency = ConfigboxCurrencyHelper::getCurrencyByIsoCode('USD');
	
		if (!$currency) {
			KLog::log('Currency USD not found or unpublished.','error');
			return false;
		}
		else {
			return $currency->id;
		}
	
	}
	
	function getStatId() {
		$statId = NULL;
		return $statId;
	}
	
	function getPaidAmount() {
		$paidAmount = KRequest::getFloat('x_amount',NULL,'POST');
		return $paidAmount;
	}
	
	function getPaymentSuccess() {

		$paymentStatus = KRequest::getString('x_response_code',NULL,'POST');

		KLog::log('PSP sent x_response_code with value "'.$paymentStatus.'".','payment');

		if ($paymentStatus == '1') {
			return true;
		}
		else {
			return false;
		}
	
	}
	
	function getErrorMessage() {
	
		$errorMessage = KRequest::getString('x_response_reason_text',NULL,'POST');
	
		return $errorMessage;
	
	}
	
	function getResponseString() {
	
		if ($this->getPaymentSuccess()) {
			$url = KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.$this->getOrderId(), false, true);
			$return = 'Thank you for your order.';
		}
		else {
			KenedoPlatform::p()->sendSystemMessage($this->getErrorMessage());
			$url = KLink::getRoute('index.php?option=com_configbox&view=checkout', false, true);
			$return = 'Your transaction has not been processed. Please try again and confirm that the credit card number, expiration date and billing address entered are correct.';
		}

		if ($this->getPaymentSuccess()) {
			$return .= '<script type="text/javascript">window.location.href = "'.$url.'"</script>';
		}
		else {
			$return .= '<p><a href="';
			$return .= $url;
			$return .= '">'.KText::_('Back to checkout').'</a><p>';
		}
		
		
		
		return $return;
	}
	
}
