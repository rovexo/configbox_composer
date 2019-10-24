<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmintaxclasses extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admintaxclasses';

	/**
	 * @return ConfigboxModelAdmintaxclasses
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmintaxclasses');
	}

	function getPageTitle() {
		return KText::_('Tax Classes');
	}

}