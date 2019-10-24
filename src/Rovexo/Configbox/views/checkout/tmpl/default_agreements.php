<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckout */
?>
<div class="agreements">
	<?php if ($this->confirmTerms) { ?>
		<div class="agreement-terms">
			<input type="checkbox" id="agreement-terms" name="agreement-terms" value="1" />			
			<label for="agreement-terms"><?php echo KText::_('I have read and agree to the terms and conditions.');?></label>
			<a class="trigger-show-terms"><?php echo KText::_('Terms and Conditions');?></a>
		</div>
	<?php } ?>
	
	<?php if ($this->confirmRefundPolicy) { ?>
		<div class="agreement-refund-policy">
			<input type="checkbox" id="agreement-refund-policy" name="agreement-refund-policy" value="1" />			
			<label for="agreement-refund-policy"><?php echo KText::_('I have read and agree to the refund policy.');?></label>
			<a class="trigger-show-refund-policy"><?php echo KText::_('Refund Policy');?></a>
		</div>
	<?php } ?>
</div>