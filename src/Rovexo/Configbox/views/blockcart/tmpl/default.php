<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewBlockCart */
?>

<div class="<?php echo hsc($this->wrapperClasses);?>">

	<?php if ($this->showBlockTitle) { ?>
		<h2 class="block-title"><?php echo hsc($this->blockTitle);?></h2>
	<?php } ?>
	<?php if (!$this->cartDetails || empty($this->cartDetails->positions) || count($this->cartDetails->positions) == 0) { ?>
		<p><?php echo KText::_('There are no products in your cart.');?></p>
	<?php } else { ?>
		<ul class="cart-order-listing">
			<?php foreach ($this->cartDetails->positions as $positionid=>$position) { ?>
				<li>
					<span class="cart-product-title"><?php echo intval($position->quantity);?> x <?php echo hsc($position->productTitle);?></span>
					
					<?php if ($position->{$this->totalKey}) { ?>
						<span class="cart-product-price">
							<span class="cart-product-price-amount"><?php echo cbprice($position->{$this->totalKey});?></span>
						</span>
					<?php } ?>
					
					<?php if ($position->{$this->totalKeyRecurring}) { ?>
						<span class="cart-product-price-recurring">
							<span class="cart-product-price-recurring-amount"><?php echo cbprice($position->{$this->totalKeyRecurring});?></span>
							<span class="cart-product-price-recurring-label"><?php echo $position->productData->priceLabelRecurring;?></span>
						</span>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
		<div class="cart-totals">
			<span class="cart-total-key"><?php echo KText::_('Total');?></span>
			<span class="cart-total-price"><?php echo cbprice($this->cartDetails->{$this->totalKey});?></span>
		</div>
		<?php if ($this->cartDetails->{$this->totalKeyRecurring}) { ?>
		<div class="cart-totals-recurring">
			<span class="cart-total-key"><?php echo KText::_('Total Recurring Charges');?></span>
			<span class="cart-total-price"><?php echo cbprice($this->cartDetails->{$this->totalKeyRecurring});?></span>
		</div>
		<?php } ?>
		<div class="cart-controls">
			<a class="add-to-cart" href="<?php echo KLink::getRoute('index.php?option=com_configbox&view=cart');?>"><?php echo KText::_('To cart')?></a>
		</div>
	<?php } ?>
</div>