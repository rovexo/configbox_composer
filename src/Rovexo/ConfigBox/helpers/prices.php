<?php
class ConfigboxPrices {
	
	protected static $taxRateCache = NULL;

	protected static $taxClasses = NULL;

	/**
	 * Memoizes the return value for ConfigboxPrices::showNetPrices
	 *
	 * @var boolean|NULL
	 */
	protected static $showNetPrices = array();

	/**
	 * Memoizes the return value for ConfigboxPrices::pricesEnteredNet
	 *
	 * @var boolean|NULL
	 */
	protected static $pricesEnteredNet = NULL;

	/**
	 * Tells if prices in the system are entered as net prices
	 *
	 * @return bool
	 */
	public static function pricesEnteredNet() {

		if (self::$pricesEnteredNet === NULL) {

			if (KenedoPlatform::getName() == 'magento') {
				self::$pricesEnteredNet = (Mage::getStoreConfig('tax/calculation/price_includes_tax')) ? false : true;
			}
			else {
				self::$pricesEnteredNet = true;
			}

		}

		return self::$pricesEnteredNet;

	}

	/**
	 * Tells if prices shall be shown net (wherever the system does not show net/tax/gross)
	 *
	 * @param int|null $userId User ID (or NULL to have it auto-determined)
	 * @return bool
	 */
	public static function showNetPrices($userId = NULL) {

		if (KenedoPlatform::getName() == 'magento') {
			$taxDisplayType = Mage::getStoreConfig('tax/display/type');
			$showNet = ($taxDisplayType == 1);
			return $showNet;
		}

		if ($userId === NULL) {
			$userId = ConfigboxUserHelper::getUserId();
		}

		if (!isset(self::$showNetPrices[$userId])) {
			self::$showNetPrices[$userId] = ConfigboxPermissionHelper::canGetB2BMode($userId);
		}

		return self::$showNetPrices[$userId];
	}

	/**
	 * @param int $taxClassId See Backend -> Settings -> Tax Classes
	 *
	 * @return float $taxRate
	 */
	public static function getTaxRate($taxClassId) {

		if (KenedoPlatform::getName() == 'magento') {
			return KSession::get('cbtaxrate');
		}

		$user = ConfigboxUserHelper::getUser();
		return self::getTaxRateForPlace($taxClassId, $user->country, $user->state, $user->county, $user->city_id, $user->vatin);
	}

	/**
	 * @param int $taxClassId
	 * @param int $countryId
	 * @param int $stateId
	 * @param int $countyId
	 * @param int $cityId
	 * @param string $vatIn
	 *
	 * @return float
	 */
	public static function getTaxRateForPlace($taxClassId, $countryId, $stateId, $countyId, $cityId, $vatIn) {

		// Avoid any complications from weak typing
		$taxClassId = intval($taxClassId);
		$countryId = intval($countryId);
		$stateId = intval($stateId);
		$countyId = intval($countyId);
		$cityId = intval($cityId);

		$vatFreeCountries = ConfigboxCacheHelper::getCountryIdsVatFree();
		if (in_array($countryId, $vatFreeCountries)) {
			return 0;
		}

		if ($vatIn) {

			// Get EU countries
			$ids = ConfigboxCacheHelper::getCountryIdsEuVatArea();

			// The the store's country
			$storeRecord = ConfigboxStoreHelper::getStoreRecord();

			// It may not be set..
			if ($storeRecord->country_id) {
				//.. but if so, remove the country from the list
				$key = array_search($storeRecord->country_id, $ids);
				if ($key) {
					unset($ids[$key]);
				}
			}

			if (in_array($countryId, $ids)) {
				return 0;
			}

		}

		$taxRates = ConfigboxCacheHelper::getTaxRates();

		$taxRate = 0;

		// Take the default rate
		if (!empty($taxRates[$taxClassId]['tax_rate'])) {
			$taxRate = $taxRates[$taxClassId]['tax_rate'];
		}

		// Override with country overrite rate
		if (!empty($taxRates[$taxClassId]['country_override'][$countryId]['tax_rate'])) {
			$taxRate = $taxRates[$taxClassId]['country_override'][$countryId]['tax_rate'];
		}

		// Override with state overrite rate
		if (!empty($taxRates[$taxClassId][$countryId]['state_override'][$stateId]['tax_rate'])) {
			$taxRate = $taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['tax_rate'];
		}

		// Add county tax rate
		if (!empty($taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['county_tax_rate'][$countyId]['tax_rate'])) {
			$taxRate .= $taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['county_tax_rate'][$countyId]['tax_rate'];
		}

		// Add city tax rate
		if (!empty($taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['county_tax_rate'][$countyId]['city_tax_rate'][$cityId]['tax_rate'])) {
			$taxRate .= $taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['county_tax_rate'][$countyId]['city_tax_rate'][$cityId]['tax_rate'];
		}

		return $taxRate;

	}

