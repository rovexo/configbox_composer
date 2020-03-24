<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincurrencies extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincurrencies';

	/**
	 * @return ConfigboxModelAdmincurrencies
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincurrencies');
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/adminCurrencies::initListEach';
		return $calls;
	}

	function getPageTitle() {
		return KText::_('Currencies');
	}

}