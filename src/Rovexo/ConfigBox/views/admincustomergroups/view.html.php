<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincustomergroups extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincustomergroups';

	/**
	 * @return ConfigboxModelAdmincustomergroups
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincustomergroups');
	}

	function getPageTitle() {
		return KText::_('Customer Groups');
	}

}