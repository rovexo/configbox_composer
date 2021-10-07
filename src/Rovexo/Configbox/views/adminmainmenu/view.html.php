<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminmainmenu extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	public $showEcommerceItems = true;
	/**
	 * @return NULL
	 * @throws Exception
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		if (KenedoPlatform::getName() == 'magento2') {
			$this->showEcommerceItems = false;
		}

		if (KenedoPlatform::getName() == 'wordpress' && KenedoPlatform::p()->usesWcIntegration()) {
			$this->showEcommerceItems = false;
		}

		$this->addViewCssClasses();
	}
	
}
