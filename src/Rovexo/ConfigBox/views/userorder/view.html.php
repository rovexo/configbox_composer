<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewUserorder extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var ConfigboxOrderData $orderRecord
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $orderRecord;

	/**
	 * @var string $urlBackToAccount
	 */
	public $urlBackToAccount;

	/**
	 * @var string $urlCheckoutOrder
	 */
	public $urlCheckoutOrder;

	/**
	 * @var bool $canCheckout Indicates if the customer can checkout. Depends on customer group settings.
	 */
	public $canCheckout;

	/**
	 * @var string $orderRecordHtml
	 * @see ConfigboxViewRecord
	 */
	public $orderRecordHtml;

	/**
	 * @var string $orderStatusString The readable title of the status ('Ordered', 'In Checkout' etc)
	 */
	public $orderStatusString;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function display() {

		$this->prepareTemplateVars();

		// Show not found if no order record
		if (empty($this->orderRecord)) {
			$this->renderView('notfound');
			return;
		}

		// Check if order belongs to user
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = KRequest::getInt('order_id');
		$doesBelong = $orderModel->orderBelongsToUser($orderId);

		// Show not found if order does not belong to the user
		if ($doesBelong == false) {
			$this->renderView('notfound');
			return;
		}

		$this->renderView();

	}

	function prepareTemplateVars() {

		// Get the order id
		$orderId = KRequest::getInt('order_id');

		// Get the model
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');

		// Check if order belongs to user
		$doesBelong = $orderModel->orderBelongsToUser($orderId);

		// Abort if order does not belong
		if ($doesBelong == false) {
			return;
		}

		// Get the order record
		$orderRecord = $orderModel->getOrderRecord($orderId);

		// Abort if there's no order record
		if (!$orderRecord) {
			return;
		}

		// Put order record display into view
		$view = KenedoView::getView('ConfigboxViewRecord');
		$view->assignRef('orderRecord', $orderRecord);
		$view->prepareTemplateVars();
		$view->assign('showIn', 'confirmation');
		$view->assign('showChangeLinks', false);
		$view->assign('showProductDetails', true);
		$html = $view->getViewOutput();
		$this->assignRef('orderRecordHtml', $html);

		// Add the order record status string
		$orderStatuses = $orderModel->getOrderStatuses();

		// Legacy thing, remove in CB 4.0
		$orderRecord->statusString = $orderStatuses[$orderRecord->status]->title;

		$this->orderStatusString = $orderStatuses[$orderRecord->status]->title;

		// Add the order record data
		$this->assignRef('orderRecord', $orderRecord);

		// Add invoice data
		$invoiceModel = KenedoModel::getModel('ConfigboxModelInvoice');
		$invoiceData = $invoiceModel->getInvoiceData( $orderId );
		$this->assignRef('invoiceData', $invoiceData);

		// Add permissions
		$this->assign('canCheckout', $orderRecord->status == 11 || $orderRecord->status == 14);

		// Add urls to checkout and account
		$this->assign('urlCheckoutOrder', KLink::getRoute('index.php?option=com_configbox&view=cart&cart_id='.intval($orderRecord->cart_id), true, CbSettings::getInstance()->get('securecheckout')));
		$this->assign('urlBackToAccount', KLink::getRoute('index.php?option=com_configbox&view=user', true, CbSettings::getInstance()->get('securecheckout')));
		
	}
	
}