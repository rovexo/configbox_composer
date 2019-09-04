<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminshipper extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminshippers';

	/**
	 * @return ConfigboxModelAdminshippers
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminshippers');
	}

	function getPageTitle() {
		return KText::_('Shipper');
	}

}