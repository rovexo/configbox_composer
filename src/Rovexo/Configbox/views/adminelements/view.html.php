<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminelements extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminelements';

	/**
	 * @return ConfigboxModelAdminelements
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminelements');
	}

	function getPageTitle() {
		return KText::_('Elements');
	}

}