<?php
defined('CB_VALID_ENTRY') or die();
?>

<div class="clear">
	<?php echo KText::_('Your server needs to run PHP 5.3 or higher and you need to use One Page Checkout for this PSP.');?>
</div>

<div id="property-name-testmode" class="property-name-testmode kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Mode');?>
	</div>
	<div class="property-body">
		<input id="testmodeyes" type="radio" name="testmode" value="1" <?php echo ($this->settings->get('testmode') == 1) ? 'checked = "checked"':''; ?> /><label for="testmodeyes"><?php echo KText::_('Test system');?></label>
		<input id="testmodeno" type="radio" name="testmode" value="0" <?php  echo ($this->settings->get('testmode') == 0) ? 'checked = "checked"':''; ?> /><label for="testmodeno"><?php echo KText::_('Live system');?></label>
	</div>
</div>

<div class="clear"><h4><?php echo KText::_('Test system');?></h4></div>

<div id="property-name-private_api_key_test" class="property-name-private_api_key_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Test Secret Key');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="private_api_key_test" value="<?php echo hsc($this->settings->get('private_api_key_test'));?>" />
		</div>
	</div>
</div>

<div id="property-name-public_api_key_test" class="property-name-public_api_key_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Test Publishable Key');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="public_api_key_test" value="<?php echo hsc($this->settings->get('public_api_key_test'));?>" />
		</div>
	</div>
</div>

<div class="clear"><h4><?php echo KText::_('Live system');?></h4></div>

<div id="property-name-private_api_key_production" class="property-name-private_api_key_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Live Secret Key');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="private_api_key_production" value="<?php echo hsc($this->settings->get('private_api_key_production'));?>" />
		</div>
	</div>
</div>

<div id="property-name-public_api_key_production" class="property-name-public_api_key_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Live Publishable Key');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="public_api_key_production" value="<?php echo hsc($this->settings->get('public_api_key_production'));?>" />
		</div>
	</div>
</div>
