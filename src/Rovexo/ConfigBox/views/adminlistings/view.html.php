<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminlistings extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminlistings';

	/**
	 * @return ConfigboxModelAdminlistings
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminlistings');
	}

	function getPageTitle() {
		return KText::_('Product Listings');
	}

}