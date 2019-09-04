<?php
defined('CB_VALID_ENTRY') or die();

class IpnWorldpay_bg {

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
	
	/**
	 * 
	 * Tells if request is valid to make sure it can be processed
	 * 
	 * @return mixed true if valid, string with error when not
	 */
	function isValidRequest() {
		return true;
	}
	
	function getTransactionId() {
		
		$tix = KRequest::getString('transId','','POST');
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('cartId',NULL,'POST');
		$ex = explode('-',$orderId);
		return $ex[0];
	}
	
	function getCurrencyId() {
		
		$isoCode = KRequest::getString('currency',NULL,'POST');
		
		$currency = ConfigboxCurrencyHelper::getCurrencyByIsoCode($isoCode);
		
		if (!$currency) {
			KLog::log('Currency "'.$isoCode.'" not found or unpublished.','error');
			return false;
		}
		else {
			return $currency->id;
		}
		
	}
	
	function getPaidAmount() {
		$paidAmount = KRequest::getFloat('amount',NULL,'POST');
		
		return $paidAmount;
	}
	
	function getPaymentSuccess() {
		
		$paymentStatus = KRequest::getString('transStatus',NULL,'POST');
		KLog::log('Payment status is "'.$paymentStatus.'"','payment');
		if ($paymentStatus == 'Y') {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getErrorMessage() {
		
		$paymentStatus = '';
		
		return $paymentStatus;
		
	}
	
	function requiresAmountCheck() {
		return true;
	}
	
	function getResponseString() {
		
		$status = "All good";
		
		return $status;
	}
	
}