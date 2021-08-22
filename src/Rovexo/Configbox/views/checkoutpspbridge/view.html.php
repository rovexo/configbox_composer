<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCheckoutpspbridge extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'checkoutpspbridge';

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	/**
	 * Holds all information about the order
	 * @var ConfigboxOrderData $orderRecord
	 * @see ConfigboxModelOrderrecord::getOrderREcord
	 */
	public $orderRecord;

	/**
	 * Holds all information about the order
	 * @var ConfigboxShopData $shopData
	 * @see ConfigboxStoreHelper::getStoreRecord
	 */
	public $shopData;

	/**
	 * @var string URL to redirect the customer to when the payment was successful
	 */
	public $successUrl;

	/**
	 * @var string URL to redirect the customer to when the payment was not successful
	 */
	public $failureUrl;

	/**
	 * @var string URL to redirect the customer to when he cancels while on payment pages
	 */
	public $cancelUrl;

	/**
	 * @var string URL to send payment notification to (HTTP or HTTPS depends on setting 'secure_checkout')
	 * @see ConfigboxControllerIpn::processIpn
	 */
	public $notificationUrl;

	/**
	 * @var string $pspBridgeFilePath Path to the PSP connector's bridge template file
	 */
	public $pspBridgeFilePath;

	function display() {

		$this->prepareTemplateVars();

		// Render only if we got a payment method selected
		if ($this->orderRecord->payment) {
			$this->renderView();
		}

	}

	function prepareTemplateVars() {
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		
		$this->orderRecord = $orderModel->getOrderRecord($orderId);

		// Get store information
		$this->shopData = ConfigboxStoreHelper::getStoreRecord($this->orderRecord->store_id);

		if (empty($this->orderRecord->payment)) {
			return;
		}

		$this->notificationUrl = KLink::getRoute('index.php?option=com_configbox&controller=ipn&task=processipn&output_mode=view_only&Itemid=0&connector_name='.$this->orderRecord->payment->connector_name,false, true);
		$this->successUrl = KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.$this->orderRecord->id, false, true);
		$this->failureUrl = KLink::getRoute('index.php?option=com_configbox&view=cart&cart_id='.$this->orderRecord->cart_id, false, true);
		$this->cancelUrl = KLink::getRoute('index.php?option=com_configbox&view=cart&cart_id='.$this->orderRecord->cart_id, false, true);

		$this->pspBridgeFilePath = ConfigboxPspHelper::getPspConnectorFolder($this->orderRecord->payment->connector_name) . '/bridge.php';

	}
}