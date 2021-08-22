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
			'label'=>KText::_('ID'),
			'canSortBy'=>true,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'langType'=>120,
			'required'=>1,
			'canSortBy'=>true,
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminexamples',
			'positionList'=>300,
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

			'addDropdownFilter'=>true,

			'required'=>1,
			'positionList'=>400,
			'canSortBy'=>true,
			'listCellWidth'=>'200px',
			'positionForm'=>1200,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'default'=>1,
			'type'=>'published',
			'positionList'=>500,
			'canSortBy'=>true,
			'addDropdownFilter'=>true,
			'listCellWidth'=>'60px',
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
			'tooltip'=>'Tooltip content',
			'type'=>'translatable',
			'langType'=>121,
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
			'positionList'=>200,
			'canSortBy'=>true,
			'positionForm'=>1700,
		);

		return $propDefs;

	}

}