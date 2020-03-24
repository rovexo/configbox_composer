<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminzones extends KenedoModel {

	function getTableName() {
		return '#__configbox_zones';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array (
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'positionForm'=>100,
		);

		$propDefs['label'] = array (
			'name'=>'label',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'listing'=>1,
			'listinglink'=>1,
			'required'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminzones',
			'positionForm'=>200,
		);

		$propDefs['countries'] = array(
			'name'=>'countries',
			'label'=>KText::_('Countries'),
			'required'=>0,
			'type'=>'multiselect',

			'modelClass'=>'ConfigboxModelAdmincountries',
			'modelMethod'=>'getRecords',

			'xrefTable'=>'#__configbox_xref_country_zone',
			'fkOwn'=>'zone_id',
			'fkOther'=>'country_id',

			'keyOwn' => 'id',

			'tableOther'=>'#__configbox_countries',
			'keyOther'=>'id',
			'displayColumnOther'=>'country_name',
			'asCheckboxes' => true,
			'positionForm'=>300,
		);

		return $propDefs;
	}

}
