<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminnotifications extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminnotifications';

	/**
	 * @return ConfigboxModelAdminnotifications
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminnotifications');
	}

	function getPageTitle() {
		return KText::_('Notifications');
	}

}
