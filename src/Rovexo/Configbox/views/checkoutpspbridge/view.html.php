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
	 * @var object $shopData
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
	 * @var string URL to send payment notification to (HTTP)
	 * @see ConfigboxControllerIpn::processIpn
	 */
	public $notificationUrlNormal;

	/**
	 * @var string URL to send payment notification to (HTTPS)
	 * @see ConfigboxControllerIpn::processIpn
	 */
	public $notificationUrlSecure;

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
		
		$orderRecord = $orderModel->getOrderRecord($orderId);
		$this->assignRef('orderRecord',$orderRecord);
		
		// Get store information
		$shopdata = ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);
		$this->assignRef('shopdata',$shopdata);
		$this->assignRef('shopData',$shopdata);

		$this->assign('statid',0);

		if (empty($orderRecord->payment)) {
			return;
		}

		// New path generation for payment options
		$notificationPath = KLink::getRoute('index.php?option=com_configbox&controller=ipn&task=processipn&Itemid=0&connector_name='.$orderRecord->payment->connector_name,false);
		$successPath = KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.$this->orderRecord->id, false);
		$failurePath = KLink::getRoute('index.php?option=com_configbox&view=checkout', false);
		$cancelPath = KLink::getRoute('index.php?option=com_configbox&view=checkout', false);

		if (KenedoPlatform::getName() == 'joomla') {
			$prefixNormal = 'http://'.KPATH_HOST;
			$prefixSecure = 'https://'.KPATH_HOST;
		}
		else {
			$prefixNormal = '';
			$prefixSecure = '';
		}

		if (CbSettings::getInstance()->get('securecheckout')) {
			$successUrl 		= $prefixSecure . $successPath;
			$failureUrl 		= $prefixSecure . $failurePath;
			$cancelUrl 			= $prefixSecure . $cancelPath;
			$notificationUrl 	= $prefixSecure . $notificationPath;
		}
		else {
			$successUrl 		= $prefixNormal . $successPath;
			$failureUrl 		= $prefixNormal . $failurePath;
			$cancelUrl 			= $prefixNormal . $cancelPath;
			$notificationUrl 	= $prefixNormal . $notificationPath;
		}

		$notificationUrlNormal = $prefixNormal . $notificationPath;
		$notificationUrlSecure = $prefixSecure . $notificationPath;

		$pspBridgeFilePath = ConfigboxPspHelper::getPspConnectorFolder($orderRecord->payment->connector_name) . DS . 'bridge.php';
		$this->assignRef('pspBridgeFilePath', $pspBridgeFilePath);

		$this->assignRef('successUrl', $successUrl);
		$this->assignRef('failureUrl', $failureUrl);
		$this->assignRef('cancelUrl', $cancelUrl);
		$this->assignRef('notificationUrl', $notificationUrl);
		$this->assignRef('notificationUrlNormal', $notificationUrlNormal);
		$this->assignRef('notificationUrlSecure', $notificationUrlSecure);


	}
}