<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-merchant" class="property-name-merchant kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('QuickPay ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="merchant" value="<?php echo hsc($this->settings->get('merchant'));?>" />
		</div>
	</div>
</div>

<div id="property-name-md5secret" class="property-name-md5secret kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('MD5 Secret');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="md5secret" value="<?php echo hsc($this->settings->get('md5secret'));?>" />
		</div>
	</div>
</div>

<div id="property-name-testmode" class="property-name-testmode kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Test Mode');?>
	</div>
	<div class="property-body">
		<input id="testmodeyes" type="radio" name="testmode" value="1" <?php echo ($this->settings->get('testmode') == 1) ? 'checked = "checked"':''; ?> /><label for="testmodeyes"><?php echo KText::_('CBYES');?></label>
		<input id="testmodeno" type="radio" name="testmode" value="0" <?php  echo ($this->settings->get('testmode') == 0) ? 'checked = "checked"':''; ?> /><label for="testmodeno"><?php echo KText::_('CBNO');?></label>
	</div>
</div>

