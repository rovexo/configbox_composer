<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewPaymentresult */
?>
<div <?php echo $this->getViewAttributes();?>>
	
	<p><?php echo KText::_('Thank you for your order.');?>
	<?php  
	if (!empty($this->shopData->shopemailsales)) {
		echo KText::_('If you have any further questions please send an email to'). ' ';
		?>
		<a href="mailto:<?php echo $this->shopData->shopemailsales;?>"><?php echo $this->shopData->shopemailsales;?></a>
		<?php
		
		if (!empty($this->shopData->shopphonesales)) {
			echo KText::sprintf('or call us at %s',$this->shopData->shopphonesales);
		}
		?>.
		
		<?php
	}
	?>
	</p>

	<div class="wrapper-buttons">

		<a class="btn btn-default button-go-to-order" href="<?php echo $this->linkToOrder;?>"><?php echo KText::_('See your order status');?></a>
		<a class="btn btn-default button-go-to-profile" href="<?php echo $this->linkToCustomerProfile;?>"><?php echo KText::_('Go to your customer account');?></a>

		<?php if ($this->showContinueButton) { ?>
			<a class="btn btn-default button-continue-shopping" href="<?php echo $this->urlContinueShopping;?>"><?php echo KText::_('Continue Shopping');?></a>
		<?php } ?>

	</div>

</div>