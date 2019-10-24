<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminpostinstall */
?>

<div class="wrapper-store-info cube-step" data-step="4">

	<h2><?php echo KText::_('POST_INSTALL_STORE_INFO_HEADING');?></h2>
	<div class="bs-callout"><?php echo KText::_('POST_INSTALL_STORE_INFO_INTRO');?></div>

	<div class="form-group">
		<label for="shopName"><?php echo KText::_('Store Name');?></label>
		<input class="form-control" type="text" id="shopName" aria-describedby="help-shopName" value="<?php echo hsc($this->shopData->shopname);?>">
		<div class="help-block validation-placeholder"></div>
		<span id="help-shopName" class="help-block"><?php echo KText::_('TOOLTIP_SHOPDATA_SHOPNAME');?></span>
	</div>

	<div class="form-group">
		<label for="shopWebsite"><?php echo KText::_('Store Website');?></label>
		<input class="form-control" type="text" id="shopWebsite" aria-describedby="help-shopWebsite" value="<?php echo empty($this->shopData->shopwebsite) ? KPATH_HOST : hsc($this->shopData->shopwebsite);?>">
		<div class="help-block validation-placeholder"></div>
		<span id="help-shopWebsite" class="help-block"><?php echo KText::_('TOOLTIP_SHOPDATA_SHOPWEBSITE');?></span>
	</div>

	<div class="form-group">
		<label for="email"><?php echo KText::_('Email for notifications');?></label>
		<input class="form-control" type="email" id="email" aria-describedby="help-shop-email" value="<?php echo hsc($this->shopData->shopemailsales);?>">
		<div class="help-block validation-placeholder"></div>
		<span id="help-email" class="help-block"><?php echo KText::_('POST_INSTALL_EMAIL');?></span>
	</div>

	<a class="btn btn-primary pull-right trigger-store-shop-data" data-next-step="5"><?php echo KText::_('Next');?></a>

</div>
