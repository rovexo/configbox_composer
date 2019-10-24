<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminproducttour extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/productTour::initAdminProductTour';
		return $calls;
	}

	function prepareTemplateVars() {

	}
	
}