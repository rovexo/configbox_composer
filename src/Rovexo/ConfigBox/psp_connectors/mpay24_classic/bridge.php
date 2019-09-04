<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if ($this->orderRecord->payment->params->get('testmode',0) == 1) {
	$merchantId = $this->orderRecord->payment->params->get('merchant_id_test','');
	$password = $this->orderRecord->payment->params->get('password_test','');
	$url = 'https://test.mpay24.com/app/bin/etpv5';
	$isTestMode = true;
}
else {
	$merchantId = $this->orderRecord->payment->params->get('merchant_id_production','');
	$password = $this->orderRecord->payment->params->get('password_production','');
	$url = "https://mpay24.com/app/bin/etpv5";
	$isTestMode = false;
}

require_once(dirname(__FILE__).DS.'php_api'.DS.'MPay24Shop.php');

class MyShop extends MPay24Shop {

	var $tid = "";
	var $price = 0;

	/**
	 * Simply a reference to the view so we don't have to load it again in the methods of this class.
	 * @var ConfigboxViewCheckoutpspbridge
	 * @see MyShop::setConfirmationTemplateView
	 */
	var $view;
	
	function updateTransaction($tid, $args, $shippingConfirmed) {
	}
	function getTransaction($tid) {
	}
	function createProfileOrder($tid) {
	}
	function createExpressCheckoutOrder($tid) {
	}
	function createFinishExpressCheckoutOrder($tid, $shippingCosts, $amount, $cancel) {
	}
	function write_log($operation, $info_to_log) {
	}
	function createSecret($tid, $amount, $currency, $timeStamp) {
	}
	function getSecret($tid) {
	}
	function createTransaction() {
		
		$transaction = new Transaction($this->view->orderRecord->id);
		$transaction->PRICE = round($this->view->orderRecord->payableAmount,2);

		return $transaction;
	}
	function createMDXI($transaction) {
		$mdxi = new ORDER();

		$mdxi->Order->Tid   = $transaction->TID;
		$mdxi->Order->Price = $transaction->PRICE;
		
		$mdxi->Order->Currency = strtoupper($this->view->orderRecord->currency->code);

		$mdxi->Order->Customer->setUseProfile("true");
		$mdxi->Order->Customer->setId($this->view->orderRecord->user_id);
		
		$mdxi->Order->BillingAddr->setMode("ReadOnly");
		$mdxi->Order->BillingAddr->Name = $this->view->orderRecord->orderAddress->billingfirstname . ' '. $this->view->orderRecord->orderAddress->billinglastname;
		$mdxi->Order->BillingAddr->Street = $this->view->orderRecord->orderAddress->billingaddress1;
		$mdxi->Order->BillingAddr->Street2 = $this->view->orderRecord->orderAddress->billingaddress2;
		$mdxi->Order->BillingAddr->Zip = $this->view->orderRecord->orderAddress->billingzipcode;
		$mdxi->Order->BillingAddr->City = $this->view->orderRecord->orderAddress->billingcity;
		$mdxi->Order->BillingAddr->Country->setCode($this->view->orderRecord->orderAddress->billingcountry_2_code);
		$mdxi->Order->BillingAddr->Email = $this->view->orderRecord->orderAddress->billingemail;
		
		$mdxi->Order->URL->Success = $this->view->successUrl;
		$mdxi->Order->URL->Error = $this->view->failureUrl;
		$mdxi->Order->URL->Confirmation = $this->view->notificationUrl;
		$mdxi->Order->URL->Cancel = $this->view->cancelUrl;
		
		return $mdxi;
	}

	function setConfirmationTemplateView($view) {
		$this->view = $view;
	}
	
}

$myShop = new MyShop($merchantId, $password, $isTestMode);
$myShop->setConfirmationTemplateView($this);

$result = $myShop->pay();
$status = $result->getGeneralResponse()->getStatus();
$returnCode = $result->getGeneralResponse()->getReturnCode();

if ($status != 'OK') {
	KLog::log('The PSP MPAY24 returned an error message. Message is "'.$returnCode.'".','error');
	?>
	<div>
		<div><?php echo KText::_('The payment service provider returned an error message. Please check the error log file configbox_errors for details.');?></div>
		<div><?php echo KText::_('Please check your payment service provider settings for MPAY24.');?></div>
	</div>
	<?php
}
else {
	$location = $result->getLocation();
	?>
	<div>
		<a class="trigger-redirect-to-psp" href="<?php echo $location;?>"></a>
	</div>
	<?php
}
