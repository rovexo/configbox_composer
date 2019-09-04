<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminproducts extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminproducts';

	/**
	 * @return ConfigboxModelAdminproducts
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproducts');
	}

	function getPageTitle() {
		return KText::_('Products');
	}

}