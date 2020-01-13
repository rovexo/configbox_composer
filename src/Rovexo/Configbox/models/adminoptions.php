<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminoptions extends KenedoModel {

	function getTableName() {
		return '#__configbox_options';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'listing'=>10,
			'listingwidth'=>'50px',
			'order'=>100,
			'label'=>KText::_('ID'),
			'positionForm'=>1000,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>5,
			'required'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminoptions',
			'order'=>20,
			'search'=>1,
			'filter'=>2,
			'positionForm'=>1200,
		);

		$propDefs['component_pricing_start'] = array(
			'name'=>'component_pricing_start',
			'type'=>'groupstart',
			'title'=>KText::_('GROUP_TITLE_COMPONENT_PRICING'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>1300,
		);

		$propDefs['sku'] = array(
			'name'=>'sku',
			'label'=>KText::_('FIELD_LABEL_COMPONENT_SKU'),
			'tooltip'=>KText::_('TOOLTIP_COMPONENT_SKU'),
			'type'=>'string',
			'size'=>'50',
			'required'=>0,
			'listing'=>30,
			'order'=>30,
			'search'=>1,
			'filter'=>1,
			'listingwidth'=>'150px',
			'positionForm'=>1400,
		);

		$propDefs['price'] = array (
			'name'=>'price',
			'label'=>KText::_('Price'),
			'type'=>'string',
			'stringType'=>'price',
			'allow'=>'?-[0-9]',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'listing'=>40,
			'order'=>40,
			'listingwidth'=>'50px',
			'positionForm'=>1420,
		);

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['price_overrides'] = array(
				'name' => 'price_overrides',
				'label' => KText::_('Price Overrides'),
				'type' => 'groupPrice',
				'overridePropertyName' => 'price',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'positionForm' => 1430,
			);

		}

		$propDefs['was_price'] = array (
			'name'=>'was_price',
			'label'=>KText::_('Was Price'),
			'tooltip'=>KText::_('The Was Price is the striked-through price when you set a price reduction. It is NOT the effective price.'),
			'type'=>'string',
			'stringType'=>'price',
			'allow'=>'?-[0-9]',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>1500,
		);

		$propDefs['price_recurring'] = array (
			'name'=>'price_recurring',
			'label'=>KText::_('Price Recurring'),
			'type'=>'string',
			'stringType'=>'price',
			'allow'=>'?-[0-9]',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'listing'=>50,
			'order'=>50,
			'listingwidth'=>'50px',
			'positionForm'=>1600,
		);

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {

			$propDefs['price_recurring_overrides'] = array(
				'name' => 'price_recurring_overrides',
				'label' => KText::_('Price Recurring Overrides'),
				'type' => 'groupPrice',
				'overridePropertyName' => 'price_recurring',
				'unit' => ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
				'positionForm' => 1650,
			);

		}

		$propDefs['was_price_recurring'] = array (
			'name'=>'was_price_recurring',
			'label'=>KText::_('Was Price Recurring'),
			'tooltip'=>KText::_('The Was Price is the striked-through price when you set a price reduction. It is NOT the effective price.'),
			'type'=>'string',
			'stringType'=>'price',
			'allow'=>'?-[0-9]',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'positionForm'=>1700,
		);

		$propDefs['weight'] = array (
			'name'=>'weight',
			'label'=>KText::_('Weight'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>CbSettings::getInstance()->get('weightunits'),
			'positionForm'=>1800,
		);

		$propDefs['component_pricing_end'] = array(
			'name'=>'component_pricing_end',
			'type'=>'groupend',
			'positionForm'=>2000,
		);

		$propDefs['availability_start'] = array(
			'name'=>'availability_start',
			'type'=>'groupstart',
			'title'=>KText::_('Availability'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('You can temporarily disable options which are sold out or currently unavailable and set the date when they customers can select them again. The date is only informing the customer, you need to make the option available manually.'),
			'positionForm'=>2100,
		);

		$propDefs['available'] = array(
			'name'=>'available',
			'label'=>KText::_('Available'),
			'type'=>'boolean',
			'default'=>1,
			'tooltip'=>KText::_('Set to no, if the option is currently not available.'),
			'listing'=>70,
			'order'=>70,
			'listingwidth'=>'50px',
			'positionForm'=>2200,
		);

		$propDefs['disable_non_available'] = array(
			'name'=>'disable_non_available',
			'label'=>KText::_('Disable when not available'),
			'type'=>'boolean',
			'default'=>0,
			'tooltip'=>KText::_('When set to yes, the option will be unselectable and greyed out.'),
			'positionForm'=>2300,
			'appliesWhen' => array(
				'available'=>'1',
			),
		);

		$propDefs['availibility_date'] = array(
			'name'=>'availibility_date',
			'label'=>KText::_('Availability date'),
			'type'=>'datetime',
			'default'=>NULL,
			'tooltip'=>KText::_('Set the date on which the part will be available again.'),
			'positionForm'=>2400,
			'appliesWhen'=>array(
				'available'=>'1',
			),
		);

		$propDefs['availability_end'] = array(
			'name'=>'availability_end',
			'type'=>'groupend',
			'positionForm'=>2500,
		);

		$propDefs['description_start'] = array(
			'name'=>'description_start',
			'type'=>'groupstart',
			'title'=>KText::_('Description'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>2600,
		);

		$propDefs['desc_display_method'] = array(
			'name'=>'desc_display_method',
			'label'=>KText::_('LABEL_ANSWER_DESCRIPTION_DISPLAY_METHOD'),
			'type'=>'dropdown',
			'choices'=> array(
				'tooltip'=>KText::_('ANSWER_DESC_DISPLAY_TYPE_TOOLTIP'),
				'modal'=>KText::_('ANSWER_DESC_DISPLAY_TYPE_MODAL'),
			),
			'default'=>'tooltip',
			'positionForm'=>2650,
		);

		$propDefs['description'] = array(
			'name'=>'description',
			'label'=>KText::_('Description'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>15,
			'required'=>0,
			'tooltip'=>KText::_('Write a description here to display in a tooltip like this one.'),
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>2700,
		);


		$propDefs['option_image'] = array (
			'name'=>'option_image',
			'label'=>KText::_('Option Image'),
			'type'=>'file',
			'appendSerial'=>1,
			'allowedExtensions'=>array('jpg','jpeg','gif','tif','bmp','png'),
			'filetype'=>'image',
			'tooltip'=>KText::_('Not used in the built-in templates. You can use the data for custom templates.'),
			'allow'=>array('image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png','image/x-png'),
			'required'=>0,
			'size'=>'1000',
			'dirBase'=>CONFIGBOX_DIR_ANSWER_IMAGES,
			'urlBase'=>CONFIGBOX_URL_ANSWER_IMAGES,
			'options'=>'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
			'positionForm'=>2750,
		);


		$propDefs['description_end'] = array(
			'name'=>'description_end',
			'type'=>'groupend',
			'positionForm'=>2800,
		);

		$propDefs['custom_fields_start'] = array(
			'name'=>'custom_fields_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Global Answer Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('With these fields you can add your own data. You can use this data in your own templates and calculation models.'),
			'positionForm'=>3300,
		);

		$label = CbSettings::getInstance()->get('label_option_custom_1');
		if (!$label) {
			$label = KText::_('Custom Field 1');
		}

		$propDefs['option_custom_1'] = array(
			'name'=>'option_custom_1',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','option_custom_1'),
			'positionForm'=>3400,
		);

		$label = CbSettings::getInstance()->get('label_option_custom_2');
		if (!$label) {
			$label = KText::_('Custom Field 2');
		}

		$propDefs['option_custom_2'] = array(
			'name'=>'option_custom_2',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','option_custom_2'),
			'positionForm'=>3500,
		);

		$label = CbSettings::getInstance()->get('label_option_custom_3');
		if (!$label) {
			$label = KText::_('Custom Field 3');
		}

		$propDefs['option_custom_3'] = array(
			'name'=>'option_custom_3',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','option_custom_3'),
			'positionForm'=>3600,
		);

		$label = CbSettings::getInstance()->get('label_option_custom_4');
		if (!$label) {
			$label = KText::_('Custom Field 4');
		}

		$propDefs['option_custom_4'] = array(
			'name'=>'option_custom_4',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','option_custom_4'),
			'positionForm'=>3700,
		);

		$label = CbSettings::getInstance()->get('label_option_custom_5');
		if (!$label) {
			$label = KText::_('Custom Field 5');
		}

		$propDefs['option_custom_5'] = array(
			'name'=>'option_custom_5',
			'label'=> $label,
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>60,
			'required'=>0,
			'tooltip'=>KText::sprintf('You can access this field with the key %s','option_custom_5'),
			'positionForm'=>3800,
		);

		$label = CbSettings::getInstance()->get('label_option_custom_6');
		if (!$label) {
			$label = KText::_('Custom Field 6');
		}

		$propDefs['option_custom_6'] = array(
			'name'=>'option_custom_6',
			'label'=> $label,
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>61,
			'required'=>0,
			'tooltip'=>KText::sprintf('You can access this field with the key %s','option_custom_6'),
			'positionForm'=>3900,
		);

		$propDefs['custom_fields_end'] = array(
			'name'=>'custom_fields_end',
			'type'=>'groupend',
			'positionForm'=>4000,
		);

		return $propDefs;

	}

	function delete($id) {

		$response = parent::delete($id);

		if ($response == false) {
			return false;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_xref_element_option` WHERE `option_id` = ".intval($id);
		$db->setQuery($query);
		$ids = $db->loadResultList('id');

		if (!$ids) {
			return true;
		}

		$model = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$success = $model->delete($ids);
		return $success;

	}

}