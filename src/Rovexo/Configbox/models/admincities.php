<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincities extends KenedoModel {

	function getTableName() {
		return '#__configbox_cities';
	}

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
			'positionForm'=>10,
		);

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listingwidth'=>'50px',
			'listing'=>10,
			'positionForm'=>20,
		);

		$propDefs['city_name'] = array(
			'name'=>'city_name',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'order'=>10,
			'filter'=>1,
			'search'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincities',
			'positionForm'=>30,
		);

		$propDefs['county_id'] = array(
			'name'=>'county_id',
			'label'=>KText::_('County'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'county_name',
			'defaultlabel'=>KText::_('Select County'),

			'modelClass'=>'ConfigboxModelAdmincounties',
			'modelMethod'=>'getRecords',

			'joinAdditionalProps'=>array(
				array(
					'propertyName'=>'county_name',
					'selectAliasOverride'=>'county_name',
					),
			),

			'groupby'=>'country_name',

			'required'=>1,
			'parent'=>1,
			'filterparents'=>1,
			'filter'=>2,
			'listing'=>40,
			'order'=>40,

			'listingwidth'=>'250px',
			'positionForm'=>40,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'listing'=>100,
			'listingwidth'=>'50px',
			'default'=>1,
			'positionForm'=>50,
		);

		$propDefs['general_end'] = array(
			'name'=>'general_end',
			'type'=>'groupend',
			'positionForm'=>60,
		);

		$propDefs['custom_start'] = array(
			'name'=>'custom_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>70,
		);

		$propDefs['custom_1'] = array(
			'name'=>'custom_1',
			'label'=>KText::_('Custom Field 1'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>80,
		);

		$propDefs['custom_2'] = array(
			'name'=>'custom_2',
			'label'=>KText::_('Custom Field 2'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>90,
		);

		$propDefs['custom_3'] = array(
			'name'=>'custom_3',
			'label'=>KText::_('Custom Field 3'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>100,
		);

		$propDefs['custom_4'] = array(
			'name'=>'custom_4',
			'label'=>KText::_('Custom Field 4'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>110,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'order'=>6,
			'listing'=>15,
			'group'=>'county_id',
			'positionForm'=>120,
		);

		$propDefs['custom_end'] = array(
			'name'=>'custom_end',
			'type'=>'groupend',
			'positionForm'=>130,
		);

		$propDefs['tax_start'] = array(
			'name'=>'tax_start',
			'type'=>'groupstart',
			'title'=>KText::_('City Tax'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>140,
		);

		$propDefs['tax_class_rates'] = array(
			'name'=>'tax_class_rates',
			'type'=>'taxclassrates',
			'taxclasstype'=>'city',
			'positionForm'=>150,
		);

		$propDefs['tax_end'] = array(
			'name'=>'tax_end',
			'type'=>'groupend',
			'positionForm'=>160,
		);

		return $propDefs;

	}

}