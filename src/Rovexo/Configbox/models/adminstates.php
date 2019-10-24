<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminstates extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_states';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['general_start'] = array(
			'name'=>'general_start',
			'type'=>'groupstart',
			'title'=>KText::_('General'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>100,
		);

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listingwidth'=>'50px',
			'listing'=>10,
			'positionForm'=>200,
		);

		$propDefs['name'] = array(
			'name'=>'name',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'order'=>10,
			'filter'=>1,
			'search'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminstates',
			'positionForm'=>300,
		);

		$propDefs['iso_code'] = array(
			'name'=>'iso_code',
			'label'=>KText::_('State code'),
			'type'=>'string',
			'required'=>0,
			'listing'=>30,
			'order'=>20,
			'listingwidth'=>'50px',
			'positionForm'=>400,
		);

		$propDefs['fips_number'] = array(
			'name'=>'fips_number',
			'label'=>KText::_('FIPS Number'),
			'type'=>'string',
			'required'=>1,
			'listing'=>35,
			'order'=>30,
			'listingwidth'=>'50px',
			'positionForm'=>500,
		);

		$propDefs['country_id'] = array(
			'name'=>'country_id',
			'label'=>KText::_('Country'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'country_name',
			'defaultlabel'=>KText::_('Select Country'),

			'modelClass'=>'ConfigboxModelAdmincountries',
			'modelMethod'=>'getRecords',

			'joinAdditionalProps'=>array(
				array('propertyName'=>'country_name', 	'selectAliasOverride'=>'country_name'),
			),

			'required'=>1,
			'parent'=>1,
			'filterparents'=>1,
			'filter'=>2,
			'listing'=>40,
			'order'=>40,

			'listingwidth'=>'100px',
			'positionForm'=>600,
		);

		$propDefs['custom_1'] = array(
			'name'=>'custom_1',
			'label'=>KText::_('Custom Field 1'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>700,
		);

		$propDefs['custom_2'] = array(
			'name'=>'custom_2',
			'label'=>KText::_('Custom Field 2'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>800,
		);

		$propDefs['custom_3'] = array(
			'name'=>'custom_3',
			'label'=>KText::_('Custom Field 3'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>900,
		);

		$propDefs['custom_4'] = array(
			'name'=>'custom_4',
			'label'=>KText::_('Custom Field 4'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1000,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'order'=>6,
			'listing'=>15,
			'group'=>'country_id',
			'positionForm'=>1100,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'listing'=>100,
			'listingwidth'=>'50px',
			'default'=>1,
			'positionForm'=>1200,
		);

		$propDefs['general_end'] = array(
			'name'=>'general_end',
			'type'=>'groupend',
			'positionForm'=>1300,
		);

		$propDefs['tax_start'] = array(
			'name'=>'tax_start',
			'type'=>'groupstart',
			'title'=>KText::_('Tax Override'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>1400,
		);

		$propDefs['tax_class_rates'] = array(
			'name'=>'tax_class_rates',
			'type'=>'taxclassrates',
			'taxclasstype'=>'state',
			'positionForm'=>1500,
		);

		$propDefs['tax_end'] = array(
			'name'=>'tax_end',
			'type'=>'groupend',
			'positionForm'=>1600,
		);

		return $propDefs;

	}

}