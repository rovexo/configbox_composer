<?php


class ConfigboxCartData {

	/**
	 * @var int
	 */
	var $id;
	/**
	 * @var int
	 */
	var $user_id;
	/**
	 * @var int
	 */
	var $status;

	/**
	 * @var ConfigboxCartPositionData[]
	 */
	var $positions;

	/**
	 * @var ConfigboxUserData
	 */
	var $userInfo;

	/**
	 * @var ConfigboxGroupData
	 */
	var $groupData;

	/**
	 * @var bool
	 */
	var $isVatFree;

	/**
	 * @var float
	 */
	var $weight;

	/**
	 * @var ConfigboxDiscountData
	 */
	var $discount;

	/**
	 * @var ConfigboxDiscountData
	 */
	var $discountRecurring;

	/**
	 * @var null|ConfigboxPaymentmethodData
	 */
	var $payment;

	/**
	 * @var null|ConfigboxDeliveryMethodData
	 */
	var $delivery;

	/**
	 * @var bool
	 */
	var $deliveryAndPaymentAdded;

	/**
	 * @var bool Indicates if the cart has positions with recurring prices
	 */
	var $usesRecurring = false;

	/**
	 * @var string Tells what to put in price overview headings for regular price
	 */
	var $labelRegular = '';

	/**
	 * @var string Tells what to put in price overview headings for recurring price
	 */
	var $labelRecurring = '';

	/**
	 * @var array Nested array with the total tax amount for each tax rate
	 */
	var $taxes;


	// Prices - in base currency
	var $baseTotalUnreducedNet;
	var $baseTotalUnreducedGross;
	var $baseTotalUnreducedTax;

	var $baseTotalUnreducedRecurringNet;
	var $baseTotalUnreducedRecurringGross;
	var $baseTotalUnreducedRecurringTax;

	var $baseTotalDiscountNet;
	var $baseTotalDiscountTax;
	var $baseTotalDiscountGross;

	var $baseTotalDiscountRecurringNet;
	var $baseTotalDiscountRecurringTax;
	var $baseTotalDiscountRecurringGross;

	var $baseTotalNet;
	var $baseTotalTax;
	var $baseTotalGross;

	var $baseTotalRecurringNet;
	var $baseTotalRecurringTax;
	var $baseTotalRecurringGross;

	var $baseCouponDiscountNet;
	var $baseCouponDiscountTax;
	var $baseCouponDiscountGross;

	// Prices - in current currency
	var $totalUnreducedNet;
	var $totalUnreducedGross;
	var $totalUnreducedTax;

	var $totalUnreducedRecurringNet;
	var $totalUnreducedRecurringGross;
	var $totalUnreducedRecurringTax;

	var $totalDiscountNet;
	var $totalDiscountTax;
	var $totalDiscountGross;

	var $totalDiscountRecurringNet;
	var $totalDiscountRecurringTax;
	var $totalDiscountRecurringGross;

	var $totalNet;
	var $totalTax;
	var $totalGross;

	var $totalRecurringNet;
	var $totalRecurringTax;
	var $totalRecurringGross;

	var $couponDiscountNet;
	var $couponDiscountTax;
	var $couponDiscountGross;

	var $custom_1;
	var $custom_2;
	var $custom_3;
	var $custom_4;
	var $custom_5;
	var $custom_6;
	var $custom_7;
	var $custom_8;
	var $custom_9;
	var $custom_10;

}