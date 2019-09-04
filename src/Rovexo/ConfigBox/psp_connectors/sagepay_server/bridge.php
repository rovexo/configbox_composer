<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if ($this->orderRecord->payment->params->get('testmode',0) == 1) {
	$username = $this->orderRecord->payment->params->get('vendor_name_test','');
	$url = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';
}
else {
	$username = $this->orderRecord->payment->params->get('vendor_name_production','');
	$url = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
}

$params = array();

$params['VPSProtocol']		= '3.00';
$params['TxType']			= 'PAYMENT';
$params['Vendor']			= $username;
$params['VendorTxCode']		= $this->orderRecord->id.'-'.rand(0,10000);
$params['Amount']			= number_format($this->orderRecord->payableAmount,2);
$params['Currency']			= $this->orderRecord->currency->code;
$params['Description']		= KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);

$params['NotificationURL'] 	= $this->notificationUrl;
$params['RedirectionURL'] 	= $this->cancelUrl;

$params['BillingFirstnames']	= hsc($this->orderRecord->orderAddress->billingfirstname);
$params['BillingSurname']		= hsc($this->orderRecord->orderAddress->billinglastname);
$params['BillingAddress1']		= hsc($this->orderRecord->orderAddress->billingaddress1);
$params['BillingAddress2']		= hsc($this->orderRecord->orderAddress->billingaddress2);
$params['BillingCity']			= hsc($this->orderRecord->orderAddress->billingcity);
$params['BillingPostCode']		= hsc($this->orderRecord->orderAddress->billingzipcode);
$params['BillingCountry']		= hsc($this->orderRecord->orderAddress->billingcountry_2_code);
$params['BillingPhone']			= hsc($this->orderRecord->orderAddress->billingphone);

$params['DeliveryFirstnames']	= hsc($this->orderRecord->orderAddress->firstname);
$params['DeliverySurname']		= hsc($this->orderRecord->orderAddress->lastname);
$params['DeliveryAddress1']		= hsc($this->orderRecord->orderAddress->address1);
$params['DeliveryAddress2']		= hsc($this->orderRecord->orderAddress->address2);
$params['DeliveryCity']			= hsc($this->orderRecord->orderAddress->city);
$params['DeliveryPostCode']		= hsc($this->orderRecord->orderAddress->zipcode);
$params['DeliveryCountry']		= hsc($this->orderRecord->orderAddress->country_2_code);
$params['DeliveryPhone']		= hsc($this->orderRecord->orderAddress->phone);

$params['CustomerEMail']		= hsc($this->orderRecord->orderAddress->billingemail);
$params['StoreToken']			= '0';

ob_start();
$paymentTypes = array('VISA', 'AMEX', 'DELTA', 'JCB', 'DC', 'MC', 'UKE');
?>
<!--suppress HtmlUnknownTag -->
<surcharges>
	<?php foreach ($paymentTypes as $paymentType) { ?>
		<surcharge><paymentType><?php echo $paymentType;?></paymentType><percentage>0.00</percentage></surcharge>
	<?php } ?>
</surcharges>
<?php

$params['SurchargeXML']			= ob_get_clean();

$postData = http_build_query($params,'','&');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, '3' );

$responseString = curl_exec($ch);
curl_close($ch);

$keyValuePairs = explode("\r\n",$responseString);
$response = array();
foreach ($keyValuePairs as $pair) {
	$ex = explode('=',$pair,2);
	$response[$ex[0]] = $ex[1];
}

if (!isset($response['Status'])) {
	KLog::log('The PSP sagepay_server did not return a valid response. Response was "'.var_export($responseString,true).'".','error');
}

if ($response['Status'] != 'OK') {
	KLog::log('The PSP sagepay_server returned an error on the transaction registration request. Message is "'.$response['StatusDetail'].'".','error');
	?>
	<div>
		<div><?php echo KText::_('The payment service provider returned an error message. Please check the error log file configbox_errors for details.');?></div>
		<div><?php echo KText::_('Please check your payment service provider settings for SagePay Server.');?></div>
	</div>
	<?php
}
else {
	?>
	<div>
		<a class="trigger-redirect-to-psp" href="<?php echo $response['NextURL'];?>">
			<span class="nav-center"><?php echo KText::_('TEXT_ORDER_BUTTON');?></span>
		</a>
	</div>
	<?php
}
