<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminzones extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminzones';

	/**
	 * @return ConfigboxModelAdminzones
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminzones');
	}

	function getPageTitle() {
		return KText::_('Zones');
	}

}
