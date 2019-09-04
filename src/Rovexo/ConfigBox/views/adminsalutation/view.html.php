<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminsalutation extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminsalutations';

	/**
	 * @return ConfigboxModelAdminsalutations
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminsalutations');
	}

	function getPageTitle() {
		return KText::_('Salutation');
	}

}
