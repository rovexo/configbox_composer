<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminconnector extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminconnectors';

	/**
	 * @return ConfigboxModelAdminconnectors
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminconnectors');
	}

	function getPageTitle() {
		return KText::_('Connector');
	}

}