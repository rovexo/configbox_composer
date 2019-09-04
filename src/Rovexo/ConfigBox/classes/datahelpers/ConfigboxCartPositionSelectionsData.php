<?php

class ConfigboxCartPositionSelectionsData {

	var $type;
	var $questionId;
	var $questionTitle;
	var $selection;
	var $outputValue;
	var $showInOverviews;

	var $basePriceNet;
	var $basePriceTax;
	var $basePriceGross;

	var $basePriceRecurringNet;
	var $basePriceRecurringTax;
	var $basePriceRecurringGross;

	var $priceNet;
	var $priceTax;
	var $priceGross;

	var $priceRecurringNet;
	var $priceRecurringTax;
	var $priceRecurringGross;

	var $weight;

	/**
	 * @var string JSON encoded array of arrays with group_id and price. Used for overriding pricing for specified groups.
	 */
	public $priceOverrides;

	/**
	 * @var string JSON encoded array of arrays with group_id and price. Used for overriding pricing for specified groups.
	 */
	public $priceRecurringOverrides;

	/**
	 * @var string JSON encoded array of arrays with group_id and calculation_id. Used for overriding pricing for specified groups.
	 */
	public $priceCalculationOverrides;

	/**
	 * @var string JSON encoded array of arrays with group_id and calculation_id. Used for overriding pricing for specified groups.
	 */
	public $priceRecurringCalculationOverrides;

}