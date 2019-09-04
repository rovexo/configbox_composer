<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminshippingmethods extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_shipping_methods';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listingwidth'=>'50px',
			'listing'=>10,
			'positionForm'=>100,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>45,
			'required'=>1,
			'listing'=>30,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminshippingmethods',
			'positionForm'=>200,
		);

		$propDefs['shipper_id'] = array(
			'name'=>'shipper_id',
			'label'=>KText::_('Shipper'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Shipper'),

			'modelClass'=>'ConfigboxModelAdminshippers',
			'modelMethod'=>'getRecords',

			'joinAdditionalProps'=>array(
				array('propertyName'=>'title', 	'selectAliasOverride'=>'shipper_title'),
			),

			'required'=>1,
			'filterparents'=>1,
			'filter'=>2,
			'parent'=>1,
			'listing'=>40,
			'listingwidth'=>'120px',
			'positionForm'=>300,
		);

		$propDefs['zone_id'] = array(
			'name'=>'zone_id',
			'label'=>KText::_('Zone'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'label',
			'defaultlabel'=>KText::_('Select Zone'),

			'modelClass'=>'ConfigboxModelAdminzones',
			'modelMethod'=>'getRecords',

			'joinAdditionalProps'=>array(
				array('propertyName'=>'label', 	'selectAliasOverride'=>'zone_label'),
			),

			'parent'=>1,
			'filterparents'=>1,
			'filter'=>1,
			'required'=>1,
			'listing'=>50,
			'listingwidth'=>'120px',
			'positionForm'=>400,
		);

		$propDefs['minweight'] = array(
			'name'=>'minweight',
			'label'=>KText::_('Minimum Weight'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>CbSettings::getInstance()->get('weightunits'),
			'required'=>1,
			'listing'=>60,
			'listingwidth'=>'60px',
			'order'=>14,
			'positionForm'=>500,
		);

		$propDefs['maxweight'] = array(
			'name'=>'maxweight',
			'label'=>KText::_('Maximum Weight'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>CbSettings::getInstance()->get('weightunits'),
			'required'=>1,
			'listing'=>70,
			'listingwidth'=>'60px',
			'order'=>15,
			'positionForm'=>600,
		);

		$propDefs['deliverytime'] = array(
			'name'=>'deliverytime',
			'label'=>KText::_('Delivery Time'),
			'type'=>'string',
			'stringType'=>'number',
			'required'=>0,
			'unit'=>KText::_('Days'),
			'positionForm'=>700,
		);

		$propDefs['price'] = array(
			'name'=>'price',
			'label'=>KText::_('Price'),
			'type'=>'string',
			'stringType'=>'price',
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'required'=>0,
			'listing'=>80,
			'listingwidth'=>'60px',
			'order'=>20,
			'positionForm'=>800,
		);

		$propDefs['taxclass_id'] = array(
			'name'=>'taxclass_id',
			'label'=>KText::_('Tax Class'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',

			'modelClass'=>'ConfigboxModelAdmintaxclasses',
			'modelMethod'=>'getRecords',

			'tooltip'=>KText::_('Choose a tax class that determines which tax rate will be used. In the order management you can override the tax rate of a tax rate for each country or state. This way you can have different tax rates for one product or service, depending on the delivery country.'),
			'required'=>0,
			'options'=>'SKIPDEFAULTFIELD NOFILTERSAPPLY',
			'positionForm'=>900,
		);

		$propDefs['external_id'] = array(
			'name'=>'external_id',
			'label'=>KText::_('External ID'),
			'type'=>'string',
			'stringType'=>'string',
			'required'=>0,
			'positionForm'=>1000,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'listing'=>100,
			'listingwidth'=>'60px',
			'order'=>50,
			'positionForm'=>1100,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'order'=>60,
			'listing'=>20,
			'positionForm'=>1200,
		);

		return $propDefs;

	}
}
