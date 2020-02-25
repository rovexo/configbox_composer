<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerPayments extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	function capturePayment() {
		
		$connectorName = KRequest::getString('connector_name','');
		
		switch ($connectorName) {
			case 'paymill_api':
				$this->capturePaymentPaymill();
				break;
				
			case 'stripe':
				$this->capturePaymentStripe();
				break;
		}
		
	}
	
	function capturePaymentStripe() {
	
		// Get order
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		$orderRecord = $orderModel->getOrderRecord( $orderId );
	
		$shopdata = ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);
	
		$testMode = $orderRecord->payment->params->get('testmode',0);
	
		if ($testMode) {
			$privateKey = $orderRecord->payment->params->get('private_api_key_test','');
		}
		else {
			$privateKey = $orderRecord->payment->params->get('private_api_key_production','');
		}

		$pspFolder = ConfigboxPspHelper::getPspConnectorFolder('stripe');
		include_once($pspFolder.DS.'lib'.DS.'Stripe.php');
		
		Stripe::setApiKey($privateKey);
		
		$token = KRequest::getString('token');
		
		try {
			$charge = Stripe_Charge::create(array(
					"amount" => number_format($orderRecord->payableAmount,2) * 100, // amount in cents, again
					"currency" => strtolower($orderRecord->currency->code),
					"card" => $token,
					"description" => KText::sprintf('Order ID %s from %s', $orderRecord->id, $shopdata->shopname))
			);

			/** @noinspection PhpUndefinedFieldInspection */
			if ($charge->captured == true) {
				
				$db = KenedoPlatform::getDb();
				/** @noinspection PhpUndefinedFieldInspection */
				$query = "UPDATE `#__cbcheckout_order_records` SET `transaction_id` = '".$db->getEscaped($charge->balance_transaction)."' WHERE `id` = ".intval($orderRecord->id);
				$db->setQuery($query);
				$db->query();
				
				// Set Status
				$status = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('paid'), true);
				$orderModel->setStatus($status, $orderRecord->id);
				
				$response = new stdClass();
				$response->success = true;
				$response->errors = array();
					
				echo json_encode($response);
				return;
			}
			else {
				/** @noinspection PhpUndefinedFieldInspection */
				$message = 'The charge could not be captured. Error code/message as following: ' .$charge->failure_code. ' - '.$charge->failure_message;
				KLog::log($message,'payment');
				
				$response = new stdClass();
				$response->success = false;
				$response->errors = array( $message );
				
				echo json_encode($response);
				return;
			}
			
			
		} catch(Exception $e) {
			
			KLog::log("Error in transaction response:\n"."Message:".$e->getMessage()."\n",'payment');
			$response = new stdClass();
			$response->success = false;
			$response->errors = array($e->getMessage());
			
			echo json_encode($response);
			
		}
		
		
	}
	
	function capturePaymentPaymill() {
		
		// Get order
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		$orderRecord = $orderModel->getOrderRecord( $orderId );
		
		$shopdata = ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);
		
		$testMode = $orderRecord->payment->params->get('testmode',0);
		
		if ($testMode) {
			$privateKey = $orderRecord->payment->params->get('private_api_key_test','');
		}
		else {
			$privateKey = $orderRecord->payment->params->get('private_api_key_production','');
		}
		
		$token = KRequest::getString('token');

		$pspFolder = ConfigboxPspHelper::getPspConnectorFolder('paymill_api');
		require_once($pspFolder.DS.'api'.DS.'Request.php');
		require_once($pspFolder.DS.'api'.DS.'Models'.DS.'Internal'.DS.'AbstractAddress.php');
		require_once($pspFolder.DS.'api'.DS.'Models'.DS.'Internal'.DS.'BillingAddress.php');
		require_once($pspFolder.DS.'api'.DS.'Models'.DS.'Internal'.DS.'ShippingAddress.php');
		require_once($pspFolder.DS.'api'.DS.'Models'.DS.'Internal'.DS.'Item.php');
		require_once($pspFolder.DS.'api'.DS.'Models'.DS.'Request'.DS.'Base.php');
		require_once($pspFolder.DS.'api'.DS.'Models'.DS.'Response'.DS.'Base.php');
		require_once($pspFolder.DS.'api'.DS.'API'.DS.'CommunicationAbstract.php');
		require_once($pspFolder.DS.'api'.DS.'API'.DS.'Curl.php');
		$files = KenedoFileHelper::getFiles($pspFolder.DS.'api','.php',true,true);
		
		foreach ($files as $file) {
			require_once($file);
		}
		
		
		$service = new Paymill\Request($privateKey);
		$client = new Paymill\Models\Request\Client();
		$payment = new Paymill\Models\Request\Payment();
		$transaction = new \Paymill\Models\Request\Transaction();
		
		try{
			$client->setEmail($orderRecord->orderAddress->billingemail);
			$client->setDescription($orderRecord->orderAddress->billingfirstname.' '.$orderRecord->orderAddress->billinglastname);
			$clientResponse = $service->create($client);
		
			$payment->setToken($token);
			$payment->setClient($clientResponse->getId());
			$paymentResponse = $service->create($payment);
		
			$transaction->setPayment($paymentResponse->getId());
			$transaction->setAmount(round($orderRecord->payableAmount,2) * 100);
			$transaction->setCurrency($orderRecord->currency->code);
			$transaction->setDescription(KText::sprintf('Order ID %s from %s', $orderRecord->id, $shopdata->shopname));
			$transactionResponse = $service->create($transaction);
			
			KLog::log("Transaction response was as following:\n".var_export($transactionResponse,true),'payment');

			/** @noinspection PhpUndefinedMethodInspection */
			if ($transactionResponse->getStatus() == 'closed') {
				
				// Set Status
				$status = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('paid'), true);
				$orderModel->setStatus($status,$orderId);
				
				$response = new stdClass();
				$response->success = true;
				$response->errors = array();
					
				echo json_encode($response);
			}
			else {
				$response = new stdClass();
				$response->success = false;
				$response->errors = array(KText::_('The payment was not successful.'));
					
				echo json_encode($response);
			}
			
		} 
		catch (\Paymill\Services\PaymillException $e){
			KLog::log("Error in transaction response:\n"."Code:".$e->getResponseCode()."\n"."Message:".$e->getErrorMessage()."\n",'payment');
			$response = new stdClass();
			$response->success = false;
			$response->errors = array($e->getErrorMessage());
				
			echo json_encode($response);
		}
		
		
	}
	
}