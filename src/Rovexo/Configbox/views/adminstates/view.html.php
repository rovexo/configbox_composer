<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminstates extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminstates';

	/**
	 * @return ConfigboxModelAdminstates
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminstates');
	}

	function getPageTitle() {
		return KText::_('States');
	}

}
