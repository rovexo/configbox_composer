<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincustomergroup extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincustomergroups';

	/**
	 * @return ConfigboxModelAdmincustomergroups
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincustomergroups');
	}

	function getPageTitle() {
		return KText::_('Customer Group');
	}

}
