<?php
defined('CB_VALID_ENTRY') or die();

class IpnSagepay_server {

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
		
		$tix = KRequest::getString('VPSTxID','','POST');
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('VendorTxCode',NULL,'POST');
		$ex = explode('-',$orderId);
		return $ex[0];
	}
	
	function getCurrencyId() {
		
		$isoCode = 'GBP';
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
		$paidAmount = NULL;
		
		return $paidAmount;
	}
	
	function getPaymentSuccess() {
		
		$paymentStatus = KRequest::getString('Status',NULL,'POST');
		KLog::log('Payment status is "'.$paymentStatus.'"','payment');
		if ($paymentStatus == 'OK' || $paymentStatus == 'AUTHENTICATED') {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getErrorMessage() {
		
		$paymentStatus = KRequest::getString('StatusDetail');
		
		return $paymentStatus;
		
	}
	
	function requiresAmountCheck() {
		return false;
	}
	
	function getResponseString() {
		
		if ($this->getPaymentSuccess() == true) {
			$redirectUrl = KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.$this->getOrderId(),false,false);
		}
		else {
			$redirectUrl = KLink::getRoute('index.php?option=com_configbox&view=checkout',false,false);
		}
		
		if (CbSettings::getInstance()->get('securecheckout')) {
			$redirectUrl = 'https://'.KPATH_HOST . $redirectUrl;
		}
		else {
			$redirectUrl = 'http://'.KPATH_HOST . $redirectUrl;
		}
		
		$status = "Status=OK\r\nRedirectURL=$redirectUrl\r\nStatusDetail=All Good";
		
		return $status;
	}
	
}