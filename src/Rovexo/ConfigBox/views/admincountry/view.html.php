<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincountry extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincountries';

	/**
	 * @return ConfigboxModelAdmincountries
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincountries');
	}

	function getPageTitle() {
		return KText::_('Country');
	}

}
