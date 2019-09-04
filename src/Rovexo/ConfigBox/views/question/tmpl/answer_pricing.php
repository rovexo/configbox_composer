<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewQuestion */
?>
<span class="answer-price-display">

	<span class="answer-price-wrapper" <?php echo ($answer->price == 0) ? 'style="display:none"':'';?>>
		<?php if ($answer->was_price != 0) { ?>
			<span class="answer-was-price answer-was-price-<?php echo intval($answer->id);?>"> <?php echo cbprice($answer->was_price);?></span>
		<?php } ?>
		<span class="answer-price answer-price-<?php echo intval($answer->id);?>"> <?php echo cbprice($answer->price);?></span>
		<span class="answer-price-label answer-price-label-<?php echo intval($answer->id);?>"><?php echo hsc($this->priceLabel);?></span>
	</span>

	<span class="answer-price-recurring-wrapper" <?php echo ($answer->price_recurring == 0) ? 'style="display:none"':'';?>>
		<?php if ($answer->was_price_recurring != 0) { ?>
			<span class="answer-was-price-recurring answer-was-price-recurring-<?php echo intval($answer->id);?>"> <?php echo cbprice($answer->was_price_recurring);?></span>
		<?php } ?>
		<span class="answer-price-recurring answer-price-recurring-<?php echo intval($answer->id);?>"><?php echo cbprice($answer->price_recurring);?></span>
		<span class="answer-price-recurring-label answer-price-recurring-label-<?php echo intval($answer->id);?>"><?php echo hsc($this->priceLabelRecurring);?></span>
	</span>

</span>
