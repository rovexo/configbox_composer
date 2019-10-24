<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var ConfigboxShopData $shopData
 * @see ConfigboxModelAdminshopdata
 */
?>
<div class="store-information">
	<h3><?php echo KText::_('Store Information')?></h3>
	<table>
		<tr class="store-name">
			<td class="key"><?php echo KText::_('Store Name');?></td>
			<td class="value"><?php echo hsc($shopData->shopname);?></td>
		</tr>
		<?php if ($shopData->shopowner) { ?>
			<tr class="store-owner">
				<td class="key"><?php echo KText::_('Company Owner');?></td>
				<td class="value"><?php echo hsc($shopData->shopowner);?></td>
			</tr>
		<?php } ?>
		<tr class="store-website">
			<td class="key"><?php echo KText::_('Store Website');?></td>
			<td class="value"><?php echo hsc($shopData->shopwebsite);?></td>
		</tr>
		<tr class="store-address">
			<td class="key"><?php echo KText::_('Store Address');?></td>
			<td class="value">
				<div class="address1"><?php echo hsc($shopData->shopaddress1);?></div>
				<?php if ($shopData->shopaddress2) { ?>
					<div class="address2"><?php echo hsc($shopData->shopaddress1);?></div>
				<?php } ?>
				<div class="zip-code-city"><?php echo hsc($shopData->shopzipcode);?> <?php echo hsc($shopData->shopcity);?></div>
				<div class="country"><?php echo hsc(ConfigboxCountryHelper::getCountryName($shopData->country_id));?></div>
			</td>
		</tr>
		<tr class="store-phone">
			<td class="key"><?php echo KText::_('Phone Support');?></td>
			<td class="value"><?php echo hsc($shopData->shopphonesupport);?></td>
		</tr>
		<tr class="store-email">
			<td class="key"><?php echo KText::_('Email Support');?></td>
			<td class="value"><?php echo hsc($shopData->shopemailsupport);?></td>
		</tr>
		<?php if ($shopData->shopfax) { ?>
			<tr class="store-fax">
				<td class="key"><?php echo KText::_('Fax');?></td>
				<td class="value"><?php echo hsc($shopData->shopfax);?></td>
			</tr>
		<?php } ?>
		
		<?php if ($shopData->shoplegalvenue) { ?>
			<tr class="store-legal-venue">
				<td class="key"><?php echo KText::_('Legal Venue');?></td>
				<td class="value"><?php echo hsc($shopData->shoplegalvenue);?></td>
			</tr>
		<?php } ?>
		
		<?php if ($shopData->shopcomreg) { ?>
			<tr class="store-legal-venue">
				<td class="key"><?php echo KText::_('Commercial Register ID');?></td>
				<td class="value"><?php echo hsc($shopData->shopcomreg);?></td>
			</tr>
		<?php } ?>
		
		<?php if ($shopData->shopbankname) { ?>
			<tr class="store-bank-name">
				<td class="key"><?php echo KText::_('Bank name');?></td>
				<td class="value"><?php echo hsc($shopData->shopbankname);?></td>
			</tr>
		<?php } ?>
		
		<?php if ($shopData->shopbankaccountholder) { ?>
			<tr class="store-bank-account-holder">
				<td class="key"><?php echo KText::_('Bank Account Holder');?></td>
				<td class="value"><?php echo hsc($shopData->shopbankaccountholder);?></td>
			</tr>
		<?php } ?>
		
		<?php if ($shopData->shopbic) { ?>
			<tr class="store-bank-bic">
				<td class="key"><?php echo KText::_('BIC');?></td>
				<td class="value"><?php echo hsc($shopData->shopbic);?></td>
			</tr>
		<?php } ?>
		
		<?php if ($shopData->shopiban) { ?>
			<tr class="store-bank-iban">
				<td class="key"><?php echo KText::_('IBAN/SWIFT');?></td>
				<td class="value"><?php echo hsc($shopData->shopiban);?></td>
			</tr>
		<?php } ?>
	</table>
</div>
<div class="store-refund-policy">
	<h3><?php echo KText::_('Refund Policy');?></h3>
	<div class="refund-policy-text"><?php echo $shopData->refundpolicy;?></div>
</div>

<div class="store-terms">
	<h3><?php echo KText::_('Terms and Conditions');?></h3>
	<div class="terms-text"><?php echo $shopData->tac;?></div>
</div>

