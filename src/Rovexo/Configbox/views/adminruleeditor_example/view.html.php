<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminRuleeditor_example extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {
		$this->addViewCssClasses();
	}
	
}
