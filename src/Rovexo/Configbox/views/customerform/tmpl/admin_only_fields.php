<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCustomerform */
?>
<div class="admin-only-fields">

	<div class="customer-field customer-field-platform_user_id">
		<label for="platform_user_id"><?php echo KText::_('Platform User ID');?></label>
		<input class="form-control" type="text" id="platform_user_id" name="platform_user_id" placeholder="<?php echo KText::_('Platform User ID');?>" value="<?php echo intval($this->customerData->platform_user_id);?>" />
		<div class="validation-tooltip"></div>
	</div>

	<div class="customer-field customer-field-group_id">
		<label for="group_id"><?php echo KText::_('Customer group');?></label>
		<div class="form-field">
			<?php echo $this->groupDropDownHtml;?>
			<div class="validation-tooltip"></div>
		</div>
	</div>

	<div class="customer-field customer-field-custom_1">
		<label for="custom_1"><?php echo KText::_('Custom field 1');?></label>
		<input class="form-control" type="text" id="custom_1" name="custom_1" value="<?php echo hsc($this->customerData->custom_1);?>" />
		<div class="validation-tooltip"></div>
	</div>

	<div class="customer-field customer-field-custom_2">
		<label for="custom_2"><?php echo KText::_('Custom field 2');?></label>
		<input class="form-control" type="text" id="custom_2" name="custom_2" value="<?php echo hsc($this->customerData->custom_2);?>" />
		<div class="validation-tooltip"></div>
	</div>

	<div class="customer-field customer-field-custom_3">
		<label for="custom_3"><?php echo KText::_('Custom field 3');?></label>
		<input class="form-control" type="text" id="custom_3" name="custom_3" value="<?php echo hsc($this->customerData->custom_3);?>" />
		<div class="validation-tooltip"></div>
	</div>

	<div class="customer-field customer-field-custom_4">
		<label for="custom_4"><?php echo KText::_('Custom field 4');?></label>
		<input class="form-control" type="text" id="custom_4" name="custom_4" value="<?php echo hsc($this->customerData->custom_4);?>" />
		<div class="validation-tooltip"></div>
	</div>

</div>