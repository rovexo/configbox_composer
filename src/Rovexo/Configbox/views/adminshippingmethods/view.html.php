<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminshippingmethods extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminshippingmethods';

	/**
	 * @return ConfigboxModelAdminshippingmethods
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminshippingmethods');
	}

	function getPageTitle() {
		return KText::_('Shipping Methods');
	}

}
