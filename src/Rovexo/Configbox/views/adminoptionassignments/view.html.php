<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminoptionassignments extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminoptionassignments';

	/**
	 * @return ConfigboxModelAdminoptionassignments
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminoptionassignments');
	}

	function getPageTitle() {
		return KText::_('Possible Answers');
	}

}