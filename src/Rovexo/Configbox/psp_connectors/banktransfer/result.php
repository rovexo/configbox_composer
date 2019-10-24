<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var ConfigboxViewPaymentresult $this
 */
?>
<div id="com_configbox" class="cb-content">
	<div id="view-paymentresult">
	
		<h2><?php echo KText::_('Payment via bank transaction');?></h2>
		<p><?php echo KText::sprintf('To pay via bank transaction, please transfer the amount of %s to following bank account:',cbprice($this->orderRecord->payableAmount));?></p>

		<table>
			<?php if (!empty($this->shopdata->shopbankname)) { ?>
				<tr>
					<td><?php echo KText::_('Bank Name');?></td>
					<td><?php echo hsc($this->shopdata->shopbankname);?></td>
				</tr>
			<?php } ?>
			
			<?php if (!empty($this->shopdata->shopbankaccountholder)) { ?>
				<tr>
					<td><?php echo KText::_('Bank Account Name');?></td>
					<td><?php echo hsc($this->shopdata->shopbankaccountholder);?></td>
				</tr>
			<?php } ?>
			
			<?php if (!empty($this->shopdata->shopbankaccount)) { ?>
				<tr>
					<td><?php echo KText::_('Bank Account Number');?></td>
					<td><?php echo hsc($this->shopdata->shopbankaccount);?></td>
				</tr>
			<?php } ?>
			
			<?php if (!empty($this->shopdata->shopbankcode)) { ?>
				<tr>
					<td><?php echo KText::_('Bank Code');?></td>
					<td><?php echo hsc($this->shopdata->shopbankcode);?></td>
				</tr>
			<?php } ?>
			
			<?php if (!empty($this->shopdata->shopbic)) { ?>
				<tr>
					<td><?php echo KText::_('BIC');?></td>
					<td><?php echo hsc($this->shopdata->shopbic);?></td>
				</tr>
			<?php } ?>
			
			<?php if (!empty($this->shopdata->shopiban)) { ?>
				<tr>
					<td><?php echo KText::_('IBAN');?></td>
					<td><?php echo hsc($this->shopdata->shopiban);?></td>
				</tr>
			<?php } ?>
			
			<tr>
				<td><?php echo KText::_('Purpose');?></td>
				<td><?php echo KText::sprintf('Order Number %s',$this->orderRecord->id);?></td>
			</tr>
		</table>
		<p><?php echo KText::_('Thank you for your order.');?>
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
