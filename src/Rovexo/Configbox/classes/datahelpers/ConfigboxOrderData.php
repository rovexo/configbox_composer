<?php
class ConfigboxOrderData {
	public $id = 315;
	public $user_id = 9;
	public $delivery_id = 1;
	public $payment_id = 1;
	public $cart_id = 180;
	public $store_id = 1;
	public $created_on = '2016-12-18 23:03:07';
	public $paid = '0';
	public $paid_on = NULL;
	public $status = '1';
	public $invoice_released = '0';
	public $comment = '';
	public $custom_1 = '';
	public $custom_2 = '';
	public $custom_3 = '';
	public $custom_4 = '';
	public $transaction_id = '';
	public $transaction_data = '';
	public $custom_5 = '';
	public $custom_6 = '';
	public $custom_7 = '';
	public $custom_8 = '';
	public $custom_9 = '';
	public $custom_10 = '';

	/**
	 * @var string Google Analytics Client ID. Set with an XHR call during checkout
	 * @see ConfigboxModelOrderrecord::storeGaClientId()
	 */
	public $ga_client_id = '';

	/**
	 * @var ConfigboxUserData
	 */
	public $orderAddress;

	/**
	 * @var ConfigboxGroupData
	 */
	public $groupData;
	public $isVatFree = false;
	/**
	 * @var ConfigboxCurrencyData[]
	 */
	public $currencies = array();
	/**
	 * @var ConfigboxCurrencyData
	 */
	public $currency;
	public $baseTotalUnreducedNet = 0.00;
	public $baseTotalUnreducedTax = 0.00;
	public $baseTotalUnreducedGross = 0.00;
	public $baseTotalUnreducedRecurringNet = 0.00;
	public $baseTotalUnreducedRecurringTax = 0.00;
	public $baseTotalUnreducedRecurringGross = 0.00;
	public $weight = 9;
	/**
	 * @var ConfigboxOrderPositionData[]
	 */
	public $positions = array();
	public $dispatchTime = 0;
	public $baseTotalDiscountNet = 0.00;
	public $baseTotalDiscountTax = 0.00;
	public $baseTotalDiscountGross = 0.00;
	public $baseTotalDiscountRecurringNet = 0.00;
	public $baseTotalDiscountRecurringTax = 0.00;
	public $baseTotalDiscountRecurringGross = 0.00;
	/**
	 * @var ConfigboxDiscountData
	 */
	public $discount;
	/**
	 * @var ConfigboxDiscountData
	 */
	public $discountRecurring;
	public $baseTotalNet = 0.00;
	public $baseTotalTax = 0.00;
	public $baseTotalGross = 0.00;
	public $baseTotalRecurringNet = 0.00;
	public $baseTotalRecurringTax = 0.00;
	public $baseTotalRecurringGross = 0.00;
	public $baseCouponDiscountNet = '0.0000';
	/**
	 * @var ConfigboxCouponData
	 */
	public $couponData;
	/**
	 * @var ConfigboxDeliveryMethodData
	 */
	public $delivery;
	/**
	 * @var ConfigboxPaymentMethodData
	 */
	public $payment;
	public $usesDiscount = true;
	public $usesRecurring = false;
	public $basePayableAmount = 0.00;
	public $totalUnreducedNet = 0.00;
	public $totalUnreducedTax = 0.00;
	public $totalUnreducedGross = 0.00;
	public $totalUnreducedRecurringNet = 0.00;
	public $totalUnreducedRecurringTax = 0.00;
	public $totalUnreducedRecurringGross = 0.00;
	public $totalDiscountNet = 0.00;
	public $totalDiscountTax = 0.00;
	public $totalDiscountGross = 0.00;
	public $totalDiscountRecurringNet = 0.00;
	public $totalDiscountRecurringTax = 0.00;
	public $totalDiscountRecurringGross = 0.00;
	public $totalNet = 0.00;
	public $totalTax = 0.00;
	public $totalGross = 0.00;
	public $totalRecurringNet = 0.00;
	public $totalRecurringTax = 0.00;
	public $totalRecurringGross = 0.00;
	public $couponDiscountNet = 0.00;
	public $payableAmount = 0.00;
	public $taxSummary = array();
	public $labelRegular = 'Price';
	public $labelRecurring = 'Recurring Price';
};