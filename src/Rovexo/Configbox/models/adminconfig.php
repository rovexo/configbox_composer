<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminconfig extends KenedoModel {

	function getTableName() {
		return '#__configbox_config';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>1,
			'label'=>KText::_('ID'),
			'canSortBy'=>true,
			'positionList'=>1,
			'positionForm'=>1,
		);

		if (KenedoPlatform::getName() == 'wordpress' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['wp_urls_start'] = array(
				'name'=>'wp_urls_start',
				'type'=>'groupstart',
				'title'=>KText::_('Wordpress URLs'),
				'positionForm'=>2,
			);

			$propDefs['url_segment_cart'] = array(
				'name'=>'url_segment_cart',
				'label'=>KText::_('URL Segment for Cart Page'),
				'tooltip'=>KText::_('Enter how the URL to your cart page should be like. Use only characters that can be in a URL'),
				'type'=>'translatable',
				'langType'=>100,
				'required'=>0,
				'positionForm'=>3,
			);

			$propDefs['url_segment_user'] = array(
				'name'=>'url_segment_user',
				'label'=>KText::_('URL Segment for Account Page'),
				'tooltip'=>KText::_('Enter how the URL to your account page should be like. Use only characters that can be in a URL'),
				'type'=>'translatable',
				'langType'=>105,
				'required'=>0,
				'positionForm'=>3,
			);

			$propDefs['wp_urls_end'] = array(
				'name'=>'wp_urls_end',
				'type'=>'groupend',
				'positionForm'=>19,
			);

		}

		$propDefs['lang_start'] = array(
			'name'=>'lang_start',
			'type'=>'groupstart',
			'title'=>KText::_('Languages'),
			'positionForm'=>20,
		);

		$propDefs['language_tag'] = array(
			'name'=>'language_tag',
			'label'=>KText::_('Shop Manager default language'),
			'tooltip'=>KText::_('Choose a language that is used when the language for the shop manager cannot be determined. For instance for email notifications.'),
			'type'=>'join',

			'required'=>0,
			'propNameKey'=>'tag',
			'propNameDisplay'=>'label',

			'modelClass'=>'ConfigboxModelAdminlanguages',
			'modelMethod'=>'getActiveLanguages',

			'options'=>'SKIPDEFAULTFIELD NOFILTERSAPPLY',
			'positionForm'=>30,
		);

		if (KenedoPlatform::getName() == 'magento2') {
			$propDefs['language_tag']['invisible'] = true;
		}

		$propDefs['active_languages'] = array(
			'name'=>'active_languages',
			'label'=>KText::_('Active Languages'),
			'tooltip'=>KText::_('Choose the languages you like to have translations for. Use this to enter translations before you activate the language in your platform. Missing translations result in empty titles etc. if the customer can choose that language.'),
			'type'=>'multiselect',
			'asCheckboxes'=>true,
			'required'=>1,

			'activeLanguageHack'=>true,

			'modelClass'=>'ConfigboxModelAdminlanguages',
			'modelMethod'=>'getAllLanguages',

			'xrefTable'=>'#__configbox_active_languages',
			'fkOwn'=>'',
			'fkOther'=>'tag',

			'keyOwn'=>'id',

			'tableOther'=>'',
			'keyOther'=>'tag',
			'displayColumnOther'=>'label',

			'positionForm'=>40,
		);

		if (KenedoPlatform::getName() == 'wordpress' && KenedoPlatform::p()->hasWpml() == false) {
			$propDefs['active_languages']['invisible'] = true;
		}

		$propDefs['lang_end'] = array(
			'name'=>'lang_end',
			'type'=>'groupend',
			'positionForm'=>50,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['currencies'] = array(
				'name' => 'currencies',
				'label' => KText::_('Currencies'),
				'hideAdminLabel' => true,
				'type' => 'childentries',
				'viewClass'=>'ConfigboxViewAdmincurrencies',
				'viewFilters'=>array(),
				'foreignKeyField'=>'',
				'parentKeyField'=>'',
				'positionForm'=>60,
			);

			$propDefs['taxclasses'] = array(
				'name' => 'taxclasses',
				'label' => KText::_('Tax Classes'),
				'hideAdminLabel' => true,
				'type' => 'childentries',
				'viewClass'=>'ConfigboxViewAdmintaxclasses',
				'viewFilters'=>array(),
				'foreignKeyField'=>'',
				'parentKeyField'=>'',
				'positionForm'=>70,
			);

			$propDefs['salutations'] = array(
				'name' => 'salutations',
				'label' => KText::_('Salutations'),
				'hideAdminLabel' => true,
				'type' => 'childentries',
				'viewClass'=>'ConfigboxViewAdminsalutations',
				'viewFilters'=>array(),
				'foreignKeyField'=>'',
				'parentKeyField'=>'',
				'positionForm'=>80,
			);

			$propDefs['customer_groups'] = array(
				'name' => 'customer_groups',
				'label' => KText::_('Customer Groups'),
				'hideAdminLabel' => true,
				'type' => 'childentries',
				'viewClass'=>'ConfigboxViewAdmincustomergroups',
				'viewFilters'=>array(),
				'foreignKeyField'=>'',
				'parentKeyField'=>'',
				'positionForm'=>90,
			);

			$propDefs['checkout_group'] = array(
				'name' => 'checkout_group',
				'type' => 'groupstart',
				'title' => KText::_('Checkout'),
				'toggle' => true,
				'defaultState' => 'closed',
				'positionForm'=>100,
			);

			$propDefs['continue_listing_id'] = array(
				'name' => 'continue_listing_id',
				'label' => KText::_('Product Listing for continue shopping'),
				'tooltip' => KText::_('Choose a product listing to which customers go when they click continue shopping links.'),
				'type' => 'join',
				'defaultlabel' => KText::_('None'),
				'propNameKey' => 'id',
				'propNameDisplay' => 'title',
				'modelClass' => 'ConfigboxModelAdminlistings',
				'modelMethod' => 'getRecords',
				'positionForm'=>110,
			);
		}

		$propDefs['default_customer_group_id'] = array(
			'name'=>'default_customer_group_id',
			'label'=>KText::_('Default Customer Group'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdmincustomergroups',
			'modelMethod'=>'getRecords',
			'required'=>0,
			'tooltip'=>KText::_('Choose the customer group a new customer should be put in.'),
			'positionForm'=>120,
		);

		if (KenedoPlatform::getName() == 'magento2' || ConfigboxWordpressHelper::isWcIntegration()) {
			$propDefs['default_customer_group_id']['invisible'] = true;
		}

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['default_country_id'] = array(
				'name'=>'default_country_id',
				'label'=>KText::_('Default Country'),
				'defaultlabel'=>KText::_('No automatic selection'),
				'tooltip'=>KText::_('Choose the country a new customer should have, when automatic determination failed or is not available.'),
				'type'=>'join',
				'propNameKey'=>'id',
				'propNameDisplay'=>'country_name',
				'modelClass'=>'ConfigboxModelAdmincountries',
				'modelMethod'=>'getRecords',
				'required'=>0,
				'positionForm'=>130,
			);

			$propDefs['securecheckout'] = array(
				'name'=>'securecheckout',
				'label'=>KText::_('Secure Checkout'),
				'type'=>'boolean',
				'tooltip'=>KText::_('Choose yes to route to the checkout procedure over an encrypted connection. SSL server support required and valid certificate recommended.'),
				'positionForm'=>140,
			);

			$propDefs['disable_delivery'] = array(
				'name'=>'disable_delivery',
				'label'=>KText::_('Disable Shipping'),
				'type'=>'boolean',
				'default'=>0,
				'tooltip'=>KText::_('Choose yes to disable shipping functionality in the system.'),
				'positionForm'=>160,
			);

			$propDefs['sku_in_order_record'] = array(
				'name'=>'sku_in_order_record',
				'label'=>KText::_('Show SKU in checkout record'),
				'type'=>'boolean',
				'default'=>0,
				'tooltip'=>KText::_('Choose yes to show the product SKU and SKUs of selected options in the checkout page, order overview in notification emails and the shop manager order overview.'),
				'positionForm'=>170,
			);

			$propDefs['newsletter_preset'] = array(
				'name'=>'newsletter_preset',
				'label'=>KText::_('Preselect newsletter opt-in'),
				'type'=>'boolean',
				'default'=>0,
				'tooltip'=>KText::_('Choose yes to preselect the newsletter opt-in on any forms in the system.'),
				'positionForm'=>180,
			);

			$propDefs['alternate_shipping_preset'] = array(
				'name'=>'alternate_shipping_preset',
				'label'=>KText::_('Show delivery address fields by default'),
				'type'=>'boolean',
				'default'=>0,
				'tooltip'=>KText::_('Choose yes to show delivery address form by default, customers can select -same as billing- to hide it.'),
				'positionForm'=>190,
			);

			$propDefs['show_recurring_login_cart'] = array(
				'name'=>'show_recurring_login_cart',
				'label'=>KText::_('Show recurring customer login'),
				'type'=>'boolean',
				'default'=>1,
				'tooltip'=>KText::_('Choose yes to show a login form in forms for quotation requests and save order forms.'),
				'positionForm'=>200,
			);

			$propDefs['explicit_agreement_terms'] = array(
				'name'=>'explicit_agreement_terms',
				'label'=>KText::_('Explicit agreement to terms'),
				'type'=>'boolean',
				'default'=>0,
				'tooltip'=>KText::_('Choose yes to let the customer agree to the terms and conditions before placing an order.'),
				'positionForm'=>210,
			);

			$propDefs['explicit_agreement_rp'] = array(
				'name'=>'explicit_agreement_rp',
				'label'=>KText::_('Explicit agreement to refund policy'),
				'type'=>'boolean',
				'default'=>0,
				'tooltip'=>KText::_('Choose yes to let the customer agree to the refund policy before placing an order.'),
				'positionForm'=>220,
			);

			$propDefs['checkout_group_end'] = array(
				'name'=>'checkout_group_end',
				'type'=>'groupend',
				'positionForm'=>230,
			);

			$propDefs['invoicestart'] = array(
				'name'=>'invoicestart',
				'type'=>'groupstart',
				'title'=>KText::_('Invoicing'),
				'toggle'=>true,
				'defaultState'=>'closed',
				'notes'=>KText::sprintf('Invoice PDF files are stored in the folder %s.',str_replace(array(DIRECTORY_SEPARATOR, '/', "\\"), '/'.'<wbr>',KenedoPlatform::p()->getDirDataCustomer().'/private/invoices') ),
				'positionForm'=>280,
			);

			$propDefs['enable_invoicing'] = array(
				'name'=>'enable_invoicing',
				'label'=>KText::_('Enable Invoicing'),
				'type'=>'boolean',
				'default'=>'1',
				'tooltip'=>KText::_('Choose yes to offer invoices for download by the customer in the customer account page.'),
				'positionForm'=>290,
			);

			$propDefs['send_invoice'] = array(
				'name'=>'send_invoice',
				'label'=>KText::_('Send invoice via email'),
				'type'=>'boolean',
				'default'=>'1',
				'tooltip'=>KText::_('Choose yes to send the invoice to the customer via email. The email is sent as soon as the invoice is released.'),
				'positionForm'=>300,
			);

			$propDefs['invoice_generation'] = array(
				'name'=>'invoice_generation',
				'label'=>KText::_('Invoice Generation'),
				'type'=>'dropdown',
				'choices'=> array(0=>KText::_('Automatic'), 1=>KText::_('Automatic after Clearance'), 2=>KText::_('After Manual Upload')),
				'default'=>0,
				'tooltip'=>KText::_('Automatic means that the system generates the invoice and offers it as soon as the order has the status PAID. Automatic after Clearance means that the shop manager has to set clearance in the order details page for the order. After Manual Upload means that the shop manager uploads a PDF before the invoice is offered.'),
				'positionForm'=>310,
			);

			$propDefs['invoice_number_prefix'] = array (
				'name'=>'invoice_number_prefix',
				'label'=>KText::_('Invoice Number Prefix'),
				'tooltip'=>KText::_('A text that is prepended to the invoice number.'),
				'default'=>'',
				'type'=>'string',
				'stringType'=>'string',
				'positionForm'=>320,
			);

			$propDefs['invoice_number_start'] = array (
				'name'=>'invoice_number_start',
				'label'=>KText::_('Invoice Number Start'),
				'tooltip'=>KText::_('The invoice number the system should start with.'),
				'type'=>'string',
				'stringType'=>'number',
				'default'=>'1',
				'positionForm'=>330,
			);

			$propDefs['invoiceend'] = array(
				'name'=>'invoiceend',
				'type'=>'groupend',
				'positionForm'=>340,
			);

			$propDefs['reviews_start'] = array(
				'name'=>'reviews_start',
				'type'=>'groupstart',
				'title'=>KText::_('Reviews'),
				'toggle'=>true,
				'defaultState'=>'closed',
				'positionForm'=>410,
			);

			$propDefs['enable_reviews_products'] = array(
				'name'=>'enable_reviews_products',
				'label'=>KText::_('Reviews for Products'),
				'tooltip'=>KText::_('Choose yes to to let customers review products. You can override this setting for selected products at the product edit screen.'),
				'type'=>'boolean',
				'default'=>0,
				'positionForm'=>420,
			);

			$propDefs['review_notification_email'] = array(
				'name'=>'review_notification_email',
				'label'=>KText::_('Review notification email address'),
				'tooltip'=>KText::_('The system sends a notification to this email address when a new review was created.'),
				'default'=>'',
				'type'=>'string',
				'required'=>1,
				'positionForm'=>430,
			);

			$propDefs['reviews_end'] = array(
				'name'=>'reviews_end',
				'type'=>'groupend',
				'positionForm'=>450,
			);

		} // If platform is not Magento

		$propDefs['configuration_start'] = array(
			'name'=>'configuration_start',
			'type'=>'groupstart',
			'title'=>KText::_('Configurator'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>460,
		);

		$propDefs['page_nav_show_tabs'] = array(
			'name'=>'page_nav_show_tabs',
			'label' => KText::_('Show page navigation using tabs'),
			'tooltip' => KText::_('Choose yes to show page tabs above the page questions.'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>470,
		);

		$propDefs['page_nav_show_buttons'] = array(
			'name' => 'page_nav_show_buttons',
			'label' => KText::_('Show page navigation with next and previous buttons'),
			'tooltip' => KText::_('Choose yes to show next and previous buttons below the page questions.'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>480,
		);

		$propDefs['page_nav_block_on_missing_selections'] = array(
			'name'=>'page_nav_block_on_missing_selections',
			'label'=>KText::_('Block continuing if selections are missing'),
			'tooltip'=>KText::_('Choose yes to disable the next and finish button if there are required unselected elements on the configurator page. This setting can be overridden for each configuration page.'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>490,
		);

		$propDefs['page_nav_cart_button_last_page_only'] = array(
			'name'=>'page_nav_cart_button_last_page_only',
			'label'=>KText::_('FIELD_LABEL_PRODUCT_CART_BUTTON_LAST_PAGE_ONLY'),
			'tooltip'=>KText::_('TOOLTIP_PRODUCT_CART_BUTTON_LAST_PAGE_ONLY'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>500,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['show_conversion_table'] = array(
				'name'=>'show_conversion_table',
				'label'=>KText::_('Display conversion table'),
				'tooltip'=>KText::_('Choose yes to display a currency conversion table below the currency module.'),
				'type'=>'boolean',
				'default'=>0,
				'positionForm'=>511,
			);
		}

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['defaultprodimage'] = array (
				'name'=>'defaultprodimage',
				'label'=>KText::_('Default Product Image'),
				'type'=>'image',
				'appendSerial'=>1,
				'allowedExtensions'=>array('jpg','jpeg','gif','tif','bmp','png'),
				'allowedMimeTypes'=>array('image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png'),
				'maxFileSizeKb'=>'500',
				'filetype'=>'image',
				'dirBase'=>KenedoPlatform::p()->getDirDataStore().'/public/default_images',
				'urlBase'=>KenedoPlatform::p()->getUrlDataStore().'/public/default_images',
				'filename'=>'default_prod_image',
				'required'=>0,
				'options'=>'PRESERVE_EXT SAVE_FILENAME',
				'tooltip'=>KText::_('The product image to be displayed in listings when no specific image is uploaded.'),
				'positionForm'=>520,
			);

		}

		$propDefs['use_internal_question_names'] = array(
			'name'=>'use_internal_question_names',
			'label'=>KText::_('Show internal question names in backend.'),
			'type'=>'boolean',
			'default'=>0,
			'tooltip'=>KText::_('Choose yes to show the internal question names in backend listings. Useful if you use duplicate question titles.'),
			'positionForm'=>540,
		);

		$propDefs['use_internal_answer_names'] = array(
			'name'=>'use_internal_answer_names',
			'label'=>KText::_('Show internal answer names in backend.'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>541,
		);

		$propDefs['weightunits'] = array(
			'name'=>'weightunits',
			'label'=>KText::_('Weight Unit'),
			'default'=>'kg',
			'type'=>'string',
			'required'=>0,
			'positionForm'=>550,
		);

		$propDefs['configuration_end'] = array(
			'name'=>'configuration_end',
			'type'=>'groupend',
			'positionForm'=>560,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['blocktitles_start'] = array(
				'name' => 'blocktitles_start',
				'type' => 'groupstart',
				'toggle' => true,
				'defaultState' => 'closed',
				'title' => KText::_('Block Headings'),
				'notes' => KText::_('These texts will be used as headings above the respective blocks in the configurator when custom layouts are used.'),
				'positionForm'=>570,
			);

			$propDefs['blocktitle_cart'] = array(
				'name' => 'blocktitle_cart',
				'label' => KText::_('Cart Block'),
				'type' => 'translatable',
				'langType'=>70,
				'positionForm'=>580,
			);

			$propDefs['blocktitle_currencies'] = array(
				'name' => 'blocktitle_currencies',
				'label' => KText::_('Currencies Block'),
				'type' => 'translatable',
				'langType'=>71,
				'positionForm'=>590,
			);

			$propDefs['blocktitle_navigation'] = array(
				'name' => 'blocktitle_navigation',
				'label' => KText::_('Navigation Block'),
				'type' => 'translatable',
				'langType'=>72,
				'positionForm'=>600,
			);

			$propDefs['blocktitle_pricing'] = array(
				'name' => 'blocktitle_pricing',
				'label' => KText::_('Price Overview Block'),
				'type' => 'translatable',
				'langType'=>73,
				'positionForm'=>610,
			);

			$propDefs['blocktitle_visualization'] = array(
				'name' => 'blocktitle_visualization',
				'label' => KText::_('Visualization Block'),
				'type' => 'translatable',
				'langType'=>74,
				'positionForm'=>620,
			);

			$propDefs['blocktitles_end'] = array(
				'name' => 'blocktitles_end',
				'type' => 'groupend',
				'positionForm'=>630,
			);

		}

		// Watch out - for M2 the entire group is hidden (by CSS style in admin.css)
		$propDefs['price_module_start'] = array(
			'name'=>'price_module_start',
			'type'=>'groupstart',
			'toggle'=>true,
			'defaultState'=>'closed',
			'title'=>KText::_('Selection overview on configurator pages'),
			'positionForm'=>640,
		);

		$propDefs['pm_show_regular_first'] = array(
			'name'=>'pm_show_regular_first',
			'label'=>KText::_('Overview to show first'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('Regular Prices'), 0=>KText::_('Recurring Prices')),
			'default'=>1,
			'tooltip'=>KText::_('The order in which to show the 2 overviews.'),
			'positionForm'=>650,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['pm_show_delivery_options'] = array(
				'name' => 'pm_show_delivery_options',
				'label' => KText::_('Show delivery option'),
				'type' => 'boolean',
				'default' => 0,
				'positionForm'=>660,
			);

			$propDefs['pm_show_payment_options'] = array(
				'name' => 'pm_show_payment_options',
				'label' => KText::_('Show payment option'),
				'type' => 'boolean',
				'default' => 0,
				'positionForm'=>670,
			);

		}

		$propDefs['pm_show_net_in_b2c'] = array(
			'name'=>'pm_show_net_in_b2c',
			'label'=>KText::_('Show net in B2C mode'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>680,
		);

		$propDefs['pm_price_module_start_regular'] = array(
			'name'=>'price_module_start_regular',
			'type'=>'groupstart',
			'title'=>KText::_('Regular Prices'),
			'toggle'=>false,
			'defaultState'=>'opened',
			'positionForm'=>690,
		);

		$propDefs['pm_regular_show_overview'] = array(
			'name'=>'pm_regular_show_overview',
			'label'=>KText::_('Show overview'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>700,
		);

		$propDefs['pm_regular_show_prices'] = array(
			'name'=>'pm_regular_show_prices',
			'label'=>KText::_('Show Prices'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>710,
		);

		$propDefs['pm_regular_show_categories'] = array(
			'name'=>'pm_regular_show_categories',
			'label'=>KText::_('Show Configurator Pages'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>720,
		);

		$propDefs['pm_regular_show_elements'] = array(
			'name'=>'pm_regular_show_elements',
			'label'=>KText::_('Show questions'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>730,
		);

		$propDefs['pm_regular_show_elementprices'] = array(
			'name'=>'pm_regular_show_elementprices',
			'label'=>KText::_('Show question prices'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>740,
		);

		$propDefs['pm_regular_expand_categories'] = array(
			'name'=>'pm_regular_expand_categories',
			'label'=>KText::_('Expand Configurator Pages'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('All'), 0=>KText::_('No Page'), 2=>KText::_('Active page only')),
			'default'=>2,
			'positionForm'=>750,
		);

		$propDefs['pm_regular_show_taxes'] = array(
			'name'=>'pm_regular_show_taxes',
			'label'=>KText::_('Show taxes'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>760,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['pm_regular_show_cart_button'] = array(
				'name' => 'pm_regular_show_cart_button',
				'label' => KText::_('Show cart button'),
				'type' => 'boolean',
				'default' => 0,
				'positionForm'=>770,
			);

		}

		$propDefs['pm_price_module_end_regular'] = array(
			'name'=>'price_module_end_regular',
			'type'=>'groupend',
			'positionForm'=>790,
		);

		$propDefs['pm_price_module_start_recurring'] = array(
			'name'=>'price_module_start_recurring',
			'type'=>'groupstart',
			'title'=>KText::_('Recurring Prices'),
			'positionForm'=>790,
		);

		$propDefs['pm_recurring_show_overview'] = array(
			'name'=>'pm_recurring_show_overview',
			'label'=>KText::_('Show overview'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>800,
		);

		$propDefs['pm_recurring_show_prices'] = array(
			'name'=>'pm_recurring_show_prices',
			'label'=>KText::_('Show Prices'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>810,
		);

		$propDefs['pm_recurring_show_categories'] = array(
			'name'=>'pm_recurring_show_categories',
			'label'=>KText::_('Show Configurator Pages'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>820,
		);

		$propDefs['pm_recurring_show_elements'] = array(
			'name'=>'pm_recurring_show_elements',
			'label'=>KText::_('Show questions'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>830,
		);

		$propDefs['pm_recurring_show_elementprices'] = array(
			'name'=>'pm_recurring_show_elementprices',
			'label'=>KText::_('Show question prices'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>840,
		);

		$propDefs['pm_recurring_expand_categories'] = array(
			'name'=>'pm_recurring_expand_categories',
			'label'=>KText::_('Expand Configurator Pages'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('All'), 0=>KText::_('No Page'), 2=>KText::_('Active page only')),
			'default'=>2,
			'positionForm'=>850,
		);

		$propDefs['pm_recurring_show_taxes'] = array(
			'name'=>'pm_recurring_show_taxes',
			'label'=>KText::_('Show taxes'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>860,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['pm_recurring_show_cart_button'] = array(
				'name' => 'pm_recurring_show_cart_button',
				'label' => KText::_('Show cart button'),
				'type' => 'boolean',
				'default' => 0,
				'positionForm'=>870,
			);

		}

		$propDefs['price_module_end_recurring'] = array(
			'name'=>'price_module_end_recurring',
			'type'=>'groupend',
			'positionForm'=>890,
		);

		$propDefs['price_module_end'] = array(
			'name'=>'price_module_end',
			'type'=>'groupend',
			'positionForm'=>900,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['geolocation_start'] = array(
				'name' => 'geolocation_start',
				'type' => 'groupstart',
				'toggle' => true,
				'defaultState' => 'closed',
				'title' => KText::_('IP Geolocation tracking'),
				'notes' => KText::_('Enable IP Geolocation tracking to auto-determine the customer\'s country, state and zip code by IP address.'),
				'positionForm'=>960,
			);

			$propDefs['enable_geolocation'] = array(
				'name' => 'enable_geolocation',
				'label' => KText::_('Enable IP Geolocation tracking'),
				'type' => 'boolean',
				'default' => 0,
				'positionForm'=>970,
			);

			$propDefs['geolocation_type'] = array(
				'name'=>'geolocation_type',
				'label'=>KText::_('Geolocation Service Type'),
				'type'=>'dropdown',
				'choices'=> array('maxmind_geoip2_db'=>KText::_('Local'), 'maxmind_geoip2_web'=>KText::_('Webservice')),
				'default'=>0,
				'tooltip'=>KText::_('Local - use local file. WebService - use MaxMind Web Service.'),
				'positionForm'=>973,
			);

			$propDefs['maxmind_user_id'] = array(
				'name' => 'maxmind_user_id',
				'label' => KText::_('MaxMind User ID'),
				'tooltip' => KText::_('TOOLTIP_CONFIGURATION_MAXMIND_LICENSE_KEY'),
				'type' => 'string',
				'required' => 0,
				'positionForm'=>975,
			);

			$propDefs['maxmind_license_key'] = array(
				'name' => 'maxmind_license_key',
				'label' => KText::_('MaxMind License Key'),
				'tooltip' => KText::_('TOOLTIP_CONFIGURATION_MAXMIND_LICENSE_KEY'),
				'type' => 'string',
				'required' => 0,
				'positionForm'=>980,
			);

			$propDefs['geolocation_end'] = array(
				'name' => 'geolocation_end',
				'type' => 'groupend',
				'positionForm'=>990,
			);

		}

		$propDefs['sep3'] = array(
			'name'=>'sep3',
			'type'=>'groupstart',
			'title'=>KText::_('License Key'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>1000,
		);

		$propDefs['product_key'] = array(
			'name'=>'product_key',
			'label'=>KText::_('License Key'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1010,
		);

		$propDefs['license_manager_satellites'] = array(
			'name'=>'license_manager_satellites',
			'label'=>KText::_('License Manager Satellites'),
			'type'=>'string',
			'required'=>1,
			'invisible'=>true,
			'default'=>'licenses.configbox.at',
			'positionForm'=>1020,
		);

		$propDefs['sep4'] = array(
			'name'=>'sep4',
			'type'=>'groupend',
			'positionForm'=>1030,
		);

		$propDefs['custom_fields_start'] = array(
			'name'=>'custom_fields_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'notes'=>KText::_('Here you can define labels for your custom fields for better identification.'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>1040,
		);

		$propDefs['label_product_custom_1'] = array(
			'name'=>'label_product_custom_1',
			'label'=>KText::sprintf('Label for product custom field %s',1),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1050,
		);

		$propDefs['label_product_custom_2'] = array(
			'name'=>'label_product_custom_2',
			'label'=>KText::sprintf('Label for product custom field %s',2),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1060,
		);

		$propDefs['label_product_custom_3'] = array(
			'name'=>'label_product_custom_3',
			'label'=>KText::sprintf('Label for product custom field %s',3),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1070,
		);

		$propDefs['label_product_custom_4'] = array(
			'name'=>'label_product_custom_4',
			'label'=>KText::sprintf('Label for product custom field %s',4),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1080,
		);

		$propDefs['label_product_custom_5'] = array(
			'name'=>'label_product_custom_5',
			'label'=>KText::sprintf('Label for product custom field %s',5),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1090,
		);

		$propDefs['label_product_custom_6'] = array(
			'name'=>'label_product_custom_6',
			'label'=>KText::sprintf('Label for product custom field %s',6),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1100,
		);

		$propDefs['label_element_custom_1'] = array(
			'name'=>'label_element_custom_1',
			'label'=>KText::sprintf('Label for question custom field %s',1),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1110,
		);

		$propDefs['label_element_custom_2'] = array(
			'name'=>'label_element_custom_2',
			'label'=>KText::sprintf('Label for question custom field %s',2),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1120,
		);

		$propDefs['label_element_custom_3'] = array(
			'name'=>'label_element_custom_3',
			'label'=>KText::sprintf('Label for question custom field %s',3),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1130,
		);

		$propDefs['label_element_custom_4'] = array(
			'name'=>'label_element_custom_4',
			'label'=>KText::sprintf('Label for question custom field %s',4),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1140,
		);

		$propDefs['label_element_custom_translatable_1'] = array(
			'name'=>'label_element_custom_translatable_1',
			'label'=>KText::sprintf('Label for question custom field %s', 5),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1141,
		);

		$propDefs['label_element_custom_translatable_2'] = array(
			'name'=>'label_element_custom_translatable_2',
			'label'=>KText::sprintf('Label for question custom field %s', 6),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1142,
		);

		$propDefs['label_assignment_custom_1'] = array(
			'name'=>'label_assignment_custom_1',
			'label'=>KText::sprintf('Label for Answer Custom Field %s',1),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1150,
		);

		$propDefs['label_assignment_custom_2'] = array(
			'name'=>'label_assignment_custom_2',
			'label'=>KText::sprintf('Label for Answer Custom Field %s',2),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1160,
		);

		$propDefs['label_assignment_custom_3'] = array(
			'name'=>'label_assignment_custom_3',
			'label'=>KText::sprintf('Label for Answer Custom Field %s',3),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1170,
		);

		$propDefs['label_assignment_custom_4'] = array(
			'name'=>'label_assignment_custom_4',
			'label'=>KText::sprintf('Label for Answer Custom Field %s',4),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1180,
		);


		$propDefs['label_option_custom_1'] = array(
			'name'=>'label_option_custom_1',
			'label'=>KText::sprintf('Label for global answer custom field %s',1),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1190,
		);

		$propDefs['label_option_custom_2'] = array(
			'name'=>'label_option_custom_2',
			'label'=>KText::sprintf('Label for global answer custom field %s',2),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1200,
		);

		$propDefs['label_option_custom_3'] = array(
			'name'=>'label_option_custom_3',
			'label'=>KText::sprintf('Label for global answer custom field %s',3),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1210,
		);

		$propDefs['label_option_custom_4'] = array(
			'name'=>'label_option_custom_4',
			'label'=>KText::sprintf('Label for global answer custom field %s',4),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1210,
		);

		$propDefs['label_option_custom_5'] = array(
			'name'=>'label_option_custom_5',
			'label'=>KText::sprintf('Label for global answer custom field %s',5),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1220,
		);

		$propDefs['label_option_custom_6'] = array(
			'name'=>'label_option_custom_6',
			'label'=>KText::sprintf('Label for global answer custom field %s',6),
			'default'=>'',
			'type'=>'string',
			'positionForm'=>1230,
		);

		$propDefs['custom_fields_end'] = array(
			'name'=>'custom_fields_end',
			'type'=>'groupend',
			'positionForm'=>1240,
		);

		// Watch out - for M2 the entire group is hidden (by CSS style in admin.css)
		$propDefs['maintenance_start'] = array(
			'name' => 'maintenance_start',
			'type' => 'groupstart',
			'title' => KText::_('Maintenance'),
			'toggle' => true,
			'defaultState' => 'closed',
			'positionForm'=>1250,
		);

		$propDefs['usertime'] = array(
			'name' => 'usertime',
			'label' => KText::_('User Lifetime'),
			'type' => 'string',
			'unit' => KText::_('Hours'),
			'tooltip' => KText::_('Time for unregistered users without orders before deletion'),
			'required' => 1,
			'default' => 24,
			'positionForm'=>1260,
		);

		$propDefs['unorderedtime'] = array(
			'name' => 'unorderedtime',
			'label' => KText::_('Unordered Orders Lifetime'),
			'type' => 'string',
			'unit' => KText::_('Hours'),
			'tooltip' => KText::_('Time for unordered orders before deletion'),
			'required' => 1,
			'default' => 24,
			'positionForm'=>1270,
		);

		$propDefs['intervals'] = array(
			'name' => 'intervals',
			'label' => KText::_('Maintainance Intervals'),
			'type' => 'string',
			'unit' => KText::_('Hours'),
			'tooltip' => KText::_('Intervals to run the maintainance scripts'),
			'required' => 1,
			'default' => 24,
			'positionForm'=>1290,
		);

		$propDefs['labelexpiry'] = array(
			'name' => 'labelexpiry',
			'label' => KText::_('Old Alias Lifetime'),
			'type' => 'string',
			'unit' => KText::_('Days'),
			'tooltip' => KText::_('How long old aliases should be remembered. Recommended time is the interval of search engine indexings. If you are not sure, use 21 days.'),
			'default' => 28,
			'required' => 1,
			'positionForm'=>1300,
		);

		$propDefs['maintenance_end'] = array(
			'name' => 'maintenance_end',
			'type' => 'groupend',
			'positionForm'=>1310,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['connectors_start'] = array(
				'name'=>'connectors_start',
				'type'=>'groupstart',
				'title'=>KText::_('Connectors'),
				'toggle'=>true,
				'defaultState'=>'closed',
				'positionForm'=>1400,
			);

			$propDefs['connectors'] = array (
				'name'=>'connectors',
				'label'=>KText::_('Connectors'),
				'hideAdminLabel'=>true,
				'type'=>'childentries',
				'viewClass'=>'ConfigboxViewAdminconnectors',
				'viewFilters'=>array(),
				'foreignKeyField'=>'',
				'parentKeyField'=>'',
				'positionForm'=>1500,
			);

			$propDefs['connectors_end'] = array(
				'name'=>'connectors_end',
				'type'=>'groupend',
				'positionForm'=>1600,
			);

		}

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['structureddata_start'] = array(
				'name' => 'structureddata_start',
				'type' => 'groupstart',
				'toggle' => true,
				'defaultState' => 'closed',
				'title' => KText::_('SD_TITLE'),
				'notes' => KText::_('SD_TITLE_NOTES'),
				'positionForm'=>1700,
			);

			$propDefs['structureddata'] = array(
				'name'=>'structureddata',
				'label'=>KText::_('SD_USE'),
				'tooltip'=>KText::_('SD_USE_TOOLTIP'),
				'type'=>'boolean',
				'default'=>1,
				'positionForm'=>1800,
			);

			$propDefs['structureddata_in'] = array(
				'name'=>'structureddata_in',
				'label'=>KText::_('SD_USE_IN'),
				'tooltip'=>KText::_('SD_USE_IN_TOOLTIP'),
				'type'=>'dropdown',
				'choices'=> array(
					'configurator' =>KText::_('SD_USE_IN_OPTION_CONFIGURATOR'),
					'product' =>KText::_('SD_USE_IN_OPTION_PRODUCT')
				),
				'default'=> 'configurator',
				'appliesWhen' => array(
					'structureddata' => array(1),
				),
				'positionForm'=>1900,
			);

			$propDefs['use_ga_ecommerce'] = array(
				'name'=>'use_ga_ecommerce',
				'label'=>KText::_('LABEL_USE_GA_ECOMMERCE'),
				'tooltip'=>KText::_('TOOLTIP_USE_GA_ECOMMERCE'),
				'type'=>'boolean',
				'default'=>0,
				'positionForm'=>2000,
			);

			$propDefs['ga_property_id'] = array(
				'name'=>'ga_property_id',
				'label'=>KText::_('LABEL_GA_PROPERTY_ID'),
				'tooltip'=>KText::_('TOOLTIP_GA_PROPERTY_ID'),
				'type'=>'string',
				'required'=>1,
				'appliesWhen'=>array(
					'use_ga_ecommerce'=>'1',
				),
				'positionForm'=>2100,
			);

			$propDefs['ga_behavior_offline_psps'] = array(
				'name'=>'ga_behavior_offline_psps',
				'label'=>KText::_('LABEL_GA_BEHAVIOR_OFFLINE_PSPS'),
				'tooltip'=>KText::_('TOOLTIP_GA_BEHAVIOR_OFFLINE_PSPS'),
				'type'=>'dropdown',
				'choices'=>array(
					'conversion_when_ordered' => KText::_('CHOICE_TRACK_WHEN_ORDERED'),
					'conversion_when_paid' => KText::_('CHOICE_TRACK_WHEN_PAID'),
				),
				'default'=>'conversion_when_paid',
				'appliesWhen'=>array(
					'use_ga_ecommerce'=>'1',
				),
				'positionForm'=>2200,
			);

			if (ConfigboxAddonHelper::hasAddon('ga_enhanced_ecommerce') == true) {
				$propDefs['use_ga_enhanced_ecommerce'] = array(
					'name'=>'use_ga_enhanced_ecommerce',
					'label'=>KText::_('LABEL_USE_GA_ENHANCED_ECOMMERCE'),
					'tooltip'=>KText::_('TOOLTIP_USE_GA_ENHANCED_ECOMMERCE'),
					'type'=>'boolean',
					'default'=>'0',
					'appliesWhen'=>array(
						'use_ga_ecommerce'=>'1',
					),
					'positionForm'=>2300,
				);
			}
			else {
				$propDefs['use_ga_enhanced_ecommerce'] = array(
					'name'=>'use_ga_enhanced_ecommerce',
					'label'=>KText::_('LABEL_USE_GA_ENHANCED_ECOMMERCE'),
					'tooltip'=>KText::_('TOOLTIP_USE_GA_ENHANCED_ECOMMERCE'),
					'type'=>'note',
					'default'=>'0',
					'appliesWhen'=>array(
						'use_ga_ecommerce'=>'1',
					),
					'positionForm'=>2400,
				);
			}

			$propDefs['structureddata_end'] = array(
				'name' => 'structureddata_end',
				'type' => 'groupend',
				'positionForm'=>2500,
			);

		}


		$propDefs['debugging_start'] = array(
			'name'=> 'debugging_start',
			'type'=> 'groupstart',
			'toggle'=> true,
			'defaultState'=> 'closed',
			'title'=>KText::_('CONFIG_DEBUGGING_GROUP_TITLE'),
			'positionForm'=>3000,
		);

		$propDefs['enable_performance_tracking'] = array(
			'name'=>'enable_performance_tracking',
			'label'=>KText::_('CONFIG_DEBUGGING_SELECTION_CALL_PROFILING_LABEL'),
			'tooltip'=>KText::_('CONFIG_DEBUGGING_SELECTION_CALL_PROFILING_TOOLTIP'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>3010,
		);

		$propDefs['use_minified_js'] = array(
			'name'=>'use_minified_js',
			'label'=>KText::_('CONFIG_DEBUGGING_MINIFIED_JS_LABEL'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>3020,
		);

		$propDefs['use_minified_css'] = array(
			'name'=>'use_minified_css',
			'label'=>KText::_('CONFIG_DEBUGGING_MINIFIED_CSS_LABEL'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>3030,
		);

		$propDefs['use_assets_cache_buster'] = array(
			'name'=>'use_assets_cache_buster',
			'label'=>KText::_('CONFIG_DEBUGGING_USE_ASSETS_CACHE_BUSTER_LABEL'),
			'tooltip'=>KText::_('CONFIG_DEBUGGING_USE_ASSETS_CACHE_BUSTER_TOOLTIP'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>3040,
		);

		$propDefs['debugging_end'] = array(
			'name'=>'debugging_end',
			'type'=>'groupend',
			'positionForm'=>3100,
		);


		return $propDefs;

	}

	/**
	 * @param int $id
	 * @param string $languageTag
	 * @return object | ConfigboxSettingsData
	 * @throws Exception
	 */
	function getRecord($id, $languageTag = '') {
		return parent::getRecord($id, $languageTag);
	}

	/**
	 * @param array $filters
	 * @param array $pagination
	 * @param array $sortSpecs
	 * @param string $languageTag
	 * @param bool $countOnly
	 * @return int|object[]|ConfigboxSettingsData[]
	 * @throws Exception
	 */
	function getRecords($filters = array(), $pagination = array(), $sortSpecs = array(), $languageTag = '', $countOnly = false) {
		return parent::getRecords($filters, $pagination, $sortSpecs, $languageTag, $countOnly);
	}

	/**
	 *
	 * @return array $tasks Array holding task buttons
	 */
	function getDetailsTasks() {
		$tasks = array();
		$tasks[] = array('title'=>KText::_('Save'), 	'task'=>'apply');
		return $tasks;
	}
	
}