	/**
	 * @param float   $price            Price of the item as entered in the backend (may be gross or net, depends on setting)
	 * @param boolean $getNet           If the price should come out net or gross
	 * @param int     $itemId           ID of either the product or element to look up the tax class ID
	 * @param int  $taxClassId See ConfigboxPrices::populateCache
	 *
	 * @return float
	 * @throws Exception
	 */
	protected static function getNormalizedPrice($price, $getNet, $itemId, $taxClassId) {
		
		if (self::pricesEnteredNet() == true && $getNet == true) {
			return $price;
		}
		elseif (self::pricesEnteredNet() == false && $getNet == false) {
			return $price;
		}
		elseif(self::pricesEnteredNet() == true && $getNet == false) {
			$taxRate = self::getTaxRate($taxClassId);
			$taxAmount = round($price / 100 * $taxRate, 2);
			return $price + $taxAmount;
		}
		elseif(self::pricesEnteredNet() == false && $getNet == true) {
			$taxRate = self::getTaxRate($taxClassId);
			// Tax amount is not rounded, because that will get us the right net from which you will
			// be able to get the right rounded tax amount and with that the matching and original gross amount.
			$taxAmount = $price / (100 + $taxRate) * $taxRate;
			$netAmount = round($price - $taxAmount, 3);
			return $netAmount;
		}
		else {
			throw new Exception('Wrong setting in pricing', 500);
		}
	
	}

	/**
	 * @param int  $productId      ID of the product
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Product base price
	 */
	public static function getProductPrice($productId, $getNet = NULL, $inBaseCurrency = true) {

		if (!$productId) {
			return 0;
		}

		if (KenedoPlatform::getName() == 'magento') {

			$sessionKey = 'cbproduct_base_price_'.$productId;

			$price = KSession::get($sessionKey, 0);

			if (!$inBaseCurrency) {
				$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
			}

			return $price;

		}

		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);

		$price = $cacheData['priceByProduct'][$productId];

		// Check for overrides
		$overrides = $cacheData['priceOverridesByProduct'][$productId];
		if (count($overrides)) {
			$groupId = ConfigboxUserHelper::getGroupId();
			foreach ($overrides as $override) {
				if ($override['group_id'] == $groupId) {
					$price = $override['price'];
				}
			}
		}

		if ($inBaseCurrency == false) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$taxClassId = $cacheData['taxClassIdByProduct'][$productId];

