<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincounties extends KenedoModel {

	function getTableName() {
		return '#__configbox_counties';
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
			'positionForm'=>100,
		);

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listing'=>10,
			'positionForm'=>200,
		);

		$propDefs['county_name'] = array(
			'name'=>'county_name',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'order'=>10,
			'filter'=>1,
			'search'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincounties',
			'positionForm'=>300,
		);

		$propDefs['state_id'] = array(
			'name'=>'state_id',
			'label'=>KText::_('State'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('Select State'),

			'modelClass'=>'ConfigboxModelAdminstates',
			'modelMethod'=>'getRecords',

			'joinAdditionalProps'=>array(
				array(
					'propertyName'=>'name',
					'selectAliasOverride'=>'name',
					),
			),

			'groupby'=>'country_name',

			'required'=>1,
			'parent'=>1,
			'filterparents'=>1,
			'filter'=>2,
			'listing'=>40,
			'order'=>40,

			'listingwidth'=>'100px',
			'positionForm'=>400,
		);


		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'listing'=>100,
			'listingwidth'=>'50px',
			'default'=>1,
			'positionForm'=>500,
		);

		$propDefs['general_end'] = array(
			'name'=>'general_end',
			'type'=>'groupend',
			'positionForm'=>600,
		);

		$propDefs['custom_start'] = array(
			'name'=>'custom_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>700,
		);

		$propDefs['custom_1'] = array(
			'name'=>'custom_1',
			'label'=>KText::_('Custom Field 1'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>800,
		);

		$propDefs['custom_2'] = array(
			'name'=>'custom_2',
			'label'=>KText::_('Custom Field 2'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>900,
		);

		$propDefs['custom_3'] = array(
			'name'=>'custom_3',
			'label'=>KText::_('Custom Field 3'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1000,
		);

		$propDefs['custom_4'] = array(
			'name'=>'custom_4',
			'label'=>KText::_('Custom Field 4'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1100,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'order'=>6,
			'listing'=>15,
			'group'=>'state_id',
			'positionForm'=>1200,
		);

		$propDefs['custom_end'] = array(
			'name'=>'custom_end',
			'type'=>'groupend',
			'positionForm'=>1300,
		);

		$propDefs['tax_start'] = array(
			'name'=>'tax_start',
			'type'=>'groupstart',
			'title'=>KText::_('County Tax'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>1400,
		);

		$propDefs['tax_class_rates'] = array(
			'name'=>'tax_class_rates',
			'type'=>'taxclassrates',
			'taxclasstype'=>'county',
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