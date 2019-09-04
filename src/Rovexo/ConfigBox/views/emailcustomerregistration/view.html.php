<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewEmailcustomerregistration extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var ConfigboxUserData Customer data, needs to be set from outside
	 * @see ConfigboxUserHelper::getUser
	 */
	public $customer;

	/**
	 * @var string User password unencrypted
	 */
	public $passwordClear;

	/**
	 * @var object Store information (see backend: Store information)
	 * @see ConfigboxModelAdminshopdata::getShopdata
	 */
	public $shopData;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

}