<?php
class CbSettings {

	/**
	 * @var CbSettings
	 */
	protected static $instance;
	/**
	 * @var string[] Set values here before first initialization when you do not want the class to load settings from the DB.
	 */
	public static $override;

	protected $id;
	protected $language_tag;
	protected $continue_listing_id;
	protected $default_customer_group_id;
	protected $default_country_id;
	protected $securecheckout;
	protected $disable_delivery;
	protected $sku_in_order_record;
	protected $newsletter_preset;
	protected $alternate_shipping_preset;
	protected $show_recurring_login_cart;
	protected $explicit_agreement_terms;
	protected $explicit_agreement_rp;
	protected $enable_invoicing;
	protected $send_invoice;
	protected $invoice_generation;
	protected $invoice_number_prefix;
	protected $invoice_number_start;
	protected $review_notification_email;
	protected $enable_reviews_products;
	protected $show_conversion_table;
	protected $page_nav_show_tabs;
	protected $page_nav_show_buttons;
	protected $page_nav_block_on_missing_selections;
	protected $page_nav_cart_button_last_page_only;
	protected $defaultprodimage;
	protected $enable_performance_tracking;
	protected $use_internal_question_names;
	protected $use_internal_answer_names;
	protected $weightunits;
	protected $blocktitle_cart;
	protected $blocktitle_currencies;
	protected $blocktitle_navigation;
	protected $blocktitle_pricing;
	protected $blocktitle_visualization;
	protected $pm_show_regular_first;
	protected $pm_show_delivery_options;
	protected $pm_show_payment_options;
	protected $pm_show_net_in_b2c;
	protected $pm_regular_show_overview;
	protected $pm_regular_show_prices;
	protected $pm_regular_show_categories;
	protected $pm_regular_show_elements;
	protected $pm_regular_show_elementprices;
	protected $pm_regular_expand_categories;
	protected $pm_regular_show_taxes;
	protected $pm_regular_show_cart_button;
	protected $pm_recurring_show_overview;
	protected $pm_recurring_show_prices;
	protected $pm_recurring_show_categories;
	protected $pm_recurring_show_elements;
	protected $pm_recurring_show_elementprices;
	protected $pm_recurring_expand_categories;
	protected $pm_recurring_show_taxes;
	protected $pm_recurring_show_cart_button;
	protected $enable_geolocation;
	protected $maxmind_license_key;
	protected $product_key;
	protected $license_manager_satellites;
	protected $label_product_custom_1;
	protected $label_product_custom_2;
	protected $label_product_custom_3;
	protected $label_product_custom_4;
	protected $label_product_custom_5;
	protected $label_product_custom_6;
	protected $label_element_custom_1;
	protected $label_element_custom_2;
	protected $label_element_custom_3;
	protected $label_element_custom_4;
	protected $label_element_custom_translatable_1;
	protected $label_element_custom_translatable_2;
	protected $label_assignment_custom_1;
	protected $label_assignment_custom_2;
	protected $label_assignment_custom_3;
	protected $label_assignment_custom_4;
	protected $label_option_custom_1;
	protected $label_option_custom_2;
	protected $label_option_custom_4;
	protected $label_option_custom_3;
	protected $label_option_custom_5;
	protected $label_option_custom_6;
	protected $usertime;
	protected $unorderedtime;
	protected $orderedtime;
	protected $intervals;
	protected $labelexpiry;
	protected $defaultprodimage_href;
	protected $defaultprodimage_path;
	protected $enter_net;
	protected $structureddata;
	protected $structureddata_in;
	protected $use_ga_enhanced_ecommerce;
	protected $use_minified_js;
	protected $use_minified_css;
	protected $use_assets_cache_buster;

	/**
	 * How tracking should work for offline payments (like bank transaction or cash on delivery)
	 * 'When ordered' means track conversions/transaction should be done once the order status goes to 'ordered'
	 * 'When paid' means same but for when status goes to 'paid'
	 * @var string 'conversion_when_ordered'|'conversion_when_ordered'.
	 */
	protected $ga_behavior_offline_psps;

	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	protected function __construct() {

		//TODO: remove try/catch block after analysis with M2 tech check
		try {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_config` WHERE `id` = 1";
			$db->setQuery($query);
			$db->loadObject();
		}
		catch(Exception $e) {

			$stack = debug_backtrace(false);
			$slice = array_slice($stack, 0, 10);

			throw new Exception('Config table problem. '.var_export($slice, true));
		}
		
		if (empty(self::$override)) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_config` WHERE `id` = 1";
			$db->setQuery($query);
			$settings = $db->loadObject();

			if (!$settings) {
				KLog::log('There is no config row in configbox_config table', 'warning');
				return;
			}

			if (KenedoPlatform::getName() == 'magento') {
				$settings->enter_net = (Mage::getStoreConfig('tax/calculation/price_includes_tax')) ? false : true;
			} else if (KenedoPlatform::getName() == 'magento2') {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
                $isTaxInclusive = $scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $settings->enter_net = $isTaxInclusive ? false : true;
            } else {
				$settings->enter_net = true;
			}

            if ((KenedoPlatform::getName() == 'magento') || (KenedoPlatform::getName() == 'magento2')) {
				$settings->weightunits = '';
			}

		}
		else {
			$settings = self::$override;
		}

		foreach ($settings as $key=>$value) {
			$this->$key = $value;

			// Legacy configuration constants - REMOVE WITH CB 4.0
			if (!defined('CONFIGBOX_'.strtoupper($key))) {
				define('CONFIGBOX_'.strtoupper($key),$value);
			}
			if (!defined('CBCHECKOUT_'.strtoupper($key))) {
				define('CBCHECKOUT_'.strtoupper($key),$value);
			}

		}

	}

	/**
	 * @param string $key
	 * @param mixed  $fallBackValue Optional - Fallback value in case the field is empty
	 *
	 * @return null
	 * @throws Exception If $key is empty or setting does not exist
	 */
	public function get($key, $fallBackValue = NULL) {

		if (empty($key) || !is_string($key)) {
			KLog::log('Setting requested with empty key.', 'error');
			throw new Exception('Setting requested with empty key.');
		}

		// Load translatable settings on demand (we got into an endless loop between KText and CbSettings in some cases)
		if (strpos($key, 'blocktitle_') === 0) {
			if ($this->blocktitle_cart === NULL) {
				$this->blocktitle_cart          = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 70, 1);
				$this->blocktitle_currencies    = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 71, 1);
				$this->blocktitle_navigation    = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 72, 1);
				$this->blocktitle_pricing       = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 73, 1);
				$this->blocktitle_visualization = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 74, 1);
			}
		}

		if (property_exists($this, $key) == false) {
			$message = 'Unknown (most likely obsolete) setting requested. Key was "'.$key.'"';
			KLog::log($message, 'error');
			throw new Exception($message);
		}

		return (isset($this->$key)) ? $this->$key : $fallBackValue;
	}

	/**
	 * Gets you all settings as key/value pairs
	 * @return mixed[]
	 */
	public function getAll() {
		$settings = array();
		foreach ($settings as $key=>$value) {
			if (is_scalar($value)) {
				$settings[$key] = $value;
			}
		}
		return $settings;
	}

	public function override($key, $value) {
		$this->$key = $value;
	}

}