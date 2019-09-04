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

<div id="property-name-vendor_name_test" class="property-name-vendor_name_test kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Vendor Name');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="vendor_name_test" value="<?php echo hsc($this->settings->get('vendor_name_test'));?>" />
		</div>
	</div>
</div>


<div class="clear"><b><?php echo KText::_('Production system');?></b></div>

<div id="property-name-vendor_name_production" class="property-name-vendor_name_production kenedo-property property-type-string">
	<div class="property-label">
		<?php echo KText::_('Vendor Name');?>
	</div>
	<div class="property-body">
		<div class="property-type-text">
			<input class="form-control" type="text" name="vendor_name_production" value="<?php echo hsc($this->settings->get('vendor_name_production'));?>" />
		</div>
	</div>
</div>
