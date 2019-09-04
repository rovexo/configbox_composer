<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckout */
?>
<div <?php echo $this->getViewAttributes();?> <?php echo $this->viewAttributes;?>>

	<div class="wrapper-order-address">
		<h2 class="step-title"><?php echo KText::_('Your address');?></h2>
		<div class="order-address-form"<?php echo ($this->orderAddressComplete) ? 'style="display:none"':'';?>>

			<?php echo $this->orderAddressFormHtml; ?>

			<div class="order-address-buttons">
				<a class="trigger-save-order-address btn btn-primary"><i class="fa fa-spin fa-spinner loading-indicator"></i><span class="btn-text"><?php echo KText::_('Update Address');?></span></a>
			</div>

		</div>
		<div class="order-address-display"<?php echo ($this->orderAddressComplete) ? '':'style="display:none"';?>>
			<?php echo $this->orderAddressHtml; ?>
		</div>
	</div>

	<div class="wrapper-payment-options">
		<?php echo $this->paymentHtml; ?>
	</div>

	<?php if ($this->useDelivery) { ?>
		<div class="wrapper-delivery-options">
			<?php echo $this->deliveryHtml; ?>
		</div>
	<?php } ?>

	<h2 class="step-title"><?php echo KText::_('Review your order');?></h2>
	<div class="wrapper-order-record">
		<?php echo $this->orderRecordHtml; ?>
	</div>
	
	<div class="wrapper-agreements">
		<?php $this->renderView('default_agreements'); ?>
	</div>
	
	<div class="wrapper-psp-bridge" style="display:none"></div>
	
	<div class="wrapper-order-buttons">
		<?php $this->renderView('default_orderbuttons'); ?>
	</div>

	<?php $this->renderView('modals'); ?>

</div>
