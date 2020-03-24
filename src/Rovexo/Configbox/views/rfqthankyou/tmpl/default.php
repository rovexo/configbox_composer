<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewRfqthankyou */
?>
<div <?php echo $this->getViewAttributes();?>>

	<h1 class="page-title page-title-rfq"><?php echo KText::_('Thank you');?></h1>

	<?php if ($this->showQuotationDownload) { ?>
		<div class="quotation-download">
			<p><?php echo KText::_('Thank you, your quotation is ready to download.');?></p>
			<p><a class="btn btn-primary button-quotation-download" download href="<?php echo $this->urlQuotationDownload;?>"><?php echo KText::_('Download');?></a></p>
		</div>
	<?php } ?>

	<?php if ($this->showQuotationEmail) { ?>
		<div class="quotation-download">
			<p><?php echo KText::_('Thank you for your quotation request. We sent you an email with your quotation attached.');?></p>
		</div>
	<?php } ?>

	<?php if ($this->showRequestConfirmation) { ?>
		<div class="request-confirmation">
			<p><?php echo KText::_('Thank you for your quotation request. We will process your request and get back to you.');?></p>
		</div>
	<?php } ?>

	<?php if ($this->showContinueButton) { ?>
		<a class="btn btn-default button-continue-shopping" href="<?php echo $this->urlContinueShopping;?>"><?php echo KText::_('Continue Shopping');?></a>
	<?php } ?>

	<?php if ($this->showAccountLink) { ?>
		<a class="btn btn-default button-account" href="<?php echo $this->urlAccount;?>"><?php echo KText::_('Go to account page');?></a>
	<?php } ?>

	<div class="tracking-code">
		<?php echo $this->trackingCode;?>
	</div>

</div>