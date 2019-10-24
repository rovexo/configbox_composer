<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCart */
?>
<div class="cart-buttons">

	<?php if ($this->showContinueButton) { ?>
		<a class="btn btn-default button-continue-shopping" href="<?php echo $this->urlContinueShopping;?>"><?php echo KText::_('Continue Shopping')?></a>
	<?php } ?>

	<?php if ($this->canSaveOrder) { ?>
		<a rel="nofollow" class="btn btn-default button-save-order" href="<?php echo $this->urlSaveOrder;?>"><?php echo KText::_('Save Cart')?></a>
	<?php } ?>

	<?php if ($this->canRequestQuote) { ?>
		<a rel="nofollow" class="btn btn-default button-get-quotation" href="<?php echo $this->urlGetQuotation;?>"><?php echo KText::_('Request Quote')?></a>
	<?php } ?>

	<?php if ($this->canCheckout) { ?>
		<a rel="nofollow" class="btn btn-primary button-checkout trigger-checkout-cart" href="<?php echo $this->urlCheckout;?>"><?php echo KText::_('Checkout')?></a>
	<?php } ?>

</div>