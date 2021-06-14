<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

// Add test mode var on test mode
if ($this->orderRecord->payment->params->get('testmode',0) == 1) {
	$formUrl = 'https://e-payment.postfinance.ch/ncol/test/orderstandard_utf8.asp';
	$pspId = $this->orderRecord->payment->params->get('psp_id_test','');
	if (!$pspId) {
		echo 'Setup error - no PSPID entered for test system.';
	}
}
else {
	$formUrl = 'https://e-payment.postfinance.ch/ncol/prod/orderstandard_utf8.asp';
	$pspId = $this->orderRecord->payment->params->get('psp_id_production','');
	if (!$pspId) {
		echo 'Setup error - no PSPID entered for test system.';
	}
}

$formParams = new stdClass();

$formParams->PSPID 			= $pspId;
$formParams->ORDERID 		= $this->orderRecord->id;
$formParams->AMOUNT 		= $this->orderRecord->payableAmount * 100;
$formParams->CURRENCY 		= strtoupper($this->orderRecord->currency->code);
$formParams->LANGUAGE  		= KText::getLanguageCode().'_'.strtoupper(KText::getCountryCode());
$formParams->CN 			= $this->orderRecord->orderAddress->billingfirstname . '_'. $this->orderRecord->orderAddress->billinglastname;
$formParams->EMAIL 			= $this->orderRecord->orderAddress->billingemail;
$formParams->OWNERZIP 		= $this->orderRecord->orderAddress->billingzipcode;

if( $this->orderRecord->orderAddress->billingaddress2) {
	$address = $this->orderRecord->orderAddress->billingaddress1 . ', '. $this->orderRecord->orderAddress->billingaddress2;
}
else {
	$address = $this->orderRecord->orderAddress->billingaddress1;
}

$formParams->OWNERADDRESS 	= $address;
$formParams->OWNERCTY 		= $this->orderRecord->orderAddress->billingcity;
$formParams->OWNERTELNO 	= $this->orderRecord->orderAddress->billingphone;

$formParams->ACCEPTURL 		= $this->successUrl;
$formParams->DECLINEURL 	= $this->failureUrl;
$formParams->EXCEPTIONURL 	= $this->failureUrl;
$formParams->CANCELURL	 	= $this->cancelUrl;
$formParams->BACKURL	 	= $this->cancelUrl;
$formParams->CATALOGURL	 	= KenedoPlatform::p()->getUrlBase();
$formParams->HOMEURL	 	= KenedoPlatform::p()->getUrlBase();

$hashData = (array)$formParams;
	
if (isset($hashData['ORDERSHIPMETH'])) {
	unset($hashData['ORDERSHIPMETH']);
}

// Sort the data alphabetically
ksort($hashData);

// Store the SHA IN passphrase
$shaInPassPhrase = $this->orderRecord->payment->params->get('sha_in_passphrase',0);

// Prepare the hash input string
$hashString = '';
foreach ($hashData as $key=>$value) {
	if (trim($value) == '') continue;
	$hashString .= $key.'='.$value. $shaInPassPhrase;
}

// Add it to the form data
$formParams->SHASIGN = strtoupper(hash('sha256',$hashString));
?>

<form id="checkoutform" action="<?php echo $formUrl;?>" method="post">
<div>
<?php foreach ($formParams as $key=>$value) { ?>
	<input type="hidden" name="<?php echo $key;?>" value="<?php echo hsc($value); ?>" />
<?php } ?>
	
</div>

<div>
	<a class="trigger-redirect-to-psp">
		<span class="nav-center"><?php echo KText::_('TEXT_ORDER_BUTTON');?></span>
	</a>
</div>

</form> 
