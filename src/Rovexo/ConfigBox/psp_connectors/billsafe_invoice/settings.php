<?php
defined('CB_VALID_ENTRY') or die();
?>

<div id="property-name-merchant_id" class="property-name-merchant_id kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Merchant ID');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="merchant_id" value="<?php echo hsc($this->settings->get('merchant_id'));?>" />
		</div>
	</div>
</div>

<div id="property-name-merchant_license_test" class="property-name-merchant_license_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Merchant License Sandbox');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="merchant_license_test" value="<?php echo hsc($this->settings->get('merchant_license_test'));?>" />
		</div>
	</div>
</div>

<div id="property-name-merchant_license_live" class="property-name-merchant_license_live kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Merchant License Live');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="merchant_license_live" value="<?php echo hsc($this->settings->get('merchant_license_live'));?>" />
		</div>
	</div>
</div>

<div id="property-name-application_signature" class="property-name-application_signature kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Application Signature');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="application_signature" value="<?php echo hsc($this->settings->get('application_signature'));?>" />
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

<div class="extra-info">
<?php echo KText::_('With this connector you can handle BillSAFE invoice payments. Installments and partial shipments are not supported. You cannot use a custom invoice template when offering this payment method. See Order Management -> Shop Data -> Invoice about custom invoice template settings.');?>
</div>
