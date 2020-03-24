<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminreviews extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_reviews';
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
			'listing'=>10,
			'positionForm'=>100,
		);

		$propDefs['name'] = array(
			'name'=>'name',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminreviews',
			'positionForm'=>200,
		);

		$propDefs['rating'] = array(
			'name'=>'rating',
			'label'=>KText::_('Rating'),
			'type'=>'dropdown',
			'choices'=> array(
				'0'=>KText::_('No rating'),
				'1.0'=>1,
				'2.0'=>2,
				'3.0'=>3,
				'4.0'=>4,
				'5.0'=>5,
			),
			'default'=>'5.0',
			'positionForm'=>300,
		);

		$propDefs['comment'] = array(
			'name'=>'comment',
			'label'=>KText::_('Comment'),
			'type'=>'string',
			'required'=>1,
			'controller'=>'adminreviews',
			'options'=>'USE_TEXTAREA',
			'style'=>'height:200px',
			'positionForm'=>400,
		);

		$propDefs['date_created'] = array(
			'name'=>'date_created',
			'label'=>KText::_('Creation date'),
			'type'=>'datetime',
			'default'=>'',
			'positionForm'=>500,
		);

		$propDefs['product_id'] = array(
			'name'=>'product_id',
			'label'=>KText::_('Product'),
			'defaultlabel'=>KText::_('Select Product'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',

			'modelClass'=>'ConfigboxModelAdminproducts',
			'modelMethod'=>'getRecords',

			'parent'=>1,
			'filterparents'=>0,

			'required'=>0,
			'listing'=>30,
			'order'=>20,
			'filter'=>1,
			'positionForm'=>600,
		);

		$propDefs['language_tag'] = array(
			'name'=>'language_tag',
			'label'=>KText::_('Language'),
			'tooltip'=>KText::_('The language of the review. The language is automatically determined by the site language at the time of writing.'),
			'type'=>'join',
			'isPseudoJoin'=>true,

			'required'=>1,
			'propNameKey'=>'tag',
			'propNameDisplay'=>'label',

			'modelClass'=>'ConfigboxModelAdminlanguages',
			'modelMethod'=>'getActiveLanguages',

			'options'=>'SKIPDEFAULTFIELD NOFILTERSAPPLY',
			'positionForm'=>800,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'default'=>1,
			'type'=>'published',
			'listing'=>140,
			'order'=>130,
			'filter'=>3,
			'listingwidth'=>'60px',
			'positionForm'=>000,
		);

		return $propDefs;

	}
}