<?php
defined('CB_VALID_ENTRY') or die();

class IpnPaypal_wpstandard {

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
		
		if (!$this->comesFromPaypal()) {
			return 'not from paypal';
		}
		
		if (!$this->isCorrectReceiver()) {
			return 'wrong receiver email';
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
		
		if (KRequest::getVar('payment_status') == NULL) {
			return 'no payment status';
		}
		
		return true;
	}
	
	function comesFromPaypal() {
		
		$req = 'cmd=_notify-validate';

		$ignore = array('task','system','controller');
		$post = KRequest::getAll('POST');
		foreach ($post as $key => $value) {
			if (in_array($key,$ignore)) {
				continue;
			}	
			if (ini_get('magic_quotes_gpc') == 1) {
				$value = urlencode(stripslashes($value));
			}
			else {
				$value = urlencode($value);
			}

			$req .= "&$key=$value";
		}
		
		KLog::log('Payment option parameters are:'.var_export($this->settings->getProperties(),true),'payment');
		KLog::log('Test mode is '.(  $this->settings->get('testmode') ? 'on':'off'  ).'.','payment');
		
		if ($this->settings->get('testmode') && KRequest::getInt('test_ipn',0)) {
			$host = 'www.sandbox.paypal.com';
		}
		else {
			$host = 'www.paypal.com';
		}
		
		$scheme = 'ssl://';
		$port = 443;

		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Host: ".$host."\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	
		KLog::log('Trying to connect to PayPal to check if it is an authentic request. URL is "'.$host.'", data will be '.var_export($req,true),'payment');
	
		$fp = fsockopen ($scheme.$host, $port, $errno, $errstr, 30);
		
		if (!$fp) {
			KLog::log('Could not establish a connection to PayPal to check if it is an authentic request.','payment');
			return false;	
		}
		
		fputs ($fp, $header . $req);
		
		$foundVerfied = false;
		$response = '';
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			
			KLog::log('Current response line is "'.$line.'".','debug');
			if ($line == "VERIFIED") {
				$foundVerfied = true;
			}
			$response .= $line;
		}
		
		KLog::log("PayPal response from authenticity check:\n". $response, 'payment');
		
		return $foundVerfied;
	}
	
	function isCorrectReceiver() {
		
		$emailSetting = $this->settings->get('paypalid','');
		$emailIpn = KRequest::getString('receiver_email','');
		
		KLog::log('PayPal ID by setting is "'.$emailSetting.'", PayPal id by IPN is "'.$emailIpn.'".','payment');
		
		if ($emailSetting == $emailIpn) {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getTransactionId() {
		
		$tix = KRequest::getString('txn_id','','POST');
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('invoice',NULL,'POST');
		
		return $orderId;
	}
	
	function getCurrencyId() {
		
		$isoCode = KRequest::getString('mc_currency','','POST');
		
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
		$statId = KRequest::getInt('custom',NULL,'POST');
		return $statId;
	}
	
	function getPaidAmount() {
		$paiddAmount = KRequest::getFloat('mc_gross',NULL,'POST');
		
		return $paiddAmount;
	}
	
	function getPaymentSuccess() {
		
		$paymentStatus = KRequest::getString('payment_status',NULL,'POST');
		
		if ($paymentStatus == 'Completed') {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getErrorMessage() {
		
		$paymentStatus = KRequest::getString('payment_status',NULL,'POST');
		
		if ($paymentStatus != 'Completed') {
			return $paymentStatus;
		}
		else {
			return '';
		}
		
	}
	
}