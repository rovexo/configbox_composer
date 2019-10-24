<?php 
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewEmailtemplate */
?>
<html><body>
<style type="text/css">
	body { background:#F6F6F6; color:#111; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
	td,th { color:#111; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="width:700px; background:#fff; border:1px solid #E0E0E0;">
	
	<?php if ($this->useShopLogo) { ?>
		<div class="shop-logo" style="padding-left:20px;padding-top: 20px;margin-bottom:20px">
			<img width="<?php echo $this->shopLogoWidth;?>px" height="<?php echo $this->shopLogoHeight;?>px" src="<?php echo $this->shopLogoUrl;?>" alt="Company Logo" />
		</div>
	<?php } ?>
	
	<div class="email-content" style="padding-left:20px;padding-right:20px;padding-bottom:20px;margin-top:20px">
		<?php echo $this->emailContent;?>
	</div>
	
	<div class="email-footer" style="background:#efefef; padding:20px 20px">
		
		<table class="shop-data-table" border="0" style="width:100%">
			<tr>
				<td class="address" width="30%" style="border:none;vertical-align: top">
					<div class="shop-name"><?php echo hsc($this->shopData->shopname);?></div>
					<div class="shop-address1"><?php echo hsc($this->shopData->shopaddress1);?></div>
					<?php if ($this->shopData->shopaddress2) { ?>
					<div class="shop-address2"><?php echo hsc($this->shopData->shopaddress2);?></div>
					<?php } ?>
					<div class="shop-city-zipcode"><?php echo hsc($this->shopData->shopzipcode);?> <?php echo hsc($this->shopData->shopcity);?></div>
					<div class="shop-country"><?php echo hsc($this->shopCountryName);?></div>
				</td>
				
				<td class="contact" width="32%" style="border:none;vertical-align: top">
										
					<div class="shop-website"><span class="key"><?php echo KText::_('Web');?>:</span> <?php echo hsc($this->shopData->shopwebsite);?></div>
					
					<div class="shop-email-sales"><span class="key"><?php echo KText::_('Email');?>:</span> <?php echo hsc($this->shopData->shopemailsales);?></div>
					
					<?php if ($this->shopData->shopphonesales) { ?>
					<div class="shop-phone-sales"><span class="key"><?php echo KText::_('Phone');?>:</span> <?php echo hsc($this->shopData->shopphonesales);?></div>
					<?php } ?>
					
					<?php if ($this->shopData->shopfax) { ?>
					<div class="shop-fax"><span class="key"><?php echo KText::_('Fax');?>:</span> <?php echo hsc($this->shopData->shopfax);?></div>
					<?php } ?>
				</td>
				
				<td class="legal" width="32%" style="border:none;vertical-align: top;padding-left:4%">
					
					<?php if ($this->shopData->shopowner) { ?>
					<div class="shop-owner"><span class="key"><?php echo KText::_('Company Owner');?>:</span> <?php echo hsc($this->shopData->shopowner);?></div>
					<?php } ?>
					
					<?php if ($this->shopData->shopuid) { ?>
					<div class="shop-vatin"><span class="key"><?php echo KText::_('VAT IN');?>:</span> <?php echo hsc($this->shopData->shopuid);?></div>
					<?php } ?>
					
					<?php if ($this->shopData->shoplegalvenue) { ?>
					<div class="shop-legal-venue"><span class="key"><?php echo KText::_('Legal Venue');?>:</span> <?php echo hsc($this->shopData->shoplegalvenue);?></div>
					<?php } ?>
					
					<?php if ($this->shopData->shopcomreg) { ?>
					<div class="shop-comreg"><span class="key"><?php echo KText::_('Commercial Register ID');?>:</span> <?php echo hsc($this->shopData->shopcomreg);?></div>
					<?php } ?>
				</td>
				
			</tr>
		</table>
		
	</div>
	
</div>
</body></html>