<?php

class ConfigboxCartPositionData {

	public $id;
	public $cart_id;
	public $prod_id;
	public $weight;
	public $quantity;
	/**
	 * @var int $finished Tells if the user actuall added the position to the cart (or if he started configuring and left it unfinished)
	 */
	public $finished;

	/**
	 * @var ConfigboxCartPositionSelectionsData[]
	 */
	public $selections;
	public $usesRecurring;
	public $isConfigurable;
	public $productTitle;

	/**
	 * @var ConfigboxProductData
	 */
	public $productData;

	public $baseProductBasePriceNet;
	public $baseProductBasePriceTax;
	public $baseProductBasePriceGross;

	public $baseProductBasePriceRecurringNet;
	public $baseProductBasePriceRecurringTax;
	public $baseProductBasePriceRecurringGross;

	public $baseTotalUnreducedNet;
	public $baseTotalUnreducedTax;
	public $baseTotalUnreducedGross;

	public $baseTotalUnreducedRecurringNet;
	public $baseTotalUnreducedRecurringTax;
	public $baseTotalUnreducedRecurringGross;

	public $baseTotalDiscountNet;
	public $baseTotalDiscountTax;
	public $baseTotalDiscountGross;

	public $baseTotalDiscountRecurringNet;
	public $baseTotalDiscountRecurringTax;
	public $baseTotalDiscountRecurringGross;

	public $baseTotalReducedNet;
	public $baseTotalReducedTax;
	public $baseTotalReducedGross;

	public $baseTotalReducedRecurringNet;
	public $baseTotalReducedRecurringTax;
	public $baseTotalReducedRecurringGross;



	public $productBasePriceNet;
	public $productBasePriceTax;
	public $productBasePriceGross;

	public $productBasePriceRecurringNet;
	public $productBasePriceRecurringTax;
	public $productBasePriceRecurringGross;

	public $totalUnreducedNet;
	public $totalUnreducedTax;
	public $totalUnreducedGross;

	public $totalUnreducedRecurringNet;
	public $totalUnreducedRecurringTax;
	public $totalUnreducedRecurringGross;

	public $totalDiscountNet;
	public $totalDiscountTax;
	public $totalDiscountGross;

	public $totalDiscountRecurringNet;
	public $totalDiscountRecurringTax;
	public $totalDiscountRecurringGross;

	public $totalReducedNet;
	public $totalReducedTax;
	public $totalReducedGross;

	public $totalReducedRecurringNet;
	public $totalReducedRecurringTax;
	public $totalReducedRecurringGross;

}