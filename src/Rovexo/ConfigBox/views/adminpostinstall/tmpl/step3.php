<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminpostinstall */
?>
<div class="wrapper-vat cube-step" data-step="3">

	<h2><?php echo KText::_('POST_INSTALL_TAX_HEADING');?></h2>

	<div class="form-group">
		<label for="taxRate"><?php echo KText::_('Default VAT rate');?></label>
		<div class="input-group">
			<input class="form-control" type="text" id="taxRate" aria-describedby="help-taxRate" value="<?php echo hsc(KText::getLocalizedNumber($this->defaultTaxRate));?>">
			<div class="input-group-addon">%</div>
		</div>
		<div class="help-block validation-placeholder"></div>
		<span id="help-taxRate" class="help-block"><?php echo KText::_('TOOLTIP_DEFAULT_VAT_RATE_HELP');?></span>
	</div>

	<div class="form-group">
		<label for="countryId"><?php echo KText::_('Country');?></label>
		<?php echo KenedoHtml::getSelectField('countryId', $this->countryOptions, $this->shopData->country_id, '', false, 'make-me-chosen', 'countryId', array('placeholder'=>KText::_('Select a country'), 'aria-describedby' => 'help-countryId') ); ?>
		<div class="help-block validation-placeholder"></div>
		<span id="help-countryId" class="help-block"><?php echo KText::_('POST_INSTALL_COUNTRY');?></span>
	</div>

	<div class="form-group">
		<label for="taxMode"><?php echo KText::_('B2B or B2C');?></label>
		<select id="taxMode" aria-describedby="help-taxMode">
			<option value="b2b"><?php echo KText::_('B2B');?></option>
			<option value="b2c"><?php echo KText::_('B2C');?></option>
		</select>
		<div class="help-block validation-placeholder"></div>
		<span id="help-taxMode" class="help-block"><?php echo KText::_('POST_INSTALL_TAX_MODE');?></span>
	</div>

	<a class="btn btn-primary pull-right trigger-store-vat-data" data-next-step="4"><?php echo KText::_('Next');?></a>

</div>