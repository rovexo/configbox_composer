<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminexamples extends KenedoModel {

	function getTableName() {
		return '#__configbox_examples';
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
			'listing'=>1,
			'order'=>100,
			'positionForm'=>1000,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>100,
			'required'=>1,
			'listing'=>10,
			'order'=>3,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminexamples',
			'positionForm'=>1100,
		);

		$propDefs['product_id'] = array(
			'name'=>'product_id',
			'label'=>KText::_('Product'),
			'type'=>'join',

			'modelClass'=>'ConfigboxModelAdminproducts',
			'modelMethod'=>'getFilterSelectData',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',

			'defaultlabel'=>KText::_('Select Product'),

			'filter'=>1,
			'filterparents'=>1,
			'parent'=>1,

			'required'=>1,
			'listing'=>20,
			'order'=>1,

			'listingwidth'=>'200px',
			'positionForm'=>1200,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'default'=>1,
			'type'=>'published',
			'listing'=>40,
			'order'=>30,
			'filter'=>3,
			'listingwidth'=>'60px',
			'positionForm'=>1300,
		);

		$propDefs['display_start'] = array(
			'name'=>'display_start',
			'type'=>'groupstart',
			'title'=>KText::_('Display'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>1400,
		);

		$propDefs['description'] = array(
			'name'=>'description',
			'label'=>KText::_('Description'),
			'tooltip'=>KText::_('The description is displayed above the elements and can introduce this set of elements.'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>13,
			'required'=>0,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>1500,
		);

		$propDefs['display_end'] = array(
			'name'=>'display_end',
			'type'=>'groupend',
			'positionForm'=>1600,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'group'=>'product_id',
			'listing'=>5,
			'order'=>20,
			'positionForm'=>1700,
		);

		return $propDefs;

	}

}