<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewBlockcurrencies extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var KStorage $params
	 */
	public $params;

	/**
	 * @var boolean Indicates if the block title shall be shown. Depends on if there is a title set in the backend settings.
	 */
	public $showBlockTitle;

	/**
	 * @var string Title of the block. Data comes from backend settings
	 */
	public $blockTitle;

	/**
	 * @var ConfigboxCurrencyData[]
	 * @see ConfigboxCurrencyHelper::getCurrencies
	 */
	public $currencies;

	/**
	 * @var ConfigboxCurrencyData $baseCurrency The base currency data (Base currency is the backend's currency. It is set in the currency record).
	 * @see ConfigboxCurrencyHelper::getBaseCurrency()
	 */
	public $baseCurrency;

	/**
	 * @var string $dropDown HTML for the whole <select>
	 */
	public $dropdown;

	/**
	 * @var bool $showConversionTable Depends on setting show_conversion_table in CB settings
	 */
	public $showConversionTable;

	/**
	 * @var string[] baseTitle currTitle exchangeRate in an array with currency ids as keys
	 */
	public $exchangeRates = array();

	/**
	 * @var string CSS classes for the block's wrapper
	 */
	public $wrapperClasses;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars($tpl = null) {

		$this->params = new KStorage();

		if (empty($this->params)) {
			$this->params = new KStorage();
		}
		
		$baseCurrency = ConfigboxCurrencyHelper::getBaseCurrency();

		// Do the currency changer dropdown
		$currencies = ConfigboxCurrencyHelper::getCurrencies();

		$options = array();
		foreach ($currencies as $currency) {
			$options[$currency->id] = $currency->title;
		}

		$dropdown = KenedoHtml::getSelectField('currency_id', $options, ConfigboxCurrencyHelper::getCurrency()->id);

		// Deal with conversion rate table
		if (CbSettings::getInstance()->get('show_conversion_table')) {

			$exchangeRates = array();
			foreach ($currencies as $currency) {
				if ($currency->base) continue;
				$exchangeRates[$currency->id] = array(
					'baseTitle' => $baseCurrency->title,
					'currTitle' => $currency->title,
					'exchangeRate' => round(floatval($currency->multiplicator) / floatval($baseCurrency->multiplicator), 4),
				);
			}
			$this->exchangeRates = $exchangeRates;

		}

		$this->blockTitle = CbSettings::getInstance()->get('blocktitle_currencies', '');
		$this->showBlockTitle = !empty($this->blockTitle);

		$this->baseCurrency = $baseCurrency;
		$this->currencies = ConfigboxCurrencyHelper::getCurrencies();
		$this->dropdown = $dropdown;
		$this->showConversionTable = (count($this->exchangeRates) && CbSettings::getInstance()->get('show_conversion_table'));

		$wrapperClasses = array(
			'cb-content',
			'configbox-block',
			'block-currencies',
			$this->params->get('moduleclass_sfx', ''),
		);

		$this->wrapperClasses = trim(implode(' ', $wrapperClasses));

	}

}
