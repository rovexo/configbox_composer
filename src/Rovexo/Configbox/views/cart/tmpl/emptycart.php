<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCart */
?>
<div <?php echo $this->getViewAttributes();?>>

	<?php if ($this->showPageHeading) { ?>
		<h1 class="page-title"><?php echo hsc($this->pageHeading);?></h1>
	<?php } ?>

	<div class="empty-cart-notice">
		<p><?php echo KText::_('You have no products in your cart.');?></p>
	</div>

	<?php if ($this->showContinueButton) { ?>
		<div class="cart-buttons">
			<a rel="nofollow" class="btn btn-primary continue-shopping" href="<?php echo $this->urlContinueShopping;?>"><?php echo KText::_('Continue Shopping');?></a>
		</div>
	<?php } ?>

</div>