<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewRecord */

$b2b = $this->orderRecord->groupData->b2b_mode;
$taxIncEx = ($b2b) ? 'excl.':'incl.';
$taxPlusInc = ($b2b) ? 'Plus':'Incl.';
?>
<div <?php echo $this->getViewAttributes();?>>
	<div class="order-overview order-overview-<?php echo ($b2b ? 'b2b':'b2c');?>">
		<table class="order-overview-table table">
			<thead>
				<tr>
					<th class="item-name"><?php echo KText::_('Position');?></th>
					<?php if ($this->orderRecord->usesRecurring) { ?>
						<th class="item-price item-price-recurring"><?php echo hsc($this->orderRecord->labelRecurring);?></th>
					<?php } ?>
					<th class="item-price"><?php echo hsc($this->orderRecord->labelRegular);?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($this->orderRecord->positions as $position) { ?>
					<tr class="position-total position-product-id-<?php echo (int)$position->product_id;?>">
						<td class="item-name">
							<span class="position-title"><?php echo (int) $position->quantity;?> x <?php echo hsc( $position->productTitle );?></span>
							<?php if ($position->quantity > 1) { ?>
							<span class="position-unit-price"><?php echo KText::sprintf('(Price per item %s)', cbprice( ( ($b2b) ? $position->totalUnreducedNet : $position->totalUnreducedGross) / $position->quantity ) );?></span>
							<?php } ?>
							<span class="position-tax-rate"><?php echo KText::sprintf( ($position->totalUnreducedRecurringNet && $position->taxRateRecurring != $position->taxRate) ? '('.$taxIncEx.' %s regular, %s tax recurring)' : '('.$taxIncEx.' %s tax)' , cbtaxrate($position->taxRate), cbtaxrate($position->taxRateRecurring));?></span>

							<?php if (!empty($this->showProductDetails) && ($position->productDescription || $position->configuration)) { ?>
								<a class="details-button trigger-show-position-modal" data-position-id="<?php echo intval($position->id);?>"><?php echo KText::_('Details');?></a>
							<?php } ?>
						</td>

						<?php if ($this->orderRecord->usesRecurring) { ?>
						<td class="item-price item-price-recurring">
							<span class="recurring-interval"><?php echo hsc($position->interval);?></span>
							<span class="position-recurring-price"><?php echo cbprice( ($b2b) ? $position->totalUnreducedRecurringNet : $position->totalUnreducedRecurringGross );?></span>
						</td>
						<?php } ?>

						<td class="item-price item-price-payable"><?php echo cbprice( ($b2b) ? $position->totalUnreducedNet : $position->totalUnreducedGross );?></td>

					</tr>
				<?php } ?>

				<?php if ($this->orderRecord->usesDiscount) { ?>
					<tr class="total-discount">
						<td class="item-name">

							<?php if (!empty($this->orderRecord->discountRecurring->title)) { ?>
								<span class="discount-title discount-title-recurring"><?php echo hsc($this->orderRecord->discountRecurring->title);?></span>
							<?php } ?>
							<?php if (!empty($this->orderRecord->discount->title) && !empty($this->orderRecord->discountRecurring->title)) { ?>
								<span class="discount-title-separator">,</span>
							<?php } ?>
							<?php if (!empty($this->orderRecord->discount->title)) { ?>
								<span class="discount-title discount-title-regular"><?php echo hsc($this->orderRecord->discount->title);?></span>
							<?php } ?>

						</td>
						<?php if ($this->orderRecord->usesRecurring) { ?>
							<td class="item-price item-price-recurring"><?php echo cbprice( ($b2b) ? $this->orderRecord->totalDiscountRecurringNet : $this->orderRecord->totalDiscountRecurringGross );?></td>
						<?php } ?>
						<td class="item-price item-price-payable"><?php echo cbprice( ($b2b) ? $this->orderRecord->totalDiscountNet : $this->orderRecord->totalDiscountGross );?></td>
					</tr>
				<?php } ?>

				<?php
				/*
				 * Please do not use the member $this->orderRecord->couponDiscountNet. This is part of preliminary feature that is not completed.
				 */
				?>

				<?php if ($this->orderRecord->couponDiscountNet != 0) { ?>
					<tr class="total-coupon-discount">
						<td class="item-name">
							<span class="coupon-discount-title"><?php echo KText::_('Coupon Discount');?></span>
						</td>
						<?php if ($this->orderRecord->usesRecurring) { ?>
							<td class="item-price item-price-recurring"></td>
						<?php } ?>
						<td class="item-price item-price-payable"><?php echo cbprice($this->orderRecord->couponDiscountNet);?></td>
					</tr>
				<?php } ?>



				<?php if ($this->orderRecord->usesDiscount or count($this->orderRecord->positions) > 1) { ?>
					<tr class="sub-total sub-total-merchandise">
						<td class="item-name"><?php echo KText::_('Subtotal Products');?></td>
						<?php if ($this->orderRecord->usesRecurring) { ?>
							<td class="item-price item-price-recurring"><?php echo cbprice( ($b2b) ? $this->orderRecord->totalRecurringNet : $this->orderRecord->totalRecurringGross );?></td>
						<?php } ?>
						<td class="item-price item-price-payable"><?php echo cbprice( ($b2b) ? $this->orderRecord->totalNet : $this->orderRecord->totalGross );?></td>
					</tr>
				<?php } ?>

				<?php if (CbSettings::getInstance()->get('disable_delivery') == 0) { ?>
					<tr class="total-delivery">

						<?php if (!empty($this->deliveryLineReplacement)) {
							echo $this->deliveryLineReplacement;
						} else { ?>

							<td class="item-name" colspan="<?php echo (1 + intval($this->orderRecord->usesRecurring));?>">
								<?php if ($this->orderRecord->delivery) { ?>
									<?php if ($this->orderRecord->delivery->priceNet != 0) { ?>
										<span class="delivery-text"><?php echo KText::_('Plus delivery');?></span>
									<?php } else { ?>
										<span class="delivery-text"><?php echo KText::_('Delivery:');?></span>
									<?php } ?>

									<span class="delivery-title"><?php echo hsc($this->orderRecord->delivery->title);?></span>

									<?php if ($this->orderRecord->delivery->priceNet != 0 && $this->orderRecord->isVatFree == false) { ?>
										<span class="delivery-tax"><?php echo KText::sprintf('('.$taxIncEx.' %s tax)', cbtaxrate($this->orderRecord->delivery->taxRate));?></span>
									<?php } ?>

									<?php if ($this->orderRecord->delivery->deliverytime != 0) { ?>
										<span class="delivery-time"><?php echo KText::sprintf('Delivery time %s days',$this->orderRecord->delivery->deliverytime);?></span>
									<?php } ?>
								<?php } else { ?>
									<span class="delivery-text"><?php echo KText::_('No delivery chosen');?></span>
								<?php } ?>

								<?php if (!empty($this->showChangeLinks)) { ?>
									<a class="trigger-change-delivery" href="<?php echo KLink::getRoute('index.php?option=com_configbox&view=delivery');?>"><?php echo KText::_('change')?></a>
								<?php } ?>
							</td>

							<td class="item-price item-price-delivery"><?php if ($this->orderRecord->delivery) { ?><?php echo cbprice( ($b2b) ? $this->orderRecord->delivery->priceNet : $this->orderRecord->delivery->priceGross );?><?php } ?> </td>
						<?php } ?>
					</tr>
				<?php } ?>

				<tr class="total-payment">

					<?php if (!empty($this->paymentLineReplacement)) {
						echo $this->paymentLineReplacement;
					} else { ?>

						<td class="item-name" colspan="<?php echo (1 + intval($this->orderRecord->usesRecurring));?>">

							<?php if ($this->orderRecord->payment) { ?>
								<?php if ($this->orderRecord->payment->priceNet != 0) { ?>
									<span class="payment-text"><?php echo KText::_('Plus payment fee');?></span>
								<?php } else { ?>
									<span class="payment-text"><?php echo KText::_('Payment method');?>:</span>
								<?php } ?>

								<span class="payment-title"><?php echo hsc($this->orderRecord->payment->title);?></span>

								<?php if ($this->orderRecord->payment->priceNet != 0 && $this->orderRecord->isVatFree == false) { ?>
									<span class="payment-tax"><?php echo KText::sprintf('('.$taxIncEx.' %s tax)', cbtaxrate($this->orderRecord->payment->taxRate));?></span>
								<?php } ?>
							<?php } else { ?>
								<span class="payment-text"><?php echo KText::_('No payment method chosen');?></span>
							<?php } ?>

							<?php if ($this->showChangeLinks) { ?>
								<a class="trigger-change-payment" href="<?php echo KLink::getRoute('index.php?option=com_configbox&view=payment');?>"><?php echo KText::_('change')?></a>
							<?php } ?>
						</td>

						<td class="item-price item-price-payment"><?php if ($this->orderRecord->payment) { ?><?php echo cbprice( ($b2b) ? $this->orderRecord->payment->priceNet : $this->orderRecord->payment->priceGross );?><?php } ?></td>
					<?php } ?>

				</tr>

				<?php if ($this->orderRecord->isVatFree == false) { ?>
					<?php foreach ($this->orderRecord->taxSummary as $taxRate=>$tax) { ?>
						<tr class="total-tax">
							<td class="item-name"><?php echo KText::sprintf($taxPlusInc.' %s tax',cbtaxrate($taxRate));?></td>
							<?php if ($this->orderRecord->usesRecurring) { ?>
								<td class="item-price item-price-recurring"><?php echo cbprice( $tax['recurring'] );?></td>
							<?php } ?>
							<td class="item-price item-price-payable"><?php echo cbprice( $tax['regular'] );?></td>
						</tr>
					<?php } ?>
				<?php } ?>

				<tr class="grand-total">
					<td class="item-name"><?php echo KText::_('Total including tax');?></td>

					<?php if ($this->orderRecord->usesRecurring) { ?>
					<td class="item-price item-price-recurring"><?php echo cbprice( $this->orderRecord->totalRecurringGross);?></td>
					<?php } ?>

					<td class="item-price item-price-payable"><?php echo cbprice( $this->orderRecord->payableAmount );?></td>

				</tr>
				<?php if ($this->orderRecord->isVatFree) { ?>
					<tr class="total-no-tax-notice">
						<td class="item-name" colspan="<?php echo (2 + intval($this->orderRecord->usesRecurring));?>">
							<span class="no-tax-notice"><?php echo KText::_('No tax is charged. The tax liability is shifted to the recipient of the supply.');?></span>
							<?php if ($this->orderRecord->orderAddress->vatin) { ?><span class="vat-in"><?php echo KText::sprintf('Customer VAT IN: %s', hsc($this->orderRecord->orderAddress->vatin));?></span><?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php if ($this->orderRecord->dispatchTime != 0) { ?>
			<div class="dispatch-time-notice">
				<?php echo KText::sprintf('Ready for dispatch in %s working days.',intval($this->orderRecord->dispatchTime));?>
			</div>
		<?php } ?>
	</div>

	<?php if (!empty($this->showProductDetails) && ($position->productDescription || $position->configuration)) { ?>

		<div class="position-modals">
			<?php foreach ($this->positionHtml as $positionId=>$positionHtml) { ?>
				<div class="modal position-id-<?php echo intval($positionId);?>" role="document">
					<div class="modal-dialog" role="document">
						<div class="modal-content">

							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

							<div class="modal-body">
								<?php echo $positionHtml;?>
							</div>

						</div>
					</div>
				</div>
			<?php } ?>
		</div>

	<?php } ?>

	<?php $this->renderView('metadata');?>

</div>