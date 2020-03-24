<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCheckout extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'checkout';

	/**
	 * @var string CSS classes for the view's wrapping div
	 * @see ConfigboxViewConfiguratorpage::prepareTemplateVars
	 */
	public $pageCssClasses;

	/**
	 * @var string HTML attributes for the view (contains URLs for loading various sub views)
	 */
	public $viewAttributes;

	/**
	 * @see ConfigboxUserHelper::orderAddressComplete()
	 * @var boolean $orderAddressComplete Indicates if customer data is complete enough for checkout
	 */
	public $orderAddressComplete;

	/**
	 * Holds all information about the order
	 * @var ConfigboxOrderData $orderRecord
	 * @see ConfigboxModelOrderrecord::getOrderREcord
	 */
	public $orderRecord;

	/**
	 * @var string $orderAddressFormHtml HTML for the order address form
	 * @see ConfigboxViewCustomerform
	 */
	public $orderAddressFormHtml;

	/**
	 * @var string $orderAddressHtml HTML for the order address display
	 * @see ConfigboxViewCheckoutaddress
	 */
	public $orderAddressHtml;

	/**
	 * @var boolean $useDelivery Indicates if delivery methods are used (Can be disabled in settings -> checkout)
	 */
	public $useDelivery;

	/**
	 * @var string $deliveryHtml HTML for picking the delivery method
	 * @see ConfigboxViewCheckoutdelivery
	 */
	public $deliveryHtml;

	/**
	 * @var string $paymentHtml HTML for picking the payment method
	 * @see ConfigboxViewCheckoutpayment
	 */
	public $paymentHtml;

	/**
	 * @var string $orderRecordHtml HTML for showing the positions, pricing on the bottom of the checkout page
	 * @see ConfigboxViewRecord
	 */
	public $orderRecordHtml;

	/**
	 * @var boolean $confirmTerms Indicates if the customer needs to confirm the T&C (settings -> checkout)
	 */
	public $confirmTerms;

	/**
	 * @var boolean $confirmRefundPolicy Indicates if the customer needs to confirm the refund policy (settings -> checkout)
	 */
	public $confirmRefundPolicy;

	/**
	 * @var string $linkTerms Ready-made HTML link for the T&C with modal window functionality
	 */
	public $linkTerms;

	/**
	 * @var string $linkRefundPolicy Ready-made HTML link for the refund policy with modal window functionality
	 */
	public $linkRefundPolicy;

	/**
	 * @var boolean $canGoBackToCart Indicates if the customer can go back to cart (and discard the order)
	 */
	public $canGoBackToCart;

	/**
	 * @var string $backToCartUrl URL to the cart page
	 */
	public $backToCartUrl;

	/**
	 * @var string HTML from the terms and conditions view
	 * @see ConfigboxViewTerms
	 */
	public $termsHtml;

	/**
	 * @var string HTML from the refund policy view
	 * @see ConfigboxViewRefundpolicy
	 */
	public $refundPolicyHtml;

	/**
	 * @var bool Indicates if GA Enhanced Ecommerce Tracking should be used
	 */
	public $useGaEnhancedTracking;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function display() {

		$this->prepareTemplateVars();

		if (empty($this->orderRecord)) {
			echo KText::_('Order not found.');
			return;
		}

		$this->renderView();

	}

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/checkout.css';
		return $urls;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/checkout::initCheckoutPage';

		if (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1') {
			$calls[] = 'configbox/ga::initEcCheckoutPage';
		}

		return $calls;
	}

	function prepareTemplateVars() {

		// Get the order id (if a customer comes into this view with a set ID, the controller's display method handles setting)
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();

		$this->orderRecord = $orderModel->getOrderRecord($orderId);

		// Bounce if we got no order record
		if (empty($this->orderRecord)) {
			return;
		}

		// Check if GA tracking should used
		$this->useGaEnhancedTracking = (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1');

		$attributes = array(
			'data-url-address-view' => KLink::getRoute('index.php?option=com_configbox&view=checkoutaddress&output_mode=view_only', false),
			'data-url-delivery-view' => KLink::getRoute('index.php?option=com_configbox&view=checkoutdelivery&output_mode=view_only', false),
			'data-url-payment-view' => KLink::getRoute('index.php?option=com_configbox&view=checkoutpayment&output_mode=view_only', false),
			'data-url-order-view' => KLink::getRoute('index.php?option=com_configbox&view=record&output_mode=view_only', false),
			'data-url-psp-view' => KLink::getRoute('index.php?option=com_configbox&view=checkoutpspbridge&output_mode=view_only', false),
			'data-agree-to-terms' => CbSettings::getInstance()->get('explicit_agreement_terms'),
			'data-agree-to-rp' => CbSettings::getInstance()->get('explicit_agreement_rp'),
			'data-text-agree-terms' => KText::_('Please agree to the terms and conditions.'),
			'data-text-agree-rp' => KText::_('Please agree to the refund policy.'),
			'data-text-agree-both' => KText::_('Please agree to the terms and conditions and to the refund policy.'),
		);

		$viewAttributes = array();
		foreach ($attributes as $key=>$value) {
			$viewAttributes[] = $key . '="'.hsc($value).'"';
		}
		$this->viewAttributes = implode(' ', $viewAttributes);

		$view = KenedoView::getView('ConfigboxViewCustomerform');
		$view->customerFields = ConfigboxUserHelper::getUserFields();
		$view->customerData = $this->orderRecord->orderAddress;
		$view->formType = 'checkout';
		$this->orderAddressFormHtml = $view->getHtml();

		$this->confirmTerms = CbSettings::getInstance()->get('explicit_agreement_terms');
		$this->confirmRefundPolicy = CbSettings::getInstance()->get('explicit_agreement_rp');

		// Links to some pages
		$this->linkTerms = '<a class="rovedo-modal modal-link-terms" data-modal-width="900" data-modal-height="700" href="'.KLink::getRoute('index.php?option=com_configbox&view=terms&tmpl=component').'">'.KText::_('Terms and Conditions').'</a>';
		$this->linkRefundPolicy = '<a class="rovedo-modal modal-link-refund-policy" data-modal-width="900" data-modal-height="700" href="'.KLink::getRoute('index.php?option=com_configbox&view=refundpolicy&tmpl=component').'">'.KText::_('Refund Policy').'</a>';

		// Back to cart URL
		$this->backToCartUrl = KLink::getRoute('index.php?option=com_configbox&controller=checkout&task=backToCart');

		// Check if the customer can actually go back
		$cartModel = KenedoModel::getModel('ConfigboxModelCart');
		$cartDetails = $cartModel->getCartDetails($this->orderRecord->cart_id);
		$this->canGoBackToCart 	= ConfigboxPermissionHelper::isPermittedAction('goBackToCart', $cartDetails);

		// Pass over whether to use delivery or not
		$this->useDelivery = (CbSettings::getInstance()->get('disable_delivery') == 0);

		// Figure out if address is complete for checkout
		$this->orderAddressComplete = ConfigboxUserHelper::orderAddressComplete($this->orderRecord->orderAddress);

		// Assign the order address subview HTML
		if ($this->orderAddressComplete) {
			$this->orderAddressHtml = KenedoView::getView('ConfigboxViewCheckoutaddress')->getHtml();
		}
		else {
			$this->orderAddressHtml = '';
		}

		// Assign the delivery subview HTML
		$this->deliveryHtml = KenedoView::getView('ConfigboxViewCheckoutdelivery')->getHtml();

		// Assign the payment subview HTML
		$this->paymentHtml = KenedoView::getView('ConfigboxViewCheckoutpayment')->getHtml();

		$this->termsHtml = KenedoView::getView('ConfigboxViewTerms')->getHtml();
		$this->refundPolicyHtml = KenedoView::getView('ConfigboxViewRefundpolicy')->getHtml();

		
		// Assign the checkout record subview HTML
		$view = KenedoView::getView('ConfigboxViewRecord');
		$view->orderRecord = $this->orderRecord;
		$view->hideSkus = true;
		$view->showProductDetails = false;
		$this->orderRecordHtml = $view->getHtml();

	}
	
}