<?php
class ConfigboxProductData {
	var $id = 8;
	var $title = 'Computer';
	var $product_listing_ids = array();
	var $sku = 'COMPUTER';
	var $dispatch_time = 0;
	var $baseweight = 9;
	var $prod_image = '8.png';
	var $baseimage = '';
	var $published = 1;
	var $layoutname = 'default';
	var $page_nav_show_tabs = 2;
	var $page_nav_show_buttons = 2;
	var $page_nav_block_on_missing_selections = 0;
	var $page_nav_cart_button_last_page_only = 0;
	var $show_buy_button = 1;
	var $label = 'computer';
	var $baseprice_overrides = '[]';
	var $was_price = 0;
	var $taxclass_id = 1;
	var $taxclass_id_display_value = 'Standard';
	var $pricelabel = '';
	var $custom_price_text = 'Starting at € 840.00';
	var $baseprice_recurring_overrides = '[]';
	var $was_price_recurring = 0;
	var $taxclass_recurring_id = 1;
	var $taxclass_recurring_id_display_value = 'Standard';
	var $pricelabel_recurring = '';
	var $recurring_interval = '';
	var $custom_price_text_recurring = '';
	var $description = '<p>This demo products demonstrates how ConfigBox can be used to set up an IT-equipment store using compatibility rules that define which computer components can be used together.</p>';
	var $longdescription = '';
	var $product_detail_panes_method = 'tabs';
	var $product_detail_panes_in_product_pages = 1;
	var $product_detail_panes_in_configurator_steps = 0;
	var $enable_reviews = 2;
	var $external_reviews_id = '';
	var $product_custom_1 = '';
	var $product_custom_2 = '';
	var $product_custom_3 = '';
	var $product_custom_4 = '';
	var $product_custom_5 = '';
	var $product_custom_6 = '';
	var $pm_show_regular_first = 1;
	var $pm_show_delivery_options = 1;
	var $pm_show_payment_options = 1;
	var $pm_show_net_in_b2c = 0;
	var $pm_regular_show_overview = 2;
	var $pm_regular_show_prices = 1;
	var $pm_regular_show_categories = 1;
	var $pm_regular_show_elements = 1;
	var $pm_regular_show_elementprices = 1;
	var $pm_regular_expand_categories = 2;
	var $pm_regular_show_taxes = 1;
	var $pm_regular_show_cart_button = 1;
	var $pm_recurring_show_overview = 0;
	var $pm_recurring_show_prices = 1;
	var $pm_recurring_show_categories = 1;
	var $pm_recurring_show_elements = 1;
	var $pm_recurring_show_elementprices = 1;
	var $pm_recurring_expand_categories = 2;
	var $pm_recurring_show_taxes = 0;
	var $pm_recurring_show_cart_button = 0;
	var $prod_image_href = 'http://configbox.dev/components/com_configbox/data/prod_images/8.png';
	var $prod_image_path = '/Users/martin/PhpstormProjects/configbox/components/com_configbox/data/prod_images/8.png';
	var $baseimage_href = '';
	var $baseimage_path = '';
	var $imagesrc = 'http://configbox.dev/components/com_configbox/data/prod_images/8.png';
	var $listing_id = 1;
	var $priceLabel = 'Price';
	var $priceLabelRecurring = 'Recurring Price';
	var $taxRate = 20;
	var $taxRateRecurring = 20;
	var $basePriceNet = 2000;
	var $basePriceGross = 2400;
	var $basePriceTax = 400;
	var $basePriceRecurringNet = 0;
	var $basePriceRecurringGross = 0;
	var $basePriceRecurringTax = 0;
	var $priceNet = 2000;
	var $priceGross = 2400;
	var $priceTax = 400;
	var $priceRecurringNet = 0;
	var $priceRecurringGross = 0;
	var $priceRecurringTax = 0;
	var $price = 2000;
	var $priceRecurring = 0;
	var $wasPriceNet = 0;
	var $wasPriceGross = 0;
	var $wasPriceTax = 0;
	var $wasPriceRecurringNet = 0;
	var $wasPriceRecurringGross = 0;
	var $wasPriceRecurringTax = 0;
	var $wasPrice = 0;
	var $wasPriceRecurring = 0;
	var $showReviews = false;
	var $isConfigurable = true;
	var $firstPageId = 9;
	/**
	 * @var string Either 'none', 'composite' or 'shapediver' (as of 3.1)
	 */
	var $visualization_type = '';
	/**
	 * @var string JSON data about the model
	 */
	var $shapediver_model_data = '';

	/**
	 * @var string '0' or '1'
	 */
	var $use_recurring_pricing;

	/**
	 * @var string 0\1 If a detail button should be shown on listing page
	 */
	var $show_product_details_button;

	/**
	 * @var string 'External' URL to a detail page
	 */
	var $product_details_url;

	/**
	 * @var string none|configbox_page|cms_page
	 */
	var $product_details_page_type;
}