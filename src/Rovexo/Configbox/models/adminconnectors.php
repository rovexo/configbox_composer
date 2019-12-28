<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminconnectors extends KenedoModel {

	function getTableName() {
		return '#__configbox_connectors';
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
			'listing'=>10,
			'listingwidth'=>'50px',
			'order'=>100,
			'label'=>KText::_('ID'),
			'positionForm'=>100,
		);

		$propDefs['name'] = array(
			'name'=>'name',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'size'=>'90',
			'required'=>0,
			'listing'=>30,
			'order'=>30,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminconnectors',
			'positionForm'=>200,
		);

		$propDefs['after_system'] = array(
			'name'=>'after_system',
			'label'=>KText::_('Execute after system connectors'),
			'type'=>'boolean',
			'default'=>1,
			'tooltip'=>KText::_('Select yes, if the connector shall be executed after the system connectors have, no to execute them before.'),
			'positionForm'=>300,
		);

		$propDefs['file'] = array (
			'name'=>'file',
			'label'=>KText::_('Connector File'),
			'type'=>'file',
			'filetype'=>'file',
			'appendSerial'=>0,
			'allowedExtensions'=>array('php'),
			'required'=>0,
			'size'=>'100000',
			'dirBase'=>KenedoPlatform::p()->getDirCustomization().DS.'custom_observers',
			'urlBase'=>'',
			'options'=>'PRESERVE_EXT SAVE_FILENAME',
			'positionForm'=>400,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'order'=>25,
			'listing'=>20,
			'positionForm'=>500,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'listing'=>110,
			'listingwidth'=>'50px',
			'positionForm'=>600,
		);

		return $propDefs;

	}
}