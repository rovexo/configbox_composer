<?php
defined('CB_VALID_ENTRY') or die('Restricted access');
/** @var $this ConfigboxViewBlockCurrencies */
?>

<div class="<?php echo hsc($this->wrapperClasses);?>">

	<?php if ($this->showBlockTitle) { ?>
		<h2 class="block-title"><?php echo hsc($this->blockTitle);?></h2>
	<?php } ?>
	
	<form method="post" action="">
		<div>
			<label class="hidden-label" for="currency_id"><?php echo KText::_('Currency');?></label>
			<input class="hidden-button" type="submit" name="submits" value="<?php echo KText::_('Change');?>" />
			<?php echo $this->dropdown;?>
			<noscript>
				<div>
					<input type="submit" name="submits" value="<?php echo KText::_('Change');?>" />
				</div>
			</noscript>
			
		</div>
	</form>
	
	<?php if ($this->showConversionTable == 1) { ?>

		<div class="conversion-table">
			<?php foreach ($this->exchangeRates as $exchangeRate) { ?>
				<div>1 <?php echo hsc($exchangeRate['baseTitle']);?> = <?php echo cbprice($exchangeRate['exchangeRate'], false); ?> <?php echo hsc($exchangeRate['currTitle']);?></div>
			<?php } ?>
		</div>
	
	<?php } ?>

</div>