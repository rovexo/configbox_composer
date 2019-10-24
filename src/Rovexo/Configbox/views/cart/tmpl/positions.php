<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCart */
?>

<div class="cart-positions">

	<?php foreach ($this->cart->positions as $positionId=>$position) { ?>

		<div class="cart-position-item" id="cart-position-<?php echo intval($position->id);?>">

			<div class="modal" tabindex="-1" role="dialog">
				<div class="modal-dialog" role="document">
					<div class="modal-content">

						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

						<div class="modal-body">

							<div class="row">
									<div class="col-sm-6">

										<h3 class="cart-position-product-title">
											<?php echo intval($position->quantity);?> x <?php echo hsc($position->productTitle);?>
										</h3>

										<?php if (count($position->selections)) { ?>

										<table class="table cart-position-configuration cart-position-configuration-product-<?php echo intval($position->productData->id);?>">

											<?php if ($position->usesRecurring) { ?>
												<tr>
													<th class="selection-list-item"><?php echo KText::_('Your Selections');?></th>
													<?php if ($this->displayPricing) { ?>

														<?php if ($position->usesRecurring) { ?>
															<th class="selection-list-price-recurring"><?php echo hsc($position->productData->priceLabelRecurring);?></th>
														<?php } ?>

														<th class="selection-list-price"><?php echo hsc($position->productData->priceLabel);?></th>

													<?php } ?>
												</tr>
											<?php } ?>

											<?php if ($this->displayPricing) { ?>
												<?php if ($position->productBasePriceNet or $position->productBasePriceRecurringNet) { ?>
													<tr>
														<td class="selection-list-item"><?php echo (count($position->selections)) ? KText::_('Base Price') : KText::_('Price');?></td>
														<?php if ($position->totalUnreducedRecurringNet) { ?>
															<td class="selection-list-price-recurring">
																<?php echo cbprice( ($this->isB2b) ? $position->productBasePriceRecurringNet : $position->productBasePriceRecurringGross, true, true); ?>
															</td>
														<?php } ?>
														<td class="selection-list-price">
															<?php echo cbprice( ($this->isB2b) ? $position->productBasePriceNet : $position->productBasePriceGross, true, true); ?>
														</td>

													</tr>
												<?php } ?>
											<?php } ?>

											<?php foreach ($position->selections as $selection) { ?>

												<?php if ( $selection->showInOverviews == false ) continue; ?>

												<tr>
													<td class="selections type-<?php echo hsc($selection->type);?>">
														<span class="selection-question-title"><?php echo hsc($selection->questionTitle);?><span class="question-answer-seperator">:</span></span>
														<span class="selection-output-value"><?php echo $selection->outputValue;?></span>
													</td>

													<?php if ($this->displayPricing) { ?>

														<?php if ($position->usesRecurring) { ?>
															<td class="selection-price-recurring">
																<?php echo cbprice( ($this->isB2b) ? $selection->priceRecurringNet : $selection->priceRecurringGross, true, true); ?>
															</td>
														<?php } ?>

														<td class="selection-price">
															<?php echo cbprice( ($this->isB2b) ? $selection->priceNet : $selection->priceGross, true, true); ?>
														</td>

													<?php } ?>
												</tr>

											<?php } ?>


											<?php if ($this->displayPricing && $position->quantity > 1) { ?>

												<tr class="selection-list-total-unit-price">
													<td class="selection-list-item"><?php echo KText::_('Price per item');?></td>

													<?php if ($position->usesRecurring) { ?>
														<td class="selection-list-price-recurring">
															<?php echo cbprice(  ( ($this->isB2b) ? $position->totalUnreducedRecurringNet : $position->totalUnreducedRecurringGross ) / $position->quantity, true, true);?>
														</td>
													<?php } ?>

													<td class="selection-list-price"><?php echo cbprice( ( ($this->isB2b) ? $position->totalUnreducedNet : $position->totalUnreducedGross ) / $position->quantity);?></td>
												</tr>

											<?php } ?>


											<?php if ($this->displayPricing && count($position->selections) != 0) { ?>

												<tr class="selection-list-total">
													<td class="selection-list-item"><?php echo KText::_('Total');?></td>

													<?php if ($position->usesRecurring) { ?>
														<td class="selection-list-price-recurring">
															<?php echo cbprice( ($this->isB2b) ? $position->totalUnreducedRecurringNet : $position->totalUnreducedRecurringGross, true, true );?>
														</td>
													<?php } ?>

													<td class="selection-list-price"><?php echo cbprice( ($this->isB2b) ? $position->totalUnreducedNet : $position->totalUnreducedGross );?></td>
												</tr>

											<?php } ?>

										</table>
									<?php } ?>
								</div>

								<div class="col-sm-6">

									<div class="cart-position-product-image">
										<?php echo $this->positionImages[$positionId]; ?>
									</div>

								</div>

								<div class="col-xs-12">
									<?php
									// These are the buttons and drop-downs for editing, removing etc.
									$this->position = $position;
									$this->renderView('positioncontrols');
									?>
								</div>

							</div>


						</div>
					</div>
				</div>
			</div>

		</div>

	<?php } ?>

</div>
