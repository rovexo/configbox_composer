<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewBlank extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var string $output Supposed to be set from outside
	 */
	public $output = '';

	function getViewCssClasses() {
		$cssClasses = parent::getViewCssClasses();
		$cssClasses[] = 'container';
		return $cssClasses;
	}

	/**
	 * Override to ignore the requested template
	 * @param null $template
	 */
	function renderView($template = NULL) {
		parent::renderView('default');
	}

	function prepareTemplateVars() {
		$this->addViewCssClasses();
	}
}
