<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if ($this->orderRecord->payment->params->get('testmode',0) == 1) {
	$paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
}
else {
	$paypalURL = "https://www.paypal.com/cgi-bin/webscr";
}
?>

<form id="checkoutform" action="<?php echo $paypalURL;?>" method="post">
<div>
<input type="hidden" name="charset" value="utf-8" />
<input type="hidden" name="cmd" value="_cart" />
<input type="hidden" name="upload" value="1" />
<input type="hidden" name="business" value="<?php echo $this->orderRecord->payment->params->get('paypalid','');?>" />
<input type="hidden" name="item_name_1" value="<?php echo KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);?>" />
<input type="hidden" name="amount_1" value="<?php echo round($this->orderRecord->payableAmount,2); ?>" />
<input type="hidden" name="currency_code" value="<?php echo hsc($this->orderRecord->currency->code); ?>" />
<input type="hidden" name="no_shipping" value="1" />
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="lc" value="<?php echo strtoupper(KText::getCountryCode());?>" />
<input type="hidden" name="notify_url" value="<?php echo hsc($this->notificationUrl);?>" />
<input type="hidden" name="return" value="<?php echo hsc($this->successUrl.((strpos($this->successUrl, '?') !== false) ? '&':'?').'utm_nooverride=1');?>" />
<input type="hidden" name="charset" value="utf-8" />
<input type="hidden" name="address_override" value="0" />
<input type="hidden" name="invoice" value="<?php echo 'CB-'.intval($this->orderRecord->id);?>" />
<input type="hidden" name="no_note" value="1" />

<input type="hidden" name="first_name" value="<?php echo hsc($this->orderRecord->orderAddress->billingfirstname);?>" />
<input type="hidden" name="last_name" value="<?php echo hsc($this->orderRecord->orderAddress->billinglastname);?>" />
<input type="hidden" name="address1" value="<?php echo hsc($this->orderRecord->orderAddress->billingaddress1);?>" />
<input type="hidden" name="address2" value="<?php echo hsc($this->orderRecord->orderAddress->billingaddress2);?>" />
<input type="hidden" name="city" value="<?php echo hsc($this->orderRecord->orderAddress->billingcity);?>" />
<input type="hidden" name="zip" value="<?php echo hsc($this->orderRecord->orderAddress->billingzipcode);?>" />
<input type="hidden" name="country" value="<?php echo hsc($this->orderRecord->orderAddress->billingcountry_2_code);?>" />
<input type="hidden" name="night_phone_a" value="<?php echo hsc($this->orderRecord->orderAddress->billingphone);?>" />
<input type="hidden" name="state" value="<?php echo hsc($this->orderRecord->orderAddress->billingstatecode);?>" />
<input type="hidden" name="email" value="<?php echo hsc($this->orderRecord->orderAddress->billingemail);?>" />
</div>

<div>
	<a class="trigger-redirect-to-psp"></a>
</div>

</form> 
