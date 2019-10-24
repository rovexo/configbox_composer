<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincounties extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincounties';

	/**
	 * @return ConfigboxModelAdmincounties
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincounties');
	}

	function getPageTitle() {
		return KText::_('Counties');
	}

}