<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminLicense extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminlicense';

	/**
	 * @var string Currently stored license key
	 */
	public $licenseKey;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {
		$this->licenseKey = CbSettings::getInstance()->get('product_key');
	}

}