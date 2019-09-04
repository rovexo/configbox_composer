<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminelement extends KenedoView {

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
		return KText::_('Question');
	}

}