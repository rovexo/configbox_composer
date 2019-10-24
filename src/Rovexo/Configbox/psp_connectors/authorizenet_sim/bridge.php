<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if ($this->orderRecord->payment->params->get('testmode',1)) {
	$gatewayURL = 'https://test.authorize.net/gateway/transact.dll';
}
else {
	$gatewayURL = "https://secure2.authorize.net/gateway/transact.dll";
}

if ($this->orderRecord->currency->code != 'USD') {

	$multiplicator = NULL;
	$currency = ConfigboxCurrencyHelper::getCurrencyByIsoCode('USD');
	if ($currency) {
		$multiplicator = $currency->multiplicator;
	}

	if (!$multiplicator) {
		echo 'USD is not in the currency list!';
		$multiplicator = 1;
	}

	$payableAmount = round($this->orderRecord->basePayableAmount * $multiplicator,2);
}
else {
	$payableAmount = round($this->orderRecord->payableAmount,2);
}

$fp_sequence = rand(1, 1000);
$fp_timestamp = time();

if (function_exists('hash_hmac')) {
	$fingerprint = hash_hmac("md5", $this->orderRecord->payment->params->get('api_login_id','') . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . round($payableAmount,2) . "^", $this->orderRecord->payment->params->get('transactionkey',''));
}
else {
	$fingerprint = bin2hex(mhash(MHASH_MD5, $this->orderRecord->payment->params->get('api_login_id','') . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . round($payableAmount,2) . "^", $this->orderRecord->payment->params->get('transactionkey','')));
}

$fields['x_login'] = $this->orderRecord->payment->params->get('api_login_id','');
$fields['x_fp_hash'] = $fingerprint;
$fields['x_fp_sequence'] = $fp_sequence;
$fields['x_fp_timestamp'] = $fp_timestamp;
$fields['x_type'] = 'AUTH_CAPTURE';
$fields['x_amount'] = round($payableAmount,2);
$fields['x_show_form'] = 'PAYMENT_FORM';
$fields['x_customer_ip'] = ConfigboxLocationHelper::getClientIpV4Address();
$fields['x_version'] = '3.1';

$fields['x_relay_response'] = 'true';
$fields['x_relay_always'] = 'true';
$fields['x_relay_url'] = $this->notificationUrl;

$fields['x_receipt_link_url'] = $this->successUrl;
$fields['x_receipt_link_method'] = 'GET';
$fields['x_cancel_url'] = $this->cancelUrl;
$fields['x_footer_html_receipt'] = "<a href='".$this->successUrl."'>Return to ".$this->shopData->shopname."</a>";

$fields['x_invoice_num'] = $this->orderRecord->id;
$fields['x_cust_id'] = $this->orderRecord->user_id;
$fields['x_description'] = KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);

if ($this->orderRecord->payment->params->get('testmode',1)) {
	$fields['x_test_request'] = 'TRUE';
}

$fields['x_first_name'] = hsc($this->orderRecord->orderAddress->billingfirstname);
$fields['x_last_name'] = hsc($this->orderRecord->orderAddress->billinglastname);
$fields['x_company'] = hsc($this->orderRecord->orderAddress->billingcompanyname);
$fields['x_address'] = hsc($this->orderRecord->orderAddress->billingaddress1 . ' '.$this->orderRecord->orderAddress->billingaddress2);
$fields['x_city'] = hsc($this->orderRecord->orderAddress->billingcity);
$fields['x_zip'] = hsc($this->orderRecord->orderAddress->billingzipcode);
$fields['x_state'] = hsc($this->orderRecord->orderAddress->billingstatecode);
$fields['x_country'] = hsc($this->orderRecord->orderAddress->billingcountryname);
$fields['x_phone'] = hsc($this->orderRecord->orderAddress->billingphone);
$fields['x_email'] = hsc($this->orderRecord->orderAddress->billingemail);

$fields['x_ship_to_first_name'] = hsc($this->orderRecord->orderAddress->firstname);
$fields['x_ship_to_last_name'] = hsc($this->orderRecord->orderAddress->lastname);
$fields['x_ship_to_company'] = hsc($this->orderRecord->orderAddress->companyname);
$fields['x_ship_to_address'] = hsc($this->orderRecord->orderAddress->address1 . ' '.$this->orderRecord->orderAddress->address2);
$fields['x_ship_to_city'] = hsc($this->orderRecord->orderAddress->city);
$fields['x_ship_to_zip'] = hsc($this->orderRecord->orderAddress->zipcode);
$fields['x_ship_to_state'] = hsc($this->orderRecord->orderAddress->statecode);
$fields['x_ship_to_country'] = hsc($this->orderRecord->orderAddress->countryname);
$fields['x_ship_to_phone'] = hsc($this->orderRecord->orderAddress->phone);
$fields['x_ship_to_email'] = hsc($this->orderRecord->orderAddress->email);

?>
<form id="checkoutform" action="<?php echo $gatewayURL;?>" method="post">
	<div>
		<?php foreach ($fields as $key=>$value) { ?>
			<input type="hidden" name="<?php echo $key;?>" value="<?php echo hsc($value);?>">
		<?php } ?>
		<a class="trigger-redirect-to-psp"></a>
	</div>
</form>
