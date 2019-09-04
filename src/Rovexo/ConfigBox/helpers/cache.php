<?php
class ConfigboxCacheHelper {

	static $cache;
	static $keyPrefix;
	static $empty = '';

	public static function getKeyPrefix() {
		if (empty(self::$keyPrefix)) {
			self::$keyPrefix = md5(__FILE__);
		}
		return self::$keyPrefix;
	}

	/**
	 * @param int $calculationId
	 * @return ConfigboxCalculationData
	 */
	public static function &getCalculation($calculationId) {
		$ass = self::getAssignments();
		$productId = !empty($ass['calculation_to_product'][$calculationId]) ? $ass['calculation_to_product'][$calculationId] : 0;
		$calculations = self::getCalculationsForProduct($productId);

		$return = (!empty($calculations[$calculationId])) ? $calculations[$calculationId] : null;

		return $return;
	}

	/**
	 * Gets you a product's calculations
	 * @param int $productId
	 *
	 * @return ConfigboxCalculationData[]
	 */
	public static function &getCalculationsForProduct($productId) {

		// Check memo cache
		if (!isset(self::$cache['calculations'][$productId])) {

			// Check cache
			self::$cache['calculations'][$productId] = self::getFromCache('calculations.product_'.$productId);

			// If nothing in cache, populate it and put stuff in memo cache
			if (self::$cache['calculations'][$productId] == NULL) {
				self::writeCalculationModelCache($productId);
				self::$cache['calculations'][$productId] = self::getFromCache('calculations.product_'.$productId);
			}

		}

		return self::$cache['calculations'][$productId];

	}

	public static function writeCalculationModelCache($productId) {

		$query = "
		SELECT m.name, m.type, t.*, f.*, m.id, e.calc, m.product_id
		FROM `#__configbox_calculations` AS m
		LEFT JOIN `#__configbox_calculation_matrices` AS t ON t.id = m.id
		LEFT JOIN `#__configbox_calculation_codes` AS f ON f.id = m.id
		LEFT JOIN `#__configbox_calculation_formulas` AS e ON e.id = m.id
		WHERE m.`product_id` = ".intval($productId)."
		";

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$calculations = $db->loadObjectList('id');

		self::writeToCache('calculations.product_'.intval($productId), $calculations);

	}

	public static function getCalcMatrixData($calculationId) {

		$ass = self::getAssignments();
		$productId = (!empty($ass['calculation_to_product'][$calculationId])) ? $ass['calculation_to_product'][$calculationId] : 0;

		$empty = array();
		$matrices = self::getCalcMatricesForProduct($productId);

		return (isset($matrices[$calculationId])) ? $matrices[$calculationId] : $empty;

	}

	public static function &getCalcMatricesForProduct($productId) {

		$cacheKey = 'calcMatrices.product_'.$productId;

		if (!isset( self::$cache['calcMatrices'][$productId])) {

			// Get from cache
			self::$cache['calcMatrices'][ $productId ] = self::getFromCache($cacheKey);

			// If not in cache, write cache items
			if ( self::$cache['calcMatrices'][ $productId ] === null ) {
				self::writeCalcMatricesForProduct( $productId );
				self::$cache['calcMatrices'][ $productId ] = self::getFromCache($cacheKey);
			}

			// If there is no data, memoize an empty array
			if ( empty( self::$cache['calcMatrices'][ $productId ] ) ) {
				self::$cache['calcMatrices'][ $productId ] = array();
			}
		}

		return self::$cache['calcMatrices'][ $productId ];

	}

	protected static function writeCalcMatricesForProduct($productId) {
		$ass = self::getAssignments();
		$calculationIds = (!empty($ass['product_to_calculation'][$productId])) ? $ass['product_to_calculation'][$productId] : array();

		if (count($calculationIds)) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_calculation_matrices_data` WHERE `id` IN (".implode(',', $calculationIds).")";
			$db->setQuery($query);
			$cells = $db->loadObjectList();
			$data = array();
			foreach ($cells as $cell) {
				$data[$cell->id][] = $cell;
			}
			$cacheKey = 'calcMatrices.product_'.$productId;
			self::writeToCache($cacheKey, $data);
		}

	}

	public static function &getAllCalcMatrices() {

		if (empty(self::$cache['calcMatrixData'])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_calculation_matrices_data` ORDER BY `ordering`, `y`, `x`";
			$db->setQuery($query);
			$all = $db->loadAssocList();
			foreach ($all as $item) {
				self::$cache['calcMatrixData'][$item->id] = $item;
			}

		}

