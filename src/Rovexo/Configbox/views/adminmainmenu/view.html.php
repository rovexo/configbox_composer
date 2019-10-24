<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminmainmenu extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @return NULL
	 * @throws Exception
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {
		$this->addViewCssClasses();
	}
	
}
