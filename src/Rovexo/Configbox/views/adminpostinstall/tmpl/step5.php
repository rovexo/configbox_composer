<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminpostinstall */
?>
<div class="wrapper-currencies cube-step" data-step="5">

	<h2><?php echo KText::_('POST_INSTALL_CURRENCIES_HEADING');?></h2>
	<div class="bs-callout"><?php echo KText::_('POST_INSTALL_CURRENCIES_INTRO');?></div>

	<div class="row base-currency">
		<div class="col-sm-3">
			<div class="form-group">
				<label for="baseCurrencyTitle"><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_NAME');?></label>
				<input class="form-control" type="text" id="baseCurrencyTitle" aria-describedby="help-baseCurrencyTitle" value="<?php echo hsc($this->baseCurrency->title);?>">
			</div>
		</div>
		<div class="col-sm-2">
			<div class="form-group">
				<label for="baseCurrencySymbol"><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_SYMBOL');?></label>
				<input class="form-control" type="text" id="baseCurrencySymbol" aria-describedby="help-baseCurrencySymbol" value="<?php echo hsc($this->baseCurrency->symbol);?>">
			</div>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<label for="baseCurrencyCode"><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_CODE');?></label>
				<input class="form-control" type="text" id="baseCurrencyCode" aria-describedby="help-baseCurrencyCode" value="<?php echo hsc($this->baseCurrency->code);?>">
			</div>
		</div>
	</div>

	<div class="currencies">
		<?php foreach ($this->currencies as $currency) { ?>

			<div class="row" data-currency-id="<?php echo intval($currency->id);?>">
				<div class="col-sm-3">
					<div class="form-group">
						<label><?php echo KText::_('POST_INSTALL_CURRENCY_NAME');?></label>
						<input class="form-control currency-title" type="text" value="<?php echo hsc($currency->title);?>">
					</div>
				</div>
				<div class="col-sm-2">
					<div class="form-group">
						<label><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_SYMBOL');?></label>
						<input class="form-control currency-symbol" type="text" value="<?php echo hsc($currency->symbol);?>">
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<label><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_CODE');?></label>
						<input class="form-control currency-code" type="text" value="<?php echo hsc($currency->code);?>">
					</div>
				</div>
				<div class="col-sm-2">
					<div class="form-group">
						<label><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_MULTIPLIER');?></label>
						<input class="form-control currency-multiplier" type="text" value="<?php echo hsc(KText::getLocalizedNumber($currency->multiplicator));?>">
					</div>
				</div>
				<div class="col-sm-2">
					<div class="form-group">
						<label style="visibility: hidden; display: block">&nbsp;</label>
						<a class="btn btn-default pull-right trigger-remove-currency"><?php echo KText::_('Remove');?></a>
					</div>
				</div>
			</div>

		<?php } ?>
	</div>

	<div class="blueprint-currency">

		<div class="row" data-currency-id="">
			<div class="col-sm-3">
				<div class="form-group">
					<label><?php echo KText::_('POST_INSTALL_CURRENCY_NAME');?></label>
					<input class="form-control currency-title" type="text">
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<label><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_SYMBOL');?></label>
					<input class="form-control currency-symbol" type="text">
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_CODE');?></label>
					<input class="form-control currency-code" type="text">
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<label><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_MULTIPLIER');?></label>
					<input class="form-control currency-multiplier" type="text">
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<label style="visibility: hidden; display: block">&nbsp;</label>
					<a class="btn btn-default pull-right trigger-remove-currency"><?php echo KText::_('Remove');?></a>
				</div>
			</div>
		</div>

	</div>

	<span id="help-baseCurrencyTitle" class="help-block"><?php echo KText::_('POST_INSTALL_BASE_CURRENCY_HELP');?></span>
	<div class="help-block validation-placeholder"></div>

	<a class="btn btn-default trigger-add-currency"><?php echo KText::_('Add a currency');?></a>
	<a class="btn btn-primary pull-right trigger-store-currencies" data-next-step="6"><?php echo KText::_('Next');?></a>

</div>
