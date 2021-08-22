<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminsalutations extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_salutations';
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
			'positionForm'=>100,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'langType'=>55,
			'required'=>1,
			'positionList'=>20,
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminsalutations',
			'positionForm'=>200,
		);

		$propDefs['gender'] = array(
			'name'=>'gender',
			'label'=>KText::_('Gender'),
			'type'=>'radio',
			'choices'=> array(0=>KText::_('Unspecified'), 1=>KText::_('Male'), 2=>KText::_('Female')),
			'default'=>0,
			'tooltip'=>KText::_('Use this to store whether the user is male or female.'),
			'positionList'=>30,
			'listCellWidth'=>'50px',
			'positionForm'=>300,
		);

		return $propDefs;

	}

}