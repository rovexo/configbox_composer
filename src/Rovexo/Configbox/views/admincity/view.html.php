<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincity extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincities';

	/**
	 * @return ConfigboxModelAdmincities
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincities');
	}

	function getPageTitle() {
		return KText::_('City');
	}

}
