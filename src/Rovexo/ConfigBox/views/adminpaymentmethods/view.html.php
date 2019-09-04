<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminpaymentmethods extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminpaymentmethods';

	/**
	 * @return ConfigboxModelAdminpaymentmethods
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpaymentmethods');
	}

	function getPageTitle() {
		return KText::_('Payment Methods');
	}

}
