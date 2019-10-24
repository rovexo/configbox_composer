<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-testmode" class="property-name-testmode kenedo-property property-type-radio">
	<div class="property-label">
		<?php echo KText::_('Mode');?>
	</div>
	<div class="property-body">
		<input id="testmodeyes" type="radio" name="testmode" value="1" <?php echo ($this->settings->get('testmode') == 1) ? 'checked = "checked"':''; ?> /><label for="testmodeyes"><?php echo KText::_('Test system');?></label>
		<input id="testmodeno" type="radio" name="testmode" value="0" <?php  echo ($this->settings->get('testmode') == 0) ? 'checked = "checked"':''; ?> /><label for="testmodeno"><?php echo KText::_('Production system');?></label>
	</div>
</div>

<div class="clear"><b><?php echo KText::_('Test system');?></b></div>

<div id="property-name-merchant_id_test" class="property-name-merchant_id_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Merchant ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="merchant_id_test" value="<?php echo hsc($this->settings->get('merchant_id_test'));?>" />
		</div>
	</div>
</div>

<div id="property-name-password_test" class="property-name-password_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Webservice/SOAP password');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="password_test" value="<?php echo hsc($this->settings->get('password_test'));?>" />
		</div>
	</div>
</div>

<div class="clear"><b><?php echo KText::_('Production system');?></b></div>

<div id="property-name-merchant_id_production" class="property-name-merchant_id_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Merchant ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="merchant_id_production" value="<?php echo hsc($this->settings->get('merchant_id_production'));?>" />
		</div>
	</div>
</div>

<div id="property-name-password_production" class="property-name-password_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Webservice/SOAP password');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="password_production" value="<?php echo hsc($this->settings->get('password_production'));?>" />
		</div>
	</div>
</div>

<div>
	
	<p><b><?php echo KText::_('Important note:');?></b> <?php echo KText::_('The merchant ID must be entered without the leading U character. The Webservice/SOAP password is not to be confused with the merchant interface password.');?></p>
	
</div>

