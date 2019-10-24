<?php
defined('CB_VALID_ENTRY') or die();

class IpnVrpay_virtuell {

	/**
	 * @var KStorage $settings
	 */
	protected $settings;

	function __construct() {
		KLog::log('VRPAY gateway selected (Class "'. __CLASS__ .'").','payment');
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
		$paymentStatus = KRequest::getString('STATUS','','POST');
		if ( empty($paymentStatus) ) {
			return 'no payment status';
		}
		
		return true;
	}
	
	function getTransactionId() {
		
		$tix = KRequest::getInt('TSAID',0,'POST');;
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('REFERENZNR',0,'POST');
		
		return $orderId;
	}
	
	function getCurrencyId() {
		
		$isoCode =KRequest::getKeyword('WAEHRUNG','','POST');
		
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
		
		// Custom fields are not supported
		$statId = NULL;
		return $statId;
	}
	
	function getPaidAmount() {
		$paidAmount = (int) KRequest::getString('BETRAG',NULL,'POST');
		$paidAmount = $paidAmount / 100;
		
		return $paidAmount;
	}
	
	function getPaymentSuccess() {
		
		$paymentStatus = KRequest::getString('STATUS');
		KLog::log('Payment status is "'.$paymentStatus.'"','payment');
		if ($paymentStatus == "GEKAUFT") return true;
		else return false;
		
	}
	
	function getErrorMessage() {
		
		$errorMessage = KRequest::getInt('RMSG',0,'POST');
		
		return $errorMessage;
		
		
	}
	
	
}