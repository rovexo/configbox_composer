<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewBlockPricing */
?>

<div class="pricing <?php echo hsc($this->cssClass);?> pricing-product-<?php echo (int)$this->productId;?>">
	
	<?php if (!empty($this->pricing[$this->labelKey])) { ?>
		<h3 class="pricing-title"><?php echo hsc($this->pricing[$this->labelKey]);?></h3>
	<?php } ?>
	
	<?php if ($this->showPages) { ?>
		<?php if ($this->pricing['total']['productPrice'] != 0 && $this->canSeePricing && $this->showPrices) { ?>
			<div class="pricing-item pricing-product">
				<span class="item-name"><?php echo hsc(KText::_('Base Price'));?></span>
				<span class="item-price"><?php echo cbprice($this->pricing['total'][$this->productPriceKey]);?></span>
			</div>
		<?php } ?>
	<?php } ?>
	
	<?php if ($this->showPages) { ?>
		<ul class="configurator-page-list">
			<?php foreach($this->pricing['tree']['pages'] as $pageId=>$page) { ?>
				<li class="configurator-page configurator-page-<?php echo intval($pageId);?> <?php echo ($this->showQuestions == 0 || count($page['questions']) == 0) ? ' no-questions':'';?><?php echo ($this->expandPages == 1 || ( $this->expandPages == 2 && intval($this->pageId) == $pageId)) ? ' configurator-page-expanded':'';?>">
					<h4 class="configurator-page-title pricing-item">
						<span class="item-name"><?php echo hsc($page['pageTitle']);?></span>
						<?php if ($this->canSeePricing && $this->showPrices) { ?>
							<span class="item-price pricing-configurator-page pricing-configurator-page-<?php echo intval($pageId);?>"><?php echo cbprice($page[$this->priceKey], true, true);?></span>
						<?php } ?>
					</h4>
					<?php if ($this->showQuestions) { ?>
						<ul class="question-list">
							<?php foreach($page['questions'] as $questionId=> $question) { ?>
								<?php if ($question['showInOverview'] == 0) continue;?>
								<li class="<?php echo hsc($question['cssClassesList']);?>">
									<span class="item-name">
										<span class="question-item-title"><?php echo hsc($question['questionTitle']);?>:</span>
										<span class="<?php echo hsc($question['cssClassesOutputValue']);?>"><?php echo $question['outputValue'];?></span>
									</span>
									<?php if ($this->canSeePricing && $this->showPrices && $this->showQuestionPrices) { ?>
										<span class="<?php echo hsc($question['cssClassesPrice']);?>">
											<?php echo cbprice($question[$this->priceKey], true, true);?>
										</span>
									<?php } ?>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
	
	<?php if ($this->canSeePricing && $this->showPrices) { ?>
		
		<?php if ($this->mode == 'b2c') { ?>
			<div class="totals-b2c">
			
				<div class="total-per-item pricing-item"<?php echo ($this->pricing['quantity'] == 1) ? ' style="display:none" ' : '';?>>
					<span class="item-name"><?php echo KText::_('Gross Price per item');?></span>
					<span class="item-price pricing-per-item-gross"><?php echo cbprice($this->pricing['total'][$this->pricePerItemGrossKey]);?></span>
				</div>
				
				<?php if ($this->isRegular) { ?>
				<div class="total-gross pricing-item">
					<?php if ($this->pricing['quantity'] != 1) { ?>
						<span class="item-name"><?php echo KText::sprintf('Gross Price for %s items', '<span class="item-quantity">'.intval($this->pricing['quantity']).'</span>');?></span>
					<?php } else { ?>
						<span class="item-name"><?php echo hsc(KText::_('Gross Price'));?></span>
					<?php } ?>
					<span class="item-price pricing-total-gross"><?php echo cbprice($this->pricing['total'][$this->totalGrossKey]);?></span>
				</div>
				<?php } ?>

				<?php if ($this->showNetInB2c) { ?>
					<div class="total-net pricing-item">
						<?php if ($this->pricing['quantity'] != 1) { ?>
							<span class="item-name"><?php echo KText::sprintf('Net Price for %s items', '<span class="item-quantity">'.$this->pricing['quantity'].'</span>');?></span>
						<?php } else { ?>
							<span class="item-name"><?php echo hsc(KText::_('Net Price'));?></span>
						<?php } ?>
						<span class="item-price pricing-total-net"><?php echo cbprice($this->pricing['total'][$this->totalNetKey]);?></span>
					</div>
				<?php } ?>
				
				<?php if ($this->isRegular) { ?>

					<?php if ($this->showDelivery) { ?>
						<div class="total pricing-item delivery-cost"<?php echo (!empty($this->pricing['delivery']['title'])) ? '':' style="display:none" '?>>
							<span class="item-name"><?php echo hsc(KText::_('Delivery'));?>: <span class="best-delivery-title"><?php echo (!empty($this->pricing['delivery']['title'])) ? hsc($this->pricing['delivery']['title']) : '';?></span></span>
							<span class="item-price pricing-total-delivery-net"><?php echo (isset($this->pricing['delivery']['priceGross'])) ? cbprice($this->pricing['delivery']['priceGross']) : '';?></span>
						</div>
					<?php } ?>

					<?php if ($this->showPayment) { ?>
						<div class="total pricing-item payment-cost"<?php echo (!empty($this->pricing['payment']['title'])) ? '':' style="display:none" '?>>
							<span class="item-name"><?php echo hsc(KText::_('Payment'));?>: <span class="best-payment-title"><?php echo (!empty($this->pricing['payment']['title'])) ? hsc($this->pricing['payment']['title']) : '';?></span></span>
							<span class="item-price pricing-total-payment-net"><?php echo (isset($this->pricing['payment']['priceGross'])) ? cbprice($this->pricing['payment']['priceGross']) : '';?></span>
						</div>
					<?php } ?>

					<?php if ($this->showPayment || $this->showDelivery) { ?>
						<div class="total-gross pricing-item">
							<?php if ($this->pricing['quantity'] != 1) { ?>
								<span class="item-name"><?php echo KText::sprintf('Gross Total for %s items', '<span class="item-quantity">'.intval($this->pricing['quantity']).'</span>');?></span>
							<?php } else { ?>
								<span class="item-name"><?php echo hsc(KText::_('Gross Total'));?></span>
							<?php } ?>
							<span class="item-price pricing-total-plus-extras-gross"><?php echo cbprice($this->pricing['totalPlusExtras']['priceGross']);?></span>
						</div>
					<?php } ?>
					
				<?php } else { ?>
					
					<div class="total-gross pricing-item">
						
						<?php if ($this->pricing['quantity'] != 1) { ?>
							<span class="item-name"><?php echo KText::sprintf('Gross Total for %s items', '<span class="item-quantity">'.intval($this->pricing['quantity']).'</span>');?></span>
						<?php } else { ?>
							<span class="item-name"><?php echo hsc(KText::_('Gross Total'));?></span>
						<?php } ?>
						
						<span class="item-price pricing-total-gross"><?php echo cbprice($this->pricing['total'][$this->totalGrossKey]);?></span>
					</div>
					
				<?php } ?>
				
				<?php if ($this->showTaxes) { ?>
				
					<?php if ($this->isRegular) { ?>
						<?php foreach ($this->pricing['taxes'] as $taxRate=>$taxAmount) { ?>
							<div class="taxes">
								<span class="item-name"><?php echo KText::sprintf('Including %s tax', cbtaxrate($taxRate));?></span>
								<span class="item-price pricing-taxrate-<?php echo str_replace('.', '-', $taxRate)?>"><?php echo cbprice($taxAmount);?></span>
							</div>
						<?php } ?>
						
					<?php } else { ?>
						<div class="total-tax pricing-item">
							<span class="item-name"><?php echo KText::sprintf('Including %s tax', cbtaxrate($this->pricing['total']['productTaxRateRecurring']));?></span>
							<span class="item-price pricing-total-tax"><?php echo cbprice($this->pricing['total']['priceTax']);?></span>
						</div>
					<?php } ?>
					
				<?php } ?>
			</div>
		<?php } else { ?>
							
			<div class="totals-b2b">
				
				<div class="total-per-item pricing-item"<?php echo ($this->pricing['quantity'] == 1) ? ' style="display:none" ':'';?>>
					<span class="item-name"><?php echo KText::_('Net Price per item');?></span>
					<span class="item-price pricing-per-item-net"><?php echo cbprice($this->pricing['total'][$this->pricePerItemNetKey]);?></span>
				</div>
				
				<div class="total-net pricing-item">
					<?php if ($this->pricing['quantity'] != 1) { ?>
						<span class="item-name"><?php echo KText::sprintf('Net price for %s items', '<span class="item-quantity">'.intval($this->pricing['quantity']).'</span>');?></span>
					<?php } else { ?>
						<span class="item-name"><?php echo hsc(KText::_('Net price'));?></span>
					<?php } ?>
					<span class="item-price pricing-total-net"><?php echo cbprice($this->pricing['total'][$this->totalNetKey]);?></span>
				</div>
				
				<?php if ($this->isRegular) { ?>
					
					<?php if ($this->showDelivery) { ?>
						<div class="total pricing-item delivery-cost"<?php echo (!empty($this->pricing['delivery']['title'])) ? '':' style="display:none" '?>>
							<span class="item-name"><?php echo hsc(KText::_('Delivery'));?>: <span class="best-delivery-title"><?php echo (!empty($this->pricing['delivery']['title'])) ? hsc($this->pricing['delivery']['title']) : '';?></span></span>
							<span class="item-price pricing-total-delivery-net"><?php echo (isset($this->pricing['delivery']['priceNet'])) ? cbprice($this->pricing['delivery']['priceNet']) : '';?></span>
						</div>
					<?php } ?>
					
					<?php if ($this->showPayment) { ?>
						<div class="total pricing-item payment-cost"<?php echo (!empty($this->pricing['payment']['title'])) ? '':' style="display:none" '?>>
							<span class="item-name"><?php echo hsc(KText::_('Payment'));?>: <span class="best-payment-title"><?php echo (!empty($this->pricing['payment']['title'])) ? hsc($this->pricing['payment']['title']) : '';?></span></span>
							<span class="item-price pricing-total-payment-net"><?php echo (isset($this->pricing['payment']['priceNet'])) ? cbprice($this->pricing['payment']['priceNet']) : '';?></span>
						</div>
					<?php } ?>
					
					<?php if ($this->showTaxes) { ?>
						
						<?php foreach ($this->pricing['taxes'] as $taxRate=>$taxAmount) { ?>
							<div class="taxes">
								<span class="item-name"><?php echo KText::sprintf('Plus %s tax', cbtaxrate($taxRate));?></span>
								<span class="item-price pricing-taxrate-<?php echo str_replace('.', '-', $taxRate)?>"><?php echo cbprice($taxAmount);?></span>
							</div>
						<?php } ?>
						
					<?php } ?>
					
				<?php } elseif($this->showTaxes) { ?>
			
					<div class="total-tax pricing-item">
						<span class="item-name"><?php echo KText::sprintf('Plus %s tax', cbtaxrate($this->pricing['total']['productTaxRateRecurring']));?></span>
						<span class="item-price pricing-total-tax"><?php echo cbprice($this->pricing['total']['priceRecurringTax']);?></span>
					</div>
				
				<?php } ?>
				
				<?php if ($this->isRegular && ($this->showDelivery || $this->showPayment)) { ?>				
					<div class="total-gross pricing-item">
						<span class="item-name"><?php echo KText::_('Gross Total');?></span>
						<span class="item-price pricing-total-plus-extras-gross"><?php echo cbprice($this->pricing['totalPlusExtras']['priceGross']);?></span>
					</div>
				<?php } else { ?>
					<div class="total-gross pricing-item">
						<span class="item-name"><?php echo KText::_('Gross Total');?></span>
						<span class="item-price pricing-total-gross"><?php echo cbprice($this->pricing['total'][$this->totalGrossKey]);?></span>
					</div>
				<?php } ?>
				
			</div>
			
			
		<?php } ?>
		
	<?php } ?>
	
	<?php if ($this->showCartButton) { ?>
		<div class="buttons">
				<a class="<?php echo hsc($this->addToCartLinkClasses);?> btn btn-primary" href="<?php echo $this->addToCartLink;?>"><?php echo KText::_('Add to Cart');?></a>
		</div>
	<?php } ?>
	
</div>

