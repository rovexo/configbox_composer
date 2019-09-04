<?php
defined('CB_VALID_ENTRY') or die();

class IpnQuickpay {

	/**
	 * @var KStorage $settings
	 */
	protected $settings;
	
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
		
		if (!$this->isAuthenticRequest()) {
			return 'not an authentic request from quickpay';
		}
		
		if (!$this->getOrderId()) {
			return 'no order id';
		}
		
		if (!$this->getCurrencyId()) {
			return 'no currency id';
		}
		
		if (!$this->getPaidAmount()) {
			return 'no amount';
		}
		
		return true;
	}
	
	function isAuthenticRequest() {
		return true;
	}
	
	function getTransactionId() {
		
		$tix = KRequest::getString('transaction','','POST');
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('ordernumber',NULL,'POST');
		
		return $orderId;
	}
	
	function getCurrencyId() {
		
		$isoCode = KRequest::getString('currency','','POST');
		
		$currency = ConfigboxCurrencyHelper::getCurrencyByIsoCode($isoCode);
		
		if (!$currency) {
			KLog::log('Currency "'.$isoCode.'" not found or unpublished.','error');
			return false;
		}
		else {
			return $currency->id;
		}
		
	}
	
	function getStatId() {
		return NULL;
	}
	
	function getPaidAmount() {
		
		// QuickPay sends the amount value in smallest unit (1 EUR is 100)
		$paidAmount = KRequest::getFloat('amount',0,'POST') / 100;
		$fee = KRequest::getFloat('fee',0,'POST') / 100;
		
		return $paidAmount - $fee;
	}
	
	function getPaymentSuccess() {

		$paymentStatus = KRequest::getInt('qpstat',NULL,'POST');
		
		if ($paymentStatus == '000') {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getErrorMessage() {
		
		$message = KRequest::getString('qpstatmsg',NULL,'POST');
		
		return $message;
		
	}
	
}