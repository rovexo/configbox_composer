<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelPayment extends KenedoModel {

	/**
	 * @param ConfigboxUserData $orderAddress
	 * @param float $baseDeliveryAndOrder base gross price of shipping + order
	 * @return ConfigboxPaymentmethodData[] possible payment options
	 */
	function getPaymentOptions($orderAddress = NULL, $baseDeliveryAndOrder = NULL) {
		
		if (!$orderAddress) {
			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
			$orderId = $orderModel->getId();
			$order = $orderModel->getOrderRecord($orderId);
			$orderAddress = $order->orderAddress;
		}
		
		if ($baseDeliveryAndOrder === NULL) {
			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
			$orderRecordId = $orderModel->getSessionOrderId();
			$order = $orderModel->getOrderRecord($orderRecordId);
			
			if ($order->isVatFree) {
				$baseDeliveryAndOrder = $order->baseTotalNet + $order->delivery->basePriceNet;
			}
			else {
				$baseDeliveryAndOrder = $order->baseTotalGross + $order->delivery->basePriceGross;
			}
			
		}

		$methodsModel = KenedoModel::getModel('ConfigboxModelAdminpaymentmethods');

		$filters = array(
			'country_ids' => $orderAddress->billingcountry,
			'customer_group_ids' => $orderAddress->group_id,
			'published' => '1'
		);

		$methodDataItems = $methodsModel->getRecords($filters);

		$methods = [];

		foreach ($methodDataItems as $methodDataItem) {
			$methods[] = $this->getAugmentedPaymentMethod($methodDataItem, $baseDeliveryAndOrder, $orderAddress);
		}

		// That sort function makes methods sort by price (and methods with same price by method ordering number)
		$sortFunction = function($a, $b) {
			$sortHelperA = number_format($a->basePriceGross,3).$a->ordering;
			$sortHelperB = number_format($b->basePriceGross,3).$b->ordering;
			if ($sortHelperA == $sortHelperB) {
				return 0;
			}
			return ($sortHelperA < $sortHelperB) ? -1 : 1;
		};

		usort($methods, $sortFunction);

		return $methods;

	}

	/**
	 * @param int $paymentId payment method id
	 * @param float $baseDeliveryAndOrder base gross price of shipping + order
	 * @param ConfigboxUserData $orderAddress
	 * @return ConfigboxPaymentmethodData payment option
	 */
	function getPaymentOption($paymentId, $baseDeliveryAndOrder, $orderAddress) {
		$model = KenedoModel::getModel('ConfigboxModelAdminpaymentmethods');
		$data = $model->getRecord($paymentId);
		$method = $this->getAugmentedPaymentMethod($data, $baseDeliveryAndOrder, $orderAddress);
		return $method;
	}

	/**
	 * @param object $baseData Payment method data as it from model's getRecord
	 * @param float $baseDeliveryAndOrder
	 * @param ConfigboxUserData $orderAddress
	 * @return ConfigboxPaymentmethodData
	 */
	protected function getAugmentedPaymentMethod($baseData, $baseDeliveryAndOrder, $orderAddress) {

		/**
		 * @var ConfigboxPaymentmethodData $paymentMethod
		 */
		$paymentMethod = clone $baseData;
		$paymentMethod->settings = new KStorage($paymentMethod->params);

		$price = $paymentMethod->price;
		unset($paymentMethod->price);

		// Add percentage to it
		$paymentMethod->basePriceNet = round($price + $baseDeliveryAndOrder * $paymentMethod->percentage / 100, 4);

		// Cap by min and max price
		if ($paymentMethod->price_min != 0 && $price < $paymentMethod->price_min) {
			$paymentMethod->basePriceNet = $paymentMethod->price_min;
		}
		if ($paymentMethod->price_max != 0 && $price > $paymentMethod->price_max) {
			$paymentMethod->basePriceNet = $paymentMethod->price_max;
		}

		$taxRate = ConfigboxUserHelper::getTaxRate($paymentMethod->taxclass_id, $orderAddress);

		$paymentMethod->taxRate = $taxRate;

		$paymentMethod->basePriceTax = $paymentMethod->basePriceNet * $taxRate / 100;
		$paymentMethod->basePriceGross = $paymentMethod->basePriceNet + $paymentMethod->basePriceTax;

		ConfigboxCurrencyHelper::appendCurrencyPrices($paymentMethod);

		return $paymentMethod;

	}

}