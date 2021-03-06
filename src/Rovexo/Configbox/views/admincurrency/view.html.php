<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincurrency extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincurrencies';

	/**
	 * @return ConfigboxModelAdmincurrencies
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincurrencies');
	}

	function getPageTitle() {
		return KText::_('Currency');
	}

}