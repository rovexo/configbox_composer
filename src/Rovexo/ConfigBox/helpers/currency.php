<?php
class ConfigboxCurrencyHelper {

	/**
	 * @var ConfigboxCurrencyData
	 */
	static protected $baseCurrency;

	/**
	 * @var ConfigboxCurrencyData
	 */
	static protected $currentCurrency;


	/**
	 * @return ConfigboxCurrencyData[]
	 */
	static function &getCurrencies() {
		return ConfigboxCacheHelper::getCurrencies();
	}

	/**
	 * @param object $obj
	 * @param null|float $multiplier
	 */
	static function appendCurrencyPrices(&$obj, $multiplier = NULL) {

		if ($multiplier === NULL) {
			$multiplier = self::getCurrency()->multiplicator;
		}

		foreach ($obj as $key=>$value) {
			if (strpos($key,'base') === 0 && is_numeric($value)) {
				$newKey = lcfirst(substr($key,4));
				$obj->$newKey = round($value * $multiplier, 2);
			}
		}
	}

	/**
	 * @param float $price The normal number
	 * @param bool $symbol If you want the currency symbol or not
	 * @param bool $emptyOnZero If true, a zero price returns an empty string
	 * @param int $decimals Amount of decimals (default is 2)
	 * @return string Nicely formatted, localized price (e.g. € 20.00)
	 */
	static function getFormatted($price, $symbol = true, $emptyOnZero = false, $decimals = 2) {
		if ($emptyOnZero && $price == 0) {
			return '';
		}
		return ($symbol ? self::getCurrency()->symbol . ' ' : '') . number_format($price, $decimals, KText::_('DECIMAL_MARK','.'), KText::_('DIGIT_GROUPING_SYMBOL',','));
	}

	/**
	 * @param float $taxRate
	 * @param bool $symbol If you want a % sign or not
	 * @return string
	 */
	static function getFormattedTaxRate($taxRate, $symbol = true) {

		if ($taxRate === '') {
			return '';
		}

		$decimals = ($taxRate - floor($taxRate) == 0) ? 0 : 2;
		$formatted = number_format($taxRate, $decimals, KText::_('DECIMAL_MARK','.'), KText::_('DIGIT_GROUPING_SYMBOL',',')).($symbol ? '%' : '');
		return $formatted;
	}

	/**
	 * @param int $id
	 * @return ConfigboxCurrencyData|null
	 */
	static function getCurrencyById($id) {
		$currencies = self::getCurrencies();
		return (!empty($currencies[$id])) ? $currencies[$id] : NULL;
	}

	/**
	 * @param string $code Currency ISO code (as set up in the currencies)
	 * @return ConfigboxCurrencyData|null
	 */
	static function getCurrencyByIsoCode($code) {
		$currencies = self::getCurrencies();
		foreach ($currencies as $currency) {
			if ($currency->code == $code) {
				return $currency;
			}
		}
		return NULL;
	}

	/**
	 * @return null|object Currency object or NULL if we can't figure one out
	 */
	static function determineCurrentCurrency() {

		$currencies = self::getCurrencies();

		$currencyId = KRequest::getInt('currency_id', KSession::get('currency_id', 0, 'com_configbox'));

		if (!$currencyId) {
			foreach ($currencies as $currency) {
				if ($currency->default == 1) {
					$currencyId = $currency->id;
					break;
				}
			}
		}

		$current = NULL;

		foreach($currencies as $currency) {
			if ($currency->id == $currencyId) {
				$current = $currency;
			}
		}

		foreach($currencies as $currency) {
			if ($currency->default == 1) {
				$current = $currency;
				break;
			}
		}

		if ($current == NULL) {
			KLog::log('Could determine the current currency. Really bad, choose a default in settings -> currencies.', 'error');
		}

		return $current;

	}

	/**
	 * @return ConfigboxCurrencyData Currency object or NULL if we can't figure one out
	 */
	static function determineBaseCurrency() {

		$currencies = self::getCurrencies();

		$base = NULL;

		foreach($currencies as $currency) {
			if ($currency->base) {
				$base = $currency;
				break;
			}
		}

		if ($base == NULL) {
			KLog::log('Could not find a base currency. Really bad, choose one in settings -> currencies.', 'error');
		}

		return $base;

	}

	/**
	 * @return ConfigboxCurrencyData Currency object
	 */
	static function getBaseCurrency() {

		if (empty(self::$baseCurrency)) {
			self::$baseCurrency = self::determineBaseCurrency();
		}

		return self::$baseCurrency;

	}

	/**
	 * @return ConfigboxCurrencyData Currency object
	 */
	static function getCurrency() {

		if (empty(self::$currentCurrency)) {
			self::$currentCurrency = self::determineCurrentCurrency();
		}

		return self::$currentCurrency;

	}

	/**
	 * Sets the currency used throughout the runtime
	 * @param int|string|object $input 		Either the ID or ISO code of the currency or an object with the right data
	 * @param boolean 			$remember	If you want the system to remember it for the session
	 * @return boolean true if things went well, false otherwise (most likely when the currency don't exist in the system)
	 */
	static function setCurrency($input, $remember = false) {

		$current = NULL;

		if (is_int($input)) {

			$currencies = self::getCurrencies();

			foreach($currencies as $currency) {
				if ($currency->id == $input) {
					$current = $currency;
				}
			}

		}
		elseif (is_string($input)) {

			$currencies = self::getCurrencies();

			foreach($currencies as $currency) {
				if ($currency->code == $input) {
					$current = $currency;
				}
			}

		}
		elseif (is_object($input)) {

			$current = $input;

		}

		if ($current !== NULL) {
			self::$currentCurrency = $current;
			if ($remember) {
				KSession::set('currency_id', $current->id, 'com_configbox');
			}
			return true;
		}
		else {
			KLog::log('Setting currency with input "'.var_export($input, true).'" failed. Check what is going on.', 'error');
			return false;
		}

	}

}