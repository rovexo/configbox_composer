<?php
class ConfigboxCustomerGroupHelper {

	static function getFormattedDiscount($discount, $symbol = false) {

		$decimals = ($discount - floor($discount) == 0) ? 0 : 2;

		$formatted = number_format($discount, $decimals, KText::_('DECIMAL_MARK', '.'), KText::_('DIGIT_GROUPING_SYMBOL', ',')).($symbol ? ' %' : '');

		return $formatted;
	}

	/**
	 * @param ConfigboxGroupData $groupData
	 * @param float $basePriceNet
	 * @return ConfigboxDiscountData|KenedoObject
	 */
	static function getDiscount($groupData, $basePriceNet) {

		$level = NULL;
		$percentageFound = NULL;
		$lowestPrice = NULL;

		for ($i = 1; $i<=5; $i++) {

			// Skip if minimum order value is not met
			if ($groupData->{'discount_start_'.$i} > $basePriceNet) {
				continue;
			}

			$type = $groupData->{'discount_type_'.$i};
			if ($type == 'amount') {
				$price = $basePriceNet + floatval($groupData->{'discount_amount_'.$i});
			}
			elseif ($type == 'percentage') {
				$price = $basePriceNet + ( $basePriceNet * floatval($groupData->{'discount_factor_'.$i}) / 100 );
			}
			else {
				KLog::log('Invalid discount type found ("'.$type.'") in user group ID "'.$groupData->id,'error');
				$price = 0;
			}

			if ($lowestPrice === NULL || $price < $lowestPrice) {
				$lowestPrice = $price;
				$level = $i;
			}
		}

		$discount = new KenedoObject();
		$discount->title = NULL;
		$discount->level = 0;
		$discount->type = NULL;
		$discount->amount = NULL;
		$discount->percentage = NULL;

		if ($level) {
			$discount->title = $groupData->{'title_discount_'.$level};
			$discount->level = $level;
			$discount->type = $groupData->{'discount_type_'.$level};

			if ($discount->type == 'amount') {
				$discount->amount = floatval($groupData->{'discount_amount_'.$level});
			}
			else {
				$discount->percentage = floatval($groupData->{'discount_factor_'.$level});
			}
		}

		return $discount;

	}

	/**
	 * @param ConfigboxGroupData $groupData
	 * @param float $basePriceNet
	 * @return ConfigboxDiscountData|KenedoObject
	 */
	static function getDiscountRecurring($groupData, $basePriceNet) {

		$level = NULL;
		$percentageFound = NULL;
		$lowestPrice = NULL;

		for ($i = 1; $i<=5; $i++) {

			// Skip if minimum order value is not met
			if ($groupData->{'discount_recurring_start_'.$i} > $basePriceNet) {
				continue;
			}

			$type = $groupData->{'discount_recurring_type_'.$i};
			if ($type == 'amount') {
				$price = $basePriceNet + floatval($groupData->{'discount_recurring_amount_'.$i});
			}
			elseif ($type == 'percentage') {
				$price = $basePriceNet + ( $basePriceNet * floatval($groupData->{'discount_recurring_factor_'.$i}) / 100 );
			}
			else {
				KLog::log('Invalid recurring discount type found ("'.$type.'") in user group ID "'.$groupData->id,'error');
				$price = 0;
			}

			if ($lowestPrice === NULL || $price < $lowestPrice) {
				$lowestPrice = $price;
				$level = $i;
			}
		}

		$discount = new KenedoObject();
		$discount->title = NULL;
		$discount->level = 0;
		$discount->type = NULL;
		$discount->amount = NULL;
		$discount->percentage = NULL;

		if ($level) {
			$discount->title = $groupData->{'title_discount_recurring_'.$level};
			$discount->level = $level;
			$discount->type = $groupData->{'discount_recurring_type_'.$level};

			if ($discount->type == 'amount') {
				$discount->amount = floatval($groupData->{'discount_recurring_amount_'.$level});
			}
			else {
				$discount->percentage = floatval($groupData->{'discount_recurring_factor_'.$level});
			}
		}

		return $discount;

	}

}