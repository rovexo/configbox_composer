<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewCheckoutaddress */
?>
<div class="order-address-blocks <?php echo ($this->orderRecord->orderAddress->samedelivery == 1) ? 'delivery-same':'delivery-different';?>">
	
	<div class="order-address-block order-address-block-billing">
		
		<?php if ($this->orderRecord->orderAddress->samedelivery == 1) { ?>
			<div class="order-address-block-heading"><?php echo KText::_('Billing and Shipping to:');?></div>
		<?php } else { ?>
			<div class="order-address-block-heading"><?php echo KText::_('Billing to:');?></div>
		<?php } ?>
		
		<?php if ($this->orderRecord->orderAddress->billingcompanyname) { ?>
			<div class="order-address-companyname"><?php echo hsc($this->orderRecord->orderAddress->billingcompanyname);?></div>
		<?php } ?>
		
		<div class="order-address-name">
			<?php echo hsc($this->orderRecord->orderAddress->billingsalutation);?> <?php echo hsc($this->orderRecord->orderAddress->billingfirstname);?> <?php echo hsc($this->orderRecord->orderAddress->billinglastname);?>
		</div>
		<div class="order-address-address1">
			<?php echo hsc($this->orderRecord->orderAddress->billingaddress1);?>
		</div>
		<?php if ($this->orderRecord->orderAddress->billingaddress2) { ?>
			<div class="order-address-address2"><?php echo hsc($this->orderRecord->orderAddress->billingaddress2);?></div>
		<?php } ?>
		
		<div class="order-address-zipcode-city">
			<?php echo hsc($this->orderRecord->orderAddress->billingzipcode);?> <?php echo hsc($this->orderRecord->orderAddress->billingcity);?>
		</div>
		
		<div class="order-address-countryname">
			<?php echo hsc($this->orderRecord->orderAddress->billingcountryname);?>
		</div>
		
	</div> <!-- .order-address-block-billing -->
	
	<?php if ($this->orderRecord->orderAddress->samedelivery == 0) { ?>
		<div class="order-address-block order-address-block-delivery">
			
			<div class="order-address-block-heading"><?php echo KText::_('Delivery to:');?></div>
			
			<?php if ($this->orderRecord->orderAddress->companyname) { ?>
				<div class="order-address-companyname"><?php echo hsc($this->orderRecord->orderAddress->companyname);?></div>
			<?php } ?>
			
			<div class="order-address-name">
				<?php echo hsc($this->orderRecord->orderAddress->salutation);?> <?php echo hsc($this->orderRecord->orderAddress->firstname);?> <?php echo hsc($this->orderRecord->orderAddress->lastname);?>
			</div>
			<div class="order-address-address1">
				<?php echo hsc($this->orderRecord->orderAddress->address1);?>
			</div>
			<?php if ($this->orderRecord->orderAddress->address2) { ?>
				<div class="order-address-address2"><?php echo hsc($this->orderRecord->orderAddress->address2);?></div>
			<?php } ?>
			
			<div class="order-address-zipcode-city">
				<?php echo hsc($this->orderRecord->orderAddress->zipcode);?> <?php echo hsc($this->orderRecord->orderAddress->city);?>
			</div>
			
			<div class="order-address-countryname">
				<?php echo hsc($this->orderRecord->orderAddress->countryname);?>
			</div>
			
		</div> <!-- .order-address-block-delivery -->
	<?php } ?>
	
	<div class="clear"></div>
	
</div>

<div class="change-order-address-wrapper">
	<a class="trigger-show-order-address-form"><span class="fa fa-edit"></span><?php echo KText::_('Change');?></a>
</div>
