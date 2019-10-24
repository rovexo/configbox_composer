<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewPosition */
?>

<?php if ($this->position->productDescription || count($this->position->configuration)) { ?>
	<div <?php echo $this->getViewAttributes();?>>
		<div class="position-details position-product-id-<?php echo (int)$this->position->id;?>">

			<div class="position-record" style="width:100%">

				<div class="position-product-title"><?php echo hsc($this->position->productTitle);?></div>

				<?php if ($this->showSkus && $this->position->product_sku) { ?>
					<div class="position-product-sku"><?php echo KText::_('Product SKU');?>: <?php echo hsc($this->position->product_sku);?></div>
				<?php } ?>

				<?php if ($this->inAdmin && !empty($this->position->taxCode)) { ?>
					<div class="position-tax-code-regular"><?php echo KText::_('Tax Code Regular Price');?>: <?php echo hsc($this->position->taxCode);?></div>
				<?php } ?>

				<?php if ($this->inAdmin && !empty($this->position->taxCodeRecurring)) { ?>
					<div class="position-tax-code-recurring"><?php echo KText::_('Tax Code Recurring Price');?>: <?php echo hsc($this->position->taxCodeRecurring);?></div>
				<?php } ?>

				<?php if ($this->position->product_image) { ?>
					<div class="position-image">
						<img src="<?php echo $this->positionImageSrc;?>" width="<?php echo hsc($this->positionImageWidth);?>" height="<?php echo hsc($this->positionImageHeight);?>" alt="<?php echo hsc($this->position->productTitle);?>" />
					</div>
				<?php } ?>

				<?php if ($this->position->configuration) { ?>
					<table class="position-selection-table table">
						<thead>
							<tr>
								<th class="heading-selections" colspan="2"><?php echo KText::_('Your Selections');?></th>
								<?php if ($this->showSkus) { ?>
								<th class="heading-sku"><?php echo KText::_('SKU');?></th>
								<?php } ?>
								<?php if ($this->position->usesRecurring) { ?>
									<th class="heading-price-recurring price-field"><?php echo hsc($this->position->priceLabelRecurring);?></th>
								<?php } ?>
								<th class="heading-price price-field"><?php echo hsc($this->position->priceLabel);?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->position->configuration as $selection) { ?>
								<?php if ($selection->show_in_overviews) { ?>
									<tr class="position-selection position-selection-element-id-<?php echo (int)$selection->element_id;?>">
										<td class="position-element-title"><?php echo hsc($selection->elementTitle);?></td>
										<td class="position-element-output-value"><?php echo ($selection->xref_id) ? hsc($selection->optionTitle) : $selection->output_value;?></td>

										<?php if ($this->showSkus) { ?>
										<td class="position-element-sku"><?php echo ($selection->option_sku) ? hsc($selection->option_sku) : '';?></td>
										<?php } ?>
										<?php if ($this->position->usesRecurring) { ?>
											<td class="position-element-price-recurring price-field"><?php echo cbprice( $this->record->groupData->b2b_mode ? $selection->priceRecurringNet : $selection->priceRecurringGross , true, true);?></td>
										<?php } ?>
										<td class="position-element-price price-field"><?php echo cbprice( $this->record->groupData->b2b_mode ? $selection->priceNet : $selection->priceGross , true, true);?></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
						<tfoot>
							<?php if ($this->position->productBasePriceNet != 0 || $this->position->productBasePriceRecurringNet != 0) { ?>
								<tr class="position-base-price">
									<td colspan="<?php echo ($this->showSkus) ? '3':'2';?>"><?php echo KText::_('Base Price');?></td>
									<?php if ($this->position->usesRecurring) { ?>
										<td class="position-base-price-recurring price-field"><?php echo cbprice( $this->record->groupData->b2b_mode ? $this->position->productBasePriceRecurringNet : $this->position->productBasePriceRecurringGross )?></td>
									<?php } ?>
									<td class="position-base-price price-field"><?php echo cbprice( $this->record->groupData->b2b_mode ? $this->position->productBasePriceNet : $this->position->productBasePriceGross, true, true )?></td>
								</tr>
							<?php } ?>
							<tr class="position-total">
								<td colspan="<?php echo ($this->showSkus) ? '3':'2';?>"><?php echo KText::_('Total');?></td>
								<?php if ($this->position->usesRecurring) { ?>
									<td class="position-total-recurring price-field"><?php echo cbprice( $this->record->groupData->b2b_mode ? $this->position->totalUnreducedRecurringNet : $this->position->totalUnreducedRecurringGross )?></td>
								<?php } ?>
								<td class="position-total price-field"><?php echo cbprice( $this->record->groupData->b2b_mode ? $this->position->totalUnreducedNet : $this->position->totalUnreducedGross )?></td>
							</tr>
						</tfoot>
					</table>

					<?php } elseif($this->position->productDescription) { ?>
						<div class="position-description">
							<?php echo $this->position->productDescription;?>
						</div>
					<?php } ?>

					<?php
					if (!empty($this->contentBeneathPositionRecord)) {
						echo $this->contentBeneathPositionRecord;
					}
					?>

				</div>

		</div>
	</div>
<?php } ?>