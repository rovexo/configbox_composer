<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminproducts extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_products';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return 'id';
	}

	function getChildModel() {
		return 'ConfigboxModelAdminpages';
	}

    function getChildModelForeignKey() {
        return 'product_id';
    }

	function getPropertyDefinitions() {

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listing'=>10,
			'listingwidth'=>'50px',
			'order'=>100,
			'positionForm' => 10000,
		);

		$propDefs['generalStart'] = array(
			'name'=>'generalStart',
			'type'=>'groupstart',
			'title'=>KText::_('General'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'notes'=>KText::_('GROUP_NOTE_PRODUCT_GENERAL',''),
			'positionForm' => 20000,
		);

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			$propDefs['generalStart']['notes'] = '';
		}

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>1,
			'required'=>1,
			'order'=>20,
			'filter'=>1,
			'search'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminproducts',
			'positionForm' => 40000,
		);

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['product_listing_ids'] = array(
				'name' => 'product_listing_ids',
				'label' => KText::_('FIELD_LABEL_PRODUCT_LISTING_IDS'),
				'tooltip' => KText::_('TOOLTIP_PRODUCT_LISTING_IDS'),
				'type' => 'multiselect',

				'modelClass'=>'ConfigboxModelAdminlistings',
				'modelMethod'=>'getRecords',

				'xrefTable'=>'#__configbox_xref_listing_product',
				'fkOwn'=>'product_id',
				'fkOther'=>'listing_id',

				'keyOwn'=>'id',

				'tableOther'=>'#__configbox_listings',
				'keyOther'=>'id',
				'displayColumnOther'=>'title',

				'usesOrdering' => true,
				'asCheckboxes' => true,
				'required' => 0,

				'positionForm' => 50000,
			);

			$propDefs['prod_image'] = array(
				'name' => 'prod_image',
				'label' => KText::_('Product Image'),
				'type' => 'file',
				'appendSerial' => 1,
				'allowedExtensions' => array('jpg', 'jpeg', 'gif', 'tif', 'bmp', 'png'),
				'allow' => array('image/pjpeg', 'image/jpg', 'image/jpeg', 'image/gif', 'image/tif', 'image/bmp', 'image/png', 'image/x-png'),
				'size' => '2000',
				'filetype' => 'image',
				'dirBase' => CONFIGBOX_DIR_PRODUCT_IMAGES,
				'urlBase' => CONFIGBOX_URL_PRODUCT_IMAGES,
				'required' => 0,
				'options' => 'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
				'tooltip' => KText::_('The product image is displayed in product listings and product pages.'),

				'positionForm' => 90000,
			);

		}

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('LABEL_PRODUCT_ACTIVE'),
			'type'=>'published',
			'default'=>1,
			'listing'=>100,
			'filter'=>3,
			'order'=>40,
			'listingwidth'=>'50px',
			'tooltip'=>KText::_('Choose no to hide the product from lists and searches.'),
			'positionForm' => 99000,
		);

		$propDefs['generalEnd'] = array(
			'name'=>'generalEnd',
			'type'=>'groupend',
			'positionForm' => 110000,
		);

		$propDefs['visualization_start'] = array(
			'name' => 'visualization_start',
			'type' => 'groupstart',
			'title' => KText::_('Visualization'),
			'toggle' => true,
			'defaultState' => 'closed',
			'positionForm' => 110100,
		);

		$propDefs['visualization_type'] = array(
			'name' => 'visualization_type',
			'label' => KText::_('What type of visualization do you want to use?'),
			'tooltip' => KText::_('TOOLTIP_PRODUCT_VIS_TYPE'),
			'type' => 'dropdown',
			'items' => array(
				'none'=>KText::_('No Visualisation'),
				'composite' => KText::_('Composite Image'),
				'shapediver' => KText::_('ShapeDiver 3D Model'),
				),
			'default' => 'none',
			'positionForm' => 110200,
		);

		$propDefs['baseimage'] = array (
			'name'=>'baseimage',
			'label'=>KText::_('Base image of the product visualization'),
			'tooltip'=>KText::_('This image will be shown on the bottom of the image stack.'),
			'type'=>'file',
			'appendSerial'=>1,
			'allowedExtensions'=>array('jpg','jpeg','gif','tif','bmp','png'),
			'filetype'=>'image',
			'allow'=>array('image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png','image/x-png'),
			'size'=>'1000',
			'dirBase'=>CONFIGBOX_DIR_VIS_PRODUCT_BASE_IMAGES,
			'urlBase'=>CONFIGBOX_URL_VIS_PRODUCT_BASE_IMAGES,
			'required'=>0,
			'options'=>'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
			'positionForm' => 110300,
			'appliesWhen'=>array(
				'visualization_type'=>'composite',
			),
		);

		$propDefs['shapediver_model_data'] = array(
			'name'=>'shapediver_model_data',
			'label'=>KText::_('ShapeDiver Model'),
			'type'=>'shapedivermodel',
			'required'=>1,
			'positionForm'=>110400,
			'appliesWhen'=>array(
				'visualization_type'=>'shapediver',
			),
		);

		$propDefs['visualization_end'] = array(
			'name'=>'visualization_end',
			'type'=>'groupend',
			'positionForm' => 110500,
		);

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['baseprice_start'] = array(
				'name' => 'baseprice_start',
				'type' => 'groupstart',
				'title' => KText::_('Base price'),
				'toggle' => true,
				'defaultState' => 'opened',
				'positionForm' => 180000,
			);

			$propDefs['baseprice'] = array(
				'name' => 'baseprice',
				'label' => KText::_('Base Price'),
				'type' => 'string',
				'stringType' => 'price',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'listing' => 40,
				'order' => '50',
				'listingwidth' => '70px',
				'tooltip' => KText::_('The product price without any upgrades.'),
				'positionForm' => 190000,
			);

			$propDefs['baseprice_overrides'] = array(
				'name' => 'baseprice_overrides',
				'label' => KText::_('Base Price Override'),
				'type' => 'groupPrice',
				'overridePropertyName' => 'baseprice',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'positionForm' => 191000,
			);

			$propDefs['was_price'] = array(
				'name' => 'was_price',
				'label' => KText::_('Was Price'),
				'tooltip' => KText::_('The Was Price is the striked-through price when you set a price reduction. It is NOT the effective price.'),
				'type' => 'string',
				'stringType' => 'price',
				'allow' => '?-[0-9]',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'listingwidth' => '100px',
				'positionForm' => 200000,
			);

			$propDefs['taxclass_id'] = array(
				'name' => 'taxclass_id',
				'label' => KText::_('LABEL_PRODUCT_TAX_CLASS'),
				'tooltip' => KText::_('TOOLTIP_PRODUCT_TAX_CLASS'),
				'type' => 'join',
				'propNameKey' => 'id',
				'propNameDisplay' => 'title',
				'modelClass' => 'ConfigboxModelAdmintaxclasses',
				'modelMethod' => 'getRecords',
				'required' => 0,
				'options' => 'SKIPDEFAULTFIELD NOFILTERSAPPLY',
				'positionForm' => 210000,
			);

			$propDefs['pricelabel'] = array(
				'name' => 'pricelabel',
				'label' => KText::_('Price Label'),
				'type' => 'translatable',
				'stringTable' => '#__configbox_strings',
				'langType'=>26,
				'required' => 0,
				'tooltip' => KText::_('Use this text to explain the purpose of the price. E.g. Setup cost, downpayment etc.'),
				'positionForm' => 220000,
				'appliesWhen' => array(
					'use_recurring_pricing'=>'1',
				),
			);

			$propDefs['custom_price_text'] = array(
				'name' => 'custom_price_text',
				'label' => KText::_('Custom Price Text'),
				'type' => 'translatable',
				'stringTable' => '#__configbox_strings',
				'langType'=>29,
				'required' => 0,
				'tooltip' => KText::_('TOOLTIP_PRODUCT_CUSTOM_PRICE_TEXT'),
				'positionForm' => 230000,
			);

			$propDefs['baseprice_end'] = array(
				'name' => 'baseprice_end',
				'type' => 'groupend',
				'opentable' => 0,
				'positionForm' => 240000,
			);

			$propDefs['baseprice_recurring_start'] = array(
				'name' => 'baseprice_recurring_start',
				'type' => 'groupstart',
				'title' => KText::_('Base price Recurring'),
				'toggle' => true,
				'defaultState' => 'closed',
				'positionForm' => 250000,
				'appliesWhen' => array(
					'use_recurring_pricing'=>'1',
				),
			);

			$propDefs['baseprice_recurring'] = array(
				'name' => 'baseprice_recurring',
				'label' => KText::_('Base Price Recurring'),
				'type' => 'string',
				'stringType' => 'price',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'listing' => 50,
				'order' => '50',
				'listingwidth' => '70px',
				'tooltip' => KText::_('The recurring product price without any upgrades.'),
				'positionForm' => 260000,
			);

			$propDefs['baseprice_recurring_overrides'] = array(
				'name' => 'baseprice_recurring_overrides',
				'label' => KText::_('Base Price Recurring Override'),
				'type' => 'groupPrice',
				'overridePropertyName' => 'baseprice_recurring',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'positionForm' => 261000,
			);

			$propDefs['was_price_recurring'] = array(
				'name' => 'was_price_recurring',
				'label' => KText::_('Was Price Recurring'),
				'tooltip' => KText::_('The Was Price is the striked-through price when you set a price reduction. It is NOT the effective price.'),
				'type' => 'string',
				'stringType' => 'price',
				'allow' => '?-[0-9]',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'listingwidth' => '100px',
				'positionForm' => 270000,
			);

			$propDefs['taxclass_recurring_id'] = array(
				'name' => 'taxclass_recurring_id',
				'label' => KText::_('Tax Class'),
				'tooltip' => KText::_('TOOLTIP_TAX_CLASSES'),
				'type' => 'join',
				'propNameKey' => 'id',
				'propNameDisplay' => 'title',
				'modelClass' => 'ConfigboxModelAdmintaxclasses',
				'modelMethod' => 'getRecords',
				'required' => 0,
				'options' => 'SKIPDEFAULTFIELD NOFILTERSAPPLY',
				'positionForm' => 280000,
			);

			$propDefs['pricelabel_recurring'] = array(
				'name' => 'pricelabel_recurring',
				'label' => KText::_('Price Label Recurring'),
				'type' => 'translatable',
				'stringTable' => '#__configbox_strings',
				'langType'=>31,
				'required' => 0,
				'tooltip' => KText::_('Use this text to explain the purpose of the price. E.g. Setup cost, downpayment etc.'),
				'positionForm' => 290000,
			);

			$propDefs['recurring_interval'] = array(
				'name' => 'recurring_interval',
				'label' => KText::_('Recurring Interval'),
				'type' => 'translatable',
				'stringTable' => '#__configbox_strings',
				'langType'=>25,
				'required' => 0,
				'tooltip' => KText::_("The interval recurring prices are due. E.g. 'monthly'."),
				'positionForm' => 300000,
			);

			$propDefs['custom_price_text_recurring'] = array(
				'name' => 'custom_price_text_recurring',
				'label' => KText::_('Custom Price Text Recurring Price'),
				'type' => 'translatable',
				'stringTable' => '#__configbox_strings',
				'langType'=>30,
				'required' => 0,
				'tooltip' => KText::_('TOOLTIP_PRODUCT_CUSTOM_PRICE_TEXT'),
				'positionForm' => 310000,
			);

			$propDefs['baseprice_recurring_end'] = array(
				'name' => 'baseprice_recurring_end',
				'type' => 'groupend',
				'positionForm' => 320000,
			);

			$propDefs['display_detail_page_start'] = array(
				'name' => 'display_detail_page_start',
				'type' => 'groupstart',
				'title' => KText::_('Display in product detail page'),
				'toggle' => true,
				'defaultState' => 'closed',
				'positionForm' => 330000,
			);

			$propDefs['product_details_page_type'] = array(
				'name' => 'product_details_page_type',
				'label' => KText::_('What kind of product detail page do you want to use?'),
				'tooltip' => KText::_('You can link to any CMS page or use the built-in ConfigBox product detail page.'),
				'type' => 'dropdown',
				'choices'=>array(
					'none' => KText::_('No detail page'),
					'cms_page' => KText::_('A CMS page'),
					'configbox_page' => KText::_('A ConfigBox product page'),
				),
				'default' => 'none',
				'positionForm' => 330200,
			);

			$propDefs['product_details_url'] = array(
				'name'=>'product_details_url',
				'label'=>KText::_('URL to product detail page'),
				'tooltip' => KText::_('We recommend using a relative URL'),
				'type'=>'translatable',
				'stringTable'=>'#__configbox_strings',
				'langType'=>101,
				'required'=>1,
				'positionForm' => 330300,
				'appliesWhen' => array('product_details_page_type'=>'cms_page'),
			);

			$propDefs['layoutname'] = array(
				'name' => 'layoutname',
				'label' => KText::_('Theme for product detail page'),
				'tooltip' => KText::_('You can have custom themes made for product detail pages by ConfigBox service providers and use them for all or individual products.'),
				'type' => 'join',
				'isPseudoJoin' => true,
				'default' => 'default',
				'propNameKey' => 'value',
				'propNameDisplay' => 'title',
				'modelClass' => 'ConfigboxModelAdmintemplates',
				'modelMethod' => 'getProductTemplates',
				'required' => 0,
				'options' => 'SKIPDEFAULTFIELD NOFILTERSAPPLY',
				'positionForm' => 330400,
				'appliesWhen' => array('product_details_page_type'=>'configbox_page'),
			);

			$propDefs['longdescription'] = array(
				'name'=>'longdescription',
				'label'=>KText::_('Description on product detail page'),
				'type'=>'translatable',
				'stringTable'=>'#__configbox_strings',
				'langType'=>24,
				'required'=>0,
				'options'=>'USE_HTMLEDITOR ALLOW_HTML',
				'positionForm' => 331000,
				'appliesWhen' => array('product_details_page_type'=>'configbox_page'),
			);

			$propDefs['product_detail_panes_in_product_pages'] = array(
				'name'=>'product_detail_panes_in_product_pages',
				'label'=>KText::_('Show product detail panes in product pages'),
				'tooltip'=>KText::_('See the section product detail panes below.'),
				'type'=>'boolean',
				'default'=>1,
				'positionForm' => 332000,
				'appliesWhen' => array('product_details_page_type'=>'configbox_page'),
			);

			$propDefs['display_detail_page_end'] = array(
				'name' => 'display_detail_page_end',
				'type' => 'groupend',
				'positionForm' => 340000,
			);

		}

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['display_listing_start'] = array(
				'name' => 'display_listing_start',
				'type' => 'groupstart',
				'title' => KText::_('Display in product listings'),
				'toggle' => true,
				'defaultState' => 'closed',
				'positionForm' => 361000,
			);

			$propDefs['show_buy_button'] = array(
				'name' => 'show_buy_button',
				'label' => KText::_('Show buy button on product listing page'),
				'tooltip' => KText::_('Choose yes if your product can be bought straight away without going through the configurator.'),
				'type' => 'boolean',
				'default' => 1,
				'positionForm' => 362000,
			);

			$propDefs['show_product_details_button'] = array(
				'name' => 'show_product_details_button',
				'label' => KText::_('Show a product details button on product listing page?'),
				'tooltip' => KText::_('Be sure to make appropriate settings at Display on Product detail pages.'),
				'type' => 'boolean',
				'default' => 0,
				'positionForm' => 362500,
			);

			$propDefs['enable_reviews'] = array(
				'name' => 'enable_reviews',
				'label' => KText::_('Enable Reviews'),
				'type' => 'dropdown',
				'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
				'default' => 2,
				'tooltip' => KText::_('Choose yes to enable reviews on this item. Choose Use Default to use the setting of the configuration.'),
				'positionForm' => 363000,
			);

			$propDefs['external_reviews_id'] = array(
				'name' => 'external_reviews_id',
				'label' => KText::_('External Review ID'),
				'tooltip' => KText::_('If you use an external review extension, enter the ID of the item.'),
				'type' => 'string',
				'stringType' => 'string',
				'positionForm' => 364000,
				'appliesWhen' => array('enable_reviews'=>array(1,2)),
			);

			$propDefs['description'] = array(
				'name'=>'description',
				'label'=>KText::_('Description in product listings'),
				'tooltip'=>KText::_('This content has no effect on the built-in listing design, but can be used for custom listing designs.'),
				'type'=>'translatable',
				'stringTable'=>'#__configbox_strings',
				'langType'=>11,
				'required'=>0,
				'options'=>'USE_HTMLEDITOR ALLOW_HTML',
				'positionForm' => 365000,
			);

			$propDefs['display_listing_end'] = array(
				'name' => 'display_listing_end',
				'type' => 'groupend',
				'positionForm' => 368000,
			);

		}

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['displayStart'] = array(
				'name' => 'displayStart',
				'type' => 'groupstart',
				'title' => KText::_('Display in configurator'),
				'toggle' => true,
				'defaultState' => 'closed',
				'positionForm' => 368500,
			);

		}

		$propDefs['product_detail_panes_in_configurator_pages'] = array(
			'name'=>'product_detail_panes_in_configurator_steps',
			'label'=>KText::_('Show product detail panes in configurator pages'),
			'tooltip'=>KText::_('See the section product detail panes below.'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm' => 368550,
		);

		$propDefs['page_nav_show_tabs'] = array(
			'name' => 'page_nav_show_tabs',
			'label' => KText::_('Show page navigation using tabs'),
			'tooltip' => KText::_('Choose yes to show page tabs above the page questions.'),
			'type' => 'dropdown',
			'default' => 2,
			'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
			'positionForm' => 368600,
		);

		$propDefs['page_nav_show_buttons'] = array(
			'name' => 'page_nav_show_buttons',
			'label' => KText::_('Show page navigation with next and previous buttons'),
			'tooltip' => KText::_('Choose yes to show next and previous buttons below the page questions.'),
			'type' => 'dropdown',
			'default' => 2,
			'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
			'positionForm' => 368700,
		);

		$propDefs['page_nav_block_on_missing_selections'] = array(
			'name'=>'page_nav_block_on_missing_selections',
			'label'=>KText::_('FIELD_LABEL_PRODUCT_BLOCK_ON_MISSING'),
			'tooltip'=>KText::_('TOOLTIP_PRODUCT_BLOCK_ON_MISSING'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'default'=>2,
			'positionForm'=>368800,
		);

		$propDefs['page_nav_cart_button_last_page_only'] = array(
			'name'=>'page_nav_cart_button_last_page_only',
			'label'=>KText::_('FIELD_LABEL_PRODUCT_CART_BUTTON_LAST_PAGE_ONLY'),
			'tooltip'=>KText::_('TOOLTIP_PRODUCT_CART_BUTTON_LAST_PAGE_ONLY'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'default'=>2,
			'positionForm'=>368800,
			'appliesWhen' => array(
				'page_nav_show_buttons'=>array('1', '2'),
			)
		);

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {

			$propDefs['product_detail_panes_in_configurator_pages']['invisible'] = true;
			$propDefs['product_detail_panes_in_configurator_pages']['default'] = 0;

			$propDefs['page_nav_show_buttons']['invisible'] = true;
			$propDefs['page_nav_show_buttons']['default'] = 0;

			$propDefs['page_nav_block_on_missing_selections']['invisible'] = true;
			$propDefs['page_nav_block_on_missing_selections']['default'] = 0;

			$propDefs['page_nav_cart_button_last_page_only']['invisible'] = true;
			$propDefs['page_nav_cart_button_last_page_only']['default'] = 0;
		}

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {
			$propDefs['display_end'] = array(
				'name' => 'display_end',
				'type' => 'groupend',
				'positionForm' => 369000,
			);
		}

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['detail_panes_start'] = array(
				'name' => 'detail_panes_start',
				'type' => 'groupstart',
				'title' => KText::_('Product Detail Panes'),
				'toggle' => true,
				'defaultState' => 'closed',
				'positionForm' => 370000,
			);

			$propDefs['product_detail_panes'] = array(
				'name' => 'product_detail_panes',
				'label' => KText::_('Product Detail Panes'),
				'tooltip' => KText::_('With product detail panes you can show detailled product information segmented with a tabbed interface or an accordion. You can display the panes in the product listing, the product page and the configurator page.'),
				'type' => 'childentries',
				'viewClass' => 'ConfigboxViewAdminproductdetailpanes',
				'viewFilters' => array(
					array('filterName' => 'adminproductdetailpanes.product_id', 'filterValueKey' => 'id'),
				),
				'foreignKeyField' => 'product_id',
				'parentKeyField' => 'id',
				'positionForm' => 380000,
			);

			$propDefs['product_detail_panes_method'] = array(
				'name' => 'product_detail_panes_method',
				'label' => KText::_('Product panes display method'),
				'type' => 'dropdown',
				'choices' => array('tabs' => KText::_('Tabbed interface')),
				'default' => 'accordeon',
				'tooltip' => KText::_('Choose the way the product detail panes should be displayed.'),
				'positionForm' => 390000,
				'invisible' => true,
			);

			$propDefs['detail_panes_end'] = array(
				'name' => 'detail_panes_end',
				'type' => 'groupend',
				'positionForm' => 420000,
			);

		}

		$propDefs['custom_fields_start'] = array(
			'name'=>'custom_fields_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('With these fields you can add your own data. You can use this data in your own templates and calculation models.'),
			'positionForm' => 520000,
		);

		$label = CbSettings::getInstance()->get('label_product_custom_1');
		if (!$label) {
			$label = KText::_('Custom Field 1');
		}

		$propDefs['product_custom_1'] = array(
			'name'=>'product_custom_1',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','product_custom_1'),
			'positionForm' => 530000,
		);

		$label = CbSettings::getInstance()->get('label_product_custom_2');
		if (!$label) {
			$label = KText::_('Custom Field 2');
		}

		$propDefs['product_custom_2'] = array(
			'name'=>'product_custom_2',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','product_custom_2'),
			'positionForm' => 540000,
		);

		$label = CbSettings::getInstance()->get('label_product_custom_3');
		if (!$label) {
			$label = KText::_('Custom Field 3');
		}

		$propDefs['product_custom_3'] = array(
			'name'=>'product_custom_3',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','product_custom_3'),
			'positionForm' => 550000,
		);

		$label = CbSettings::getInstance()->get('label_product_custom_4');
		if (!$label) {
			$label = KText::_('Custom Field 4');
		}

		$propDefs['product_custom_4'] = array(
			'name'=>'product_custom_4',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','product_custom_4'),
			'positionForm' => 560000,
		);

		$label = CbSettings::getInstance()->get('label_product_custom_5');
		if (!$label) {
			$label = KText::_('Custom Field 5');
		}

		$propDefs['product_custom_5'] = array(
			'name'=>'product_custom_5',
			'label'=> $label,
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>52,
			'required'=>0,
			'tooltip'=>KText::sprintf('You can access this field with the key %s','product_custom_5'),
			'positionForm' => 570000,
		);

		$label = CbSettings::getInstance()->get('label_product_custom_6');
		if (!$label) {
			$label = KText::_('Custom Field 6');
		}

		$propDefs['product_custom_6'] = array(
			'name'=>'product_custom_6',
			'label'=> $label,
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>53,
			'required'=>0,
			'tooltip'=>KText::sprintf('You can access this field with the key %s','product_custom_6'),
			'positionForm' => 580000,
		);

		$propDefs['custom_fields_end'] = array(
			'name'=>'custom_fields_end',
			'type'=>'groupend',
			'positionForm' => 590000,
		);

		$propDefs['price_module_start'] = array(
			'name'=>'price_module_start',
			'type'=>'groupstart',
			'title'=>KText::_('Selection overview on configurator pages'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm' => 600000,
		);

		$propDefs['pm_show_regular_first'] = array(
			'name'=>'pm_show_regular_first',
			'label'=>KText::_('Overview to show first'),
			'type'=>'dropdown',
			'choices'=> array(2=>KText::_('Use Default'),1=>KText::_('Regular Prices'), 0=>KText::_('Recurring Prices')),
			'default'=>2,
			'tooltip'=>KText::_('The order in which to show the 2 overviews.'),
			'positionForm' => 610000,
		);

		$propDefs['pm_show_delivery_options'] = array(
			'name' => 'pm_show_delivery_options',
			'label' => KText::_('Show delivery option'),
			'type' => 'dropdown',
			'default' => 2,
			'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
			'positionForm' => 620000,
		);

		$propDefs['pm_show_payment_options'] = array(
			'name' => 'pm_show_payment_options',
			'label' => KText::_('Show payment option'),
			'type' => 'dropdown',
			'default' => 2,
			'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
			'positionForm' => 630000,
		);

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			$propDefs['pm_show_delivery_options']['invisible'] = true;
			$propDefs['pm_show_delivery_options']['default'] = 0;

			$propDefs['pm_show_payment_options']['invisible'] = true;
			$propDefs['pm_show_payment_options']['default'] = 0;
		}

		$propDefs['pm_show_net_in_b2c'] = array(
			'name'=>'pm_show_net_in_b2c',
			'label'=>KText::_('Show net in B2C mode'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 640000,
		);

		$propDefs['pm_price_module_start_regular'] = array(
			'name'=>'price_module_start_regular',
			'type'=>'groupstart',
			'title'=>KText::_('Regular Prices'),
			'toggle'=>false,
			'positionForm' => 650000,
		);

		$propDefs['pm_regular_show_overview'] = array(
			'name'=>'pm_regular_show_overview',
			'label'=>KText::_('Show overview'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 660000,
		);

		$propDefs['pm_regular_show_prices'] = array(
			'name'=>'pm_regular_show_prices',
			'label'=>KText::_('Show Prices'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 670000,
		);

		$propDefs['pm_regular_show_categories'] = array(
			'name'=>'pm_regular_show_categories',
			'label'=>KText::_('Show Configurator Pages'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 680000,
		);

		$propDefs['pm_regular_show_elements'] = array(
			'name'=>'pm_regular_show_elements',
			'label'=>KText::_('Show questions'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 690000,
		);

		$propDefs['pm_regular_show_elementprices'] = array(
			'name'=>'pm_regular_show_elementprices',
			'label'=>KText::_('Show question prices'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 700000,
		);

		$propDefs['pm_regular_expand_categories'] = array(
			'name'=>'pm_regular_expand_categories',
			'label'=>KText::_('Expand Configurator Pages'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('All'), 0=>KText::_('No Page'), 2=>KText::_('Active page only'), 3=>KText::_('Use Default')),
			'default'=>3,
			'positionForm' => 710000,
		);

		$propDefs['pm_regular_show_taxes'] = array(
			'name'=>'pm_regular_show_taxes',
			'label'=>KText::_('Show taxes'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 720000,
		);

		$propDefs['pm_regular_show_cart_button'] = array(
			'name' => 'pm_regular_show_cart_button',
			'label' => KText::_('Show cart button'),
			'type' => 'dropdown',
			'default' => 2,
			'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
			'positionForm' => 730000,
		);

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			$propDefs['pm_regular_show_cart_button']['invisible'] = true;
			$propDefs['pm_regular_show_cart_button']['default'] = 0;
		}

		$propDefs['pm_price_module_end_regular'] = array(
			'name'=>'price_module_end_regular',
			'type'=>'groupend',
			'opentable'=>0,
			'positionForm' => 740000,
		);

		$propDefs['pm_price_module_start_recurring'] = array(
			'name'=>'price_module_start_recurring',
			'type'=>'groupstart',
			'title'=>KText::_('Recurring Prices'),
			'positionForm' => 750000,
			'appliesWhen' => array(
				'use_recurring_pricing'=>'1',
			),
		);

		$propDefs['pm_recurring_show_overview'] = array(
			'name'=>'pm_recurring_show_overview',
			'label'=>KText::_('Show overview'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 760000,
		);

		$propDefs['pm_recurring_show_prices'] = array(
			'name'=>'pm_recurring_show_prices',
			'label'=>KText::_('Show Prices'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 770000,
		);

		$propDefs['pm_recurring_show_categories'] = array(
			'name'=>'pm_recurring_show_categories',
			'label'=>KText::_('Show Configurator Pages'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 780000,
		);

		$propDefs['pm_recurring_show_elements'] = array(
			'name'=>'pm_recurring_show_elements',
			'label'=>KText::_('Show questions'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 790000,
		);

		$propDefs['pm_recurring_show_elementprices'] = array(
			'name'=>'pm_recurring_show_elementprices',
			'label'=>KText::_('Show question prices'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 800000,
		);

		$propDefs['pm_recurring_expand_categories'] = array(
			'name'=>'pm_recurring_expand_categories',
			'label'=>KText::_('Expand Configurator Pages'),
			'type'=>'dropdown',
			'choices'=> array(1=>KText::_('All'), 0=>KText::_('No Page'), 2=>KText::_('Active page only'), 3=>KText::_('Use Default')),
			'default'=>3,
			'positionForm' => 810000,
		);

		$propDefs['pm_recurring_show_taxes'] = array(
			'name'=>'pm_recurring_show_taxes',
			'label'=>KText::_('Show taxes'),
			'type'=>'dropdown',
			'default'=>2,
			'choices'=> array(1=>KText::_('CBYES'), 0=>KText::_('CBNO'), 2=>KText::_('Use Default')),
			'positionForm' => 820000,
		);

		$propDefs['pm_recurring_show_cart_button'] = array(
			'name' => 'pm_recurring_show_cart_button',
			'label' => KText::_('Show cart button'),
			'type' => 'dropdown',
			'default' => 2,
			'items' => array(1 => KText::_('CBYES'), 0 => KText::_('CBNO'), 2 => KText::_('Use Default')),
			'positionForm' => 830000,
		);

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			$propDefs['pm_recurring_show_cart_button']['invisible'] = true;
			$propDefs['pm_recurring_show_cart_button']['default'] = 0;
		}

		$propDefs['price_module_end_recurring'] = array(
			'name'=>'price_module_end_recurring',
			'type'=>'groupend',
			'positionForm'=>840000,
		);

		$propDefs['price_module_end'] = array(
			'name'=>'price_module_end',
			'type'=>'groupend',
			'opentable'=>1,
			'positionForm'=>850000,
		);

		$propDefs['misc_start'] = array(
			'name' => 'misc_start',
			'type' => 'groupstart',
			'title' => KText::_('Others'),
			'toggle' => true,
			'defaultState' => 'closed',
			'positionForm' => 1000000,
		);

		$propDefs['sku'] = array(
			'name' => 'sku',
			'label' => KText::_('LABEL_PRODUCT_SKU'),
			'type' => 'string',
			'size' => '50',
			'required' => 0,
			'tooltip' => KText::_('The article ID of the product.'),
			'listing' => 30,
			'order' => 30,
			'search' => 2,
			'filter' => 2,
			'listingwidth' => '100px',
			'positionForm' => 1001000,
		);

		$propDefs['use_recurring_pricing'] = array(
			'name' => 'use_recurring_pricing',
			'label' => KText::_('Does the product also use recurring pricing?'),
			'tooltip' => KText::_('If you want to show one-time and recurring prices, click yes. There will be price entries for two types of prices then.'),
			'type' => 'boolean',
			'default' => 0,
			'positionForm' => 1002000,
		);

		$propDefs['label'] = array(
			'name' => 'label',
			'label' => KText::_('LABEL_SEF_SEGMENT'),
			'tooltip' => KText::_('TOOLTIP_SEF_SEGMENT'),
			'required' => 0,
			'type' => 'translatable',
			'stringTable' => '#__configbox_strings',
			'langType' => 17,
			'positionForm' => 1003000,
		);

		$propDefs['dispatch_time'] = array(
			'name' => 'dispatch_time',
			'label' => KText::_('Dispatch Time'),
			'tooltip' => KText::_('Define how many days it takes to dispatch the product after the order is completed. Set to 0 for immediate dispatch.'),
			'type' => 'string',
			'stringType' => 'number',
			'default' => 0,
			'unit' => KText::_('days'),
			'positionForm' => 1005000,
		);

		$propDefs['baseweight'] = array(
			'name' => 'baseweight',
			'label' => KText::_('Base Weight'),
			'type' => 'string',
			'stringType' => 'number',
			'unit' => CbSettings::getInstance()->get('weightunits'),
			'tooltip' => KText::_('The product weight without any upgrades. You can change the weight unit in the configuration globally.'),
			'positionForm' => 1006000,
		);

		$propDefs['misc_end'] = array(
			'name' => 'misc_end',
			'type' => 'groupend',
			'positionForm' => 1200000,
		);

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {

			unset($propDefs['misc_start'], $propDefs['misc_end']);

			$propDefs['sku']['invisible'] = true;
			$propDefs['sku']['default'] = 0;

			$propDefs['label']['invisible'] = true;
			$propDefs['label']['default'] = 0;

			$propDefs['dispatch_time']['invisible'] = true;
			$propDefs['dispatch_time']['default'] = 0;

			$propDefs['baseweight']['invisible'] = true;
			$propDefs['baseweight']['default'] = 0;

			$propDefs['use_recurring_pricing']['invisible'] = true;
			$propDefs['use_recurring_pricing']['default'] = 0;
		}

		return $propDefs;

	}

	/**
	 * Auto-fills empty URL segments (label) and runs the parent prepare method.
	 *
	 * @see ConfigboxModelAdminproducts::fillEmptyUrlSegments
	 * @param object $data
	 * @return bool
	 */
	function prepareForStorage($data) {

		if (KRequest::getKeyword('task') != 'ajaxStore') {
			// In case we got labels (CB for Magento doesn't), auto-fill labels if nec.
			$props = $this->getProperties();
			if (!empty($props['label'])) {
				$this->fillEmptyUrlSegments($data);
			}
		}

		return parent::prepareForStorage($data);
	}

	/**
	 * Checks for duplicate URL segments (label) and runs the parent checks.
	 * Copies old URL segments into the old_labels table if all checks are ok.
	 *
	 * @see ConfigboxModelAdminproducts::checkForDuplicateUrlSegment, ConfigboxModelAdminproducts::storeOldUrlSegments
	 * @param object $data
	 * @param string $context
	 * @return bool
	 */
	function validateData($data, $context = '') {

		$response = parent::validateData($data, $context);

		if ($response === false) {
			return false;
		}

		// Fill the URL segment (label), but not for ajaxStore (no titles sent)
		if (KRequest::getKeyword('task') != 'ajaxStore') {
			$response = $this->checkForDuplicateUrlSegment($data);

			if ($response === false) {
				return false;
			}
		}

		$this->storeOldUrlSegments($data);

		return true;

	}

	function canDelete($id) {

		$db = KenedoPlatform::getDb();

		$query = "SELECT `id` FROM `#__configbox_pages` WHERE `product_id` = ".intval($id);
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result) {
			$this->setError(KText::_('Could not delete product because it contains configurator pages.'));
		}

		$query = "SELECT `id` FROM `#__configbox_product_detail_panes` WHERE `product_id` = ".intval($id);
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result) {
			$this->setError(KText::_('Could not delete product because it contains product detail panes.'));
		}


		if (count($this->getErrors())) {
			return false;
		}
		else {
			return true;
		}
	}

	function afterDelete( $id ) {

		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_oldlabels` WHERE `key` = ".(int)$id." AND `type` = 17";
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__configbox_xref_listing_product` WHERE `product_id` = ".(int)$id;
		$db->setQuery($query);
		$db->query();

		return true;

	}

	/**
	 * Helper method for prepareForStorage().
	 * Auto-fills empty URL Segment fields (property is called label)
	 *
	 * @param object $data Data object as coming in to prepareForStorage
	 */
	protected function fillEmptyUrlSegments(&$data) {

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			return;
		}

		$tags = KenedoLanguageHelper::getActiveLanguageTags();

		foreach ($tags as $tag) {

			// Prepare keys for readablity
			$segmentKey = 'label-'.$tag;
			$titleKey = 'title-'.$tag;

			if (empty($data->$segmentKey)) {

				// Get the corresponding title value
				$autoValue = $data->$titleKey;

				// Make the value URL-friendly
				$autoValue = str_replace(' ','-', trim($autoValue));
				$autoValue = preg_replace('/[^A-Za-z0-9\-]/', '', $autoValue);
				$autoValue = strtolower($autoValue);
				// If nothing is left of the value, use the current datetime
				if(trim(str_replace('-','',$autoValue)) == '') {
					$autoValue = KenedoTimeHelper::getFormatted('NOW','datetime');
				}

				// Set the url segment to the auto value
				$data->$segmentKey = $autoValue;

			}
		}

	}


	/**
	 * Helper method for validateData(). Checks if provided URL segment (label) is already used in other pages of
	 * the same product.
	 * @param object $data
	 * @return bool
	 */
	protected function checkForDuplicateUrlSegment($data) {

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			return true;
		}

		$languages = KenedoLanguageHelper::getActiveLanguages();

		$db = KenedoPlatform::getDb();

		foreach ($languages as $language) {

			// Prepare url segment value for convenience
			$segmentValue = $data->{'label-'.$language->tag};

			// Search for an existing product URL segment value
			$query = "
			SELECT p.id
			FROM `#__configbox_products` AS p
			LEFT JOIN `#__configbox_strings` AS url_segment ON url_segment.key = p.id AND url_segment.type = 17 AND url_segment.language_tag = '".$db->getEscaped($language->tag)."'
			WHERE p.id != ".intval($data->id)." AND url_segment.text = '".$db->getEscaped($segmentValue)."'
			LIMIT 1";
			$db->setQuery($query);
			$productId = $db->loadResult();

			if ($productId) {
				$productTitle = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 1, $productId);
				$this->setError(KText::sprintf('PRODUCTS_FEEDBACK_SEF_SEGMENT_TAKEN', $segmentValue, $productTitle, $language->label));
				return false;
			}

		}

		return true;

	}

	/**
	 * Checks if the URL segment has changed. If so, the methods stores a copy in the oldlabels table.
	 * @param object $data
	 */
	protected function storeOldUrlSegments($data) {

		if (KenedoPlatform::getName() == 'magento' || KenedoPlatform::getName() == 'magento2') {
			return;
		}

		// In case of inserts, don't act
		if ($this->isInsert($data)) {
			return;
		}

		// Get the current URL segments (The ones that will get replaced)
		$db = KenedoPlatform::getDb();
		$query = "
		SELECT segment.text, segment.language_tag, p.id AS product_id
		FROM `#__configbox_strings` AS segment
		LEFT JOIN `#__configbox_products` AS p ON p.id = ".intval($data->id)."
		WHERE segment.key = ".intval($data->id)." AND segment.type = 17 AND p.id = ".intval($data->id);
		$db->setQuery($query);
		$currentSegments = $db->loadObjectList('language_tag');

		// Get the active languages
		$languages = KenedoLanguageHelper::getActiveLanguages();

		foreach ($languages as $language) {

			// Prepare segment value for convenience
			$newSegment = $data->{'label-' . $language->tag};
			$currentSegment = !empty($currentSegments[$language->tag]->text) ? $currentSegments[$language->tag]->text : '';

			if ($currentSegment && $newSegment != $currentSegment) {
				$query = "
					REPLACE INTO `#__configbox_oldlabels` (`key`, `type`, `label`, `language_tag`, `created`, `prod_id`)
					VALUES (".intval($data->id).", 17, '".$db->getEscaped($currentSegment)."', '".$db->getEscaped($language->tag)."', ".time().", ".intval($currentSegments[$language->tag]->product_id).")";
				$db->setQuery($query);
				$db->query();
			}

		}

	}

}