<?php
defined('CB_VALID_ENTRY') or die();
?>
<div id="com_configbox" class="cb-content">
	<div id="view-paymentresult">
		<p><?php echo KText::_('Thank you for your order.');?></p>
		<p>
		<?php  
		if (!empty($this->shopdata->shopemailsales)) {
			echo KText::_('If you have any further questions please send an email to'). ' ';
			?>
			<a href="mailto:<?php echo $this->shopdata->shopemailsales;?>"><?php echo $this->shopdata->shopemailsales;?></a>
			<?php
			
			if (!empty($this->shopdata->shopphonesales)) {
				echo KText::sprintf('or call us at %s',$this->shopdata->shopphonesales);
			}
			?>.
			
			<?php
		}
		?>
		</p>
		
		<ul class="continue-links">		
			<li class="link-order"><a href="<?php echo $this->linkToOrder;?>"><?php echo KText::_('See order details');?></a></li>
			<li class="link-account"><a href="<?php echo $this->linkToCustomerProfile;?>"><?php echo KText::_('Go to customer account');?></a></li>
			<li class="link-continue-shopping"><a href="<?php echo $this->linkToDefaultProductListing;?>"><?php echo KText::_('Continue shopping');?></a></li>
		</ul>
		
	</div>
</div>
