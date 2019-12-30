<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewInvoice extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var string Complete URL to the default CSS file (there is a separate CSS file for quote PDFs)
	 */
	public $hrefCssSystem;

	/**
	 * @var string Complete URL to the custom CSS file.
	 */
	public $hrefCssCustom;

	/**
	 * @var boolean Indicates if the shop logo should be displayed (depends on if one is uploaded in shop data)
	 */
	public $useShopLogo;

	/**
	 * @var int Pixel height of the logo as it should appear.
	 */
	public $shopLogoHeight;

	/**
	 * @var int Pixel width of the logo as it should appear.
	 */
	public $shopLogoWidth;

	/**
	 * @var string Complete URL to the shop logo.
	 */
	public $shopLogoUrl;

	/**
	 * @var object $shopData Object holding all store information
	 */
	public $shopData;

	/**
	 * @var string $shopCountryName Country name of the shop data's country ID
	 */
	public $shopCountryName;

	/**
	 * @var string $shopStateName State name of the shop data's state ID
	 */
	public $shopStateName;

	/**
	 * @var int $orderId ID of the order record used in the quote - NEEDS TO BE SET BEFORE CALLING DISPLAY()
	 */
	public $orderId;

	/**
	 * @var ConfigboxOrderData $orderRecord Order record holding everything about the order
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $orderRecord;

	/**
	 * @var string $orderRecordHtml HTML with the info about all positions of the order record
	 * @see ConfigboxViewRecord
	 */
	public $orderRecordHtml;

	/**
	 * @var string $invoiceNumber Serial of the invoice. See Settings/Invoice for serial generation.
	 */
	public $invoiceNumber;

	/**
	 * @var string $paymentMethodHtml HTML that comes from the invoice.php template of the selected payment method PSP.
	 */
	public $paymentMethodHtml = '';

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		// Order ID has to be passed via ->assign()
		$orderId = $this->orderId;

		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderModel->unsetOrderRecord($orderId);
		$this->orderRecord = $orderModel->getOrderRecord($orderId);

		if (!$this->orderRecord) {
			return;
		}

		$view = KenedoView::getView('ConfigboxViewRecord');
		$view->orderRecord = $this->orderRecord;
		$view->showIn = 'quotation';
		$view->showChangeLinks = false;
		$view->showProductDetails = false;
		$this->orderRecordHtml = $view->getViewOutput();

		$connectorName = $this->orderRecord->payment->connector_name;
		$folder = ConfigboxPspHelper::getPspConnectorFolder($connectorName);

		if (is_file($folder.DS.'invoice.php')) {
			ob_start();
			include($folder.DS.'invoice.php');
			$this->paymentMethodHtml = ob_get_clean();
		}
		else {
			$this->paymentMethodHtml = '';
		}

		$this->shopData = ConfigboxStoreHelper::getStoreRecord($this->orderRecord->store_id);

		$this->shopCountryName = ConfigboxCountryHelper::getCountryName($this->shopData->country_id);
		$this->shopStateName = ConfigboxCountryHelper::getStateName($this->shopData->state_id);

		$this->hrefCssSystem = KenedoPlatform::p()->getDirAssets().DS.'css'.DS.'pdf-invoice.css';
		$this->hrefCssCustom = KenedoPlatform::p()->getDirCustomizationAssets().DS.'css'.DS.'custom.css';

		$this->useShopLogo = false;
		$this->shopLogoWidth = 0;
		$this->shopLogoHeight = 0;

		$filePath = CONFIGBOX_DIR_SHOP_LOGOS .DS. $this->shopData->shoplogo;

		if (is_file($filePath)) {

			$this->useShopLogo = true;
			$this->shopLogoUrl = $filePath;

			$image = new ConfigboxImageResizer($filePath);

			$maxWidth = 1000;
			$maxHeight = 60;

			if ($image->width > $maxWidth || $image->height > $maxHeight) {
				$dimensions = $image->getDimensions($maxWidth, $maxHeight, 'containment');
				$this->shopLogoWidth = intval($dimensions['optimalWidth']);
				$this->shopLogoHeight = intval($dimensions['optimalHeight']);
			}

		}

	}
}