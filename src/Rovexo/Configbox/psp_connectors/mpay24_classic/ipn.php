<?php
defined('CB_VALID_ENTRY') or die();

class IpnMpay24_classic {
	
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
	
	/**
	 * 
	 * Tells if request is valid to make sure it can be processed
	 * 
	 * @return mixed true if valid, string with error when not
	 */
	function isValidRequest() {

		$ip = ConfigboxLocationHelper::getClientIpV4Address();

		if ($ip != '213.164.23.169' && $ip != '213.164.25.245') {
			return 'Request not from 213.164.23.169 or 213.164.25.245, but from "'.$ip.'"';
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
		$paymentStatus = KRequest::getString('STATUS','');
		if ( empty($paymentStatus) ) {
			return 'no payment status';
		}
		
		return true;
	}
	
	function getTransactionId() {
		
		$tix = KRequest::getString('MPAYTID','','GET');
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('TID',NULL,'GET');
		
		return $orderId;
	}
	
	function getCurrencyId() {
		
		$isoCode = KRequest::getString('CURRENCY','','GET');
		
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
		
		$statId = KRequest::getInt('USER_FIELD',NULL,'GET');
		
		return $statId;
	}
	
	function getPaidAmount() {
		$paidAmount = KRequest::getInt('PRICE',NULL,'GET');
		// MPAY24 sends the amount in cents
		$paidAmount = $paidAmount / 100;
		
		return $paidAmount;
	}
	
	function getPaymentSuccess() {
		
		$paymentStatus = KRequest::getString('STATUS',NULL,'GET');
		KLog::log(__METHOD__.': Payment status is "'.$paymentStatus.'"','payment');
		if ($paymentStatus == 'RESERVED' || $paymentStatus == 'BILLED') {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getErrorMessage() {
		
		$paymentStatus = KRequest::getString('STATUS');
		
		return $paymentStatus;
		
	}
	
}