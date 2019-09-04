<?php
class ConfigboxOrderPositionData {

	public $id = 346;
	public $order_id = 315;
	public $product_id = 8;
	public $product_sku = 'COMPUTER';
	public $product_image = '315-346.png';
	public $quantity = 1;
	public $weight = 9.000;
	public $taxclass_id = 1;
	public $taxclass_recurring_id = 1;
	public $open_amount_net = 0.0000;
	public $using_deposit = '0';
	public $dispatch_time = '0';
	public $product_custom_1 = '';
	public $product_custom_2 = '';
	public $product_custom_3 = '';
	public $product_custom_4 = '';
	public $product_custom_5 = '';
	public $product_custom_6 = '';
	public $taxRate = 20.000;
	public $taxRateRecurring = 20.000;
	public $taxCode = '';
	public $taxCodeRecurring = '';
	public $baseProductBasePriceNet = 0.0000;
	public $baseProductBasePriceTax = 0;
	public $baseProductBasePriceGross = 0;
	public $baseProductBasePriceRecurringNet = 0.0000;
	public $baseProductBasePriceRecurringTax = 0;
	public $baseProductBasePriceRecurringGross = 0;
	public $baseTotalUnreducedNet = 0.0000;
	public $baseTotalUnreducedTax = 0;
	public $baseTotalUnreducedGross = 0.000;
	public $baseTotalUnreducedRecurringNet = 0.0000;
	public $baseTotalUnreducedRecurringTax = 0;
	public $baseTotalUnreducedRecurringGross = 0;

	public $productBasePriceNet = 2000;
	public $productBasePriceTax = 400;
	public $productBasePriceGross = 2400;
	public $productBasePriceRecurringNet = 0;
	public $productBasePriceRecurringTax = 0;
	public $productBasePriceRecurringGross = 0;
	public $totalUnreducedNet = 2045;
	public $totalUnreducedTax = 409;
	public $totalUnreducedGross = 2454;
	public $totalUnreducedRecurringNet = 0;
	public $totalUnreducedRecurringTax = 0;
	public $totalUnreducedRecurringGross = 0;
	/**
	 * @var ConfigboxOrderPositionConfigurationData[]
	 */
	public $configuration = array();
	public $productTitle = 'Computer';
	public $productDescription = '';
	public $interval = '';
	public $priceLabel = 'Price';
	public $priceLabelRecurring = 'Recurring Price';
	public $usesRecurring = false;
	public $baseTotalDiscountNet = 0;
	public $baseTotalDiscountTax = 0;
	public $baseTotalDiscountGross = 0;
	public $baseTotalReducedNet = 0;
	public $baseTotalReducedTax = 0;
	public $baseTotalReducedGross = 0;
	public $baseTotalDiscountRecurringNet = 0;
	public $baseTotalDiscountRecurringTax = 0;
	public $baseTotalDiscountRecurringGross = 0;
	public $baseTotalReducedRecurringNet = 0;
	public $baseTotalReducedRecurringTax = 0;
	public $baseTotalReducedRecurringGross = 0;
	public $totalDiscountNet = 0;
	public $totalDiscountTax = 0;
	public $totalDiscountGross = 0;
	public $totalReducedNet = 0;
	public $totalReducedTax = 0;
	public $totalReducedGross = 0;
	public $totalDiscountRecurringNet = 0;
	public $totalDiscountRecurringTax = 0;
	public $totalDiscountRecurringGross = 0;
	public $totalReducedRecurringNet = 0;
	public $totalReducedRecurringTax = 0;
	public $totalReducedRecurringGross = 0;

	/**
	 * @var string JSON data with group price overrides. Defaults to '[]'
	 */
	public $product_base_price_overrides;

	/**
	 * @var string JSON data with group price overrides. Defaults to '[]'
	 */
	public $product_base_price_recurring_overrides;
}