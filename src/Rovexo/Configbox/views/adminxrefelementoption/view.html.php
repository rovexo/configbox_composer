<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminxrefelementoption extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminxrefelementoptions';

	/**
	 * @return ConfigboxModelAdminxrefelementoptions
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
	}

	function getPageTitle() {
		return KText::_('Option Assignment');
	}

}