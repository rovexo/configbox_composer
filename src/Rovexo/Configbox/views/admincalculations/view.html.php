<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalculations extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincalculations';

	/**
	 * @return ConfigboxModelAdmincalculations
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalculations');
	}

	function getPageTitle() {
		return KText::_('Calculations');
	}

}