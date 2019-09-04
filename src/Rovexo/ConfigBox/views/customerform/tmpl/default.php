<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCustomerform */
?>
<div <?php echo $this->getViewAttributes();?> <?php echo $this->viewDataAttributes;?>>
	<?php
	// Recurring customer login
	if ($this->useLoginForm) {
		$this->renderView('login');
	}

	// Address form fields
	$this->renderView('address_fields');

	// Address form fields
	if ($this->userIsAdmin) {
		$this->renderView('admin_only_fields');
	}
	else {
		$this->renderView('user_only_fields');
	}

	// Hidden fields like customer ID etc
	$this->renderView('hidden_user_fields');
	?>
</div>