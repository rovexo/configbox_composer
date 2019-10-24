<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminoptions extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminoptions';

	/**
	 * @return ConfigboxModelAdminoptions
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminoptions');
	}

	function getPageTitle() {
		return KText::_('Options');
	}

}