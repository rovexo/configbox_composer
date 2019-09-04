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
			'listing'=>1,
			'listingwidth'=>'50px',
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
			'modelMethod'=>'getRecords',

			'required'=>1,
			'listing'=>50,

			'filter'=>20,
			'parent'=>1,
			'filterparents'=>0,

			'invisible'=>false,

			'listinglink'=>0,
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
			'listing'=>0,
			'order'=>20,

			'filter'=>20,
			'parent'=>0,
			'filterparents'=>0,

			'invisible'=>false,
			'listingwidth'=>'150px',
			'positionForm'=>400,

		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'group'=>'listing_id',
			'order'=>15,
			'listing'=>15,
			'positionForm'=>500,
		);

		return $propDefs;

	}
}
