<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCustomerform */
?>
<div class="hidden-fields">
	<input type="hidden" name="group_id" id="group_id" value="<?php echo intval($this->customerData->group_id);?>" />
	<input type="hidden" name="platform_user_id" id="platform_user_id" value="<?php echo intval($this->customerData->platform_user_id);?>" />

	<input type="hidden" name="custom_1" id="custom_1" value="<?php echo hsc($this->customerData->custom_1);?>" />
	<input type="hidden" name="custom_2" id="custom_2" value="<?php echo hsc($this->customerData->custom_2);?>" />
	<input type="hidden" name="custom_3" id="custom_3" value="<?php echo hsc($this->customerData->custom_3);?>" />
	<input type="hidden" name="custom_4" id="custom_4" value="<?php echo hsc($this->customerData->custom_4);?>" />

</div>
