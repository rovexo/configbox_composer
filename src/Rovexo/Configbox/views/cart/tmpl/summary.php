<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCart */
?>

<table class="cart-summary cart-summary-<?php echo ($this->cart->groupData->b2b_mode == 1) ? 'b2b':'b2c';?> table">
	<thead>

		<tr>
			<th class="item-quantity"><?php echo KText::_('Quantity');?></th>
			<th class="item-name"><?php echo KText::_('Position');?></th>

			<?php if ($this->displayPricing) { ?>

				<?php if ($this->cart->usesRecurring) { ?>
					<th class="item-price item-price-recurring"><?php echo hsc($this->cart->labelRecurring);?></th>
				<?php } ?>

				<th class="item-price"><?php echo hsc($this->cart->labelRegular);?></th>

			<?php } ?>
		</tr>

	</thead>

	<tbody>

		<?php // Each position ?>

		<?php foreach ($this->cart->positions as $positionId => $position) { ?>
			<tr class="position-row" data-position-id="<?php echo intval($position->id);?>">
				<td class="item-quantity">
					<span class="position-quantity"><?php echo intval($position->quantity);?></span>
					<?php if ($this->canEditOrder) { ?>
						<a class="trigger-edit-quantity fa fa-edit" title="<?php echo KText::_('Change Quantity');?>"></a>
						<a class="trigger-remove-position fa fa-times trigger-ga-track-remove-position"  data-position-id="<?php echo intval($position->id);?>" title="<?php echo KText::_('Remove');?>" href="<?php echo $this->positionUrls[$positionId]['urlRemove'];?>"></a>
						<span class="quantity-edit-wrapper">
							<input class="quantity-edit-box" type="text" value="<?php echo intval($position->quantity);?>" />
							<a class="trigger-store-quantity fa fa-check"></a>
							<a class="trigger-cancel-quantity-edit fa fa-times"></a>
						</span>
					<?php } ?>
				</td>
				<td class="item-name">
					<span class="product-title"><?php echo hsc($position->productTitle);?></span>
					<a class="trigger-show-position-details fa fa-info-circle"></a>
				</td>

				<?php if ($this->displayPricing) { ?>

					<?php if ($this->cart->usesRecurring) { ?>
						<td class="item-price item-price-recurring">
							<span class="recurring-interval"><?php echo hsc($position->productData->recurring_interval);?></span>
							<span class="order-recurring-price"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $position->totalUnreducedRecurringNet : $position->totalUnreducedRecurringGross );?></span>
						</td>
					<?php } ?>
					<td class="item-price"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $position->totalUnreducedNet : $position->totalUnreducedGross );?></td>

				<?php } ?>
			</tr>
		<?php } ?>

		<?php // The discount ?>

		<?php if ($this->cart->totalDiscountNet != 0 or $this->cart->totalDiscountRecurringNet != 0) { ?>
			<tr class="sub-total sub-total-discount">
				<td class="item-quantity"></td>
				<td class="item-name">
					<span class="discount-title"><?php echo hsc($this->cart->discount->title);?> <?php echo hsc($this->cart->discountRecurring->title);?></span>
					<?php if ($this->cart->discount->percentage) { ?>
						<span class="discount-percentage"><?php echo KText::sprintf( ($this->cart->usesRecurring) ? '(%s regular, %s recurring)' : '(%s)' , cbtaxrate($this->cart->discount->percentage), cbtaxrate($this->cart->discountRecurring->percentage));?></span>
					<?php } ?>
				</td>

				<?php if ($this->displayPricing) { ?>

					<?php if ($this->cart->usesRecurring) { ?>
						<td class="item-price item-price-recurring"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $this->cart->totalDiscountRecurringNet : $this->cart->totalDiscountRecurringGross );?></td>
					<?php } ?>
					<td class="item-price"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $this->cart->totalDiscountNet : $this->cart->totalDiscountGross );?></td>

				<?php } ?>

			</tr>
		<?php } ?>

		<?php // The product subtotal (only when there's more than one product or a discount applies ?>

		<?php if ($this->displayPricing) { ?>

			<?php if (count($this->cart->positions) > 1 || ($this->cart->totalDiscountNet != 0 or $this->cart->totalDiscountRecurringNet != 0)) { ?>
				<tr class="sub-total sub-total-merchandise">
					<td class="item-quantity"></td>
					<td class="item-name"><?php echo KText::_('Subtotal Products');?></td>
					<?php if ($this->cart->usesRecurring) { ?>
						<td class="item-price item-price-recurring"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $this->cart->totalRecurringNet : $this->cart->totalRecurringGross );?></td>
					<?php } ?>
					<td class="item-price"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $this->cart->totalNet : $this->cart->totalGross );?></td>
				</tr>
			<?php } ?>

		<?php } ?>

		<?php // Delivery details ?>

		<?php if ($this->cart->delivery && $this->deliveryIsDisabled == 0) { ?>
			<tr class="sub-total sub-total-delivery">
				<td class="item-quantity"></td>
				<td class="item-name" colspan="<?php echo (1 + intval($this->cart->usesRecurring));?>">

					<?php if ($this->cart->delivery->priceNet != 0) { ?>
						<span class="delivery-text"><?php echo KText::_('Plus delivery');?></span>
					<?php } else { ?>
						<span class="delivery-text"><?php echo KText::_('Delivery:');?></span>
					<?php } ?>

					<span class="delivery-title"><?php echo hsc($this->cart->delivery->title);?></span>

					<span class="delivery-time"><?php echo ($this->cart->delivery->deliverytime) ? KText::sprintf('Delivery time %s days',$this->cart->delivery->deliverytime) : '';?></span>

					<span class="delivery-tooltip">
						<a class="fa fa-info-circle cb-popover"
						   aria-label="<?php echo KText::_('Details');?>"
						   role="button"
						   tabindex="0"
						   data-toggle="popover"
						   data-trigger="hover"
						   data-placement="top"
						   data-html="true"
						   data-content="<?php echo hsc( KText::_('This is method is automatically chosen. At checkout you can choose alternatives.'));?>">
						</a>
					</span>

				</td>

				<?php if ($this->displayPricing) { ?>
					<td class="item-price item-price-payable"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $this->cart->delivery->priceNet : $this->cart->delivery->priceGross, true );?></td>
				<?php } ?>

			</tr>
		<?php } ?>

		<?php // Payment details ?>

		<?php if ($this->cart->payment) { ?>
			<tr class="sub-total sub-total-payment">
				<td class="item-quantity"></td>
				<td class="item-name" colspan="<?php echo (1 + intval($this->cart->usesRecurring));?>">

					<?php if ($this->cart->payment->priceNet != 0) { ?>
						<span class="payment-text"><?php echo KText::_('Plus payment fee');?></span>
					<?php } else { ?>
						<span class="payment-text"><?php echo KText::_('Payment method');?>:</span>
					<?php } ?>

					<span class="payment-title"><?php echo hsc($this->cart->payment->title);?></span>

					<span class="payment-tooltip">
						<a class="fa fa-info-circle cb-popover"
						   aria-label="<?php echo KText::_('Details');?>"
						   role="button"
						   tabindex="0"
						   data-toggle="popover"
						   data-trigger="hover"
						   data-placement="top"
						   data-html="true"
						   data-content="<?php echo hsc( KText::_('This is method is automatically chosen. At checkout you can choose alternatives.'));?>">
						</a>
					</span>

				</td>

				<?php if ($this->displayPricing) { ?>
					<td class="item-price item-price-payable"><?php echo cbprice( ($this->cart->groupData->b2b_mode == 1) ? $this->cart->payment->priceNet : $this->cart->payment->priceGross );?></td>
				<?php } ?>

			</tr>
		<?php } ?>

		<?php // Tax summary ?>

		<?php if ($this->displayPricing && $this->cart->isVatFree == false) { ?>
			<?php foreach ($this->cart->taxes as $taxRate=>$tax) { ?>
				<tr class="sub-total sub-total-tax">
					<td class="item-quantity"></td>
					<td class="item-name">
						<span class="tax-rate-name"><?php echo KText::sprintf((($this->cart->groupData->b2b_mode) ? 'Plus':'Incl.').' %s tax', cbtaxrate($taxRate));?></span>
						<span class="tax-rate-tooltip"><a class="fa fa-info-circle" data-toggle="popover" data-placement="top" data-content="<?php echo hsc( KText::_('The tax rate may change when you enter your billing and delivery address.'));?>"></a></span>
					</td>

					<?php if ($this->displayPricing) { ?>
						<?php if ($this->cart->usesRecurring) { ?>
							<td class="item-price item-price-recurring"><?php echo cbprice( $tax['recurring'] );?></td>
						<?php } ?>
						<td class="item-price"><?php echo cbprice( $tax['regular'] );?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		<?php } ?>


		<?php // Total ?>

		<?php if ($this->displayPricing) { ?>

			<tr class="grand-total">
				<td class="item-quantity"></td>
				<td class="item-name"><?php echo KText::_('Payable amount');?></td>

				<?php if ($this->cart->usesRecurring) { ?>
					<td class="item-price item-price-recurring"><?php echo cbprice( $this->cart->totalRecurringGross);?></td>
				<?php } ?>

				<td class="item-price"><?php echo cbprice( $this->totalPayable );?></td>
			</tr>

			<?php // VAT free notice ?>

			<?php if ($this->cart->isVatFree) { ?>
				<tr class="total-no-tax-notice">
					<td class="item-name" colspan="<?php echo (3 + intval($this->cart->usesRecurring));?>">
						<span class="no-tax-notice"><?php echo KText::_('No tax is charged. The tax liability is shifted to the recipient of the supply.');?></span>
						<?php if ($this->cart->userInfo->vatin) { ?><span class="vat-in"><?php echo KText::sprintf('Customer VAT IN: %s', hsc($this->cart->userInfo->vatin));?></span><?php } ?>
					</td>
				</tr>
			<?php } ?>

		<?php } ?>

	</tbody>
</table>

<div class="wrapper-position-modals">
	<?php $this->renderView('positions'); ?>
</div>

<?php
if ($this->useGaEnhancedTracking) {
	$this->renderView('metadata');
}
