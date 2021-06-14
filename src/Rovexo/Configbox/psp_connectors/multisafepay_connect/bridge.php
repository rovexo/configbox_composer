<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if ($this->orderRecord->payment->params->get('testmode',0) == 1) {
	$test = true;
	$accountId = $this->orderRecord->payment->params->get('account_id_test','');
	$siteId = $this->orderRecord->payment->params->get('site_id_test','');
	$siteCode = $this->orderRecord->payment->params->get('site_code_test','');
}
else {
	$test = false;
	$accountId = $this->orderRecord->payment->params->get('account_id_production','');
	$siteId = $this->orderRecord->payment->params->get('site_id_production','');
	$siteCode = $this->orderRecord->payment->params->get('site_code_production','');
}

include(__DIR__.'/api/MultiSafepay.combined.php');

$msp = new MultiSafepay();

// Merchant Settings
$msp->test                         = $test;
$msp->merchant['account_id']       = $accountId;
$msp->merchant['site_id']          = $siteId;
$msp->merchant['site_code']        = $siteCode;
$msp->merchant['notification_url'] = $this->notificationUrl;
$msp->merchant['cancel_url']       = $this->cancelUrl;
$msp->merchant['redirect_url']     = $this->successUrl;

// Customer Details
$msp->customer['locale']           = hsc($this->orderRecord->orderAddress->language_tag);
$msp->customer['firstname']        = hsc($this->orderRecord->orderAddress->billingfirstname);
$msp->customer['lastname']         = hsc($this->orderRecord->orderAddress->billinglastname);
$msp->customer['zipcode']          = hsc($this->orderRecord->orderAddress->billingzipcode);
$msp->customer['city']             = hsc($this->orderRecord->orderAddress->billingcity);
$msp->customer['country']          = hsc($this->orderRecord->orderAddress->billingcountry_2_code);
$msp->customer['phone']            = hsc($this->orderRecord->orderAddress->billingphone);
$msp->customer['email']            = hsc($this->orderRecord->orderAddress->billingemail);

$msp->parseCustomerAddress($this->orderRecord->orderAddress->billingaddress1);

// Transaction Details
$msp->transaction['id']            = $this->orderRecord->id.'-'.rand(0,10000);
$msp->transaction['currency']      = $this->orderRecord->currency->code;
$msp->transaction['amount']        = number_format($this->orderRecord->payableAmount, 2, '.', '') * 100;
$msp->transaction['description']   = KText::sprintf('Order ID %s from %s', $this->orderRecord->id, $this->shopData->shopname);
$msp->transaction['items']         = '';

// returns a payment url
$url = $msp->startTransaction();

// Get and store the transaction ID (gonna need it to connect the status update request with an order record)
$transactionId = $msp->getTransactionId();

$db = KenedoPlatform::getDb();
$query = "UPDATE #__cbcheckout_order_records SET `transaction_id` = '".$db->getEscaped($transactionId)."' WHERE `id` = ".intval($this->orderRecord->id);
$db->setQuery($query);
$db->query();

if (!$url) {
	KLog::log('The PSP multisafepay_connect did not send a redirection URL back. The PSP sends no error messages, check your settings and ask MultisafePay support for an error message.','error');
	?>
	<div>
		<div><?php echo KText::_('The payment service provider did not send a redirection URL back. Please check the error log file configbox_errors for details.');?></div>
		<div><?php echo KText::_('Please check your payment service provider settings.');?></div>
	</div>
	<?php
}
else {
	?>
	<div>
		<a class="trigger-redirect-to-psp" href="<?php echo $url;?>"></a>
	</div>
	<?php
}
