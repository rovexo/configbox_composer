<?php
defined('CB_VALID_ENTRY') or die();

class IpnMultisafepay_connect {

	protected $settings;
	protected $api;
	
	function __construct() {
		KLog::log(__CLASS__ .' gateway selected.','payment');
	}

	/**
	 * @param KStorage $settings
	 */
	function setPaymentOptionSettings($settings) {
		
		$this->settings = $settings;
		
		if ($this->settings->get('testmode',0) == 1) {
			$test = true;
			$accountId = $this->settings->get('account_id_test','');
			$siteId = $this->settings->get('site_id_test','');
			$siteCode = $this->settings->get('site_code_test','');
		}
		else {
			$test = false;
			$accountId = $this->settings->get('account_id_production','');
			$siteId = $this->settings->get('site_id_production','');
			$siteCode = $this->settings->get('site_code_production','');
		}
		
		include(dirname(__FILE__).'/api/MultiSafepay.combined.php');
		
		$this->api = new MultiSafepay();
				
		$this->api->test                         = $test;
		$this->api->merchant['account_id']       = $accountId;
		$this->api->merchant['site_id']          = $siteId;
		$this->api->merchant['site_code']        = $siteCode;
		$this->api->transaction['id']            = $this->getTransactionId();
		
		KLog::log('Account id "'.$accountId.'"'.'Site id "'.$siteId.'".'.'Site code "'.$siteCode.'". Transaction ID "'.$this->api->transaction['id'].'"','payment');
		
		$this->api->getStatus();
		
		KLog::log('Response from the PSP is: '.var_export($this->api->details, true),'payment');
		
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
		$id = KRequest::getString('transactionid');
		return $id;
	}
	
	function getOrderId() {
		
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `transaction_id` = '".$db->getEscaped($this->getTransactionId())."' LIMIT 1";
		KLog::log('Query is: '.$query,'payment');
		$db->setQuery($query);
		$orderId = $db->loadResult();
		return $orderId;
		
	}
	
	function getCurrencyId() {
		
		$currencyCode = $this->api->details['transaction']['currency'];
		
		$currency = ConfigboxCurrencyHelper::getCurrencyByIsoCode($currencyCode);
		
		if (!$currency) {
			KLog::log('Currency "'.$currencyCode.'" not found or active.','error');
			return false;
		}
		else {
			return $currency->id;
		}
		
	}
	
	function getPaidAmount() {
		$paidAmount = $this->api->details['transaction']['amount'] / 100;
		return $paidAmount;
	}
	
	function getPaymentSuccess() {
		
		if ($this->api->status == 'completed') {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getErrorMessage() {
		
		if ($this->api->error_code) {
			$errorMessage = $this->api->error_code . ": " . $this->api->error;
		}
		else {
			$errorMessage = '';
		}
		
		return $errorMessage;
		
	}
	
	function requiresAmountCheck() {
		return true;
	}
	
	function getResponseString() {
		
		$status = "OK";
		
		return $status;
	}
	
}