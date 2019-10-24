<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminRuleeditor_customergroup extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var object[] Array of calculation data objects
	 */
	public $calculations;

	/**
	 * @var ConfigboxViewAdminRuleeditor
	 */
	public $ruleEditorView;

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
