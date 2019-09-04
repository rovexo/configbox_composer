<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminorder extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminorders';

	/**
	 * @var ConfigboxOrderData $orderRecord as from ConfigboxModelOrderrecord::getOrderRecord()
	 * @see ConfigboxModelOrderrecord::getOrderRecord()
	 */
	public $orderRecord;

	/**
	 * @var string HTML for the order record
	 * @see ConfigboxViewRecord
	 */
	public $orderRecordHtml;

	/**
	 * @var string HTML for the order status dropdown
	 */
	public $statusSelect;

	/**
	 * @var object Data about the invoice.
	 * @see ConfigboxModelInvoice::getInvoiceData
	 */
	public $invoiceData;

	/**
	 * @var string Invoice prefix
	 * @see ConfigboxModelInvoice::getInvoicePrefix
	 */
	public $invoicePrefix;

	/**
	 * @var string The next possible invoice serial
	 * @see ConfigboxModelInvoice::getNextInvoiceSerial
	 */
	public $nextInvoiceSerial;

	/**
	 * @var boolean Indicates if the link to the platform user page should be shown. Depends on if customers get linked on the platform and if it's possible to link there directly.
	 */
	public $showPlatformUserEditLink;

	/**
	 * @var string Complete URL to the platform user's edit page.
	 */
	public $urlPlatformUserEditForm;

	/**
	 * @var boolean Indicates if invoicing features should show
	 * @see CbSettings::$enable_invoicing
	 */
	public $showInvoicingBox;

	/**
	 * @var int Shows the mode
	 * @see CbSettings::$invoice_generation
	 */
	public $invoiceGenerationMode;

	/**
	 * @var boolean Indicates if the platform user edit screen can be navigated to directly (works on Joomla 2.5, not on 3)
	 */
	public $platformUserEditFormIsReachable;

	/**
	 * @var boolean Indicates if the current user can edit platform users
	 */
	public $userCanEditPlatformUsers;

	/**
	 * @return ConfigboxModelOrderrecord
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelOrderrecord');
	}

	function display() {

		$this->prepareTemplateVars();

		if (!$this->orderRecord) {
			$this->renderView('notfound');
		}
		else {
			$this->renderView();
		}

	}

	function prepareTemplateVars() {

		// Get the requested record record ID
		$cid = KRequest::getArray('cid');
		if ($cid) {
			$orderRecordId = $cid[0];
		}
		else {
			$orderRecordId = KRequest::getInt('id');
		}

		// And the view CSS classes
		$this->addViewCssClasses();

		// Get the order record
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$this->orderRecord = $orderModel->getOrderRecord($orderRecordId);

		if (!$this->orderRecord) {
			return;
		}

		$this->showInvoicingBox = CbSettings::getInstance()->get('enable_invoicing');
		$this->invoiceGenerationMode = CbSettings::getInstance()->get('invoice_generation');

		$invoiceModel = KenedoModel::getModel('ConfigboxModelInvoice');

		// Get invoice data in there
		$this->invoiceData = $invoiceModel->getInvoiceData($orderRecordId);

		// Info about the next invoice serial
		$this->nextInvoiceSerial = $invoiceModel->getNextInvoiceSerial();

		// The invoice prefix
		$this->invoicePrefix = $invoiceModel->getInvoicePrefix();

		// Get the HTML for the order record
		if ($this->orderRecord) {
			// Get the order record view HTML
			$orderRecordView = KenedoView::getView('ConfigboxViewRecord');
			$orderRecordView->orderRecord = $this->orderRecord;
			$orderRecordView->showIn = 'shopmanager';
			$orderRecordView->showChangeLinks = false;
			$orderRecordView->showProductDetails = true;
			$orderRecordHtml = $orderRecordView->getHtml();
			$this->orderRecordHtml = $orderRecordHtml;
		}
		else {
			$this->orderRecordHtml = '';
		}

		// In some platforms, we can bring the user to the customer's platform user account page
		$userEditFormReachable = KenedoPlatform::p()->platformUserEditFormIsReachable();
		$userCanEditUsers = KenedoPlatform::p()->userCanEditPlatformUsers();

		$this->platformUserEditFormIsReachable = $userEditFormReachable;
		$this->userCanEditPlatformUsers = $userCanEditUsers;

		// Get the link in
		if ($userEditFormReachable && $userCanEditUsers && !empty($this->orderRecord->orderAddress->platform_user_id)) {
			$this->showPlatformUserEditLink = true;
			$this->urlPlatformUserEditForm = KenedoPlatform::p()->getPlatformUserEditUrl($this->orderRecord->orderAddress->platform_user_id);
		}
		else {
			$this->showPlatformUserEditLink = false;
		}

		// Status drop-down, used for changing order status
		$statusSelect = $orderModel->getStatusDropDown($this->orderRecord->status);
		$this->statusSelect = $statusSelect;

	}
	
}