<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewRefundpolicy extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var string Refund Policy HTML. Comes from shop data settings.
	 */
	public $refundPolicy;

	function prepareTemplateVars(){
		
		$shopData = ConfigboxStoreHelper::getStoreRecord();
		$this->refundPolicy = $shopData->refundpolicy;

	}
}