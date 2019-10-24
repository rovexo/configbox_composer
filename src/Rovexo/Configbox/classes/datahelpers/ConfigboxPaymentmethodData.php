<?php
class ConfigboxPaymentmethodData {

	public $id = '1';
	public $taxclass_id = '1';
	/**
	 * @var KStorage
	 */
	public $settings;
	public $params;
	public $ordering = 10;
	public $percentage = 0.000;
	public $price_min = 0.0000;
	public $price_max = 0.0000;
	public $connector_name = '';
	public $taxRate = 20.000;
	public $basePriceNet = 0;
	public $basePriceTax = 0;
	public $basePriceGross = 0;
	public $title = '';
	public $description = '';
	public $priceNet = 0;
	public $priceTax = 0;
	public $priceGross = 0;
}