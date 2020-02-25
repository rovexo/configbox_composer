<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincustomergroups extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_groups';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array (
			'name'=>'id',
			'label'=>KText::_('ID'),
			'type'=>'id',
			'default'=>0,
			'listing'=>10,
			'listingwidth'=>'50px',
			'positionForm'=>100,
		);

		$propDefs['title'] = array (
			'name'=>'title',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=> 20,
			'listinglink'=>true,
			'component'=>'com_configbox',
			'controller'=>'admincustomergroups',
			'positionForm'=>200,
		);

		if (KenedoPlatform::getName() == 'joomla') {

			$propDefs['joomla_user_group_id'] = array(
				'name'=>'joomla_user_group_id',
				'label'=>KText::_('Platform Group'),
				'tooltip'=>KText::_('The user group of your platform CMS. Choose one to assign new customers to that group.'),
				'type'=>'join',
				'isPseudoJoin'=>true,
				'propNameKey'=>'id',
				'propNameDisplay'=>'title',
				'modelClass'=>'ConfigboxModelAdminplatformgroups',
				'modelMethod'=>'getGroups',
				'required'=>1,
				'positionForm'=>300,
			);

		}

		$propDefs['b2b_mode'] = array(
			'name'=>'b2b_mode',
			'label'=>KText::_('Tax display mode'),
			'tooltip'=>KText::_('In B2B mode, prices in the configuration are displayed net and price overviews are displayed in the form net plus tax is gross. In B2C, no net prices are shown and the included tax is displayed along with the gross price.'),
			'type'=>'radio',
			'choices'=> array(0=>KText::_('B2C'), 1=>KText::_('B2B')),
			'default'=>0,
			'positionForm'=>400,
		);

		$propDefs['permissions_start'] = array(
			'name'=>'permissions_start',
			'type'=>'groupstart',
			'title'=>KText::_('Permissions'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>500,
		);

		$propDefs['enable_see_pricing'] = array(
			'name'=>'enable_see_pricing',
			'label'=>KText::_('Enable price display in configurator and cart'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>600,
		);

		$propDefs['enable_checkout_order'] = array(
			'name'=>'enable_checkout_order',
			'label'=>KText::_('Enable checkout'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>700,
		);

		$propDefs['enable_save_order'] = array(
			'name'=>'enable_save_order',
			'label'=>KText::_('Enable Save Order'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>800,
		);

		$propDefs['enable_request_quotation'] = array(
			'name'=>'enable_request_quotation',
			'label'=>KText::_('Enable Quotation Request'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>1100,
		);

		$propDefs['quotation_download'] = array(
			'name'=>'quotation_download',
			'label'=>KText::_('Automated quotation download'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>1200,
		);

		$propDefs['quotation_email'] = array(
			'name'=>'quotation_email',
			'label'=>KText::_('Automated quotation via email'),
			'tooltip'=>KText::_('You need to have a notification for the status Quotation sent in order. You can manage notifications at Order Management -> Notifications.'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>1300,
		);

		$propDefs['permissions_end'] = array(
			'name'=>'permissions_end',
			'type'=>'groupend',
			'positionForm'=>1400,
		);

		$propDefs['discounts_start'] = array(
			'name'=>'discounts_start',
			'type'=>'groupstart',
			'toggle'=>true,
			'title'=>KText::_('Discount Levels'),
			'defaultState'=>'closed',
			'positionForm'=>1500,
		);

		$propDefs['discounts_regular_start'] = array(
			'name'=>'discounts_regular_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Regular Discounts'),
			'positionForm'=>1600,
		);

		$propDefs['level_1_start'] = array(
			'name'=>'level_1_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 1'),
			'positionForm'=>1700,
		);

		$propDefs['discount_start_1'] = array(
			'name'=>'discount_start_1',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>1800,
		);

		$propDefs['discount_type_1'] = array(
			'name'=>'discount_type_1',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>1900,
		);

		$propDefs['discount_amount_1'] = array(
			'name'=>'discount_amount_1',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>2000,
			'appliesWhen'=>array(
				'discount_type_1'=>'amount',
			),
		);

		$propDefs['discount_factor_1'] = array(
			'name'=>'discount_factor_1',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>2100,
			'appliesWhen'=>array(
				'discount_type_1'=>'percentage',
			),
		);

		$propDefs['title_discount_1'] = array(
			'name'=>'title_discount_1',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>80,
			'positionForm'=>2200,
		);

		$propDefs['level_1_end'] = array(
			'name'=>'level_1_end',
			'type'=>'groupend',
			'positionForm'=>2300,
		);

		$propDefs['level_2_start'] = array(
			'name'=>'level_2_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 2'),
			'positionForm'=>2400,
		);

		$propDefs['discount_start_2'] = array(
			'name'=>'discount_start_2',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>2500,
		);

		$propDefs['discount_type_2'] = array(
			'name'=>'discount_type_2',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>2600,
		);

		$propDefs['discount_amount_2'] = array(
			'name'=>'discount_amount_2',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>2700,
			'appliesWhen'=>array(
				'discount_type_2'=>'amount',
			),
		);

		$propDefs['discount_factor_2'] = array(
			'name'=>'discount_factor_2',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>2800,
			'appliesWhen'=>array(
				'discount_type_2'=>'percentage',
			),
		);

		$propDefs['title_discount_2'] = array(
			'name'=>'title_discount_2',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>81,
			'positionForm'=>2900,
		);

		$propDefs['level_2_end'] = array(
			'name'=>'level_2_end',
			'type'=>'groupend',
			'positionForm'=>3000,
		);

		$propDefs['level_3_start'] = array(
			'name'=>'level_3_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 3'),
			'positionForm'=>3100,
		);

		$propDefs['discount_start_3'] = array(
			'name'=>'discount_start_3',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>3200,
		);

		$propDefs['discount_type_3'] = array(
			'name'=>'discount_type_3',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>3300,
		);

		$propDefs['discount_amount_3'] = array(
			'name'=>'discount_amount_3',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>3400,
		);

		$propDefs['discount_factor_3'] = array(
			'name'=>'discount_factor_3',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>3500,
		);

		$propDefs['title_discount_3'] = array(
			'name'=>'title_discount_3',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>82,
			'positionForm'=>3600,
		);

		$propDefs['level_3_end'] = array(
			'name'=>'level_3_end',
			'type'=>'groupend',
			'positionForm'=>3700,
		);

		$propDefs['level_4_start'] = array(
			'name'=>'level_4_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 4'),
			'positionForm'=>3800,
		);

		$propDefs['discount_start_4'] = array(
			'name'=>'discount_start_4',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>3900,
		);

		$propDefs['discount_type_4'] = array(
			'name'=>'discount_type_4',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>4000,
		);

		$propDefs['discount_amount_4'] = array(
			'name'=>'discount_amount_4',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>4100,
		);

		$propDefs['discount_factor_4'] = array(
			'name'=>'discount_factor_4',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>4200,
		);

		$propDefs['title_discount_4'] = array(
			'name'=>'title_discount_4',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>83,
			'positionForm'=>4300,
		);

		$propDefs['level_4_end'] = array(
			'name'=>'level_4_end',
			'type'=>'groupend',
			'positionForm'=>4400,
		);

		$propDefs['level_5_start'] = array(
			'name'=>'level_5_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 5'),
			'positionForm'=>4500,
		);

		$propDefs['discount_start_5'] = array(
			'name'=>'discount_start_5',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>4600,
		);

		$propDefs['discount_type_5'] = array(
			'name'=>'discount_type_5',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>4700,
		);

		$propDefs['discount_amount_5'] = array(
			'name'=>'discount_amount_5',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>4800,
		);

		$propDefs['discount_factor_5'] = array(
			'name'=>'discount_factor_5',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>4900,
		);

		$propDefs['title_discount_5'] = array(
			'name'=>'title_discount_5',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>84,
			'positionForm'=>5000,
		);

		$propDefs['level_5_end'] = array(
			'name'=>'level_5_end',
			'type'=>'groupend',
			'positionForm'=>5100,
		);

		$propDefs['discounts_regular_end'] = array(
			'name'=>'discounts_regular_end',
			'type'=>'groupend',
			'positionForm'=>5200,
		);


		$propDefs['discounts_recurring_start'] = array(
			'name'=>'discounts_recurring_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Recurring Discounts'),
			'positionForm'=>5300,
		);

		$propDefs['recurring_level_1_start'] = array(
			'name'=>'recurring_level_1_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 1'),
			'positionForm'=>5400,
		);

		$propDefs['discount_recurring_start_1'] = array(
			'name'=>'discount_recurring_start_1',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>5500,
		);

		$propDefs['discount_recurring_type_1'] = array(
			'name'=>'discount_recurring_type_1',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>5600,
		);

		$propDefs['discount_recurring_amount_1'] = array(
			'name'=>'discount_recurring_amount_1',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>5700,
		);

		$propDefs['discount_recurring_factor_1'] = array(
			'name'=>'discount_recurring_factor_1',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>5800,
		);

		$propDefs['title_discount_recurring_1'] = array(
			'name'=>'title_discount_recurring_1',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>85,
			'positionForm'=>5900,
		);

		$propDefs['recurring_level_1_end'] = array(
			'name'=>'recurring_level_1_end',
			'type'=>'groupend',
			'positionForm'=>6000,
		);

		$propDefs['recurring_level_2_start'] = array(
			'name'=>'recurring_level_2_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 2'),
			'positionForm'=>6100,
		);

		$propDefs['discount_recurring_start_2'] = array(
			'name'=>'discount_recurring_start_2',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>6200,
		);

		$propDefs['discount_recurring_type_2'] = array(
			'name'=>'discount_recurring_type_2',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>6300,
		);

		$propDefs['discount_recurring_amount_2'] = array(
			'name'=>'discount_recurring_amount_2',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>6400,
		);

		$propDefs['discount_recurring_factor_2'] = array(
			'name'=>'discount_recurring_factor_2',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>6500,
		);

		$propDefs['title_discount_recurring_2'] = array(
			'name'=>'title_discount_recurring_2',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>86,
			'positionForm'=>6600,
		);

		$propDefs['recurring_level_2_end'] = array(
			'name'=>'recurring_level_2_end',
			'type'=>'groupend',
			'positionForm'=>6700,
		);

		$propDefs['recurring_level_3_start'] = array(
			'name'=>'recurring_level_3_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 3'),
			'positionForm'=>6800,
		);

		$propDefs['discount_recurring_start_3'] = array(
			'name'=>'discount_recurring_start_3',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>6900,
		);

		$propDefs['discount_recurring_type_3'] = array(
			'name'=>'discount_recurring_type_3',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>7000,
		);

		$propDefs['discount_recurring_amount_3'] = array(
			'name'=>'discount_recurring_amount_3',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>7100,
		);

		$propDefs['discount_recurring_factor_3'] = array(
			'name'=>'discount_recurring_factor_3',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>7200,
		);

		$propDefs['title_discount_recurring_3'] = array(
			'name'=>'title_discount_recurring_3',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>87,
			'positionForm'=>7300,
		);

		$propDefs['recurring_level_3_end'] = array(
			'name'=>'recurring_level_3_end',
			'type'=>'groupend',
			'positionForm'=>7400,
		);

		$propDefs['recurring_level_4_start'] = array(
			'name'=>'recurring_level_4_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 4'),
			'positionForm'=>7500,
		);

		$propDefs['discount_recurring_start_4'] = array(
			'name'=>'discount_recurring_start_4',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>7600,
		);

		$propDefs['discount_recurring_type_4'] = array(
			'name'=>'discount_recurring_type_4',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>7700,
		);

		$propDefs['discount_recurring_amount_4'] = array(
			'name'=>'discount_recurring_amount_4',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>7800,
		);

		$propDefs['discount_recurring_factor_4'] = array(
			'name'=>'discount_recurring_factor_4',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>7900,
		);

		$propDefs['title_discount_recurring_4'] = array(
			'name'=>'title_discount_recurring_4',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>88,
			'positionForm'=>8000,
		);

		$propDefs['recurring_level_4_end'] = array(
			'name'=>'recurring_level_4_end',
			'type'=>'groupend',
			'positionForm'=>8100,
		);

		$propDefs['recurring_level_5_start'] = array(
			'name'=>'recurring_level_5_start',
			'type'=>'groupstart',
			'toggle'=>false,
			'title'=>KText::_('Discount Level 5'),
			'positionForm'=>8200,
		);

		$propDefs['discount_recurring_start_5'] = array(
			'name'=>'discount_recurring_start_5',
			'label'=>KText::_('For net order totals starting at:'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>8300,
		);

		$propDefs['discount_recurring_type_5'] = array(
			'name'=>'discount_recurring_type_5',
			'label'=>KText::_('Discount Type'),
			'type'=>'dropdown',
			'choices'=> array('percentage'=>KText::_('Percentage'), 'amount'=>KText::_('Amount')),
			'default'=>'percentage',
			'positionForm'=>8400,
		);

		$propDefs['discount_recurring_amount_5'] = array(
			'name'=>'discount_recurring_amount_5',
			'label'=>KText::_('Discount Amount'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>8500,
		);

		$propDefs['discount_recurring_factor_5'] = array(
			'name'=>'discount_recurring_factor_5',
			'label'=>KText::_('Discount Percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'positionForm'=>8600,
		);

		$propDefs['title_discount_recurring_5'] = array(
			'name'=>'title_discount_recurring_5',
			'type'=>'translatable',
			'label'=>KText::_('Title'),
			'stringTable'=>'#__configbox_strings',
			'langType'=>89,
			'positionForm'=>8700,
		);

		$propDefs['recurring_level_5_end'] = array(
			'name'=>'recurring_level_5_end',
			'type'=>'groupend',
			'positionForm'=>8800,
		);

		$propDefs['discounts_recurring_end'] = array(
			'name'=>'discounts_recurring_end',
			'type'=>'groupend',
			'positionForm'=>8900,
		);




		$propDefs['discounts_end'] = array(
			'name'=>'discounts_end',
			'type'=>'groupend',
			'positionForm'=>9000,
		);

		$propDefs['custom_start'] = array(
			'name'=>'custom_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>9100,
		);

		$propDefs['custom_1'] = array(
			'name'=>'custom_1',
			'label'=>KText::_('Custom Field 1'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>9200,
		);

		$propDefs['custom_2'] = array(
			'name'=>'custom_2',
			'label'=>KText::_('Custom Field 2'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>9300,
		);

		$propDefs['custom_3'] = array(
			'name'=>'custom_3',
			'label'=>KText::_('Custom Field 3'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>9400,
		);

		$propDefs['custom_4'] = array(
			'name'=>'custom_4',
			'label'=>KText::_('Custom Field 4'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>9500,
		);

		$propDefs['custom_end'] = array(
			'name'=>'custom_end ',
			'type'=>'groupend',
			'positionForm'=>9600,
		);

		return $propDefs;
	}

	/**
	 * @param int $id
	 * @param string $languageTag
	 * @return object|ConfigboxCustomerGroupData
	 * @throws Exception
	 */
	function getRecord($id, $languageTag = '') {
		return parent::getRecord($id, $languageTag);
	}

	/**
	 * @param array $filters
	 * @param array $pagination
	 * @param array $ordering
	 * @param string $languageTag
	 * @param bool $countOnly
	 * @return int|ConfigboxCustomerGroupData[]
	 * @throws Exception
	 */
	function getRecords($filters = array(), $pagination = array(), $ordering = array(), $languageTag = '', $countOnly = false) {
		return parent::getRecords($filters, $pagination, $ordering, $languageTag, $countOnly);
	}

	function getRecordUsageInfo() {

		$usage = array(
			'com_configbox'=>array(
				'ConfigboxModelAdmincustomers'=> array(
					array(
						'titleField'=>'billinglastname',
						'fkField'=>'group_id',
						'controller'=>'admincustomers',
						'name'=>KText::_('Customer'),
						'filterField'=>'is_temporary',
						'filterValue'=>'0',
					)
				)
			)
		);

		return $usage;

	}

}
