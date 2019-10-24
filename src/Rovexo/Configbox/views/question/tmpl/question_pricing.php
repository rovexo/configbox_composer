<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>
<?php if ($this->showPricing) { ?>
	<span class="question-price-display">
		<span class="question-price-wrapper" <?php echo ($this->price == 0) ? 'style="display:none"':'';?>>
			<span class="question-price question-price-<?php echo intval($this->question->id);?>"> <?php echo cbprice($this->price);?></span>
			<span class="question-price-label question-price-label-<?php echo intval($this->question->id);?>"><?php echo hsc($this->priceLabel);?></span>
		</span>
		<span class="question-price-recurring-wrapper" <?php echo ($this->priceRecurring == 0) ? 'style="display:none"':'';?>>
			<span class="question-price-recurring question-price-recurring-<?php echo intval($this->question->id);?>"><?php echo cbprice($this->priceRecurring);?></span>
			<span class="question-price-recurring-label question-price-recurring-label-<?php echo intval($this->question->id);?>"><?php echo hsc($this->priceLabelRecurring);?></span>
		</span>
	</span>
<?php } ?>

