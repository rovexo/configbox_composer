<?php
defined('CB_VALID_ENTRY') or die();

class IpnSofort_ueberweisung_classic {

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
	
	function check_hash() {

		$settings = $this->getPaymentOptionSettings();
		
		$user_id = $settings->get('sofortueberweisung_user_id');
		$project_id = $settings->get('sofortueberweisung_project_id');
		$notification_password = $settings->get('sofortueberweisung_notification_password');
		
		$data_hash = array(
				'transaction' => KRequest::getString('transaction','','POST'),
				'user_id' => $user_id,
				'project_id' => $project_id,
				'sender_holder' => KRequest::getString('sender_holder','','POST'),
				'sender_account_number' => KRequest::getString('sender_account_number','','POST'),
				'sender_bank_code' => KRequest::getString('sender_bank_code','','POST'),
				'sender_bank_name' => KRequest::getString('sender_bank_name','','POST'),
				'sender_bank_bic' => KRequest::getString('sender_bank_bic','','POST'),
				'sender_iban' => KRequest::getString('sender_iban','','POST'),
				'sender_country_id' => KRequest::getString('sender_country_id','','POST'),
				'recipient_holder' => KRequest::getString('recipient_holder','','POST'),
				'recipient_account_number' => KRequest::getString('recipient_account_number','','POST'),
				'recipient_bank_code' => KRequest::getString('recipient_bank_code','','POST'),
				'recipient_bank_name' => KRequest::getString('recipient_bank_name','','POST'),
				'recipient_bank_bic' => KRequest::getString('recipient_bank_bic','','POST'),
				'recipient_iban' => KRequest::getString('recipient_iban','','POST'),
				'recipient_country_id' => KRequest::getString('recipient_country_id','','POST'),
				'international_transaction' => KRequest::getString('international_transaction','','POST'),
				'amount' => KRequest::getString('amount','','POST'),
				'currency_id' => KRequest::getString('currency_id','','POST'),
				'reason_1' => KRequest::getString('reason_1','','POST'),
				'reason_2' => KRequest::getString('reason_2','','POST'),
				'security_criteria' => KRequest::getString('security_criteria','','POST'),
				'user_variable_0' => KRequest::getString('user_variable_0','','POST'),
				'user_variable_1' => KRequest::getString('user_variable_1','','POST'),
				'user_variable_2' => KRequest::getString('user_variable_2','','POST'),
				'user_variable_3' => KRequest::getString('user_variable_3','','POST'),
				'user_variable_4' => KRequest::getString('user_variable_4','','POST'),
				'user_variable_5' => KRequest::getString('user_variable_5','','POST'),
				'created' => KRequest::getString('created','','POST'),
				'notification_password' => $notification_password
		);
		
	//	KLog::log('Hash data: '. var_export($data_hash,true),'debug');
		
		$data_hash = hash('sha256', implode('|', $data_hash));
		
		if ($data_hash != KRequest::getString('hash','','POST')) {
			return false;
		}
		else {
			return true;
		}
	}
	
	function isValidRequest() {
		
		if (!$this->check_hash()) {
			return 'hash invalid';
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
	
	function getTransactionId() {
		
		$tix = KRequest::getString('transaction','','POST');
		
		return $tix;
	}
	
	function getOrderId() {
		
		$orderId = KRequest::getInt('user_variable_0',NULL,'POST');
		
		return $orderId;
	}
	
	function getCurrencyId() {
		
		$isoCode = KRequest::getString('currency_id','','POST');
		
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
		$statId = KRequest::getInt('user_variable_1',NULL,'POST');
		return $statId;
	}
	
	function getPaidAmount() {
		$paidAmount = KRequest::getFloat('amount',NULL,'POST');
		
		return $paidAmount;
	}
	
	function getPaymentSuccess() {
		
		return true;
		/*
		$paymentStatus = KRequest::getString('payment_status',NULL,'POST');
		
		if ($paymentStatus == 'Completed') {
			return true;
		}
		else {
			return false;
		}
		*/
	}
	
	function getErrorMessage() {
		/*
		$paymentStatus = KRequest::getString('payment_status',NULL,'POST');
		
		if ($paymentStatus != 'Completed') {
			return $paymentStatus;
		}
		*/
	}
	
	
}