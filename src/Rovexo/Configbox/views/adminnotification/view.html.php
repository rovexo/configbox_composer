<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminnotification extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminnotifications';

	/**
	 * @var string[] Names of user record fields suitable as notification text placeholders.
	 * @see ConfigboxModelAdminnotifications::getUserInfoKeys
	 */
	public $userKeys;

	/**
	 * @var string[] Names of the order record fields suitable as notification text placeholders.
	 * @see ConfigboxModelAdminnotifications::getOrderInfoKeys
	 */
	public $orderKeys;

	/**
	 * @var string[] Names of the store info record fields suitable as notification text placeholders.
	 * @see ConfigboxModelAdminnotifications::getStoreInfoKeys, ConfigboxModelAdminshopdata::getPropertyDefinitions
	 */
	public $storeKeys;

	/**
	 * @return ConfigboxModelAdminnotifications
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminnotifications');
	}

	function prepareTemplateVars() {
				
		$model = $this->getDefaultModel();

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=raw', false);

		$this->userKeys = $model->getUserInfoKeys();
		$this->orderKeys = $model->getOrderInfoKeys();
		$this->storeKeys = $model->getStoreInfoKeys();

		parent::prepareTemplateVars();
	}

	function getPageTitle() {
		return KText::_('Notification');
	}
	
}