		return self::$cache['calcMatrixData'];

	}

	/**
	 *
	 * Copies translations of a text to the order records translation table
	 *
	 * @param int $orderId the id of the order record
	 * @param string $translationTable translation table, where the string originally came from (with suffix)
	 * @param int $type type of the data (see langType in fields in KenedoEntity)
	 * @param int $key key of the original item
	 * @return bool success
	 * @throws Exception If the insert query fails
	 *
	 */
	public static function copyTranslationToOrder($orderId, $translationTable,$type,$key) {

		$db = KenedoPlatform::getDb();

		$tableValue = str_replace('#__','',$translationTable);

		$values = array();
		$languages = KenedoLanguageHelper::getActiveLanguages();
		foreach ($languages as $language) {
			$text = self::getTranslation($translationTable, $type, $key, $language->tag);
			if ($text == '') {
				continue;
			}
			$values[] = "( ".intval($orderId).", '".$db->getEscaped($tableValue)."', ".intval($type).", ".intval($key).", '".$db->getEscaped($language->tag)."','".$db->getEscaped($text)."')";
		}

		if (count($values)) {

			$query = "REPLACE INTO `#__cbcheckout_order_strings` (`order_id`, `table`, `type`, `key`, `language_tag`, `text`) VALUES ".implode(",\n",$values);
			$db->setQuery($query);
			$success = $db->query();

			if ($success == false) {
				KLog::log('Error inserting translation record for type "'.$type.'". SQL error is "'.$db->getErrorMsg().'"', 'error');
				throw new Exception('Error inserting translation record record. See log for more info');
			}
		}

		return true;

	}

	/**
	 *
	 * Write translations for an item to the order records translation table
	 *
	 * @param int $orderId ID of the order record
	 * @param string $translationTable Name of the translation table, where the original translations (without table prefix placeholder)
	 * @param int $type Language type (see langType in fields in KenedoEntity)
	 * @param int $key Key of the original item
	 * @param string[] $translations Array of translations (array keys are the language tag)
	 * @return bool $success
	 * @throws Exception If the insert query fails
	 */
	public static function addTranslationsToOrder($orderId, $translationTable, $type, $key, $translations) {

		$db = KenedoPlatform::getDb();

		$tableValue = str_replace('#__','',$translationTable);

		$values = array();

		foreach ($translations as $languageTag => $text) {

			if ($text == '') {
				continue;
			}

			$values[] = "( ".intval($orderId).", '".$db->getEscaped($tableValue)."', ".intval($type).", ".intval($key).", '".$db->getEscaped($languageTag)."','".$db->getEscaped($text)."')";

		}

		if (count($values)) {
			$query = "REPLACE INTO `#__cbcheckout_order_strings` (`order_id`, `table`, `type`, `key`, `language_tag`, `text`) VALUES ".implode(",\n",$values);
			$db->setQuery($query);
			$success = $db->query();

			if ($success == false) {
				KLog::log('Error inserting translation record for type "'.$type.'". SQL error is "'.$db->getErrorMsg().'"', 'error');
				throw new Exception('Error inserting translation record record. See log for more info');
			}
		}

		return true;

	}

	/**
	 * Gets a translated text that belongs to an order record
	 * @param int $orderId Order record id
	 * @param string $translationTable The table name, where the data comes from with no suffix (e.g. configbox_strings)
	 * @param int $type Type of the data (see langType in entities)
	 * @param int $key Key of the data (e.g. product id etc)
	 * @param string $languageTag The language you want the translation. NULL for current language
	 * @return string The translated text or empty if not found
	 */
	public static function &getOrderTranslation( $orderId, $translationTable, $type, $key, $languageTag = NULL) {

		if ($languageTag === NULL) {
			$languageTag = KText::getLanguageTag();
		}

		if (!isset(self::$cache['orderTranslations'][$orderId][$languageTag][$translationTable][$type])) {
			self::writeOrderTranslations($orderId, $languageTag);
		}

		if (isset(self::$cache['orderTranslations'][$orderId][$languageTag][$translationTable][$type][$key])) {
			return self::$cache['orderTranslations'][$orderId][$languageTag][$translationTable][$type][$key];
		}
		else {
			return self::$empty;
		}

	}

	public static function writeOrderTranslations($orderId, $languageTag) {

		if (!isset(self::$cache['orderTranslations'][$orderId][$languageTag])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__cbcheckout_order_strings` WHERE `order_id` = ".intval($orderId)." AND `language_tag` = '".$db->getEscaped($languageTag)."'";
			$db->setQuery($query);
			$items = $db->loadAssocList();
			foreach ($items as $item) {
				self::$cache['orderTranslations'][$orderId][$languageTag][$item['table']][$item['type']][$item['key']] = $item['text'];
			}

			unset($items);

		}

	}

	/**
	 * @param string $translationTable (incl. #__)
	 * @param int $type
	 * @param int $key
	 * @param string|null $languageTag
	 * @return string
	 */
	public static function getTranslation($translationTable, $type, $key, $languageTag = NULL) {

		if ($languageTag === NULL) {
			$languageTag = KText::getLanguageTag();
		}

		// Legacy, remove in 2.7
		if ($translationTable == '#__cbcheckout_strings') {
			KLog::logLegacyCall('Do not get translations from cbcheckout_strings, use configbox_strings instead. Some type ids have changed, see update notes on 2.6.18');
		}

		// Table names in cache file are stored as integers (desparate attempt to use SplFixedArray, abandoned idea, yeah well)
		$translationTables['#__configbox_strings'] = 0;
		$tableId = 0;

		if (!isset(self::$cache['translations'][$languageTag])) {

			$cacheKey = 'translations.'.$languageTag;

			// Get from cache
			self::$cache['translations'][$languageTag] = self::getFromCache($cacheKey);

			// If NULL in cache
			if (self::$cache['translations'][$languageTag] === NULL) {
				self::writeTranslationCache($languageTag);
				self::$cache['translations'][$languageTag] = self::getFromCache($cacheKey);
			}

		}


		if ( isset(self::$cache['translations'][$languageTag][$tableId][$type][$key]) ) {
			return self::$cache['translations'][$languageTag][$tableId][$type][$key];
		}
		else {
			return '';
		}

	}

	protected static function writeTranslationCache($languageTag) {

		$cacheKey = 'translations.'.$languageTag;

		$db = KenedoPlatform::getDb();

		$translations = array();

		$query = "SELECT * FROM `#__configbox_strings` WHERE `language_tag` = '".$db->getEscaped($languageTag)."'";
		$db->setQuery($query);
		$items = $db->loadAssocList();
		if ($items) {
			foreach ($items as &$t) {
				if (!empty($t['text']))	$translations[0][$t['type']][$t['key']] = $t['text'];
			}
		}

		return self::writeToCache($cacheKey, $translations);

	}

	static public function &getPaymentMethodAssignments() {

		if (!isset(self::$cache['paymentMethodAssignments'])) {

			// Get from cache
			self::$cache['paymentMethodAssignments'] = self::getFromCache('paymentMethodAssignments');

			// If NULL in cache
			if (self::$cache['paymentMethodAssignments'] === NULL) {
				self::writePaymentMethodAssignments();
				self::$cache['paymentMethodAssignments'] = self::getFromCache('paymentMethodAssignments');
			}

		}
		return self::$cache['paymentMethodAssignments'];
	}

	static public function writePaymentMethodAssignments() {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT a.`payment_id`, a.`country_id` 
		FROM `#__configbox_xref_country_payment_method` AS a 
		LEFT JOIN `#__configbox_countries` AS c ON a.country_id = c.id
		LEFT JOIN `#__configbox_payment_methods` AS p ON a.payment_id = p.id
		WHERE p.`published` = '1' && c.`published` = '1'";
		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			$cache['payment_to_country'][$item['payment_id']][$item['country_id']] = $item['country_id'];
			$cache['country_to_payment'][$item['country_id']][$item['payment_id']] = $item['payment_id'];
		}

		return self::writeToCache('paymentMethodAssignments', $cache);

	}

	static public function &getAssignments() {

		if (!isset(self::$cache['assignments'])) {

			// Get from cache
			self::$cache['assignments'] = self::getFromCache('assignments');

			// If NULL in cache
			if (self::$cache['assignments'] === NULL) {
				self::writeAssignments();
				self::$cache['assignments'] = self::getFromCache('assignments');
			}

		}
		return self::$cache['assignments'];
	}

	static protected function writeAssignments() {

		$db = KenedoPlatform::getDb();

		$query = "SELECT `id`, `product_id` FROM `#__configbox_calculations`";
		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			$cache['product_to_calculation'][intval($item['product_id'])][$item['id']] = $item['id'];
			$cache['calculation_to_product'][intval($item['id'])] = $item['product_id'];
		}

		$query = "SELECT `id` AS `rate_id`, `zone_id` FROM `#__configbox_shipping_methods` WHERE `published` = '1' ORDER BY `price` ASC";
		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			$cache['zone_to_shippingmethod'][$item['zone_id']][$item['rate_id']] = $item['rate_id'];
			$cache['shippingmethod_to_zone'][$item['rate_id']][$item['zone_id']] = $item['zone_id'];
		}

		$query = "
		SELECT a.`zone_id`, a.`country_id`
		FROM `#__configbox_xref_country_zone` AS a
		LEFT JOIN `#__configbox_countries` AS c ON a.country_id = c.id
		LEFT JOIN `#__configbox_zones` AS z ON a.zone_id = z.id
		WHERE c.`published` = '1'";
		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			$cache['zone_to_country'][$item['zone_id']][$item['country_id']] = $item['country_id'];
			$cache['country_to_zone'][$item['country_id']][$item['zone_id']] = $item['zone_id'];
		}

		$query = "
		SELECT states.country_id, states.id AS state_id, counties.id AS county_id, cities.id AS city_id
		FROM `#__configbox_states` AS states
		LEFT JOIN `#__configbox_counties`   AS counties ON counties.state_id = states.id
		LEFT JOIN `#__configbox_cities`     AS cities   ON cities.county_id = counties.id
		";
		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			if ($item['city_id']) {
				$cache['city_to_county'][$item['city_id']] = $item['county_id'];
				$cache['city_to_state'][$item['city_id']] = $item['state_id'];
				$cache['city_to_country'][$item['city_id']] = $item['country_id'];
			}
			if ($item['county_id']) {
				$cache['county_to_state'][$item['county_id']] = $item['state_id'];
				$cache['city_to_country'][$item['county_id']] = $item['country_id'];
			}

		}


		$query = "
		SELECT x.`listing_id`, x.`product_id` 
		FROM `#__configbox_xref_listing_product` AS x
		LEFT JOIN `#__configbox_listings` AS l ON l.id = x.listing_id
		LEFT JOIN `#__configbox_products`  AS p ON p.id = x.product_id
		WHERE p.published = '1' AND l.published = '1'
		ORDER BY x.`ordering`";
		$db->setQuery($query);
		$items = $db->loadAssocList();
		foreach ($items as $item) {
			$cache['listing_to_product'][$item['listing_id']][$item['product_id']] = $item['product_id'];
			$cache['product_to_listing'][$item['product_id']][$item['listing_id']] = $item['listing_id'];
		}

		$query = "
		SELECT e.id AS element_id, c.id AS page_id, c.product_id AS product_id
		FROM `#__configbox_elements` AS e
		LEFT JOIN `#__configbox_pages` AS c ON c.id = e.page_id
		LEFT JOIN `#__configbox_products` AS p ON p.id = c.product_id
		WHERE e.published = '1' AND c.published = '1' AND p.published = '1'
		ORDER BY p.id, c.ordering, e.ordering";

		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			$cache['element_to_product'][$item['element_id']] = $item['product_id'];
			$cache['element_to_page'][$item['element_id']] = $item['page_id'];
			$cache['product_to_element'][$item['product_id']][$item['element_id']] = $item['element_id'];
			$cache['page_to_element'][$item['page_id']][$item['element_id']] = $item['element_id'];
		}

		$query = "
		SELECT c.id AS page_id, c.product_id AS product_id
		FROM `#__configbox_pages` AS c
		WHERE c.published = '1'
		ORDER BY c.ordering";
		$db->setQuery($query);
		$items = $db->loadAssocList();
		foreach ($items as $item) {
			$cache['product_to_page'][$item['product_id']][$item['page_id']] = $item['page_id'];
			$cache['page_to_product'][$item['page_id']] = $item['product_id'];
		}

		$query = "	
		SELECT xref.id AS xref_id, o.id AS option_id, xref.element_id AS element_id, c.id AS page_id, c.product_id AS product_id
		FROM `#__configbox_xref_element_option` AS xref
		LEFT JOIN `#__configbox_options` AS o ON o.id = xref.option_id
		LEFT JOIN `#__configbox_elements` AS e ON e.id = xref.element_id
		LEFT JOIN `#__configbox_pages` AS c ON c.id = e.page_id
		LEFT JOIN `#__configbox_products` AS p ON p.id = c.product_id
		WHERE xref.published = '1' AND e.published = '1' AND c.published = '1' AND p.published = '1'
		ORDER BY p.id, c.ordering, e.ordering, xref.ordering";

		$db->setQuery($query);
		$items = $db->loadAssocList();

		foreach ($items as $item) {
			$cache['xref_to_element'][$item['xref_id']] = $item['element_id'];
			$cache['element_to_xref'] [$item['element_id']] [$item['xref_id']] = $item['xref_id'];

			$cache['xref_to_product'][$item['xref_id']] = $item['product_id'];
			$cache['product_to_xref'][$item['product_id']][$item['xref_id']] = $item['xref_id'];

			$cache['xref_to_page'][$item['xref_id']] = $item['page_id'];
			$cache['page_to_xref'][$item['page_id']][$item['xref_id']] = $item['xref_id'];
		}

		return self::writeToCache('assignments', $cache);

	}

	/**
	 * This method adds info to a product data object (like localized fields, prices, price module settings etc)
	 * to make it ready for easy use in templates
	 *
	 * @param object $product
	 */
	public static function augmentProduct(&$product) {

		// Run post-caching prop methods
		$model = KenedoModel::getModel('ConfigboxModelAdminproducts');
		$props = $model->getProperties();
		foreach ($props as $prop) {
			$prop->appendDataForPostCaching($product);
		}

		$filenameDefault = CbSettings::getInstance()->get('defaultprodimage');

		// Overwrite product image with default product image if there is none
		if (!$product->prod_image && $filenameDefault) {
			$product->prod_image_href = CONFIGBOX_URL_DEFAULT_IMAGES.'/'.$filenameDefault;
			$product->prod_image_path = CONFIGBOX_DIR_DEFAULT_IMAGES.'/'.$filenameDefault;
		}

		// LEGACY (remove in CB 4)
		$product->imagesrc = $product->prod_image_href;
		$product->listing_id = NULL;

		if (count($product->product_listing_ids)) {
			$product->listing_id = $product->product_listing_ids[0];
		}

		// Set the price label (mind the camel case notation)
		$product->priceLabel = ($product->pricelabel) ? $product->pricelabel : KText::_('Price');
		$product->priceLabelRecurring = ($product->pricelabel_recurring) ? $product->pricelabel_recurring : KText::_('Recurring Price');

		// Deal with tax rates and prices - START
		$product->taxRate = ConfigboxPrices::getProductTaxRate($product->id);
		$product->taxRateRecurring = ConfigboxPrices::getProductTaxRateRecurring($product->id);

		$product->basePriceNet = ConfigboxPrices::getProductPrice($product->id,true,true);
		$product->basePriceGross = ConfigboxPrices::getProductPrice($product->id,false,true);
		$product->basePriceTax = $product->basePriceGross - $product->basePriceNet;

		$product->basePriceRecurringNet = ConfigboxPrices::getProductPriceRecurring($product->id,true,true);
		$product->basePriceRecurringGross = ConfigboxPrices::getProductPriceRecurring($product->id,false,true);
		$product->basePriceRecurringTax = $product->basePriceRecurringGross - $product->basePriceRecurringNet;

		$product->priceNet = ConfigboxPrices::getProductPrice($product->id,true,false);
		$product->priceGross = ConfigboxPrices::getProductPrice($product->id,false,false);
		$product->priceTax = $product->priceGross - $product->priceNet;

		$product->priceRecurringNet = ConfigboxPrices::getProductPriceRecurring($product->id,true,false);
		$product->priceRecurringGross = ConfigboxPrices::getProductPriceRecurring($product->id,false,false);
		$product->priceRecurringTax = $product->priceRecurringGross - $product->priceRecurringNet;

		// These two are always in the current tax mode and the selected currency
		$product->price = ConfigboxPrices::getProductPrice($product->id, ConfigboxPrices::showNetPrices(), false);
		$product->priceRecurring = ConfigboxPrices::getProductPriceRecurring($product->id, ConfigboxPrices::showNetPrices(), false);


		$product->wasPriceNet = ConfigboxPrices::getProductWasPrice($product->id,true,false);
		$product->wasPriceGross = ConfigboxPrices::getProductWasPrice($product->id,false,false);
		$product->wasPriceTax = $product->wasPriceGross - $product->wasPriceNet;

		$product->wasPriceRecurringNet = ConfigboxPrices::getProductWasPriceRecurring($product->id,true,false);
		$product->wasPriceRecurringGross = ConfigboxPrices::getProductWasPriceRecurring($product->id,false,false);
		$product->wasPriceRecurringTax = $product->wasPriceRecurringGross - $product->wasPriceRecurringNet;

		// These two are always in the current tax mode and the selected currency
		$product->wasPrice = ConfigboxPrices::getProductWasPrice($product->id, ConfigboxPrices::showNetPrices(), false);
		$product->wasPriceRecurring = ConfigboxPrices::getProductWasPriceRecurring($product->id, ConfigboxPrices::showNetPrices(), false);

		// Unset price variables that could be misleading in templates
		unset($product->baseprice);
		unset($product->baseprice_recurring);

		// Deal with taxRate and prices - END

		// Set the custom price text for the regular price
		if ($product->custom_price_text) {
			$matches = array();
			$regEx = '/\[(.*?)\]/';
			preg_match_all($regEx, $product->custom_price_text, $matches);
			if (isset($matches[1][0])) {
				$search = $matches[0][0];
				$price = (float)$matches[1][0] * ConfigboxCurrencyHelper::getCurrency()->multiplicator;
				$price = $price + ($price / 100 * $product->taxRate);
				$output = cbprice($price);
				$product->custom_price_text = str_replace($search,$output,$product->custom_price_text);
			}
		}

		// Set the custom price text for the recurring price
		if ($product->custom_price_text_recurring) {
			$matches = array();
			$regEx = '/\[(.*?)\]/';
			preg_match_all($regEx, $product->custom_price_text_recurring, $matches);
			if (isset($matches[1][0])) {
				$search = $matches[0][0];
				$price = (float)$matches[1][0] * ConfigboxCurrencyHelper::getCurrency()->multiplicator;
				$price = $price + ($price / 100 * $product->taxRate);
				$output = cbprice($price);
				$product->custom_price_text_recurring = str_replace($search,$output,$product->custom_price_text_recurring);
			}
		}

		// Determine if reviews shall be shown
		$product->showReviews = ( $product->enable_reviews == 1 or ( $product->enable_reviews == 2 and CbSettings::getInstance()->get('enable_reviews_products') == 1) );

		// Determine the first page id (mainly used for the 'configure product' link),
		$assignments = self::getAssignments();
		$pages = isset($assignments['product_to_page'][$product->id]) ? $assignments['product_to_page'][$product->id] : array();

		$product->isConfigurable = (count($pages)) ? true : false;
		$product->firstPageId = (count($pages)) ? array_shift($pages) : NULL;

		// Deal with price module settings - START
		$ps = array (
			'pm_show_regular_first' 			=> $product->pm_show_regular_first,
			'pm_show_delivery_options' 			=> $product->pm_show_delivery_options,
			'pm_show_payment_options'			=> $product->pm_show_payment_options,
			'pm_show_net_in_b2c' 				=> $product->pm_show_net_in_b2c,
			'pm_regular_show_prices' 			=> $product->pm_regular_show_prices,
			'pm_regular_show_categories' 		=> $product->pm_regular_show_categories,
			'pm_regular_show_elements' 			=> $product->pm_regular_show_elements,
			'pm_regular_show_elementprices' 	=> $product->pm_regular_show_elementprices,
			'pm_regular_expand_categories' 		=> $product->pm_regular_expand_categories,
			'pm_regular_show_taxes'				=> $product->pm_regular_show_taxes,
			'pm_regular_show_cart_button'		=> $product->pm_regular_show_cart_button,
			'pm_recurring_show_overview'		=> $product->pm_recurring_show_overview,
			'pm_recurring_show_prices' 			=> $product->pm_recurring_show_prices,
			'pm_recurring_show_categories' 		=> $product->pm_recurring_show_categories,
			'pm_recurring_show_elements' 		=> $product->pm_recurring_show_elements,
			'pm_recurring_show_elementprices' 	=> $product->pm_recurring_show_elementprices,
			'pm_recurring_expand_categories' 	=> $product->pm_recurring_expand_categories,
			'pm_recurring_show_taxes'			=> $product->pm_recurring_show_taxes,
			'pm_recurring_show_cart_button'		=> $product->pm_recurring_show_cart_button,
		);

		foreach ($ps as $key=>&$value) {
			if (($value == 2 && $key != 'pm_regular_expand_categories' && $key != 'pm_recurring_expand_categories') or ($value == 3 && $key == 'pm_regular_expand_categories')) {
				$product->$key = constant('CONFIGBOX_'.strtoupper($key));
			}

			if (($value == 2 && $key != 'pm_regular_expand_categories' && $key != 'pm_recurring_expand_categories') or ($value == 3 && $key == 'pm_recurring_expand_categories')) {
				$product->$key = constant('CONFIGBOX_'.strtoupper($key));
			}

		}

		// Deal with price module settings - END

	}


	/**
	 * @param int $productId
	 * @return ConfigboxProductData
	 * @throws Exception
	 */
	public static function getProduct($productId) {

		if (!isset(self::$cache['products'][$productId])) {

			$product = self::getFromCache('products.'.$productId);

			if ($product === NULL) {
				$model = KenedoModel::getModel('ConfigboxModelAdminproducts');
				$product = $model->getRecord($productId);
				self::writeToCache('products.'.$productId, $product);
			}

			self::augmentProduct($product);

			self::$cache['products'][$productId] = $product;

		}

		return self::$cache['products'][$productId];

	}

	/**
	 * Returns product data of one product (if $productId was supplied) or all products (with no $productId)
	 * @param int $productId
	 * @return ConfigboxProductData|ConfigboxProductData[] $productData Object with product data or array of objects all products
	 * @deprecated Load individual product data using getProduct() instead
	 */
	public static function &getProductData($productId = NULL) {

		if ($productId) {
			return self::getProduct($productId);
		}

		if (!isset( self::$cache['products'] )) {
			// Get from cache
			self::$cache['products'] = self::getFromCache('products');

			// If NULL in cache
			if (self::$cache['products'] == NULL) {
				self::writeProductDataCache();
				self::$cache['products'] = self::getFromCache('products');
			}

			// Augment with non-cacheable data
			foreach (self::$cache['products'] as $product) {
				self::augmentProduct($product);
			}

		}

		if ($productId) {
			return self::$cache['products'][$productId];
		}
		else {
			return self::$cache['products'];
		}

	}

	protected static function writeProductDataCache() {

		$model = KenedoModel::getModel('ConfigboxModelAdminproducts');

		// Prepare filters for getting published elements only
		$filters = array(
			'adminproducts.published'=>1,
		);
		$records = $model->getRecords($filters);
		$data = array();

		// Use product ids as array keys
		foreach ($records as $record) {
			$data[$record->id] = $record;
		}

		// Write the cache entry
		self::writeToCache('products', $data);

		return true;
	}

	public static function getProductDetailPanes($productId) {

		if (empty(self::$cache['productDetailPanes'][$productId])) {

			$model = KenedoModel::getModel('ConfigboxModelAdminproductdetailpanes');

			$filters = array(
				'adminproductdetailpanes.product_id' => $productId,
			);

			$ordering = array('propertyName'=>'adminproductdetailpanes', 'direction'=>'ASC');

			$records = $model->getRecords($filters, array(), $ordering);

			foreach ($records as $record) {
				// LEGACY (remove in CB 4)
				$record->usesHeadingIcon = !empty($record->heading_icon_filename);
				$record->headingIconSrc = ($record->usesHeadingIcon) ? $record->heading_icon_filename_href : '';
			}

			self::$cache['productDetailPanes'][$productId] = $records;

		}

		return self::$cache['productDetailPanes'][$productId];

	}

	protected static function writeElementDataCache() {

		$model = KenedoModel::getModel('ConfigboxModelAdminelements');

		// Prepare filters for getting published elements only
		$filters = array(
			'adminproducts.published'=>1,
			'adminpages.published'=>1,
			'adminelements.published'=>1,
		);

		$ordering = array(
			array(
				'propertyName' => 'adminpages.ordering',
				'direction' => 'ASC',
			),
			array(
				'propertyName' => 'ordering',
				'direction' => 'ASC',
			),
		);

		$records = $model->getRecords($filters, array(), $ordering);
		$data = array();
		// Group them by product and put element ids in array keys
		foreach ($records as $record) {
			$data[$record->joinedby_page_id_to_adminpages_product_id][$record->id] = $record;
		}

		// Write each product's elements in a separate cache entry
		foreach ($data as $productId=>&$dataItem) {
			self::writeToCache('elements.product_'.$productId, $dataItem);
		}

		return true;
	}

	public static function getQuestion($id) {

	}

	/**
	 * @param int $elementId
	 * @return object|NULL
	 * @throws Exception
	 */
	public static function getElementData($elementId) {

		$assignments = self::getAssignments();

		if (!isset($assignments['element_to_product'][$elementId])) {
			$return = NULL;
			return $return;
		}

		$productId = $assignments['element_to_product'][$elementId];

		if (!$productId) {
			$return = NULL;
			return $return;
		}

		if (!isset( self::$cache['elements'][$productId][$elementId] )) {
			self::getElementsForProduct($productId);
		}

		return self::$cache['elements'][$productId][$elementId];

	}

	/**
	 * @param int $productId
	 * @return object[]|NULL
	 * @throws Exception
	 */
	public static function getElementsForProduct($productId) {

		if (!isset(self::$cache['elements'][$productId])) {

			// Get from cache
			self::$cache['elements'][$productId] = self::getFromCache('elements.product_'.$productId);

			// If NULL in cache
			if (self::$cache['elements'][$productId] === NULL) {
				self::writeElementDataCache();
				self::$cache['elements'][$productId] = self::getFromCache('elements.product_'.$productId);
			}

			if (empty(self::$cache['elements'][$productId])) {
				$return = NULL;
				return $return;
			}

			// Run the post-caching append methods of each prop
			$model = KenedoModel::getModel('ConfigboxModelAdminelements');
			$props = $model->getProperties();

			foreach (self::$cache['elements'][$productId] as $element) {

				foreach ($props as $prop) {
					$prop->appendDataForPostCaching($element);
				}

			}

		}

		return self::$cache['elements'][$productId];

	}

	public static function &getAnswerData($answerId) {

		$assignments = self::getAssignments();
		$productId = $assignments['xref_to_product'][$answerId];

		if (!isset( self::$cache['xrefs'][$productId][$answerId] )) {
			self::getAnswersForProduct($productId);
		}

		return self::$cache['xrefs'][$productId][$answerId];

	}

	public static function &getAnswersForProduct($productId) {

		if (!isset( self::$cache['xrefs'][$productId])) {

			// Get from cache
			self::$cache['xrefs'][$productId] = self::getFromCache('xrefs.product_'.$productId);

			if (self::$cache['xrefs'][$productId] === NULL) {
				self::writeAnswerDataCache($productId);
				self::$cache['xrefs'][$productId] = self::getFromCache('xrefs.product_'.$productId);
			}

			if (empty(self::$cache['xrefs'][$productId])) {
				$return = NULL;
				return $return;
			}

			// Run post-caching appends from model props
			$optionModel = KenedoModel::getModel('ConfigboxModelAdminoptions');
			$optionProps = $optionModel->getProperties();

			$xrefModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
			$xrefProps = $xrefModel->getProperties();

			foreach (self::$cache['xrefs'][$productId] as $id=>$xref) {

				foreach ($optionProps as $prop) {
					$prop->appendDataForPostCaching($xref);
				}
				unset($prop);

				foreach ($xrefProps as $prop) {
					$prop->appendDataForPostCaching($xref);
				}

			}

		}

		return self::$cache['xrefs'][$productId];

	}

	protected static function writeAnswerDataCache($productId) {

		// Prepare filters for getting xref data
		$filters = array(
			'adminproducts.id'=>$productId,
			'adminpages.published'=>1,
			'adminelements.published'=>1,
		);

		// And ordering
		$ordering = array(
			'propertyName'	=> 'ordering',
			'direction'		=> 'ASC',
		);

		// Get the product's answers
		$model = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$records = $model->getRecords($filters, array(), $ordering);

		// Key-sort the xrefs by ID
		$answerData = array();
		foreach ($records as $record) {
			$answerData[$record->id] = $record;
		}
		unset($records, $record);

		// Get options data
		$optionModel = KenedoModel::getModel('ConfigboxModelAdminoptions');
		$records = $optionModel->getRecords(array(), array(), array());

		// Key-sort the options by ID
		$optionData = array();
		foreach ($records as $record) {
			$optionData[$record->id] = $record;
		}
		unset($records, $record);

		// Merge related option data into each xref
		foreach ($answerData as $answerDataItem) {
			if (isset($optionData[$answerDataItem->option_id])) {
				foreach ($optionData[$answerDataItem->option_id] as $key=>$value) {
					if ($key == 'id') continue;
					$answerDataItem->$key = $value;
				}
			}
		}

		return self::writeToCache('xrefs.product_'.$productId, $answerData);

	}

	public static function &getPricingForProduct($productId) {

		if (!isset( self::$cache['pricing'][$productId])) {

			// Get from cache
			self::$cache['pricing'][$productId] = self::getFromCache('pricing.product_'.$productId);

			// If empty, write the cache items and fetch them
			if (self::$cache['pricing'][$productId] === NULL) {
				self::writePricingCache($productId);
				self::$cache['pricing'][$productId] = self::getFromCache('pricing.product_'.$productId);
			}

			if (empty(self::$cache['pricing'][$productId])) {
				$return = NULL;
				return $return;
			}

		}

		return self::$cache['pricing'][$productId];

	}

	/**
	 * Populates the cache
	 * @param int $productId
	 * @see ConfigboxPrices
	 */
	protected static function writePricingCache($productId) {

		KLog::start('populatePriceCache');

		$db = KenedoPlatform::getDb();

		$query = "
			SELECT 	xref.element_id AS element_id, xref.id AS xref_id, 
					xref.calcmodel AS xref_calcmodel, xref.calcmodel_recurring AS xref_calcmodel_recurring, 
					xref.calcmodel_weight AS xref_calcmodel_weight, 
					xref.price_calculation_overrides, xref.price_recurring_calculation_overrides,
					o.price AS xref_price, o.price_recurring AS xref_price_recurring, o.weight AS xref_weight,
					o.price_overrides AS xref_price_overrides, o.price_recurring_overrides AS xref_price_recurring_overrides,
					o.was_price AS xref_was_price, o.was_price_recurring AS xref_was_price_recurring
					
			FROM `#__configbox_xref_element_option` AS xref
			LEFT JOIN `#__configbox_options` AS o ON o.id = xref.option_id
			LEFT JOIN `#__configbox_elements` AS e ON e.id = xref.element_id
			LEFT JOIN `#__configbox_pages` AS p ON p.id = e.page_id
			
			WHERE p.product_id = ".intval($productId);


		$db->setQuery($query);
		$items = $db->loadAssocList();

		$cache = array();

		foreach ($items as &$item) {

			$data = json_decode($item['price_calculation_overrides'], true);
			if (!empty($data)) {
				$cache['priceCalculationOverridesByXref'][$item['xref_id']] = $data;
			}

			$data = json_decode($item['price_recurring_calculation_overrides'], true);
			if (!empty($data)) {
				$cache['priceRecurringCalculationOverridesByXref'][$item['xref_id']] = $data;
			}

			if ($item['xref_calcmodel']) {
				$cache['calcModelByXref'][$item['xref_id']]['calcmodel'] = (int)$item['xref_calcmodel'];
				$cache['calcModelByXref'][$item['xref_id']]['regardingElement'] = (int)$item['element_id'];
			}

			if ($item['xref_calcmodel_recurring']) {
				$cache['calcModelRecurringByXref'][$item['xref_id']]['calcmodelRecurring'] = (int)$item['xref_calcmodel_recurring'];
				$cache['calcModelRecurringByXref'][$item['xref_id']]['regardingElement'] = (int)$item['element_id'];
			}

			if ($item['xref_calcmodel_weight']) {
				$cache['calcModelWeightByXref'][$item['xref_id']]['calcmodelWeight'] = (int)$item['xref_calcmodel_weight'];
				$cache['calcModelWeightByXref'][$item['xref_id']]['regardingElement'] = (int)$item['element_id'];
			}

			if ($item['xref_weight'] != 0) {
				$cache['weightByXref'][$item['xref_id']] = (float)$item['xref_weight'];
			}

			if ($item['xref_price'] != 0) {
				$cache['priceByXref'][$item['xref_id']] = (float)$item['xref_price'];
			}

			if ($item['xref_price_recurring'] != 0) {
				$cache['priceRecurringByXref'][$item['xref_id']] = (float)$item['xref_price_recurring'];
			}

			$data = json_decode($item['xref_price_overrides'], true);
			if (!empty($data)) {
				$cache['priceOverridesByXref'][$item['xref_id']] = $data;
			}

			$data = json_decode($item['xref_price_recurring_overrides'], true);
			if (!empty($data)) {
				$cache['priceRecurringOverridesByXref'][$item['xref_id']] = $data;
			}

			if ($item['xref_was_price'] != 0) {
				$cache['wasPriceByXref'][$item['xref_id']] = (float)$item['xref_was_price'];
			}

			if ($item['xref_was_price_recurring'] != 0) {
				$cache['wasPriceRecurringByXref'][$item['xref_id']] = (float)$item['xref_was_price_recurring'];
			}

		}

		$query = "
		SELECT 	
			e.id AS element_id, e.calcmodel, e.calcmodel_recurring, e.calcmodel_weight, 
			p.id AS product_id, 
			p.taxclass_id, p.taxclass_recurring_id,
			p.baseprice AS product_price, p.baseprice_recurring AS product_price_recurring
			
		FROM `#__configbox_elements` AS e
		LEFT JOIN `#__configbox_pages` AS c ON c.id =  e.page_id
		LEFT JOIN `#__configbox_products` AS p ON p.id = c.product_id
		WHERE p.id = ".intval($productId);
		$db->setQuery($query);
		$mixInElements = $db->loadAssocList();

		foreach ($mixInElements as $item) {
			if ($item['calcmodel']) {
				$cache['calcModelByElement'][$item['element_id']] = (int)$item['calcmodel'];
			}
			if ($item['calcmodel_recurring']) {
				$cache['calcModelRecurringByElement'][$item['element_id']] = (int)$item['calcmodel_recurring'];
			}
			if ($item['calcmodel_weight']) {
				$cache['calcModelWeightByElement'][$item['element_id']] = (int)$item['calcmodel_weight'];
			}
			if ($item['calcmodel_weight']) {
				$cache['calcModelWeightByElement'][$item['element_id']] = (int)$item['calcmodel_weight'];
			}
			$cache['taxClassIdByElement'][$item['element_id']] = $item['taxclass_id'];
			$cache['taxClassIdByElementRecurring'][$item['element_id']] = $item['taxclass_recurring_id'];

		}

		$query = "
		SELECT 
		p.id AS product_id, 
		p.baseprice AS product_price, 
		p.baseprice_recurring AS product_price_recurring,
		p.was_price AS product_was_price, 
		p.was_price_recurring AS product_was_price_recurring, 
		p.taxclass_id, 
		p.taxclass_recurring_id,
		p.baseprice_overrides,
		p.baseprice_recurring_overrides
		
		FROM `#__configbox_products` AS p
		WHERE p.id = ".intval($productId);

		$db->setQuery($query);
		$mixInProducts = (array)$db->loadAssocList();

		foreach ($mixInProducts as $item) {
			$cache['taxClassIdByProduct'][$item['product_id']] = $item['taxclass_id'];
			$cache['taxClassIdByProductRecurring'][$item['product_id']] = $item['taxclass_recurring_id'];
			$cache['priceByProduct'][$item['product_id']] = (float)$item['product_price'];
			$cache['priceRecurringByProduct'][$item['product_id']] = (float)$item['product_price_recurring'];
			$cache['wasPriceByProduct'][$item['product_id']] = (float)$item['product_was_price'];
			$cache['wasPriceRecurringByProduct'][$item['product_id']] = (float)$item['product_was_price_recurring'];
			$cache['priceOverridesByProduct'][$item['product_id']] = json_decode($item['baseprice_overrides'], true);
			$cache['priceRecurringOverridesByProduct'][$item['product_id']] = json_decode($item['baseprice_recurring_overrides'], true);
		}

		ConfigboxCacheHelper::writeToCache('pricing.product_'.$productId, $cache);

		KLog::stop('populatePriceCache');

	}

	public static function getGroupData($groupId) {

		if (!isset( self::$cache['groups'][$groupId])) {

			// Get from cache
			self::$cache['groups'] = self::getFromCache('groups');

			// If empty, write the cache items and fetch them
			if (self::$cache['groups'] === NULL) {
				self::writeGroupCache();
				self::$cache['groups'] = self::getFromCache('groups');
			}

			if (!empty(self::$cache['groups'])) {

				// Run the post-caching append methods of each prop
				$model = KenedoModel::getModel('ConfigboxModelAdmincustomergroups');
				$props = $model->getProperties();

				foreach (self::$cache['groups'] as $data) {

					foreach ($props as $prop) {
						$prop->appendDataForPostCaching($data);
					}

				}

			}

		}

		return self::$cache['groups'][$groupId];

	}

	protected static function writeGroupCache() {

		$model = KenedoModel::getModel('ConfigboxModelAdmincustomergroups');

		$records = $model->getRecords();

		$data = array();
		foreach ($records as $record) {
			$data[$record->id] = $record;
		}

		self::writeToCache('groups', $data);

	}

	/**
	 * IDs of countries marked as VAT free (mind just those MARKED VAT free in the country data, EU VAT rules
	 * aren't considered)
	 * @return int[]
	 */
	public static function getCountryIdsVatFree() {

		if (!isset( self::$cache['countryIdsVatFree'])) {

			// Get from cache
			self::$cache['countryIdsVatFree'] = self::getFromCache('countryIdsVatFree');

			if (self::$cache['countryIdsVatFree'] === null) {

				$db = KenedoPlatform::getDb();
				$query = "SELECT `id` FROM `#__configbox_countries` WHERE `vat_free` = '1'";
				$db->setQuery($query);
				$ids = $db->loadResultList();

				self::writeToCache('countryIdsVatFree', $ids);
				self::$cache['countryIdsVatFree'] = $ids;

			}

		}

		return self::$cache['countryIdsVatFree'];

	}

	/**
	 * IDs of countries that are within the EU VAT area
	 * @return int[]
	 */
	public static function getCountryIdsEuVatArea() {

		if (!isset( self::$cache['countryIdsEuVatArea'])) {

			// Get from cache
			self::$cache['countryIdsEuVatArea'] = self::getFromCache('countryIdsEuVatArea');

			if (self::$cache['countryIdsEuVatArea'] === null) {

				$db = KenedoPlatform::getDb();
				$query = "SELECT `id` FROM `#__configbox_countries` WHERE `in_eu_vat_area` = '1'";
				$db->setQuery($query);
				$ids = $db->loadResultList();

				self::writeToCache('countryIdsEuVatArea', $ids);
				self::$cache['countryIdsEuVatArea'] = $ids;

			}

		}

		return self::$cache['countryIdsEuVatArea'];

	}

	public static function &getTaxRates() {

		if (empty(self::$cache['taxrates'])) {

			// With Magento we cheat a bit, we store the Magento-product's tax rate somewhere in the config page
			// Now we write all tax classes with that tax rate and done
			if (KenedoPlatform::getName() == 'magento') {

				$db = KenedoPlatform::getDb();
				$query = "SELECT * FROM `#__configbox_tax_classes`";
				$db->setQuery($query);
				$taxClasses = $db->loadAssoc();
				foreach ($taxClasses as $taxClass) {
					self::$cache['taxrates'][$taxClass['id']]['tax_rate'] = KSession::get('cbtaxrate', 0);
				}

			}
			else {
				// Get from cache
				self::$cache['taxrates'] = self::getFromCache('taxrates');

				// If empty, write the cache items and fetch them
				if (self::$cache['taxrates'] === NULL) {
					self::writeTaxRateCache();
					self::$cache['taxrates'] = self::getFromCache('taxrates');
				}
			}

		}

		return self::$cache['taxrates'];
	}

	protected static function writeTaxRateCache() {

		$db = KenedoPlatform::getDb();

		$query = "SELECT * FROM `#__configbox_tax_classes`";
		$db->setQuery($query);
		$taxClasses = $db->loadAssocList();

		$taxRates = array();

		// Add default tax rate for each tax class
		foreach ($taxClasses as $taxClass) {
			$taxRates[$taxClass['id']]['tax_rate'] = $taxClass['default_tax_rate'];
		}

		// Mix in country overrides
		$query = "
		SELECT `tax_class_id`, `tax_rate`, `country_id` 
		FROM `#__configbox_tax_class_rates` 
		WHERE `country_id` IS NOT NULL";
		$db->setQuery($query);
		$countryOverrides = $db->loadAssocList();

		foreach ($countryOverrides as $ovr) {
			$taxClassId = $ovr['tax_class_id'];
			// intval makes sure that NULL values become 0, strval makes sure that 0 becomes '0' so that things get grouped, not appended
			$countryId = strval(intval($ovr['country_id']));
			$taxRates[$taxClassId]['country_override'][$countryId]['tax_rate'] = $ovr['tax_rate'];
		}

		// Mix in state overrides
		$query = "
		SELECT rates.`tax_class_id`, rates.`tax_rate`, states.`country_id`, rates.`state_id`
		FROM `#__configbox_tax_class_rates` AS rates
		LEFT JOIN `#__configbox_states` AS states ON states.id = rates.state_id
		WHERE rates.`state_id` IS NOT NULL AND rates.`tax_rate` != 0";
		$db->setQuery($query);
		$stateOverrides = $db->loadAssocList();

		foreach ($stateOverrides as $ovr) {
			$taxClassId = $ovr['tax_class_id'];
			// intval makes sure that NULL values become 0, strval makes sure that 0 becomes '0' so that things get grouped, not appended
			$countryId = strval(intval($ovr['country_id']));
			$stateId = strval(intval($ovr['state_id']));
			$taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['tax_rate'] = $ovr['tax_rate'];
		}

		// Mix in county additions
		$query = "
		SELECT rates.`tax_class_id`, rates.`tax_rate`, states.`country_id`, states.id AS `state_id`, rates.`county_id`
		FROM `#__configbox_tax_class_rates` AS rates
		LEFT JOIN `#__configbox_counties` AS counties ON counties.id = rates.county_id
		LEFT JOIN `#__configbox_states` AS states ON states.id = counties.state_id
		WHERE rates.`county_id` IS NOT NULL AND rates.`tax_rate` != 0";
		$db->setQuery($query);
		$countyOverrides = $db->loadAssocList();

		foreach ($countyOverrides as $ovr) {
			$taxClassId = $ovr['tax_class_id'];
			// intval makes sure that NULL values become 0, strval makes sure that 0 becomes '0' so that things get grouped, not appended
			$countryId = strval(intval($ovr['country_id']));
			$stateId = strval(intval($ovr['state_id']));
			$countyId = strval(intval($ovr['county_id']));
			$taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['county_tax_rate'][$countyId]['tax_rate'] = $ovr['tax_rate'];
		}

		// Mix in city additions
		$query = "
		SELECT rates.`tax_class_id`, rates.`tax_rate`, states.`country_id`, states.id AS `state_id`, counties.id AS `county_id`, rates.`city_id`
		FROM `#__configbox_tax_class_rates` AS rates
		LEFT JOIN `#__configbox_cities` AS cities ON cities.id = rates.city_id
		LEFT JOIN `#__configbox_counties` AS counties ON counties.id = cities.county_id
		LEFT JOIN `#__configbox_states` AS states ON states.id = counties.state_id
		WHERE rates.`city_id` IS NOT NULL AND rates.`tax_rate` != 0";
		$db->setQuery($query);
		$cityOverrides = $db->loadAssocList();

		foreach ($cityOverrides as $ovr) {
			$taxClassId = intval($ovr['tax_class_id']);
			// intval makes sure that NULL values become 0, strval makes sure that 0 becomes '0' so that things get grouped, not appended
			$countryId = strval(intval($ovr['country_id']));
			$stateId = strval(intval($ovr['state_id']));
			$countyId = strval(intval($ovr['county_id']));
			$cityId = strval(intval($ovr['city_id']));
			$taxRates[$taxClassId]['country_override'][$countryId]['state_override'][$stateId]['county_tax_rate'][$countyId]['city_tax_rate'][$cityId]['tax_rate'] = $ovr['tax_rate'];
		}

		self::writeToCache('taxrates', $taxRates);

	}

	/**
	 * @return ConfigboxCurrencyData[]
	 */
	public static function &getCurrencies() {

		// If memo-cache for currencies is empty..
		if (empty(self::$cache['currencies'])) {

			// ..get from persistent cache..
			self::$cache['currencies'] = self::getFromCache('currencies');

			// ..is that cache is empty too, get to populate it
			if (self::$cache['currencies'] === null) {

				// Magento get's special treatment later
				if (KenedoPlatform::getName() != 'magento') {
					$db = KenedoPlatform::getDb();
					$query = "SELECT * FROM `#__configbox_currencies` WHERE `published` = '1'";
					$db->setQuery($query);
					$currencies = $db->loadObjectList('id');
					self::$cache['currencies'] = $currencies;
					self::writeToCache('currencies', $currencies);
				}

			}

			// For Magento, we sneak in currency data into the memo cache like this (it'll be the default and base currency)
			if (KenedoPlatform::getName() == 'magento') {

				$baseCurrencyCode       = Mage::app()->getStore()->getBaseCurrencyCode();
				$baseCurrency           = Mage::app()->getLocale()->currency( $baseCurrencyCode );
				$currentCurrencyCode    = Mage::app()->getStore()->getCurrentCurrencyCode();
				$currentCurrency        = Mage::app()->getLocale()->currency($currentCurrencyCode);

				$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array($currentCurrencyCode));
				$multiplier = !empty($rates[$currentCurrencyCode]) ? $rates[$currentCurrencyCode] : 1;

				$currencies = array();

				$currency = new stdClass();
				$currency->id = 1;
				$currency->default = 1;
				$currency->base = 1;
				/** @noinspection PhpUndefinedMethodInspection */
				$currency->title         = $currentCurrency->getName();
				$currency->code          = $currentCurrencyCode;
				$currency->multiplicator = $multiplier;
				/** @noinspection PhpUndefinedMethodInspection */
				$currency->symbol    = $baseCurrency->getSymbol();
				$currency->published = 1;
				$currency->ordering  = 1;

				$currencies[1] = $currency;

				self::$cache['currencies'] = $currencies;
			}
			// For Joomla and any future others, append the translatable title (shouldn't be cached)
			else {
				foreach (self::$cache['currencies'] as $currency) {
					$currency->title = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 6, $currency->id);
				}
			}

		}

		return self::$cache['currencies'];

	}

	/**
	 * @return object[]
	 * @throws Exception in case reading from DB or caching goes wrong
	 */
	public static function getCustomConnectors() {

		self::$cache['connectors'] = self::getFromCache('connectors');

		if (self::$cache['connectors'] === NULL) {

			if (ConfigboxUpdateHelper::tableExists('#__configbox_connectors')) {

				$db = KenedoPlatform::getDb();
				$query = "SELECT * FROM `#__configbox_connectors` WHERE `published` = '1' ORDER BY `ordering`";
				$db->setQuery($query);

				self::$cache['connectors'] = $db->loadObjectList();

				self::writeToCache('connectors', self::$cache['connectors']);

			}

		}

		return self::$cache['connectors'];

	}

	/**
	 * Memoizes result of hasApcu()
	 * @see hasApcu()
	 * @var null|bool
	 */
	protected static $memohasApcu = NULL;

	/**
	 * Checks if APCu is installed and enabled
	 * @return bool
	 */
	protected static function hasApcu() {

		if (self::$memohasApcu === NULL) {

			$loadedAndEnabled = extension_loaded('apcu') && ini_get('apc.enabled') == true;

			// In case we run via CLI, make sure apc.enable_cli is on
			if (in_array(php_sapi_name(), array('cli', 'cli-server'))) {
				$cliAndCliEnabled = (ini_get('apc.enable_cli') == 1);
				self::$memohasApcu = ($loadedAndEnabled && $cliAndCliEnabled);
			}
			else {
				self::$memohasApcu = $loadedAndEnabled;
			}

		}

		return self::$memohasApcu;

	}

	/**
	 * Purges the cache (incl. invalidating caches in OPcache and APC/APCu)
	 * @return bool true on success
	 * @throws Exception if anything goes wrong
	 */
	public static function purgeCache() {

		if (self::hasApcu()) {
			$prefix = self::getKeyPrefix();
			$cacheIterator = new APCUIterator('/^'.$prefix.'\./');
			return apcu_delete($cacheIterator);
		}

		// Drop any stuff from the memo cache
		self::$cache = NULL;

		// Make sure we get the real deal in the next step
		clearstatcache(true, CONFIGBOX_DIR_CACHE);

		// If the cache dir is gone already, we're done
		if (is_dir(CONFIGBOX_DIR_CACHE) == false) {
			return true;
		}

		// Throw one if we can't edit the cache dir
		if (is_writable(CONFIGBOX_DIR_CACHE) == false) {
			$msg = 'Cannot delete ConfigBox cache directory. This is critical. Please make the directory "'.CONFIGBOX_DIR_CACHE.'" writable.';
			KLog::log($msg, 'error');
			throw new Exception($msg);
		}

		// Deal with OP cache extensions
		$hasApc = function_exists('apc_delete_file');
		$hasOpcache = function_exists('opcache_invalidate');
		$cacheFiles = array();

		// We invalidate any cached files in the cache dir
		if ($hasOpcache || $hasApc) {

			$cacheFiles = KenedoFileHelper::getFiles(CONFIGBOX_DIR_CACHE, '.', true, true);

			foreach ($cacheFiles as $file) {

				if ($hasOpcache) {
					opcache_invalidate($file, true);
				}
				if ($hasApc) {
					apc_delete_file($file);
				}

			}

		}

		// We rename the cache dir and delete afterwards to minimize risk race conditions. First we find a name for the trash dir
		do {
			$cacheTrashDir = CONFIGBOX_DIR_CACHE.'_'.str_pad(rand(0, 99999), 5, 0);
		}
		while(is_dir($cacheTrashDir));

		// Now we rename
		if (rename(CONFIGBOX_DIR_CACHE, $cacheTrashDir) === false) {
			KLog::log('Could not rename cache folder prior deletion. Make sure that folder '.dirname(CONFIGBOX_DIR_CACHE).' is writable', 'error');
			throw new Exception('Could not move cache folder prior deletion. See ConfigBox error log.');
		}

		// Now we invalidate paths yet again in case of any race conditions
		if ($hasOpcache || $hasApc) {

			foreach ($cacheFiles as $file) {

				if ($hasOpcache) {
					opcache_invalidate($file, true);
				}
				if ($hasApc) {
					apc_delete_file($file);
				}

			}

		}

		// Finally we get to delete the cache dir
		$success = KenedoFileHelper::deleteFolder($cacheTrashDir);

		// If we can't delete the trashed cache dir, log as error, but let it go through
		if ($success === false) {
			KLog::log('Could not delete trashed cache folder. Location: '.$cacheTrashDir, 'error');
		}

		return true;

	}

	/**
	 * @param string $key Cache key, anything unique to the data you're storing
	 * @param mixed $data Only serializable data please
	 * @return bool
	 * @throws Exception If writing to cache fails. Puts log message in CB error log file.
	 */
	public static function writeToCache($key, &$data) {

		if (self::hasApcu()) {

			$prefix = self::getKeyPrefix();
			$wholeKey = $prefix.'.'.$key;

			$serialized = serialize($data);

			if (apcu_exists($wholeKey)) {
				$success = apcu_store($wholeKey, $serialized);
			}
			else {
				$success = apcu_add($wholeKey, $serialized);
			}

			if ($success == false) {
				KLog::log('Error writing to APCu cache. Key (non-prefixed) was "'.$key.'". Data (unserialized) was '.var_export($data, true), 'error');
				throw new Exception('Could not write cache data to APCu. See CB error log file.', 500);
			}
			return true;

		};

		$key = str_replace('.', DS, $key);

		$filename = CONFIGBOX_DIR_CACHE.DS.$key.'.cache';

		clearstatcache(true, $filename);

		if (!is_dir(dirname($filename))) {
			mkdir(dirname($filename), 0777, true);
		}

		$content = "<?php\ndefined('CB_VALID_ENTRY') or die();\n\$var = ".var_export($data,true).";";
		$response = file_put_contents($filename, $content);

		if ($response === false) {
			KLog::log('Error writing to file cache. Filename was "'.$filename.'".', 'error');
			throw new Exception('Could not write cache data to file cache. See CB error log file.', 500);
		}

		return true;

	}

	/**
	 * Tells if data for key is in cache
	 * @param string $key Unique key for what you want from the cache
	 * @return bool
	 */
	public static function isInCache($key) {

		if (self::hasApcu()) {
			$prefix = self::getKeyPrefix();
			$wholeKey = $prefix.'.'.$key;
			return apcu_exists($wholeKey);
		}

		$key = str_replace('.', DS, $key);

		$filename = CONFIGBOX_DIR_CACHE.DS.$key.'.cache';

		clearstatcache(true, $filename);

		return is_file($filename);

	}

	/**
	 * @param string $key Unique key for what you want from the cache
	 * @return mixed|null NULL
	 */
	public static function &getFromCache($key) {

		if (self::hasApcu()) {

			$prefix = self::getKeyPrefix();
			$wholeKey = $prefix.'.'.$key;

			$var = apcu_fetch($wholeKey, $success);
			if ($success == false) {
				$var = NULL;
			}
			else {
				$var = unserialize($var);
			}

			return $var;

		};

		$key = str_replace('.', DS, $key);

		$filename = CONFIGBOX_DIR_CACHE.DS.$key.'.cache';

		clearstatcache(true, $filename);

		if (is_file($filename)) {
			include($filename);
			/** @noinspection PhpUndefinedVariableInspection */
			return $var;
		}
		else {
			$var = NULL;
			return $var;
		}

	}

}