<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminpaymentmethod extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminpaymentmethods';

	public $settings;

	/**
	 * @return ConfigboxModelAdminpaymentmethods
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpaymentmethods');
	}

	function getPageTitle() {
		return KText::_('Payment Method');
	}

}
