<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewUser */
?>
<div <?php echo $this->getViewAttributes();?> <?php echo $this->viewAttributes;?>>

	<h1 class="page-title"><?php echo KText::_('Your delivery and billing information')?></h1>

	<?php echo $this->customerFormHtml;?>

	<div class="buttons">

		<a rel="nofollow" href="<?php echo $this->urlCustomerAccount;?>" class="btn btn-default">
			<?php echo KText::_('Close');?>
		</a>

		<a rel="nofollow" class="btn btn-primary button-save trigger-store-customer-form">
			<?php echo KText::_('Save');?>
		</a>

	</div>

</div>
