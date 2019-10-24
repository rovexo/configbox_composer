<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCheckoutpayment extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'checkoutpayment';

	/**
	 * @var object[] $options Array of delivery methods to choose from
	 */
	public $options;

	/**
	 * @var string $mode 'b2b' or 'b2c'. Comes from group data
	 */
	public $mode;

	/**
	 * @var boolean $optionsHavePricing Indicates if any methods have a price, may be helpful for layouting.
	 */
	public $optionsHavePricing;

	/**
	 * @var int $selected Selected option ID
	 */
	public $selected;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars(){
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		$orderRecord = $orderModel->getOrderRecord($orderId);

		$this->mode = $orderRecord->groupData->b2b_mode ? 'b2b' : 'b2c';

		$this->options = $orderModel->getOrderRecordPaymentOptions($orderRecord);

		$this->selected = $orderRecord->payment_id;
		
		if ($this->selected == 0) {
			if (count($this->options)) {
				$this->selected = $this->options[0]->id;
			}
		}
		
		$this->optionsHavePricing = false;
		foreach ($this->options as &$option) {
			if ($option->basePriceNet) {
				$this->optionsHavePricing = true;
			}
		}

	}
}