<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewEmailcustomerregistration */
?>

<p><?php echo KText::sprintf('EMAIL_CUSTOMER_REGISTRATION_SALUTATION', $this->customer->billingfirstname, $this->customer->billinglastname, $this->customer->salutation);?></p>
<p><?php echo KText::_('EMAIL_CUSTOMER_REGISTRATION_REASON_TEXT');?></p>
<p>
	<b><?php echo KText::_('EMAIL_CUSTOMER_REGISTRATION_USERNAME');?>: </b><span><?php echo hsc($this->customer->billingemail);?></span><br />
	<b><?php echo KText::_('EMAIL_CUSTOMER_REGISTRATION_PASSWORD');?>: </b><span><?php echo hsc($this->passwordClear);?></span>
</p>
<p>
	<?php echo KText::_('EMAIL_CUSTOMER_REGISTRATION_WEBSITE');?>: <?php echo hsc($this->shopData->shopwebsite);?>
</p>
