<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCustomerform */
?>
<div class="hidden-fields">
	<input type="hidden" name="id" id="id" value="<?php echo intval($this->customerData->id);?>" />
	<input type="hidden" name="form_type" id="form_type" value="<?php echo hsc($this->formType);?>" />

	<?php if ($this->allowDeliveryAddress === false) { ?>
		<input type="hidden" name="samedelivery" value="1" />
	<?php } ?>

	<?php if ($this->useOptionalRegistration === false) { ?>
		<input type="hidden" name="register" value="1" />
	<?php } ?>

	<?php if ($this->useCityLists == false) { ?>
		<input type="hidden" name="city_id" value="0" />
		<input type="hidden" name="billingcity_id" value="0" />
	<?php } ?>

</div>
