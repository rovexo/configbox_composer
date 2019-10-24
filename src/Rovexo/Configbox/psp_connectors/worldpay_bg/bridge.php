<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutpspbridge */

if ($this->orderRecord->payment->params->get('testmode',0) == 1) {
	$formUrl = 'https://secure-test.worldpay.com/wcc/purchase';
}
else {
	$formUrl = "https://secure.worldpay.com/wcc/purchase";
}
?>

<form id="checkoutform" action="<?php echo $formUrl;?>" method="post">
<div>

<?php if ($this->orderRecord->payment->params->get('testmode',0) == 1) { ?>
<input type="hidden" name="testMode" value="100">
<?php } ?>

<input type="hidden" name="instId" value="<?php echo hsc($this->orderRecord->payment->params->get('installation_id',0));?>">
<input type="hidden" name="cartId" value="<?php echo intval($this->orderRecord->id).'-'.rand(0,1000);?>">
<input type="hidden" name="amount" value="<?php echo round($this->orderRecord->payableAmount,2); ?>">
<input type="hidden" name="currency" value="<?php echo $this->orderRecord->currency->code; ?>">
<input type="hidden" name="desc" value="<?php echo KText::sprintf('Order ID %s from %s',$this->orderRecord->id,$this->shopData->shopname);?>">
<input type="hidden" name="name" value="<?php echo hsc($this->orderRecord->orderAddress->billingfirstname);?> <?php echo hsc($this->orderRecord->orderAddress->billinglastname);?>">
<input type="hidden" name="address1" value="<?php echo hsc($this->orderRecord->orderAddress->billingaddress1);?>">
<input type="hidden" name="address2" value="<?php echo hsc($this->orderRecord->orderAddress->billingaddress2);?>">
<input type="hidden" name="address3" value="">
<input type="hidden" name="town" value="<?php echo hsc($this->orderRecord->orderAddress->billingcity);?>">
<input type="hidden" name="region" value="">
<input type="hidden" name="postcode" value="<?php echo hsc($this->orderRecord->orderAddress->billingzipcode);?>">
<input type="hidden" name="country" value="<?php echo hsc($this->orderRecord->orderAddress->billingcountry_2_code);?>">
<input type="hidden" name="tel" value="<?php echo hsc($this->orderRecord->orderAddress->billingphone);?>">
<input type="hidden" name="email" value="<?php echo hsc($this->orderRecord->orderAddress->billingemail);?>">

<input type="hidden" name="authMode" value="A">

<input type=submit value="Buy This" class="trigger-redirect-to-psp">

</div>
</form> 
