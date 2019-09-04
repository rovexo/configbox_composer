<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUserorder */
?>
<div class="buttons">

	<a class="btn btn-default" href="<?php echo $this->urlBackToAccount;?>"><?php echo KText::_('Go to your customer account');?></a>

	<?php if ($this->canCheckout) { ?>
		<a class="btn btn-primary" href="<?php echo $this->urlCheckoutOrder;?>"><?php echo KText::_('Checkout');?></a>
	<?php } ?>

</div>