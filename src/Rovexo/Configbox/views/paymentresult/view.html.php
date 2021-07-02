<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewPaymentresult extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'paymentresult';

	/**
	 * @var ConfigboxOrderData $orderRecord
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $orderRecord;

	/**
	 * @var object $shopData Store information (see backend: Store information)
	 * @see ConfigboxModelAdminshopdata::getShopdata
	 */
	public $shopData;

	/**
	 * @var boolean $showContinueButton Indicates if there should be a continue button
	 */
	public $showContinueButton;

	/**
	 * @var string $urlContinueShopping URL for 'Continue Shopping' links. Depends on ConfigBox setting in section 'Checkout'
	 */
	public $urlContinueShopping;

	/**
	 * @var string $linkToOrder Complete URL to the order details page
	 * @see ConfigboxViewUserorder
	 */
	public $linkToOrder;

	/**
	 * @var string $linkToCustomerProfile Complete URL to the customer profile page
	 * @see ConfigboxViewUser
	 */
	public $linkToCustomerProfile;

	/**
	 * Listing is chosen in setting 'continue_listing_id'.
	 * @var string $linkToDefaultProductListing Complete URL to the customer product listing page
	 * @see ConfigboxViewProdutlisting
	 */
	public $linkToDefaultProductListing;

	/**
	 * @var object
	 */
	public $shopdata;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function display() {

		$this->prepareTemplateVars();

		if ($this->orderRecord->payment->connector_name) {
			$connectorFolder = ConfigboxPspHelper::getPspConnectorFolder($this->orderRecord->payment->connector_name);

			if (file_exists($connectorFolder.'/result.php' )) {
				include($connectorFolder.'/result.php');
				return;
			}
		}
		else {
			$this->renderView('default');
		}
	}

	function prepareTemplateVars() {
		
		$orderModel 	= KenedoModel::getModel('ConfigboxModelOrderrecord');

		$orderId 		= $orderModel->getId();
		$orderRecord 	= $orderModel->getOrderRecord($orderId);
		$shopData 		= ConfigboxStoreHelper::getStoreRecord($orderRecord->store_id);

		$listingId = CbSettings::getInstance()->get('continue_listing_id');
		if ($listingId) {
			$this->showContinueButton = true;
			$this->urlContinueShopping = KLink::getRoute('index.php?option=com_configbox&view=productlisting&listing_id='.intval($listingId));
		}
		else {
			$this->showContinueButton = false;
			$this->urlContinueShopping = '';
		}

		$this->linkToOrder = KLink::getRoute('index.php?option=com_configbox&view=userorder&order_id='.$orderRecord->id);
		$this->linkToDefaultProductListing = KLink::getRoute('index.php?option=com_configbox&view=productlisting&listing_id='.CbSettings::getInstance()->get('continue_listing_id'));
		$this->linkToCustomerProfile = KLink::getRoute('index.php?option=com_configbox&view=user');
		$this->user = $orderRecord->orderAddress;
		$this->orderRecord = $orderRecord;
		$this->shopdata = $this->shopData = $shopData;
		$this->total = $orderRecord->payableAmount;

		$this->placeOrderPermitted = ConfigboxPermissionHelper::isPermittedAction('placeOrder', $orderRecord);

	}
	
}