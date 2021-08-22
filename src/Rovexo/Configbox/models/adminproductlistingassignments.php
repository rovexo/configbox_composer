<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminproductlistingassignments extends KenedoModel {

	function getTableName() {
		return '#__configbox_xref_listing_product';
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
			'positionList'=>1,
			'positionForm'=>100,
		);

		$propDefs['product_id'] = array(
			'name'=>'product_id',
			'label'=>KText::_('Product'),
			'defaultlabel'=>KText::_('New Product'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdminproducts',
			'modelMethod'=>'getFilterSelectData',

			'required'=>1,
			'positionList'=>50,

			'addDropdownFilter'=>true,
			'invisible'=>false,

			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminproducts',
			'positionForm'=>300,
		);

		$propDefs['listing_id'] = array(
			'name'=>'listing_id',
			'label'=>KText::_('Listing'),
			'defaultlabel'=>KText::_('New Listing'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdminlistings',
			'modelMethod'=>'getRecords',

			'required'=>1,
			'positionList'=>0,
			'canSortBy'=>true,
			'addDropdownFilter'=>true,
			'invisible'=>false,
			'listCellWidth'=>'150px',
			'positionForm'=>400,

		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'group'=>'listing_id',
			'canSortBy'=>true,
			'positionList'=>15,
			'positionForm'=>500,
		);

		return $propDefs;

	}
}
