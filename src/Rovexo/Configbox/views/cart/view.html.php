<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCart extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'cart';

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	/**
	 * @var string $pageCssClasses String holding all CSS classes to be used in one of the wrapping divs
	 * @see prepareTemplateVars
	 */
	public $pageCssClasses;

	/**
	 * @var string HTML attributes that should be in the view's wrapper div
	 */
	public $viewAttributes;

	/**
	 * @var int Cart ID
	 */
	public $cartId;

	/**
	 * @var ConfigboxCartData $cart Object holding cart data (using printr, JDump or similar is recommended)
	 * @see ConfigboxModelCart::getCartDetails()
	 */
	public $cart;

	/**
	 * @var boolean $displayPricing Indicates if pricing shall be shown. Depends on customer group settings (Show pricing)
	 */
	public $displayPricing;

	/**
	 * @var object $position Helper object to make life easier in template 'positioncontrols'. Is set during looping
	 * through the cart's positions
	 */
	public $position;

	/**
	 * @var array[] $positionUrls Array holding URLs for various actions like removing/copying/editing a position.
	 * Array is grouped per position.
	 */
	public $positionUrls;

	/**
	 * @var string[] $quantityFields Holds HTML for each position's quantity input (visibility and type of input depends
	 * on the products settings for quantity, see backend GUI for reference)
	 */
	public $quantityFields;

	/**
	 * @var boolean $canEditOrder The system looks if the cart is already connected to an order and tells if it can be edited
	 */
	public $canEditOrder;

	/**
	 * @var string $urlPositionFormAction The URL to use for form submissions on the cart page
	 */
	public $urlPositionFormAction;

	/**
	 * @var boolean $showContinueButton Indicates if there should be a continue button
	 */
	public $showContinueButton;

	/**
	 * @var string $urlContinueShopping URL for 'Continue Shopping' links. Depends on ConfigBox setting in section 'Checkout'
	 */
	public $urlContinueShopping;

	/**
	 * @var string[] $positionImages The HTML for the product images or visualizations, listed by position ID.
	 */
	public $positionImages;

	/**
	 * @var string $urlPrintCart The URL for the print view
	 */
	public $urlPrintCart;

	/**
	 * @var bool $showPageHeading If the page heading should be shown (In Joomla that is controlled by the menu item parameters)
	 */
	public $showPageHeading;

	/**
	 * @var string $pageHeading The page heading
	 */
	public $pageHeading;

	/**
	 * @var boolean $canSaveOrder Indicates if the cart can be saved
	 */
	public $canSaveOrder;

	/**
	 * @var boolean $canCheckout Indicates if the user can checkout this cart
	 */
	public $canCheckout;

	/**
	 * @var boolean $canRequestQuote Indicates if the cart be used for a quote request
	 */
	public $canRequestQuote;

	/**
	 * @var string $urlSaveOrder URL to save an order
	 */
	public $urlSaveOrder;

	/**
	 * @var string $urlCheckout URL to checkout a cart
	 */
	public $urlCheckout;

	/**
	 * @var string $urlGetQuotation URL to request a quote
	 */
	public $urlGetQuotation;

	/**
	 * @var string URL to the checkout view
	 */
	public $urlCheckoutView;

	/**
	 * @var string URL for reloading the cart summary
	 */
	public $urlCartSummary;

	/**
	 * @var boolean $isB2b Indicates if B2B mode is on. Simply depends on group settings, it's read from the cart data.
	 */
	public $isB2b;

	/**
	 * @var boolean Indicates if delivery is disabled. See backend settings
	 */
	public $deliveryIsDisabled;

	/**
	 * @var float Total one time amount (incl. delivery and payment fee)
	 */
	public $totalPayable;

	/**
	 * @var string $quantityFieldCssClass;
	 */
	public $quantityFieldCssClass;

	/**
	 * @var bool Indicates if GA Enhanced Ecommerce Tracking should be used
	 */
	public $useGaEnhancedTracking;

	/**
	 * @var array Meta data used for GA Enhanced Ecommerce Tracking
	 */
	public $cartMetaData;

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/cart.css';
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/checkout.css';
		return $urls;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/cart::initCartPage';

		if (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1') {
			$calls[] = 'configbox/ga::initEcCartPage';
		}

		return $calls;
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/cart::initCartPageEach';
		return $calls;
	}

	function display() {

		$this->prepareTemplateVars();

		// Do the empty cart template if we got an empty cart
		if (empty($this->cart) || empty($this->cart->positions)) {
			$this->renderView('emptycart');
			return;
		}
		else {
			$this->renderView();
		}

	}

	function prepareTemplateVars() {

		// Get the cart model
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');

		// Set robots meta tag
		KenedoPlatform::p()->setMetaTag('robots', 'noindex');

		// Check if GA tracking should used
		$this->useGaEnhancedTracking = (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1');


		// Get page heading stuff
		$params = KenedoPlatform::p()->getAppParameters();
		$this->showPageHeading = ($params->get('show_page_heading', 1) && $params->get('page_title','') != '');
		$this->pageHeading = $params->get('page_title','');

		// Prepare urls
		$this->urlPrintCart = KLink::getRoute('index.php?option=com_configbox&view=cart&tmpl=component&print=1');
		$this->urlPositionFormAction = KLink::getRoute('index.php?option=com_configbox&view=cart');

		$listingId = CbSettings::getInstance()->get('continue_listing_id');
		if ($listingId) {
			$this->showContinueButton = true;
			$this->urlContinueShopping = KLink::getRoute('index.php?option=com_configbox&view=productlisting&listing_id='.intval($listingId));
		}
		else {
			$this->showContinueButton = false;
			$this->urlContinueShopping = '';
		}

		// Get the cart ID
		$cartId = KRequest::getInt('cart_id');
		if ($cartId) {
			if ($cartModel->cartBelongsToUser($cartId) == false) {
				throw new Exception('This cart does not belong to your user account.');
			}
			$this->cartId = $cartId;
		}
		else {
			$sessionCartId = $cartModel->getSessionCartId();
			if ($sessionCartId) {
				$this->cartId = $sessionCartId;
			}
		}

		// Do the empty cart template if nothing was found
		if (empty($this->cartId)) {
			return;
		}

		$this->cart = $cartModel->getCartDetails($this->cartId);
		$this->cartMetaData = array();

		// Collect meta data for GA tracking
		foreach ($this->cart->positions as $positionId => $position) {
			$this->cartMetaData[] = array(
				'positionId'=> $position->id,
				'prodTitle'	=> hsc($position->productTitle),
				'prodId'	=> hsc($position->productData->sku ? $position->productData->sku : $position->productData->id),
			);
		}

		// Do the empty cart template if we got an empty cart
		if (empty($this->cart) || empty($this->cart->positions)) {
			return;
		}

		$this->totalPayable = $this->cart->totalGross;

		if ($this->cart->delivery) {
			$this->totalPayable += $this->cart->delivery->priceGross;
		}

		if ($this->cart->payment) {
			$this->totalPayable += $this->cart->payment->priceGross;
		}

		// For convenience, store if we deal with B2B mode
		$this->isB2b = ($this->cart->groupData->b2b_mode == 1);

		// Prepare all permissions
		$this->canCheckout = ConfigboxPermissionHelper::isPermittedAction('checkoutOrder', $this->cart);
		$this->canEditOrder = ConfigboxPermissionHelper::isPermittedAction('editOrder', $this->cart);
		$this->canSaveOrder	= ConfigboxPermissionHelper::isPermittedAction('saveOrder', $this->cart);
		$this->canRequestQuote = ConfigboxPermissionHelper::isPermittedAction('requestQuote', $this->cart);

		$this->deliveryIsDisabled = CbSettings::getInstance()->get('disable_delivery');
		$this->displayPricing = ConfigboxPermissionHelper::canSeePricing();

		// Prepare cart button URLs
		$this->urlGetQuotation = KLink::getRoute('index.php?option=com_configbox&view=rfq&cart_id='.$this->cart->id, false);
		$this->urlSaveOrder = KLink::getRoute('index.php?option=com_configbox&view=saveorder&cart_id='.$this->cart->id, false);
		$this->urlCheckout = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=checkoutCart&cart_id='.$this->cart->id, false);

		$this->urlCheckoutView = KLink::getRoute('index.php?option=com_configbox&view=checkout&format=raw', false);
		$this->urlCartSummary = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=reloadCartSummary&format=raw', false);

		// Prepare position button urls
		$this->positionUrls = array();
		foreach ($this->cart->positions as $positionId=>$position) {
			$this->positionUrls[$positionId]['urlRemove'] = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=removeCartPosition&cart_position_id='.intval($position->id));
			$this->positionUrls[$positionId]['urlEdit'] = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=editCartPosition&cart_position_id='.intval($position->id));
			$this->positionUrls[$positionId]['urlCopy'] = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=copyCartPosition&cart_position_id='.intval($position->id));
		}
		unset($position);

		$attributes = array(
			'data-cart-id' => intval($this->cartId),
			'data-url-checkout-view' => KLink::getRoute('index.php?option=com_configbox&view=checkout&format=raw'),
			'data-url-cart-summary' => $this->urlCartSummary,
		);

		$viewAttributes = array();
		foreach ($attributes as $key=>$value) {
			$viewAttributes[] = $key . '="'.hsc($value).'"';
		}
		$this->viewAttributes = implode(' ', $viewAttributes);


		$this->quantityFieldCssClass = 'quantity-textfield';

		$this->positionImages = array();
		foreach($this->cart->positions as $position) {
			$this->positionImages[$position->id] = ConfigboxProductImageHelper::getProductImageHtml($position);
		}
		unset($position);

	}
}
