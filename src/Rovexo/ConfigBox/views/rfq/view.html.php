<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewRfq extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'rfq';

	/**
	 * @var string HTML attributes for the view (contains URLs for loading various sub views)
	 */
	public $viewAttributes;

	/**
	 * @var string $customerFormHtml
	 * @see ConfigboxViewCustomerform
	 */
	public $customerFormHtml;

	/**
	 * @var int $cartId Cart ID to make a quote from
	 */
	public $cartId;

	/**
	 * @var string $urlCart URL to the cart page
	 */
	public $urlCart;

	/**
	 * @var string $trackingCode You can make a template override 'tracking_code' for this view to have tracking code.
	 */
	public $trackingCode;

	/**
	 * @return ConfigboxModelRfq $model
	 */
	public function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelRfq');
	}

	function getJsInitCallsOnce() {

		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/rfq::initRfqPage';

		return $calls;
	}

	function prepareTemplateVars() {

		$view = KenedoView::getView('ConfigboxViewCustomerform');
		$view->setFormType('quotation');
		$view->prepareTemplateVars();
		$this->customerFormHtml = $view->getViewOutput();

		$this->cartId = KRequest::getInt('cart_id');
		$this->urlCart = KLink::getRoute('index.php?option=com_configbox&view=cart&cart_id='.$this->cartId);

		// Get tracking code (comes from overridden templates, empty otherwise)
		$this->trackingCode = $this->getViewOutput('tracking_code');

		$attributes = array(
			'data-cart-id' => intval($this->cartId),
		);

		$viewAttributes = array();
		foreach ($attributes as $key=>$value) {
			$viewAttributes[] = $key . '="'.hsc($value).'"';
		}

		$this->viewAttributes = implode(' ', $viewAttributes);

	}


}