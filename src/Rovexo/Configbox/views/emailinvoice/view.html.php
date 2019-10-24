<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewEmailinvoice extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'emailinvoice';

	/**
	 * @var ConfigboxOrderData $orderRecord
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $orderRecord;

	/**
	 * @var object $shopData
	 */
	public $shopData;

	/**
	 * @var ConfigboxUserData $customer
	 */
	public $customer;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {
		$this->shopData = ConfigboxStoreHelper::getStoreRecord();
		$this->customer = ConfigboxUserHelper::getUser();
		$this->renderView();
	}
	
}