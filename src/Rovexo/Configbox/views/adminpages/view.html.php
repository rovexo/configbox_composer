<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminpages extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminpages';

	/**
	 * @return ConfigboxModelAdminpages
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpages');
	}

	function getPageTitle() {
		return KText::_('Configurator Pages');
	}

}