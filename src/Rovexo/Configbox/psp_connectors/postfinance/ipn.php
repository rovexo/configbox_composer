<?php
defined('CB_VALID_ENTRY') or die();

class IpnPostfinance {

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
			return 'not an authentic request from psp';
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

		KLog::log('Starting SHA-OUT authenticity check.', 'payment');

		$shaOut = $this->settings->get('sha_out_passphrase');

		if (!$shaOut) {
			KLog::log('SHOP MANAGER CONFIGURATION ERROR: SHA-OUT PASSPHRASE IS NOT SET. THIS NEEDS TO BE SET IN THE PAYMENT METHOD SETTINGS.', 'payment');
			KLog::log('SHOP MANAGER CONFIGURATION ERROR: SHA-OUT PASSPHRASE IS NOT SET. THIS NEEDS TO BE SET IN THE PAYMENT METHOD SETTINGS.', 'error');
			return false;
		}

		$usedParams = file(__DIR__.'/misc/sha-out-parameters.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($usedParams as &$param) {
			trim($param);
		}

		usort($usedParams, 'strnatcasecmp');
		$stringToHash = '';
		foreach ($usedParams as $usedParam) {
			$value = KRequest::getVar(strtoupper($usedParam));
			if ($value) {
				$stringToHash .= strtoupper($usedParam) . '=' . KRequest::getString(strtoupper($usedParam)) . $shaOut;
			}
		}

		$calculatedHash = strtoupper(hash('sha256',$stringToHash));
		$sentHash = KRequest::getString('SHASIGN');

		if (KenedoPlatform::p()->getDebug()) {
			KLog::log('String to hash is  "'.$stringToHash.'"', 'payment');
		}

		KLog::log('Calculated hash is "'.$calculatedHash.'"', 'payment');
		KLog::log('Sent hash is       "'.$sentHash.'"', 'payment');

		if ($sentHash == $calculatedHash) {
			return true;
		}
		else {
			return false;
		}

	}

	function getTransactionId() {

		$tix = KRequest::getString('PAYID','','POST');

		return $tix;
	}

	function getOrderId() {

		$orderId = KRequest::getInt('orderID',NULL,'POST');

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

	function getPaidAmount() {

		$paidAmount = KRequest::getFloat('amount',0,'POST');

		return $paidAmount;
	}

	function getPaymentSuccess() {

		$paymentStatus = KRequest::getInt('STATUS',NULL,'POST');

		$successStatuses = array(
			5, // Authorized
			9, // Captured		
		);

		if (in_array($paymentStatus, $successStatuses)) {
			return true;
		}
		else {
			return false;
		}

	}

	function getErrorMessage() {

		$paymentStatus = KRequest::getInt('STATUS',NULL,'POST');

		switch ($paymentStatus) {

			case 0:
				$message = 'Invalid or incomplete';
				break;

			case 1:
				$message = 'Cancelled by customer';
				break;

			case 2:
				$message = 'Authorisation declined';
				break;

			default:
				$message = 'Unclear (Status code "'.$paymentStatus.'" is not known)';
				break;
		}

		return $message;

	}

}