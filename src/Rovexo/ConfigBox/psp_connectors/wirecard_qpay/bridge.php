<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

$params = array();

if ($this->orderRecord->payment->params->get('testmode','0') == 1) {
	$params['secret'] = $this->orderRecord->payment->params->get('secret_test','');
	$params['customerId'] = $this->orderRecord->payment->params->get('customer_id_test','');
}
else {
	$params['secret'] = $this->orderRecord->payment->params->get('secret_production','');
	$params['customerId'] = $this->orderRecord->payment->params->get('customer_id_production','');
}

$params['amount'] = round($this->orderRecord->payableAmount,2);
$params['currency'] = $this->orderRecord->currency->code;
$params['language'] = KText::getLanguageCode();
$params['orderDescription'] = KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);
$params['displayText'] = '';
$params['successURL'] = $this->successUrl;
$params['cancelURL'] = $this->cancelUrl;
$params['failureURL'] = $this->failureUrl;
$params['serviceURL'] = $this->orderRecord->payment->params->get('service_url');
$params['confirmURL'] = $this->notificationUrl;
$params['customField1'] = $this->orderRecord->id;
$params['paymentType'] = $this->orderRecord->payment->params->get('paymenttype','SELECT');

$params['requestFingerprintOrder'] = implode(',',array_keys($params)) . ',requestFingerprintOrder';
$params['requestFingerprint'] = md5( implode('',$params) );

unset($params['secret']);

$location = 'https://secure.wirecard-cee.com/qpay/init.php';

?>
<form id="checkoutform" action="<?php echo $location;?>" method="post"> 
<div>	
	<?php foreach ($params as $key=>&$param) { ?>
		<input type="hidden" name="<?php echo $key;?>" value="<?php echo hsc($param);?>" />
	<?php } ?>
	
	<div>
		<a class="trigger-redirect-to-psp">
			<span class="nav-center"><?php echo KText::_('TEXT_ORDER_BUTTON');?></span>
		</a>
	</div>
</div>
</form>