<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCheckoutaddress extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'checkoutaddress';

	/**
	 * Holds all information about the order
	 * @var ConfigboxOrderData $orderRecord
	 * @see ConfigboxModelOrderrecord::getOrderREcord
	 */
	public $orderRecord;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		$this->orderRecord = $orderModel->getOrderRecord($orderId);
	}

	function display() {
		$this->prepareTemplateVars();
		if (!$this->orderRecord) {
			$this->renderView('notfound');
		}
		else {
			$this->renderView('default');
		}
	}
}