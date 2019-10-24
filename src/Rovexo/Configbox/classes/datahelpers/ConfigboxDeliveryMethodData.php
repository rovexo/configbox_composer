<?php
class ConfigboxDeliveryMethodData {
	public $id = 1;
	public $shipper_id = 1;
	public $zone_id = 0;
	public $minweight = 0.0000;
	public $maxweight = 9000.0000;
	public $deliverytime = '5';
	public $taxclass_id = '1';
	public $external_id = '';
	public $ordering = '10';
	public $taxRate = '20.000';

	public $basePriceNet = 5.0000;
	public $basePriceTax = 1.0000;
	public $basePriceGross = 6.0000;

	public $priceNet = 5.0000;
	public $priceTax = 1.0000;
	public $priceGross = 6.0000;

	/**
	 * @var string
	 * @deprecated Use title instead
	 */
	public $rateTitle = '';
	/**
	 * @var string Title of the delivery method
	 */
	public $title = '';

}
