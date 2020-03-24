<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincountries extends KenedoModel {

	function getTableName() {
		return '#__configbox_countries';
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
			'label'=>KText::_('ID'),
			'positionForm'=>100,
		);

		$propDefs['general_start'] = array(
			'name'=>'general_start',
			'type'=>'groupstart',
			'title'=>KText::_('Country Edit General'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>200,
		);

		$propDefs['country_name'] = array(
			'name'=>'country_name',
			'label'=>KText::_('Country Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=>1,
			'listinglink'=>1,
			'filter'=>2,
			'search'=>true,
			'order'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincountries',
			'positionForm'=>300,
		);

		$propDefs['zones'] = array(
			'name'=>'zones',
			'label'=>KText::_('Zones'),
			'type'=>'multiselect',
			'required'=>0,

			'modelClass'=>'ConfigboxModelAdminzones',
			'modelMethod'=>'getRecords',

			'xrefTable'=>'#__configbox_xref_country_zone',
			'fkOwn'=>'country_id',
			'fkOther'=>'zone_id',

			'keyOwn'=>'id',

			'tableOther'=>'#__configbox_zones',
			'keyOther'=>'id',
			'displayColumnOther'=>'label',

			'asCheckboxes' => true,
			'filter'=>1,
			'positionForm'=>400,
		);

		$propDefs['country_3_code'] = array(
			'name'=>'country_3_code',
			'label'=>KText::_('Country 3 Code'),
			'type'=>'string',
			'required'=>1,
			'listing'=>2,
			'listingwidth'=>'30px',
			'order'=>2,
			'positionForm'=>500,
		);

		$propDefs['country_2_code'] = array(
			'name'=>'country_2_code',
			'label'=>KText::_('Country 2 Code'),
			'type'=>'string',
			'required'=>1,
			'listing'=>3,
			'listingwidth'=>'30px',
			'order'=>3,
			'positionForm'=>600,
		);

		$propDefs['vat_free'] = array(
			'name'=>'vat_free',
			'label'=>KText::_('VAT free'),
			'type'=>'boolean',
			'tooltip'=>KText::_('Send VAT free to this country.'),
			'default'=>0,
			'listing'=>4,
			'order'=>4,
			'positionForm'=>700,
		);

		$propDefs['in_eu_vat_area'] = array(
			'name'=>'in_eu_vat_area',
			'label'=>KText::_('LABEL_COUNTRIES_IN_VAT_AREA'),
			'tooltip'=>KText::_('TOOLTIP_COUNTRIES_IN_VAT_AREA'),
			'type'=>'boolean',
			'default'=>0,
			'listing'=>4,
			'order'=>4,
			'positionForm'=>700,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'listing'=>7,
			'order'=>7,
			'listingwidth'=>'50px',
			'positionForm'=>900,
		);

		$propDefs['general_end'] = array(
			'name'=>'general_end',
			'type'=>'groupend',
			'positionForm'=>1000,
		);

		$propDefs['tax_start'] = array(
			'name'=>'tax_start',
			'type'=>'groupstart',
			'title'=>KText::_('Tax Override'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>1100,
		);

		$propDefs['tax_class_rates'] = array(
			'name'=>'tax_class_rates',
			'type'=>'taxclassrates',
			'taxclasstype'=>'country',
			'positionForm'=>1200,
		);

		$propDefs['tax_end'] = array(
			'name'=>'tax_end',
			'type'=>'groupend',
			'positionForm'=>1300,
		);

		$propDefs['custom_start'] = array(
			'name'=>'custom_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>1400,
		);

		$propDefs['custom_1'] = array(
			'name'=>'custom_1',
			'label'=>KText::_('Custom Field 1'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1500,
		);

		$propDefs['custom_2'] = array(
			'name'=>'custom_2',
			'label'=>KText::_('Custom Field 2'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1600,
		);

		$propDefs['custom_3'] = array(
			'name'=>'custom_3',
			'label'=>KText::_('Custom Field 3'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1700,
		);

		$propDefs['custom_4'] = array(
			'name'=>'custom_4',
			'label'=>KText::_('Custom Field 4'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1800,
		);

		$propDefs['custom_translatable_1'] = array(
			'name'=>'custom_translatable_1',
			'label'=>KText::_('Custom Translatable 1'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>42,
			'required'=>0,
			'positionForm'=>1900,
		);

		$propDefs['custom_translatable_2'] = array(
			'name'=>'custom_translatable_2',
			'label'=>KText::_('Custom Translatable 2'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>43,
			'required'=>0,
			'positionForm'=>2000,
		);

		$propDefs['custom_end'] = array(
			'name'=>'custom_end',
			'type'=>'groupend',
			'positionForm'=>2100,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'order'=>6,
			'listing'=>6,
			'listingwidth'=>'70px',
			'disableSortable'=>true,
			'positionForm'=>2200,
		);

		return $propDefs;
	}

}