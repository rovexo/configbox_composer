<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewRfqthankyou extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var boolean $showQuotationDownload Indicates if download button should be shown. Depends on customer group settings.
	 */
	public $showQuotationDownload;

	/**
	 * @var boolean $showQuotationEmail Indicates if email dispatch confirmation should be shown. Depends on customer group settings.
	 */
	public $showQuotationEmail;

	/**
	 * @var boolean $showRequestConfirmation Indicates if only confirmation of request should be shown. Depends on customer group settings.
	 */
	public $showRequestConfirmation;

	/**
	 * @var string $urlQuotationDownload URL to the quotation PDF download.
	 */
	public $urlQuotationDownload;

	/**
	 * @var boolean $showContinueButton Indicates if there should be a continue button
	 */
	public $showContinueButton;

	/**
	 * @var string $urlContinueShopping URL to default product listing.
	 */
	public $urlContinueShopping;
	/**
	 * @var boolean $showAccountLink Indicates if account link should be shown. Depends on if customer is logged in.
	 */
	public $showAccountLink;

	/**
	 * @var string $urlAccount URL to customer account page.
	 */
	public $urlAccount;

	/**
	 * @var string $trackingCode HTML with tracking. Written to by template tracking_code.
	 */
	public $trackingCode;

	/**
	 * @var ConfigboxOrderData $orderRecord Object holding all order information.
	 * @see ConfigboxModelOrderrecord::getOrderRecord()
	 */
	public $orderRecord;

	/**
	 * @return ConfigboxModelRfq $model
	 */
	public function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelRfq');
	}

	function prepareTemplateVars() {

		// Get group info
		$groupId = ConfigboxUserHelper::getGroupId();
		$group = ConfigboxUserHelper::getGroupData($groupId);

		// Check if the user can actually request quotations
		if ($group->enable_request_quotation == false) {
			echo 'Quotation requests are not enabled';
			return;
		}

		// Set defaults
		$this->showQuotationDownload = false;
		$this->showQuotationEmail = false;
		$this->showRequestConfirmation = false;

		// If quote downloads are enabled, generate the download URL and indicate
		if ($group->quotation_download == true) {
			$orderId = KRequest::getInt('order_id');
			$this->urlQuotationDownload = KLink::getRoute('index.php?option=com_configbox&view=quotation&order_id='.$orderId);
			$this->showQuotationDownload = true;
		}
		elseif ($group->quotation_email == true) {
			$this->showQuotationEmail = true;
		}
		else {
			$this->showRequestConfirmation = true;
		}

		// Put the order record in the view (for use in tracking template)
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = KRequest::getInt('order_id');
		$this->orderRecord = $orderModel->getOrderRecord($orderId);

		// Add the continue shopping URL
		$listingId = CbSettings::getInstance()->get('continue_listing_id');
		if ($listingId) {
			$this->showContinueButton = true;
			$this->urlContinueShopping = KLink::getRoute('index.php?option=com_configbox&view=productlisting&listing_id='.intval($listingId));
		}
		else {
			$this->showContinueButton = false;
			$this->urlContinueShopping = '';
		}

		// See if we can show the account link
		$loggedIn = KenedoPlatform::p()->isLoggedIn();

		if ($loggedIn) {
			$this->urlAccount = KLink::getRoute('index.php?option=com_configbox&view=user');
			$this->showAccountLink = true;
		}
		else {
			$this->urlAccount = '';
			$this->showAccountLink = false;
		}

		// Get tracking code (comes from overridden templates, empty otherwise)
		$this->trackingCode = $this->getViewOutput('tracking_code');

		$this->addViewCssClasses();

	}
	
}