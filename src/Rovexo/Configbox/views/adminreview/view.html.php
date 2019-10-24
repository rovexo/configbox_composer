<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminreview extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminreviews';

	/**
	 * @return ConfigboxModelAdminreviews
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminreviews');
	}

	function getPageTitle() {
		return KText::_('Review');
	}

}
