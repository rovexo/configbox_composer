<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutdelivery */
?>
<?php if (count($this->options) > 1) { ?>
	<div id="subview-delivery">
		<h2 class="step-title"><?php echo KText::_('Delivery Methods');?></h2>
		<ul class="list-options">
			<?php foreach ($this->options as $option) { ?>
				<li>
					<label class="radio option-label" for="delivery-option-<?php echo $option->id;?>">
						<input class="option-control" id="delivery-option-<?php echo (int)$option->id;?>" type="radio" name="delivery_id" value="<?php echo (int)$option->id;?>" <?php echo ($option->id == $this->selected) ? 'checked="checked"':'';?> />
						<span class="option-title"><?php echo hsc($option->title);?></span>
						<?php if ($option->priceNet != 0) { ?>
							<span class="option-price"><?php echo cbprice( ($this->mode == 'b2b') ? $option->priceNet : $option->priceGross, true, true); ?></span>
						<?php } ?>
					</label>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php } ?>
