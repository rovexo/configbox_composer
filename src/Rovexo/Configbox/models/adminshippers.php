<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminshippers extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_shippers';
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
			'positionList'=>10,
			'canSortBy'=>true,
			'positionForm'=>100,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'langType'=>44,
			'required'=>1,
			'positionList'=>20,
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminshippers',
			'positionForm'=>200,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'positionList'=>30,
			'listCellWidth'=>'60px',
			'positionForm'=>300,
		);

		return $propDefs;
	}

}
