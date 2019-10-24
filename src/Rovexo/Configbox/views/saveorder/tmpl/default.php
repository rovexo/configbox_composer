<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewSaveorder */
?>
<div <?php echo $this->getViewAttributes();?> <?php echo $this->viewAttributes;?>>

	<h1 class="page-title page-title-save-order"><?php echo KText::_('Save Cart');?></h1>

	<div class="wrapper-customer-form">
		<?php echo $this->customerFormHtml; ?>
	</div>

	<div class="buttons">
		<a class="btn btn-default button-back" href="<?php echo $this->urlCart;?>"><?php echo KText::_('Back');?></a>
		<a class="btn btn-primary button-save-order trigger-save-order"><?php echo KText::_('Save');?></a>
	</div>

</div>