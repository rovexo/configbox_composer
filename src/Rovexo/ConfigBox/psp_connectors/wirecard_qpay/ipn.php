<?php
defined('CB_VALID_ENTRY') or die();

class IpnWirecard_qpay {

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

		if ($this->isAuthenticRequest() == false) {
			return 'Payment notification not genuine';
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

		$paymentStatus = KRequest::getString('paymentState','','POST');
		if ( empty($paymentStatus) ) {
			return 'no payment status';
		}

		return true;
	}

	function isAuthenticRequest() {

		$testMode = $this->settings->get('testmode');
		if ($testMode) {
			$secret = $this->settings->get('secret_test');
			KLog::log('using test mode secret for authenticity check.','payment');
		}
		else {
			$secret = $this->settings->get('secret_production');
			KLog::log('using production secret for authenticity check.','payment');
		}

		$responseFingerprintOrder = KRequest::getVar("responseFingerprintOrder");
		$responseFingerprint = KRequest::getVar("responseFingerprint");

		$str4responseFingerprint = "";
		$mandatoryFingerPrintFields = 0;
		$secretUsed = 0;

		$order = explode(",",$responseFingerprintOrder);
		for ($i = 0; $i < count($order); $i++) {
			$key = $order[$i];

			// check if there are enough fields in den responsefingerprint
			if ((strcmp($key, "paymentState")) == 0 && (strlen(KRequest::getVar($order[$i])) > 0)) {
				$mandatoryFingerPrintFields++;
			}
			if ((strcmp($key, "orderNumber")) == 0 && (strlen(KRequest::getVar($order[$i])) > 0)) {
				$mandatoryFingerPrintFields++;
			}
			if ((strcmp($key, "paymentType")) == 0 && (strlen(KRequest::getVar($order[$i])) > 0)) {
				$mandatoryFingerPrintFields++;
			}

			if (strcmp($key, "secret") == 0) {
				$str4responseFingerprint .= $secret;
				$secretUsed = 1;
			}
			else {
				$str4responseFingerprint .= KRequest::getVar($order[$i]);
			}
		}

		// recalc the fingerprint
		$responseFingerprintCalc = md5($str4responseFingerprint);

		if ((strcmp($responseFingerprintCalc,$responseFingerprint) == 0) && ($mandatoryFingerPrintFields == 3) && ($secretUsed == 1)) {
			return true;
		}
		else {
			return false;
		}

	}

	function getTransactionId() {

		$tix = KRequest::getInt('orderNumber',0,'POST');;

		return $tix;
	}

	function getOrderId() {

		$orderId = KRequest::getInt('customField1',0,'POST');

		return $orderId;
	}

	function getCurrencyId() {

		$isoCode = KRequest::getKeyword('currency','','POST');

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

		$statId = KRequest::getInt('customField2',0,'POST');

		return $statId;
	}

	function getPaidAmount() {
		$paidAmount = (float) KRequest::getString('amount',NULL,'POST');

		return $paidAmount;
	}

	function getPaymentSuccess() {

		$paymentStatus = KRequest::getString('paymentState');
		KLog::log('Payment status is "'.$paymentStatus.'"','payment');
		if ($paymentStatus == "SUCCESS") return true;
		else return false;

	}

	function getErrorMessage() {

		$errorMessage = KRequest::getInt('message',0,'POST');

		return $errorMessage;


	}


}