		return self::getNormalizedPrice($price, $getNet, $productId, $taxClassId);
		
	}

	/**
	 * @param int  $productId      ID of the product
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Product recurring base price
	 */
	public static function getProductPriceRecurring($productId, $getNet = NULL, $inBaseCurrency = true) {

		if (!$productId) {
			return 0;
		}
		
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
		
		$price = $cacheData['priceRecurringByProduct'][$productId];

		// Check for overrides
		$overrides = $cacheData['priceRecurringOverridesByProduct'][$productId];
		if (count($overrides)) {
			$groupId = ConfigboxUserHelper::getGroupId();
			foreach ($overrides as $override) {
				if ($override['group_id'] == $groupId) {
					$price = $override['price'];
				}
			}
		}

		if ($inBaseCurrency == false) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$taxClassId = $cacheData['taxClassIdByProductRecurring'][$productId];
		
		return self::getNormalizedPrice($price, $getNet, $productId, $taxClassId);
		
	}

	/**
	 * @param float $baseTotalNet
	 * @param int $productId
	 * @param bool $inBaseCurrency
	 * @return float Gross position total
	 */
	public static function getPositionPriceGross($baseTotalNet, $productId, $inBaseCurrency = true) {

		if (!$inBaseCurrency) {
			$baseTotalNet *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
		$taxClassId = $cacheData['taxClassIdByProduct'][$productId];

		$taxRate = self::getTaxRate($taxClassId);
		$taxAmount = round($baseTotalNet / 100 * $taxRate, 2);
		$grossOrderPrice = $baseTotalNet + $taxAmount;
		return $grossOrderPrice;

	}

	/**
	 * @param float $baseTotalNet
	 * @param int $productId
	 * @param bool $inBaseCurrency
	 * @return float Gross position total recurring
	 */
	public static function getPositionPriceRecurringGross($baseTotalNet, $productId, $inBaseCurrency = true) {

		if (!$inBaseCurrency) {
			$baseTotalNet *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
		$taxClassId = $cacheData['taxClassIdByProductRecurring'][$productId];

		$taxRate = self::getTaxRate($taxClassId);
		$grossOrderPrice = $baseTotalNet + round($baseTotalNet / 100 * $taxRate, 2);
		return $grossOrderPrice;

	}

	/**
	 * @param int  $productId      ID of the product
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Product base was price
	 */
	public static function getProductWasPrice($productId, $getNet = NULL, $inBaseCurrency = true) {
	
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
	
		$price = $cacheData['wasPriceByProduct'][$productId];
	
		if (!$inBaseCurrency) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$taxClassId = $cacheData['taxClassIdByProduct'][$productId];

		return self::getNormalizedPrice($price, $getNet, $productId, $taxClassId);
	
	}

	/**
	 * @param int  $productId      ID of the product
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Product recurring base was price
	 */
	public static function getProductWasPriceRecurring($productId, $getNet = NULL, $inBaseCurrency = true) {
	
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
	
		$price = $cacheData['wasPriceRecurringByProduct'][$productId];
	
		if (!$inBaseCurrency) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$taxClassId = $cacheData['taxClassIdByProductRecurring'][$productId];
	
		return self::getNormalizedPrice($price, $getNet, $productId, $taxClassId);
	
	}

	/**
	 * @param int  $elementId      ID of the element
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Element price in the current configuration
	 */
	public static function getElementPrice($elementId, $getNet = NULL, $inBaseCurrency = true) {
		
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['element_to_product'][$elementId];

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);

		if (isset($cacheData['calcModelByElement'][$elementId])) {
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$price = ConfigboxCalculation::calculate( $cacheData['calcModelByElement'][$elementId], $elementId, NULL, $selections);

			if (!$inBaseCurrency) {
				$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
			}

			$taxClassId = $cacheData['taxClassIdByElement'][$elementId];

			return self::getNormalizedPrice($price, $getNet, $elementId, $taxClassId);

		}
		else {

			// Get the current selection
			$selection = ConfigboxConfiguration::getInstance()->getSelection($elementId);
			// If there is one..
			if ($selection) {
				//.. get the question..
				$question = ConfigboxQuestion::getQuestion($elementId);
				//.. see if it has predefined answers and the selection seems to refer to it
				if (count($question->answers) && !empty($question->answers[$selection])) {
					// .. if so, get pricing for that answer
					return self::getXrefPrice($selection, $elementId, $getNet, $inBaseCurrency);
				}

			}

			return 0;

		}

	}

	/**
	 * @param int  $elementId      ID of the element
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Element recurring price in the current configuration
	 */
	public static function getElementPriceRecurring($elementId, $getNet = NULL, $inBaseCurrency = true) {
		
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['element_to_product'][$elementId];

		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
		
		if (isset($cacheData['calcModelRecurringByElement'][$elementId])) {
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$price = ConfigboxCalculation::calculate( $cacheData['calcModelRecurringByElement'][$elementId] , $elementId, NULL, $selections);
			if (!$inBaseCurrency) {
				$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
			}
			$taxClassId = $cacheData['taxClassIdByElementRecurring'][$elementId];
			return self::getNormalizedPrice($price, $getNet, $elementId, $taxClassId);
			
		}
		else {

			// Get the current selection
			$selection = ConfigboxConfiguration::getInstance()->getSelection($elementId);
			// If there is one..
			if ($selection) {
				//.. get the question..
				$question = ConfigboxQuestion::getQuestion($elementId);
				//.. see if it has predefined answers and the selection seems to refer to it
				if (count($question->answers) && !empty($question->answers[$selection])) {
					// .. if so, get pricing for that answer
					return self::getXrefPriceRecurring($selection, $elementId, $getNet, $inBaseCurrency);
				}

			}

			return 0;

		}
		
	}

	/**
	 * @param int  $answerId       ID of the answer
	 * @param int  $questionId     ID of the element (redundant but speeds things up a bit)
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Option assignment price in the current configuration
	 */
	public static function getXrefPrice($answerId, $questionId, $getNet = NULL, $inBaseCurrency = true) {

		// In case nothing specific was requested, check if we want net prices out
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		// Prepare some stuff
		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['xref_to_product'][$answerId];
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);

		// First check if we got a price calculation for the answer
		$calculationId = NULL;

		// See if there is a calculation id set
		if (isset($cacheData['calcModelByXref'][$answerId])) {
			$calculationId = $cacheData['calcModelByXref'][$answerId]['calcmodel'];
		}

		// Then check if there are overrides - mind that a calculation override can say 0 (or NULL) and it means
		// that no calculation should be used for this group.
		if (isset($cacheData['priceCalculationOverridesByXref'][$answerId])) {
			$overrides = $cacheData['priceCalculationOverridesByXref'][$answerId];
			$groupId = ConfigboxUserHelper::getGroupId();
			foreach ($overrides as $override) {
				if ($override['group_id'] == $groupId) {
					$calculationId = $override['calculation_id'];
				}
			}
		}

		// Now we know about calculations - if there is one, do the calculation..
		if ($calculationId) {

			$regardingElementId = $cacheData['calcModelByXref'][$answerId]['regardingElement'];
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$price = ConfigboxCalculation::calculate($calculationId, $regardingElementId, $answerId, $selections);

		}
		// ..otherwise check for a static price (and group price overrides)
		else {

			$price = isset($cacheData['priceByXref'][$answerId]) ? $cacheData['priceByXref'][$answerId] : 0;

			// Check for overrides
			$overrides = isset($cacheData['priceOverridesByXref'][$answerId]) ? $cacheData['priceOverridesByXref'][$answerId] : array();
			if (!empty($overrides)) {
				$groupId = ConfigboxUserHelper::getGroupId();
				foreach ($overrides as $override) {
					if ($override['group_id'] == $groupId) {
						$price = $override['price'];
					}
				}
			}

		}

		// Convert to the 'current' currency if requested
		if ($inBaseCurrency == false) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		// Get the tax class ID used in that product
		$taxClassId = $cacheData['taxClassIdByElement'][$questionId];

		// Get the price out correctly
		return self::getNormalizedPrice($price, $getNet, $questionId, $taxClassId);

	}

	/**
	 * @param int  $answerId       ID of the answer
	 * @param int  $questionId     ID of the element (redundant but speeds things up a bit)
	 * @param null|boolean $getNet True for net, false for gross, NULL to auto-determine (will depend on customer group
	 *                             setting B2B mode.
	 * @param bool $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Option assignment recurring price in the current configuration
	 */
	public static function getXrefPriceRecurring($answerId, $questionId, $getNet = NULL, $inBaseCurrency = true) {

		// In case nothing specific was requested, check if we want net prices out
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		// Prepare some stuff
		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['xref_to_product'][$answerId];
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);

		// First check if we got a price calculation for the answer
		$calculationId = NULL;

		// See if there is a calculation id set
		if (isset($cacheData['calcModelRecurringByXref'][$answerId])) {
			$calculationId = $cacheData['calcModelRecurringByXref'][$answerId]['calcmodelRecurring'];
		}

		// Then check if there are overrides
		if (isset($cacheData['priceRecurringCalculationOverridesByXref'][$answerId])) {
			$overrides = $cacheData['priceRecurringCalculationOverridesByXref'][$answerId];
			$groupId = ConfigboxUserHelper::getGroupId();
			foreach ($overrides as $override) {
				if ($override['group_id'] == $groupId) {
					$calculationId = $override['calculation_id'];
				}
			}
		}

		// Now we know about calculations - if there is one, do the calculation..
		if ($calculationId) {

			$regardingElementId = $cacheData['calcModelRecurringByXref'][$answerId]['regardingElement'];
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$price = ConfigboxCalculation::calculate($calculationId, $regardingElementId, $answerId, $selections);

		}
		// ..otherwise check for a static price (and group price overrides)
		else {

			$price = isset($cacheData['priceRecurringByXref'][$answerId]) ? $cacheData['priceRecurringByXref'][$answerId] : 0;

			// Check for overrides
			$overrides = isset($cacheData['priceRecurringOverridesByXref'][$answerId]) ? $cacheData['priceRecurringOverridesByXref'][$answerId] : array();
			if (!empty($overrides)) {
				$groupId = ConfigboxUserHelper::getGroupId();
				foreach ($overrides as $override) {
					if ($override['group_id'] == $groupId) {
						$price = $override['price'];
					}
				}
			}

		}

		// Convert to the 'current' currency if requested
		if ($inBaseCurrency == false) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		// Get the tax class ID used in that product
		$taxClassId = $cacheData['taxClassIdByElementRecurring'][$questionId];

		// Get the price out correctly
		return self::getNormalizedPrice($price, $getNet, $questionId, $taxClassId);

	}

	/**
	 * @param int          $answerId       ID of the answer
	 * @param int          $questionId     ID of the question (redundant but speeds things up a bit)
	 * @param null|bool    $getNet         True for net, false for gross, NULL to auto-determine (will depend on
	 *                                     customer group setting B2B mode.
	 * @param bool         $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Option assignment was price in the current configuration
	 */
	public static function getXrefWasPrice($answerId, $questionId, $getNet = NULL, $inBaseCurrency = true) {
		
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['xref_to_product'][$answerId];
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
	
		$price = isset($cacheData['wasPriceByXref'][$answerId]) ? $cacheData['wasPriceByXref'][$answerId] : 0;
		
		if (!$inBaseCurrency) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$taxClassId = $cacheData['taxClassIdByElement'][$questionId];
	
		return self::getNormalizedPrice($price, $getNet, $questionId, $taxClassId);
	
	}

	/**
	 * @param int          $answerId       ID of the option assignment
	 * @param int          $questionId     Element ID of the option assignment (redundant but speeds things up a bit)
	 * @param null|boolean $getNet         True for net, false for gross, NULL to auto-determine (will depend on
	 *                                     customer group setting B2B mode.
	 * @param bool         $inBaseCurrency If the price should be in base currency or in current currency
	 *
	 * @return float Option assignment recurring was price in the current configuration
	 */
	public static function getXrefWasPriceRecurring($answerId, $questionId, $getNet = NULL, $inBaseCurrency = true) {
	
		if ($getNet === NULL) {
			$getNet = ConfigboxPrices::showNetPrices();
		}

		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['xref_to_product'][$answerId];
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
	
		$price = isset($cacheData['wasPriceRecurringByXref'][$answerId]) ? $cacheData['wasPriceRecurringByXref'][$answerId] : 0;
	
		if (!$inBaseCurrency) {
			$price *= ConfigboxCurrencyHelper::getCurrency()->multiplicator;
		}

		$taxClassId = $cacheData['taxClassIdByElement'][$questionId];
	
		return self::getNormalizedPrice($price, $getNet, $questionId, $taxClassId);
	
	}

	/**
	 * @param int $questionId
	 *
	 * @return float
	 */
	public static function getElementWeight($questionId) {

		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['element_to_product'][$questionId];
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
	
		if (isset($cacheData['calcModelWeightByElement'][$questionId])) {
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$weight = ConfigboxCalculation::calculate($cacheData['calcModelWeightByElement'][$questionId], $questionId, 0, $selections);
			return $weight;
		}
		else {

			// Get the current selection
			$selection = ConfigboxConfiguration::getInstance()->getSelection($questionId);
			// If there is one..
			if ($selection) {
				//.. get the question..
				$question = ConfigboxQuestion::getQuestion($questionId);
				//.. see if it has predefined answers and the selection seems to refer to it
				if (count($question->answers) && !empty($question->answers[$selection])) {
					// .. if so, get pricing for that answer
					return self::getXrefWeight($selection);
				}

			}

			return 0;

		}
	
	}

	/**
	 * @param int $answerId
	 *
	 * @return float
	 */
	public static function getXrefWeight($answerId) {

		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['xref_to_product'][$answerId];
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
	
		if (isset($cacheData['calcModelWeightByXref'][$answerId])) {
			$calcModelId = $cacheData['calcModelByXref'][$answerId]['calcmodel'];
			$regardingElementId = $cacheData['calcModelWeightByXref'][$answerId]['regardingElement'];
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$weight = ConfigboxCalculation::calculate($calcModelId, $regardingElementId, $answerId, $selections);
		}
		else {
			$weight = isset($cacheData['weightByXref'][$answerId]) ? $cacheData['weightByXref'][$answerId] : 0;
		}
	
		return $weight;
	
	}

	/**
	 * @param int $productId
	 *
	 * @return float
	 */
	public static function getProductTaxRate($productId) {
		$taxClassId = self::getProductTaxClassId($productId);
		$taxRate = self::getTaxRate($taxClassId);
		return $taxRate;
	}

	/**
	 * @param int $productId
	 *
	 * @return float
	 */
	public static function getProductTaxClassId($productId) {
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
		return $cacheData['taxClassIdByProduct'][$productId];
	}

	/**
	 * @param int $productId
	 *
	 * @return float
	 */
	public static function getProductTaxRateRecurring($productId) {
		$taxClassId = self::getProductTaxClassIdRecurring($productId);
		$taxRate = self::getTaxRate($taxClassId);
		return $taxRate;
	}

	/**
	 * @param int $productId
	 *
	 * @return int
	 */
	public static function getProductTaxClassIdRecurring($productId) {
		$cacheData = ConfigboxCacheHelper::getPricingForProduct($productId);
		$taxClassId = $cacheData['taxClassIdByProductRecurring'][$productId];
		return $taxClassId;
	}

	/**
	 * Populates the cache
	 * @see ConfigboxPrices::$taxRateCache
	 */
	protected static function populateTaxRateCache() {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `id`, `default_tax_rate` as `taxRate` FROM `#__configbox_tax_classes`";
		$db->setQuery($query);
		$taxRates = $db->loadAssocList();
		
		foreach ($taxRates as $taxRate) {
			KenedoObserver::triggerEvent('onConfigboxGetTaxRate', array(&$taxRate['taxRate'],$taxRate['id'],NULL));
			self::$taxRateCache[$taxRate['id']] = (float)$taxRate['taxRate'];
		}
	
	}

	/**
	 * All data of the tax classes table
	 * @return string[][]
	 */
	public static function getTaxClasses() {

		if (self::$taxClasses === NULL) {
			$query = "SELECT * FROM `#__configbox_tax_classes`";
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			self::$taxClasses = $db->loadAssocList();
		}
		
		return self::$taxClasses;
	}
}