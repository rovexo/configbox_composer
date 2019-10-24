<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

$formUrl = 'https://secure.quickpay.dk/form/';

$formParams = new stdClass();

// Order of object vars matter!
$formParams->protocol 		= '7';
$formParams->msgtype 		= 'authorize';
$formParams->merchant 		= $this->orderRecord->payment->params->get('merchant',0);
$formParams->language 		= strtolower(KText::getLanguageCode());
$formParams->ordernumber  	= str_pad($this->orderRecord->id,4,'0',STR_PAD_LEFT);
$formParams->amount 		= round($this->orderRecord->payableAmount * 100, 0);
$formParams->currency 		= $this->orderRecord->currency->code;
$formParams->continueurl 	= $this->successUrl;
$formParams->cancelurl 		= $this->cancelUrl;
$formParams->callbackurl 	= $this->notificationUrl;

// Add test mode var on test mode
if ($this->orderRecord->payment->params->get('testmode',0)) {
	$formParams->testmode 		= 1;
}

// Concatenate and hash the object values
$formParams->md5check		= md5(implode('', (array) $formParams ) . $this->orderRecord->payment->params->get('md5secret','') );

?>

<form id="checkoutform" action="<?php echo $formUrl;?>" method="post">
<div>
<?php foreach ($formParams as $key=>$value) { ?>
	<input type="hidden" name="<?php echo $key;?>" value="<?php echo hsc($value) ?>" />
<?php } ?>
	
</div>

<div>
	<a class="trigger-redirect-to-psp"></a>
</div>

</form> 
