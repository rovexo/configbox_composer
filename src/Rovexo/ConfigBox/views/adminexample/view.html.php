<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminexample extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminexamples';

	/**
	 * @return ConfigboxModelAdminexamples
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminexamples');
	}

	function getPageTitle() {
		return KText::_('Example');
	}

}