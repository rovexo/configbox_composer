<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCheckoutdelivery extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'checkoutdelivery';

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

	function prepareTemplateVars() {
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$orderId = $orderModel->getId();
		$orderRecord = $orderModel->getOrderRecord($orderId);
		
		$this->assign('mode', $orderRecord->groupData->b2b_mode ? 'b2b' : 'b2c' );
		
		$shippingOptions = $orderModel->getOrderRecordDeliveryOptions($orderRecord);
		$selected = $orderRecord->delivery_id;
		
		if ($selected == 0) {
			$selected = (isset($shippingOptions[0]->id)) ? $shippingOptions[0]->id : 0;
		}
		
		$optionsHavePricing = false;
		foreach ($shippingOptions as $option) {
			$option->title = $option->rateTitle;
			if ($option->priceNet) {
				$optionsHavePricing = true;
			}
		}
		
		$this->assignRef('optionsHavePricing', $optionsHavePricing);
		$this->assignRef('selected', $selected);
		$this->assignRef('options', $shippingOptions);
		
	}

}