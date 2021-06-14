<?php
defined('CB_VALID_ENTRY') or die();

class IpnBillsafe_invoice {
	
	private $settings;
	protected $sdk;
	protected $response;
	protected $orderId;
	
	function __construct() {
		
		KLog::log(__CLASS__ .' gateway selected.','payment');

		ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . __DIR__.'/Billsafe');
		
		require (dirname(__FILE__).'/Billsafe/Sdk.php');
		require (dirname(__FILE__).'/Billsafe/LoggerFile.php');
		
		$this->sdk = new Billsafe_Sdk();
		
	}

	/**
	 * @param KStorage $settings
	 * @throws Billsafe_Exception on problems
	 */
	function setPaymentOptionSettings($settings) {
		
		$this->settings = $settings;
		
		$credentials = array(
				'merchantId'             => $this->settings->get('merchant_id',''),
				'merchantLicenseSandbox' => $this->settings->get('merchant_license_test',''),
				'merchantLicenseLive'    => $this->settings->get('merchant_license_live',''),
				'applicationSignature'   => $this->settings->get('application_signature',''),
				'applicationVersion'     => KenedoPlatform::p()->getApplicationVersion(),
		);
		
		$this->sdk->setCredentials($credentials);
		
		$logPath = KenedoPlatform::p()->getLogPath().'/configbox/billsafe.log';
		
		$this->sdk->setLogger(new Billsafe_LoggerFile($logPath));

		$params = array('token' => KRequest::getString('token',''));
		
		$this->response = $this->sdk->callMethod('getTransactionResult', $params);
		
		if ($this->response->status == 'ACCEPTED') {
			$this->getPaymentInstructions();
		}
		
		KLog::log('Response from BillSAFE is: '.var_export($this->response,true),'payment');
		
	}
	
	function getPaymentInstructions() {
		
		$params = array(
			'transactionId' => $this->getTransactionId(),
			'orderNumber' => $this->getOrderId(),
			'outputType' => 'STRUCTURED',
		);
		
		$response = $this->sdk->callMethod('getPaymentInstruction', $params);
		
		KLog::log('Payment instruction response is: '."\n".var_export($response,true),'payment');
		
		if ($response->ack == 'OK') {
			$instructions = (array)$response->instruction;
			$data = json_encode($instructions);
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__cbcheckout_order_records` SET `transaction_data` = '".$db->getEscaped($data)."' WHERE `id` = ".intval($this->getOrderId());
			$db->setQuery($query);
			$db->query();
			$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
			$model->unsetOrderRecord($this->getOrderId());
		}
		
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
		
		$tix = $this->response->transactionId;
		
		return $tix;
	}
	
	function getOrderId() {
		
		if (!$this->orderId) {
			$token = KRequest::getString('token','');
			
			$db = KenedoPlatform::getDb();
			$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `transaction_id` = '".$db->getEscaped($token)."' LIMIT 1";
			$db->setQuery($query);
			$this->orderId = $db->loadResult();
			
		}
		return $this->orderId;
	}
	
	function getCurrencyId() {
				
		$currency = ConfigboxCurrencyHelper::getCurrencyByIsoCode('EUR');
		
		if (!$currency) {
			KLog::log('Currency EUR not found or unpublished.','error');
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
		
		return ($this->response->status == 'ACCEPTED') ? true : false;
		
	}
	
	function getErrorMessage() {
		
		if (!empty($this->response->declineReason->message)) {
			$message = $this->response->declineReason->message . ' - '.$this->response->declineReason->buyerMessage;
		}
		else {
			$message = '';
		}
		
		return $message;
		
	}
	
	function requiresAmountCheck() {
		return false;
	}
	
	function getResponseString() {
		
		if ($this->getPaymentSuccess()) {
			$url = KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.$this->getOrderId(), false, CbSettings::getInstance()->get('securecheckout'));
		}
		else {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Ihr Kauf wurde zurückgewiesen.'));
			$url = KLink::getRoute('index.php?option=com_configbox&view=checkout', false, CbSettings::getInstance()->get('securecheckout'));
			if (!empty($this->response->declineReason->buyerMessage)) {
				KenedoPlatform::p()->sendSystemMessage($this->response->declineReason->buyerMessage);
			}
		}
		
		KenedoPlatform::p()->redirect($url);
		
		return '';
	}
	
}