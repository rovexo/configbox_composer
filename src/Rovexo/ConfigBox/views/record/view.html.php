<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewRecord extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var ConfigboxOrderData $orderRecord Order record holding everything about an order
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $orderRecord;

	/**
	 * @var string[] HTML for each position, to be used in modals
	 */
	public $positionHtml;

	/**
	 * @var boolean $showProductDetails Indicates if product details (the selections) should be shown
	 */
	public $showProductDetails;

	/**
	 * @var boolean $hideSkus Indicates if the SKUs should be hidden (or shown) in the selections
	 */
	public $hideSkus;

	/**
	 * @var string $showIn Can be 'quotation', 'emailNotification', 'confirmation', 'shopmanager'
	 */
	public $showIn;

	/**
	 * @var bool $showChangeLinks Indicate if change links should be shown for shipping and payment method
	 */
	public $showChangeLinks = false;

	/**
	 * @var array Array holding selected metadata about the order, useful for tracking
	 */
	public $orderMetaData = array();

	/**
	 * @return ConfigboxModelOrderrecord
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelOrderrecord');
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/orderrecord::initOrderReord';
		return $calls;
	}

	function prepareTemplateVars() {
		
		if (empty($this->orderRecord)) {
			$orderModel = $this->getDefaultModel();
			$orderId = $orderModel->getId();
			$this->orderRecord = $orderModel->getOrderRecord($orderId);
			$this->showProductDetails = true;
			$this->hideSkus = true;
		}

		$inAdmin = (!empty($this->showIn) && $this->showIn == 'shopmanager');

		foreach($this->orderRecord->positions as $position) {
			$this->positionHtml[$position->id] = ConfigboxPositionHelper::getPositionHtml($this->orderRecord, $position, 'popup', ($this->hideSkus == false), $inAdmin);
		}

		// Prepare order metadata for tracking code to read
		$this->orderMetaData = array(

			'orderId' => $this->orderRecord->id,
			'cartId' => $this->orderRecord->cart_id,
			'userId' => $this->orderRecord->user_id,

			'currencyCode'=> $this->orderRecord->currency->code,

			'orderGrandTotalNet' => $this->orderRecord->totalNet + $this->orderRecord->delivery->priceNet + $this->orderRecord->payment->priceNet,
			'orderGrandTotalTax' => $this->orderRecord->totalTax + $this->orderRecord->delivery->priceTax + $this->orderRecord->payment->priceTax,
			'orderGrandTotalGross' => $this->orderRecord->totalGross + $this->orderRecord->delivery->priceGross + $this->orderRecord->payment->priceGross,

			'deliveryNet' => ($this->orderRecord->delivery !== NULL) ? $this->orderRecord->delivery->priceNet : 0,
			'deliveryTax' => ($this->orderRecord->delivery !== NULL) ? $this->orderRecord->delivery->priceTax : 0,
			'deliveryGross' => ($this->orderRecord->delivery !== NULL) ? $this->orderRecord->delivery->priceGross : 0,

			'paymentNet' => ($this->orderRecord->payment !== NULL) ? $this->orderRecord->payment->priceNet : 0,
			'paymentTax' => ($this->orderRecord->payment !== NULL) ? $this->orderRecord->payment->priceTax : 0,
			'paymentGross' => ($this->orderRecord->payment !== NULL) ? $this->orderRecord->payment->priceGross : 0,

			'positions' => array(),

		);

		foreach($this->orderRecord->positions as $position) {

			$this->orderMetaData['positions'][] = array(

				'positionId' => $position->id,
				'quantity' => $position->quantity,

				'productId' => $position->product_id,
				'productSku' => $position->product_sku,
				'productTitle' => $position->productTitle,

				'pricePerItemNet' => number_format($position->totalReducedNet / $position->quantity, '2', '.', ''),
				'pricePerItemTax' => number_format($position->totalReducedTax / $position->quantity, '2', '.', ''),
				'pricePerItemGross' => number_format($position->totalReducedGross / $position->quantity, '2', '.', ''),

			);

		}

	}
	
}