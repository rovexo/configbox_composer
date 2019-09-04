<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewTerms extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var string Terms and Conditions from store information (see backend: Store information)
	 * @see ConfigboxModelAdminshopdata::getShopdata
	 */
	public $terms;

	function prepareTemplateVars() {
		$shopData = ConfigboxStoreHelper::getStoreRecord();
		$this->terms = $shopData->tac;
	}

}
