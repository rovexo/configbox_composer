<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelOrderrecord extends KenedoModel {

	/**
	 * @var int Current session's order record ID
	 */
	public $id;

	/**
	 * @var ConfigboxOrderData[]
	 */
	public $orderRecords;

	/**
	 * @var array
	 */
	protected $taxRateCache;

	/**
	 * @var ConfigboxGroupData[]
	 */
	protected $orderGroupDataCache;

	/**
	 * @var ConfigboxCurrencyData[]
	 */
	protected $orderCurrencies;

	function __construct($component = '') {
		parent::__construct($component);
		$this->setId();
	}

	function setId($id = 0) {

		$id = (int)$id;

		if ($id) {
			$this->id = $id;
			$this->setSessionOrderId($id);
		}

		if (!$id) {
			$this->id = $this->getSessionOrderId();
		}

	}

	function setSessionOrderId($id) {
		KSession::set('order_id', $id, 'com_configbox');
	}

	function getSessionOrderId() {
		return KSession::get('order_id',0,'com_configbox');
	}

	function getId() {
		return $this->id;
	}

	function getOrderStatuses() {
		$statusCodes = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodes', array(), true);
		$stati = array();
		foreach ($statusCodes as $id=>$title) {
			$status = new stdClass();
			$status->id = $id;
			$status->title = $title;
			$stati[] = $status;
		}
		return $stati;
	}

	function orderBelongsToUser($orderId) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `user_id` FROM `#__cbcheckout_order_records` WHERE `id` = ".intval($orderId);
		$db->setQuery($query);
		$orderUserId = $db->loadResult();

		$userId = ConfigboxUserHelper::getUserId();

		KLog::log('Comparing user ID for order "'.$orderId.'": Current user id is "'.$userId.'", order user ID is "'.$orderUserId.'".','debug');

		return ($userId == $orderUserId);

	}

	function resetOrderData() {
		$this->orderRecords = array();
	}

	function unsetOrderRecord($orderId) {
		$this->orderRecords[$orderId] = NULL;
	}

	/**
	 * @param int $orderRecordId
	 * @return object $orderRecord order record
	 * @deprecated Removed in 2.7
	 */
	function &reloadOrderRecord($orderRecordId) {
		$this->unsetOrderRecord($orderRecordId);
		$orderRecord = $this->getOrderRecord($orderRecordId);
		return $orderRecord;
	}

	/**
	 * @param int $orderRecordId
	 * @return ConfigboxOrderData|false $orderRecord order record
	 * @deprecated Removed in 2.7
	 */
	function getOrder( $orderRecordId = NULL ) {

		if ($orderRecordId === NULL) {
			$orderRecordId = $this->getId();
		}
		KLog::logLegacyCall('Old getOrder method called. Use getOrderRecord($id) instead. $id is not optional anymore.');
		return $this->getOrderRecord($orderRecordId);

	}

	/**
	 * Provide the cart id and you get the IDs of order records that use this cart ID.
	 * @param int $cartId
	 * @return int[] Order record IDs
	 */
	function getOrderRecordIdByCartId($cartId) {
		$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `cart_id` = ".intval($cartId);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$ids = $db->loadResultList();
		return $ids;
	}

	/**
	 * @param $orderId
	 * @return ConfigboxOrderData|NULL
	 */
	function getOrderRecord($orderId) {

		if (!$orderId) {
			return NULL;
		}

		if (empty($this->orderRecords[$orderId])) {

			$query = "SELECT * FROM `#__cbcheckout_order_records` WHERE `id` = ".(int)$orderId;
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			/**
			 * @var ConfigboxOrderData $record
			 */
			$record = $db->loadObject();

			if (!$record) {
				return NULL;
			}

			// Add the order address
			$record->orderAddress = ConfigboxUserHelper::getOrderAddress( $record->id );
			// Add group data
			$record->groupData = $this->getOrderGroupData($record->id, $record->orderAddress->group_id);
			// Add flag for VAT free
			$record->isVatFree = ConfigboxUserHelper::isVatFree($record->orderAddress, $record->id);
			// Add currencies and used currency
			$record->currencies = $this->getOrderCurrencies($orderId);

			$currencyId = ConfigboxCurrencyHelper::getCurrency()->id;

			$record->currency =& $record->currencies[$currencyId];

			// Init unreduced product totals
			$record->baseTotalUnreducedNet 	= 0;
			$record->baseTotalUnreducedTax 	= 0;
			$record->baseTotalUnreducedGross = 0;

			$record->baseTotalUnreducedRecurringNet 	= 0;
			$record->baseTotalUnreducedRecurringTax		= 0;
			$record->baseTotalUnreducedRecurringGross 	= 0;

			// Init weight
			$record->weight = 0;

			// Init order positions
			$record->positions = array();

			// Init dispatch times
			$dispatchTimes = array();

			// Get position data
			$query = "SELECT * FROM `#__cbcheckout_order_positions` WHERE `order_id` = ".(int)$record->id;
			$db->setQuery($query);
			/**
			 * @var ConfigboxOrderPositionData[] $positions
			 */
			$positions = $db->loadObjectList();

			foreach ($positions as $position) {

				// Tax rate for the position
				$position->taxRate 			= ($record->isVatFree) ? 0 : $this->getOrderRecordTaxRate($record->id, $position->taxclass_id, $record->orderAddress, false);
				$position->taxRateRecurring = ($record->isVatFree || empty($position->taxclass_recurring_id)) ? 0 : $this->getOrderRecordTaxRate($record->id, $position->taxclass_recurring_id, $record->orderAddress, false);

				// Tax code (it is a concatenation of all codes set in country down to city tax rate overrides)
				$position->taxCode 			= $this->getOrderRecordTaxCode($orderId, $position->taxclass_id, 			$record->orderAddress, true);
				$position->taxCodeRecurring = $this->getOrderRecordTaxCode($orderId, $position->taxclass_recurring_id, 	$record->orderAddress, true);

				// Product base price - one-time price
				$position->baseProductBasePriceNet 			= $position->product_base_price_net;
				$position->baseProductBasePriceTax			= round($position->baseProductBasePriceNet * $position->taxRate / 100, 2);
				$position->baseProductBasePriceGross 		= $position->baseProductBasePriceNet + $position->baseProductBasePriceTax;

				// Product base price - recurring price
				$position->baseProductBasePriceRecurringNet = $position->product_base_price_recurring_net;
				$position->baseProductBasePriceRecurringTax	= round($position->baseProductBasePriceRecurringNet	* $position->taxRateRecurring / 100, 2);
				$position->baseProductBasePriceRecurringGross 	= $position->baseProductBasePriceRecurringNet + $position->baseProductBasePriceRecurringTax;

				// Prime position's unreduced price with the product's base price - one-time
				$position->baseTotalUnreducedNet 	= $position->baseProductBasePriceNet * $position->quantity;
				$position->baseTotalUnreducedTax	= $position->baseProductBasePriceTax * $position->quantity;
				$position->baseTotalUnreducedGross 	= $position->baseProductBasePriceGross * $position->quantity;

				// Prime position's unreduced price with the product's base price - regular
				$position->baseTotalUnreducedRecurringNet 	= $position->baseProductBasePriceRecurringNet;
				$position->baseTotalUnreducedRecurringTax 	= $position->baseProductBasePriceRecurringTax;
				$position->baseTotalUnreducedRecurringGross = $position->baseProductBasePriceRecurringGross;

				// Now for the position's configuration
				$query = "SELECT * FROM `#__cbcheckout_order_configurations` WHERE `position_id` = ".intval($position->id)." ORDER BY `id`";
				$db->setQuery($query);
				$position->configuration = $db->loadObjectList();

				foreach ($position->configuration as $item) {

					// Set the selection's regular price
					$item->basePriceNet = $item->price_net;

					// Check for overrides
					$overrides = json_decode($item->price_overrides, true);

					// Loop through and change price if order's group matches an override
					if (is_array($overrides) && count($overrides)) {
						foreach ($overrides as $override) {
							if ($override['group_id'] == $record->orderAddress->group_id) {
								$item->basePriceNet = $override['price'];
							}
						}
					}

					// Add tax and gross amount
					$item->basePriceTax = round($item->basePriceNet * $position->taxRate / 100, 2);
					$item->basePriceGross = $item->basePriceNet + $item->basePriceTax;

					// Set the selection's recurring price
					$item->basePriceRecurringNet = $item->price_recurring_net;

					// Check for overrides
					$overrides = json_decode($item->price_recurring_overrides, true);

					// Loop through and change price if order's group matches an override
					if (is_array($overrides) && count($overrides)) {
						foreach ($overrides as $override) {
							if ($override['group_id'] == $record->orderAddress->group_id) {
								$item->basePriceRecurringNet = $override['price'];
							}
						}
					}

					// Add tax and gross amount
					$item->basePriceRecurringTax = round($item->basePriceRecurringNet * $position->taxRateRecurring / 100, 2);
					$item->basePriceRecurringGross = $item->basePriceRecurringNet + $item->basePriceRecurringTax;

					// Unset the prices from the DB to avoid confusion with the ones we made earlier
					unset($item->price_net, $item->price_recurring_net);

					$item->elementTitle 					= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings',  4, $item->element_id);
					$item->elementDescription 				= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 14, $item->element_id);
					$item->element_custom_translatable_1 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 50, $item->element_id);
					$item->element_custom_translatable_2 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 51, $item->element_id);

					if ($item->option_id) {
						$item->optionTitle 			= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings',  5, $item->option_id); // Option Title
						$item->optionDescription 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 15, $item->option_id); // Option Description
						$item->optionContract	 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 28, $item->option_id); // Option Contract
						$item->option_custom_5	 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 60, $item->option_id); // Option Custom 5
						$item->option_custom_6	 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 61, $item->option_id); // Option Custom 6
					}
					else {
						$item->optionTitle 			= '';
						$item->optionDescription 	= '';
						$item->optionContract	 	= '';
						$item->option_custom_5	 	= '';
						$item->option_custom_6	 	= '';
					}

					$position->baseTotalUnreducedNet 	+= $item->basePriceNet * $position->quantity;
					$position->baseTotalUnreducedTax	+= $item->basePriceTax * $position->quantity;
					$position->baseTotalUnreducedGross 	+= $item->basePriceGross * $position->quantity;

					$position->baseTotalUnreducedRecurringNet 	= $item->basePriceRecurringNet * $position->quantity;
					$position->baseTotalUnreducedRecurringTax 	= $item->basePriceRecurringTax * $position->quantity;
					$position->baseTotalUnreducedRecurringGross = $item->basePriceRecurringGross * $position->quantity;

					ConfigboxCurrencyHelper::appendCurrencyPrices($item, $record->currency->multiplicator);

				}

				// Now add unreduced position price to record - one-time price
				$record->baseTotalUnreducedNet 		+= round($position->baseTotalUnreducedNet, 2);
				$record->baseTotalUnreducedTax 		+= round($position->baseTotalUnreducedTax, 2);
				$record->baseTotalUnreducedGross 	+= round($position->baseTotalUnreducedGross, 2);

				// Now add unreduced position price to record - recurring price
				$record->baseTotalUnreducedRecurringNet 	 += round($position->baseTotalUnreducedRecurringNet, 2);
				$record->baseTotalUnreducedRecurringTax 	 += round($position->baseTotalUnreducedRecurringTax, 2);
				$record->baseTotalUnreducedRecurringGross 	 += round($position->baseTotalUnreducedRecurringGross, 2);

				// Add up the order weight
				$record->weight += $position->weight;

				// Unset values that are processed above
				unset($position->product_base_price_net, $position->product_base_price_recurring_net, $position->price_net, $position->price_recurring_net);

				// 1-4 is already there from the query
				$position->product_custom_5 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 52, $position->product_id);
				$position->product_custom_6 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 53, $position->product_id);

				// Append the currency prices
				ConfigboxCurrencyHelper::appendCurrencyPrices($position, $record->currency->multiplicator);

				// Check if there is a title override
				$productTitleOverride = ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings',  79, $position->id);
				// If so, use it as the position's product title, otherwise go for the regular title
				if ($productTitleOverride) {
					$position->productTitle = $productTitleOverride;
				}
				else {
					$position->productTitle 		= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings',  1, $position->product_id);
				}

				$position->productDescription 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 11, $position->product_id);
				$position->interval 			= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 25, $position->product_id);
				$position->priceLabel		 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 26, $position->product_id);
				$position->priceLabelRecurring 	= ConfigboxCacheHelper::getOrderTranslation($record->id, 'configbox_strings', 31, $position->product_id);

				// Set price labels in case any are empty
				if ($position->priceLabelRecurring == '') {
					$position->priceLabelRecurring = KText::_('Recurring Price');
				}
				if ($position->priceLabel == '') {
					$position->priceLabel = KText::_('Price');
				}

				// Flag the position if it uses recurring pricing
				$position->usesRecurring = ($position->baseTotalUnreducedRecurringNet != 0);

				// Collect the dispatch time
				$dispatchTimes[] = $position->dispatch_time;

				// Set position's tax rate
				$position->taxRate 			= ($record->isVatFree) ? 0 : $this->getOrderRecordTaxRate($record->id, $position->taxclass_id, $record->orderAddress, false);
				$position->taxRateRecurring = ($record->isVatFree || empty($position->taxclass_recurring_id)) ? 0 : $this->getOrderRecordTaxRate($record->id, $position->taxclass_recurring_id, $record->orderAddress, false);

				// Finally add the position to the record
				$record->positions[] = $position;

			}

			// Set the dispatch time (highest of all position's dispatch times)
			$record->dispatchTime = (count($dispatchTimes)) ? max($dispatchTimes) : 0;

			KenedoObserver::triggerEvent('onConfigBoxLoadOrderRecordBeforeDiscounts',array(&$record));

			// Prime the discounts on products
			$record->baseTotalDiscountNet = 0;
			$record->baseTotalDiscountTax = 0;
			$record->baseTotalDiscountGross = 0;

			$record->baseTotalDiscountRecurringNet = 0;
			$record->baseTotalDiscountRecurringTax = 0;
			$record->baseTotalDiscountRecurringGross = 0;

			// Get the volume discount
			$record->discount 			= $this->getOrderRecordDiscount($record->id, $record->orderAddress->group_id, $record->baseTotalUnreducedNet, $record->currency);
			$record->discountRecurring 	= $this->getOrderRecordDiscountRecurring($record->id, $record->orderAddress->group_id, $record->baseTotalUnreducedRecurringNet, $record->currency);

			// Add reduced prices and discount to positions (discounts are negative numbers)
			foreach ($record->positions as $position) {

				$position->baseTotalDiscountNet = 0;

				// Add net discount - regular
				if ($record->discount->type == 'amount') {
					// For 'amount', see how much percent the position makes to the whole order, then add the right amount of discount to it
					if ($position->baseTotalUnreducedNet != 0) {
						$percentageOfTotal = $position->baseTotalUnreducedNet / $record->baseTotalUnreducedNet * 100;
						$position->baseTotalDiscountNet = $record->discount->amount / 100 * $percentageOfTotal;
					}
				}
				else {
					$position->baseTotalDiscountNet = round($position->baseTotalUnreducedNet / 100 * $record->discount->percentage, 4);
				}

				// Add discount tax and gross - regular
				$position->baseTotalDiscountTax = round($position->baseTotalDiscountNet * $position->taxRate / 100, 2);
				$position->baseTotalDiscountGross = $position->baseTotalDiscountNet + $position->baseTotalDiscountTax;

				// Add reduced prices - regular
				$position->baseTotalReducedNet = $position->baseTotalUnreducedNet + $position->baseTotalDiscountNet;
				$position->baseTotalReducedTax = $position->baseTotalUnreducedTax + $position->baseTotalDiscountTax;
				$position->baseTotalReducedGross = $position->baseTotalUnreducedGross + $position->baseTotalDiscountGross;

				// Add the discount to the order record total discount
				$record->baseTotalDiscountNet += $position->baseTotalDiscountNet;
				$record->baseTotalDiscountTax += $position->baseTotalDiscountTax;
				$record->baseTotalDiscountGross += $position->baseTotalDiscountGross;

				$position->baseTotalDiscountRecurringNet = 0;

				// Add net discount - recurring
				if ($record->discountRecurring->type == 'amount') {

					if ($record->baseTotalUnreducedRecurringNet != 0) {
						$percentageOfTotal = $position->baseTotalUnreducedRecurringNet / $record->baseTotalUnreducedRecurringNet * 100;
						$position->baseTotalDiscountRecurringNet = $record->discountRecurring->amount / $percentageOfTotal * 100;
					}
					else {
						$position->baseTotalDiscountRecurringNet = 0;
					}

				}
				else {
					$position->baseTotalDiscountRecurringNet = round($position->baseTotalUnreducedRecurringNet * $record->discountRecurring->percentage / 100, 4);
				}

				// Add discount tax and gross - recurring
				$position->baseTotalDiscountRecurringTax = round($position->baseTotalDiscountRecurringNet * $position->taxRateRecurring / 100, 2);
				$position->baseTotalDiscountRecurringGross = $position->baseTotalDiscountRecurringNet + $position->baseTotalDiscountRecurringTax;

				// Add reduced prices - recurring
				$position->baseTotalReducedRecurringNet = $position->baseTotalUnreducedRecurringNet + $position->baseTotalDiscountRecurringNet;
				$position->baseTotalReducedRecurringTax = $position->baseTotalUnreducedRecurringTax + $position->baseTotalDiscountRecurringTax;
				$position->baseTotalReducedRecurringGross = $position->baseTotalUnreducedRecurringGross + $position->baseTotalDiscountRecurringGross;

				// Add the discount to the order record total discount
				$record->baseTotalDiscountRecurringNet += $position->baseTotalDiscountRecurringNet;
				$record->baseTotalDiscountRecurringTax += $position->baseTotalDiscountRecurringTax;
				$record->baseTotalDiscountRecurringGross += $position->baseTotalDiscountRecurringGross;

				// Put in the current currency prices
				ConfigboxCurrencyHelper::appendCurrencyPrices($position, $record->currency->multiplicator);

			}

			$record->baseTotalNet 	= $record->baseTotalUnreducedNet 	+ $record->baseTotalDiscountNet;
			$record->baseTotalTax 	= $record->baseTotalUnreducedTax 	+ $record->baseTotalDiscountTax;
			$record->baseTotalGross 	= $record->baseTotalUnreducedGross 	+ $record->baseTotalDiscountGross;

			$record->baseTotalRecurringNet 	= $record->baseTotalUnreducedRecurringNet 	+ $record->baseTotalDiscountRecurringNet;
			$record->baseTotalRecurringTax 	= $record->baseTotalUnreducedRecurringTax 	+ $record->baseTotalDiscountRecurringTax;
			$record->baseTotalRecurringGross = $record->baseTotalUnreducedRecurringGross	+ $record->baseTotalDiscountRecurringGross;


			// PRELIMINARY COUPON DISCOUNT THINGY - START
			$record->baseCouponDiscountNet = $record->coupon_discount_net;
			unset($record->coupon_discount_net);

			$record->couponData = $this->getOrderRecordCouponData($record);

			if ($record->couponData->baseCouponDiscountNet != 0) {
				$record->baseTotalNet 	= $record->baseTotalNet 	+ $record->couponData->baseCouponDiscountNet;
				$record->baseTotalTax 	= $record->baseTotalTax 	+ $record->couponData->baseCouponDiscountTax;
				$record->baseTotalGross = $record->baseTotalGross 	+ $record->couponData->baseCouponDiscountGross;
			}

			// PRELIMINARY COUPON DISCOUNT THINGY - END

			$record->delivery 	= $this->getOrderRecordDeliveryOption($record);

			$record->usesDiscount 	= ($record->baseTotalDiscountNet != 0 or $record->baseTotalDiscountRecurringNet != 0);
			$record->usesRecurring 	= ($record->baseTotalRecurringNet != 0);

			// Payable amount is what we use for payment and all
			$record->basePayableAmount = $record->baseTotalGross;

			// Add the delivery price to payable
			if ($record->delivery) {
				$record->basePayableAmount += $record->delivery->basePriceGross;
			}

			// Add the payment method price to payable
			$record->payment 	= $this->getOrderRecordPaymentOption($record);
			if ($record->payment) {
				$record->basePayableAmount += $record->payment->basePriceGross;
			}

			// There can be a rounding issue summing up merch/shipping/payment total, so we take rounded amounts extra here

			// Append the currency prices
			ConfigboxCurrencyHelper::appendCurrencyPrices($record, $record->currency->multiplicator);

			// Payable amount is what we use for payment and all
			$record->payableAmount = $record->totalGross;

			// Add the delivery price to payable
			if ($record->delivery) {
				$record->payableAmount += $record->delivery->priceGross;
			}

			// Add the payment method price to payable
			$record->payment 	= $this->getOrderRecordPaymentOption($record);
			if ($record->payment) {
				$record->payableAmount += $record->payment->priceGross;
			}

			// Add the tax summary
			$record->taxSummary = $this->getOrderRecordTaxSummary($record);

			$this->addPriceLabels($record);

			// Run the whole record through listeners
			KenedoObserver::triggerEvent('onConfigBoxAfterLoadOrderRecord',array(&$record));

			$this->orderRecords[$orderId] = $record;

		}

		return $this->orderRecords[$orderId];
	}

	/**
	 * Appends price labels to the order record (scans the position labels and tries to find a specific one)
	 * @param object $orderRecord
	 */
	function addPriceLabels($orderRecord) {
		// Prepare price labels (to see if we can use product specific labels)
		$labels = array(
			'regular' => array(),
			'recurring' => array(),
		);

		// Collect all unique price labels
		foreach ($orderRecord->positions as $position) {
			$labels['recurring'][$position->priceLabelRecurring] = $position->priceLabelRecurring;
			$labels['regular'][$position->priceLabel] = $position->priceLabel;
		}

		// Set price labels for regular and recurring on the record
		$orderRecord->labelRegular = (count($labels['regular']) == 1) ? array_pop($labels['regular']) : KText::_('Price');
		$orderRecord->labelRecurring = (count($labels['recurring']) == 1) ? array_pop($labels['recurring']) : KText::_('Recurring Price');

	}

	function &getOrderGroupData($orderId, $groupId) {

		if ($groupId === NULL || $orderId === NULL) {
			return NULL;
		}

		if (empty($this->orderGroupDataCache[$orderId][$groupId])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__cbcheckout_order_user_groups` WHERE `order_id` = ".intval($orderId)." AND `group_id` = ".intval($groupId);
			$db->setQuery($query);
			$data = $db->loadObject();

			if ($data) {
				$data->title_discount_1 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 80, $groupId);
				$data->title_discount_2 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 81, $groupId);
				$data->title_discount_3 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 82, $groupId);
				$data->title_discount_4 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 83, $groupId);
				$data->title_discount_5 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 84, $groupId);

				$data->title_discount_recurring_1 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 85, $groupId);
				$data->title_discount_recurring_2 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 86, $groupId);
				$data->title_discount_recurring_3 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 87, $groupId);
				$data->title_discount_recurring_4 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 88, $groupId);
				$data->title_discount_recurring_5 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 89, $groupId);
			}
			else {
				$data = NULL;
			}

			$this->orderGroupDataCache[$orderId][$groupId] = $data;
		}

		return $this->orderGroupDataCache[$orderId][$groupId];

	}

	function &getOrderCurrencies($orderId) {

		if (!isset($this->orderCurrencies[$orderId])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__cbcheckout_order_currencies` WHERE `order_id` = ".(int)$orderId;
			$db->setQuery($query);
			$this->orderCurrencies[$orderId] = $db->loadObjectList('id');
		}
		return $this->orderCurrencies[$orderId];

	}

	function getOrderRecordTaxSummary($record) {

		$taxes = array();

		if ($record->delivery && $record->delivery->taxRate != 0) {
			$taxRate = strval($record->delivery->taxRate);
			if (!isset($taxes[ $taxRate ]['regular'])) $taxes[ $taxRate ]['regular'] = 0;
			$taxes[ $taxRate ]['regular'] += $record->delivery->priceTax;
		}
		if ($record->payment && $record->payment->taxRate != 0) {
			$taxRate = strval($record->payment->taxRate);
			if (!isset($taxes[ $taxRate ]['regular'])) $taxes[ $taxRate ]['regular'] = 0;
			$taxes[ $taxRate ]['regular'] += $record->payment->priceTax;
		}

		if ($record->couponData->baseCouponDiscountNet != 0) {
			if ($record->couponData->taxRate != 0) {
				$taxRate = strval($record->couponData->taxRate);
				if (!isset($taxes[ $taxRate ]['regular'])) $taxes[ $taxRate ]['regular'] = 0;
				$taxes[ $taxRate ]['regular'] += $record->couponData->couponDiscountTax;
			}
		}

		foreach ($record->positions as $position) {

			if ($position->totalReducedRecurringTax != 0) {
				$taxRate = strval($position->taxRateRecurring);
				if (!isset($taxes[ $taxRate ]['recurring'])) $taxes[ $taxRate ]['recurring'] = 0;
				$taxes[ $taxRate ]['recurring'] += $position->totalReducedRecurringTax;
			}

			if ($position->totalReducedTax != 0) {
				$taxRate = strval($position->taxRate);
				if (!isset($taxes[ $taxRate ]['regular'])) $taxes[ $taxRate ]['regular'] = 0;
				$taxes[ $taxRate ]['regular'] += $position->totalReducedTax;
			}

		}

		// Make sure all tax groups are filled out
		foreach ($taxes as &$tax) {
			if (!isset($tax['regular'])) 	$tax['regular'] = 0;
			if (!isset($tax['recurring'])) 	$tax['recurring'] = 0;
		}

		return $taxes;
	}

	/**
	 * @param ConfigboxOrderData $orderRecord
	 * @return ConfigboxDeliveryMethodData[]
	 */
	function getOrderRecordDeliveryOptions($orderRecord) {

		$query = "
			SELECT d.*
			FROM `#__cbcheckout_order_shipping_methods` AS d
			LEFT JOIN `#__configbox_xref_country_zone` AS xref ON xref.zone_id = d.zone_id
			WHERE d.order_id = ".intval($orderRecord->id)." AND xref.country_id = ".intval($orderRecord->orderAddress->country);

		if ($orderRecord->weight != 0) {
			$query .= " AND d.minweight <= ".floatval($orderRecord->weight)." AND d.maxweight >= ".floatval($orderRecord->weight);
		}

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		/**
		 * @var ConfigboxDeliveryMethodData[] $items
		 */
		$items = $db->loadObjectList('id');

		foreach ($items as $item) {

			$item->taxRate = $this->getOrderRecordTaxRate($orderRecord->id, $item->taxclass_id, $orderRecord->orderAddress);
			$item->basePriceNet 	= $item->price;
			$item->basePriceTax 	= round($item->basePriceNet * $item->taxRate / 100, 2);
			$item->basePriceGross 	= $item->basePriceNet + $item->basePriceTax;
			$item->title	 		= ConfigboxCacheHelper::getOrderTranslation($orderRecord->id, 'configbox_strings', 45, $item->id);
			unset($item->price);

			/** @noinspection PhpDeprecationInspection */
			$item->rateTitle =& $item->title;

			if (function_exists('manipulateDeliveryOptionOrderRecord')) {
				manipulateDeliveryOptionOrderRecord($item, $orderRecord);
			}

			ConfigboxCurrencyHelper::appendCurrencyPrices($item, $orderRecord->currency->multiplicator);

		}

		// Remove deactivated options (can be deactivated in override function)
		foreach ($items as $key=>$option) {
			if (!empty($option->deactivated)) {
				unset($items[$key]);
			}
		}

		$sortFunction = function($a, $b) {
			$sortA = number_format($a->basePriceGross, 3, '.', '').$a->ordering;
			$sortB = number_format($b->basePriceGross, 3, '.', '').$b->ordering;
			return strnatcmp($sortA, $sortB);
		};

		// Sort by price
		usort($items, $sortFunction);

		return $items;
	}

	/**
	 * @param ConfigboxOrderData $orderRecord
	 * @return NULL|ConfigboxDeliveryMethodData
	 */
	function getOrderRecordDeliveryOption($orderRecord) {

		if (!$orderRecord->delivery_id) {
			return NULL;
		}

		$query = "SELECT * FROM `#__cbcheckout_order_shipping_methods` WHERE `order_id` = ".(int)$orderRecord->id." AND `id` = ".(int)$orderRecord->delivery_id;
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		/**
		 * @var ConfigboxDeliveryMethodData $item
		 */
		$item = $db->loadObject();

		if (!$item) {
			return NULL;
		}

		$item->taxRate 			= $this->getOrderRecordTaxRate($orderRecord->id, $item->taxclass_id, $orderRecord->orderAddress);
		$item->basePriceNet 	= $item->price;
		$item->basePriceTax 	= round($item->basePriceNet * $item->taxRate / 100, 2);
		$item->basePriceGross 	= $item->basePriceNet + $item->basePriceTax;
		$item->title 		= ConfigboxCacheHelper::getOrderTranslation($orderRecord->id, 'configbox_strings', 45, $item->id);

		/** @noinspection PhpDeprecationInspection */
		$item->rateTitle = $item->title;

		if (function_exists('manipulateDeliveryOptionOrderRecord')) {
			manipulateDeliveryOptionOrderRecord($item, $orderRecord);
		}

		// Ignore the delivery option if it was made unpublished
		if (!empty($item->deactivated)) {
			$return = NULL;
			return $return;
		}

		ConfigboxCurrencyHelper::appendCurrencyPrices($item, $orderRecord->currency->multiplicator);
		unset($item->price);

		return $item;

	}

	function storeOrderRecordDeliveryOption($orderId, $deliveryId) {

		$orderRecord = $this->getOrderRecord($orderId);

		if ($deliveryId != 0 && $this->isValidDeliveryOption($orderRecord, $deliveryId) == false) {
			$this->setError( KText::_('Delivery option not found'));
			return false;
		}
		else {
			$query = "UPDATE `#__cbcheckout_order_records` SET `delivery_id` = ".intval($deliveryId)." WHERE `id` = ".intval($orderId);
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				KLog::log('Could not set delivery id "'.$deliveryId.'" for order "'.$orderId.'", because of an sql error: "'.$db->getErrorMsg().'"','error',KText::_('Could not change delivery option.'));
				return false;
			}
			else {
				$this->unsetOrderRecord($orderId);
				return true;
			}
		}
	}

	function isValidDeliveryOption($orderRecord, $deliveryId) {
		$availableOptions = $this->getOrderRecordDeliveryOptions($orderRecord);
		$found = false;
		foreach ($availableOptions as $option) {
			if ($option->id == $deliveryId) {
				$found = true;
			}
		}
		return $found;
	}

	/**
	 * @param ConfigboxOrderData $orderRecord
	 * @return ConfigboxPaymentmethodData[]
	 * @throws Exception
	 */
	function getOrderRecordPaymentOptions($orderRecord) {

		$amount = $orderRecord->basePayableAmount;
		if (!empty($orderRecord->payment)) {
			$amount = $amount - $orderRecord->payment->basePriceGross;
		}

		$model = KenedoModel::getModel('ConfigboxModelPayment');
		return $model->getPaymentOptions($orderRecord->orderAddress, $amount);

	}

	/**
	 * @param ConfigboxOrderData $orderRecord
	 * @return null|object
	 */
	function &getOrderRecordPaymentOption($orderRecord) {

		if (!$orderRecord->payment_id) {
			$return = NULL;
			return $return;
		}

		$query = "SELECT * FROM `#__cbcheckout_order_payment_methods` WHERE `order_id` = ".(int)$orderRecord->id." AND `id` = ".(int)$orderRecord->payment_id;
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$item = $db->loadObject();

		if ($item == NULL) {
			$return = NULL;
			return $return;
		}

		$item->taxRate 			= $this->getOrderRecordTaxRate($orderRecord->id, $item->taxclass_id, $orderRecord->orderAddress);
		$item->basePriceNet 	= $this->calculatePaymentOptionPrice($item, $orderRecord->basePayableAmount);
		$item->basePriceTax 	= round($item->basePriceNet * $item->taxRate / 100, 2);
		$item->basePriceGross 	= $item->basePriceNet + $item->basePriceTax;

		$item->params = new KStorage($item->params);

		// Load the payment class language file
		$tag = KenedoPlatform::p()->getLanguageTag();

		$pspFolder = ConfigboxPspHelper::getPspConnectorFolder($item->connector_name);

		$file = $pspFolder .'/language/'. $tag .'/'. $tag .'.ini';
		if (is_file($file)) {
			KText::load($file, $orderRecord->orderAddress->language_tag);
		}

		$item->title 		= ConfigboxCacheHelper::getOrderTranslation($orderRecord->id, 'configbox_strings', 46, $item->id);
		$item->description 	= ConfigboxCacheHelper::getOrderTranslation($orderRecord->id, 'configbox_strings', 47, $item->id);

		ConfigboxCurrencyHelper::appendCurrencyPrices($item, $orderRecord->currency->multiplicator);
		unset($item->price);
		return $item;

	}

	function storeOrderRecordPaymentOption($orderId, $paymentId) {

		$orderRecord = $this->getOrderRecord($orderId);

		if ($paymentId) {
			$found = $this->isValidPaymentOption($orderRecord, $paymentId);
		}
		else {
			$found = true;
		}
		if ($found == false) {
			$this->setError( KText::_('Payment option not found'));
			return false;
		}
		else {
			$query = "UPDATE `#__cbcheckout_order_records` SET `payment_id` = ".intval($paymentId)." WHERE `id` = ".intval($orderId);
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			$succ = $db->query();
			if (!$succ) {
				KLog::log('Could not set payment id "'.$paymentId.'" for order "'.$orderId.'", because of an sql error: "'.$db->getErrorMsg().'"','error',KText::_('Could not change payment option.'));
				return false;
			}
			else {
				$this->unsetOrderRecord($orderId);
				return true;
			}
		}
	}

	function isValidPaymentOption($orderRecord, $paymentId) {

		$availableOptions = $this->getOrderRecordPaymentOptions($orderRecord);
		$found = false;
		foreach ($availableOptions as $option) {
			if ($option->id == $paymentId) {
				$found = true;
			}
		}
		return $found;

	}

	function calculatePaymentOptionPrice($data, $orderPrice) {

		// Add percentage of the order price
		$paymentPrice = round($data->price + $orderPrice * $data->percentage / 100, 3);

		// Cap by min and max price
		if ($data->price_min != 0 && $paymentPrice < $data->price_min) {
			$paymentPrice = $data->price_min;
		}
		if ($data->price_max != 0 && $paymentPrice > $data->price_max) {
			$paymentPrice = $data->price_max;
		}

		return $paymentPrice;
	}

	function getOrderRecordDiscount($orderId, $groupId, $basePriceNet, $currency) {

		if (function_exists('overrideGetOrderRecordDiscount')) {
			$discount = overrideGetOrderRecordDiscount($orderId, $groupId, $basePriceNet, $currency);
			if ($discount->type == 'amount') {
				ConfigboxCurrencyHelper::appendCurrencyPrices($discount, $currency->multiplicator);
			}
			return $discount;
		}

		$query = "SELECT * FROM `#__cbcheckout_order_user_groups` WHERE `order_id` = ".(int)$orderId." AND `group_id` = ".(int)$groupId;
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		/**
		 * @var ConfigboxGroupData $customerGroup
		 */
		$customerGroup = $db->loadObject();

		$levelToTranslation = array(1=>80,2=>81,3=>82,4=>83,5=>84);
		for ($i = 1; $i<=5; $i++) {
			$customerGroup->{'title_discount_'.$i} = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', $levelToTranslation[$i], $groupId);
		}

		$discount = ConfigboxCustomerGroupHelper::getDiscount($customerGroup, $basePriceNet);

		return $discount;

	}

	function getOrderRecordDiscountRecurring($orderId, $groupId, $basePriceNet, $currency) {

		if (function_exists('overrideGetOrderRecordDiscountRecurring')) {
			$discount = overrideGetOrderRecordDiscountRecurring($orderId, $groupId, $basePriceNet, $currency);
			if ($discount->type == 'amount') {
				ConfigboxCurrencyHelper::appendCurrencyPrices($discount, $currency->multiplicator);
			}
			return $discount;
		}

		$query = "SELECT * FROM `#__cbcheckout_order_user_groups` WHERE `order_id` = ".(int)$orderId." AND `group_id` = ".(int)$groupId;
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$platformGroup = $db->loadObject();

		if (!$platformGroup) {
			$defaultGroupId = CbSettings::getInstance()->get('default_customer_group_id');
			$platformGroup = ConfigboxUserHelper::getGroupData($defaultGroupId);
		}

		$levelToTranslation = array(1=>85,2=>86,3=>87,4=>88,5=>89);
		for ($i = 1; $i<=5; $i++) {
			$platformGroup->{'title_discount_recurring_'.$i} = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', $levelToTranslation[$i], $groupId);
		}

		$discount = ConfigboxCustomerGroupHelper::getDiscountRecurring($platformGroup, $basePriceNet);

		return $discount;

	}

	function getOrderRecordCouponData($orderRecord) {

		if (function_exists('overrideGetOrderRecordCouponData')) {
			$couponData = overrideGetOrderRecordCouponData($orderRecord);
		}
		else {
			$couponData = new stdClass();
			$couponData->baseCouponDiscountNet = 0;
			$couponData->baseCouponDiscountTax = 0;
			$couponData->baseCouponDiscountGross = 0;
			$couponData->taxRate = 0;
		}

		ConfigboxCurrencyHelper::appendCurrencyPrices($couponData, $orderRecord->currency->multiplicator);

		return $couponData;

	}

	function &getOrderRecordTaxRate($orderId, $taxClassId, $orderAddress, $checkVatFree = true) {

		if (function_exists('getOverrideTaxRate')) {
			$rate = getOverrideTaxRate($orderAddress);
			return $rate;
		}

		$countryId 	= (int)$orderAddress->country;
		$stateId 	= (int)$orderAddress->state;
		$countyId 	= (int)$orderAddress->county_id;
		$cityId 	= (int)$orderAddress->city_id;
		$taxClassId = (int)$taxClassId;

		if (!isset($this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId])) {

			// If this is VAT free, tax rate is zero in any case
			if ($checkVatFree && ConfigboxUserHelper::isVatFree($orderAddress, $orderId)) {
				$this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_rate'] = 0;
				$this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_code'] = '';
			}
			else {

				$db = KenedoPlatform::getDb();
				$query1 = "
				SELECT `tax_rate`, `tax_code`
				FROM `#__cbcheckout_order_tax_class_rates`
				WHERE `order_id` = ".(int)$orderId." AND `tax_class_id` = ".(int)$taxClassId." AND ( (`state_id` = ".(int)$stateId." AND `state_id` != 0) OR (`country_id` = ".(int)$countryId." AND `country_id` != 0) )
				ORDER BY `state_id` DESC 
				LIMIT 1";
				$db->setQuery($query1);
				$taxRateRecord = $db->loadAssoc();

				if ($taxRateRecord !== NULL) {
					$this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_rate'] = $taxRateRecord['tax_rate'];
					$this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_code'] = $taxRateRecord['tax_code'];
				}
				else {
					$query2 = "
					SELECT `default_tax_rate`
					FROM `#__cbcheckout_order_tax_class_rates`
					WHERE `order_id` = ".(int)$orderId." AND `tax_class_id` = ".(int)$taxClassId."
					LIMIT 1";
					$db->setQuery($query2);
					$taxRate = $db->loadResult();

					if ($taxRate === NULL) {
						KLog::log('Could not find tax rate for tax class id: '.$taxClassId, 'error', KText::_('Could not find tax rate for tax class id: "'.$taxClassId.'"'));
						return false;
					}
					else {
						$this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_rate'] = $taxRate;
						$this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_code'] = '';
					}
				}


				// Add county and city tax
				$db = KenedoPlatform::getDb();
				$query = "
				SELECT `tax_rate`, `tax_code`
				FROM `#__cbcheckout_order_tax_class_rates`
				WHERE order_id = ".intval($orderId)." AND tax_class_id = ".intval($taxClassId)." AND ( (county_id != 0 AND county_id = ".(int)$orderAddress->county_id.") OR (city_id != 0 AND city_id = ".(int)$orderAddress->city_id.") )
				ORDER BY county_id DESC, city_id DESC
				";

				$db->setQuery($query);
				$taxRates = $db->loadAssocList();

				if ($taxRates) {
					foreach ($taxRates as $taxRate) {
						$this->taxRateCache[strval($countryId)][strval($stateId)][strval($countyId)][strval($cityId)][$taxClassId]['tax_rate'] += floatval($taxRate['tax_rate']);
						$this->taxRateCache[strval($countryId)][strval($stateId)][strval($countyId)][strval($cityId)][$taxClassId]['tax_code'] .= strval($taxRate['tax_code']);
					}
				}

			}
		}


		return $this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_rate'];

	}

	function getOrderRecordTaxCode($orderId, $taxClassId, $orderAddress, $checkVatFree = true) {

		$countryId 	= (int)$orderAddress->country;
		$stateId 	= (int)$orderAddress->state;
		$countyId 	= (int)$orderAddress->county_id;
		$cityId 	= (int)$orderAddress->city_id;
		$taxClassId = (int)$taxClassId;

		$this->getOrderRecordTaxRate($orderId, $taxClassId, $orderAddress, $checkVatFree);

		if (isset($this->taxRateCache[$countryId][$stateId][$countyId][$cityId][$taxClassId]['tax_code'])) {
			return $this->taxRateCache[strval($countryId)][strval($stateId)][strval($countyId)][strval($cityId)][$taxClassId]['tax_code'];
		}
		else {
			return '';
		}

	}

	/**
	 * @param int $orderId Optional (will be auto-determined then)
	 * @return object
	 */
	function getOrderAddress($orderId = NULL) {

		if ($orderId === NULL) {
			$orderId = $this->getId();
		}

		$orderAddress = ConfigboxUserHelper::getOrderAddress($orderId);

		return $orderAddress;

	}

	function isVatFree( $orderId = NULL ) {

		if ($orderId === NULL) {
			$orderId = $this->getId();
		}

		$orderAddress = ConfigboxUserHelper::getOrderAddress($orderId, false, false);
		if (!$orderAddress) {
			return false;
		}
		$isVatFree = ConfigboxUserHelper::isVatFree($orderAddress, $orderId);

		return $isVatFree;

	}

	function setStatus($status, $orderId) {

		KLog::log('Issuing status change of order "'.$orderId. '" to "'.$status.'"','debug');

		$responses = KenedoObserver::triggerEvent('onConfigBoxSetStatus', array($orderId,$status));

		if (count($responses) == 0) {
			$this->setError(KText::_('Could not update order status. No plugins for status updates installed.'));
			KLog::log('Could not update order status in frontend. No status update plugins installed','error',KText::_('A system error occured.'));
			return false;
		}
		foreach ($responses as $response) {
			if ($response === false) {
				$this->setError(KText::_('Could not update order status.'));
				KLog::log('Status update client side was not successful. A plugin returned false.','error',KText::_('A system error occured.'));
				return false;
			}
		}
		return true;
	}

	function getStatusDropDown( $selected = NULL ) {
		$statusCodes = $this->getOrderStatuses();
		$options = array();
		foreach ($statusCodes as $id=>$status) {
			$options[$id] = $status->title;
		}

		return KenedoHtml::getSelectField('status', $options, $selected, 0, false, 'make-me-chosen');
	}

	/**
	 *
	 * Creates all DB and file data for an order record and returns the ID
	 *
	 * @param ConfigboxCartData $cartDetails Cart details as it comes out of ConfigboxModelCart::getCartDetails()
	 * @param int $status (optional) The status you want to set initially (will use the cartDetails status of omitted)
	 * @return int|boolean id of the created order record, false on error
	 */
	function createOrderRecord($cartDetails, $status = NULL) {

		if (!is_object($cartDetails)) {
			KLog::log('Invalid cartDetails parameter passed. Parameter was "'.var_export($cartDetails,true).'".','error',KText::_('System error: Could not create checkout order because input data is invalid.') );
			return false;
		}

		$db = KenedoPlatform::getDb();

		try {
			$db->startTransaction();


			/**
			 * @var ConfigboxOrderData $orderRecord
			 */
			$orderRecord = new stdClass();

			// Set those 3 to NULL for now, will be updated in the end (for referential integrity, referenced rows need to be written first)
			$orderRecord->user_id 		= NULL;
			$orderRecord->delivery_id 	= NULL;
			$orderRecord->payment_id 	= NULL;
			$orderRecord->cart_id		= $cartDetails->id;
			$orderRecord->store_id 		= ConfigboxStoreHelper::getStoreId();
			$orderRecord->created_on 	= KenedoTimeHelper::getFormattedOnly('NOW','datetime');
			$orderRecord->paid 			= '0';
			$orderRecord->paid_on 		= NULL;
			$orderRecord->status 		= ($status !== NULL) ? $status : $cartDetails->status;
			$orderRecord->comment		= NULL;

			$orderRecord->custom_1 		= empty($cartDetails->custom_1) ? '' : $cartDetails->custom_1;
			$orderRecord->custom_2	 	= empty($cartDetails->custom_2) ? '' : $cartDetails->custom_2;
			$orderRecord->custom_3	 	= empty($cartDetails->custom_3) ? '' : $cartDetails->custom_3;
			$orderRecord->custom_4 		= empty($cartDetails->custom_4) ? '' : $cartDetails->custom_4;
			$orderRecord->custom_5 		= empty($cartDetails->custom_5) ? '' : $cartDetails->custom_5;
			$orderRecord->custom_6	 	= empty($cartDetails->custom_6) ? '' : $cartDetails->custom_6;
			$orderRecord->custom_7	 	= empty($cartDetails->custom_7) ? '' : $cartDetails->custom_7;
			$orderRecord->custom_8 		= empty($cartDetails->custom_8) ? '' : $cartDetails->custom_8;
			$orderRecord->custom_9 		= empty($cartDetails->custom_9) ? '' : $cartDetails->custom_9;
			$orderRecord->custom_10		= empty($cartDetails->custom_10) ? '' : $cartDetails->custom_10;

			$orderRecord->coupon_discount_net 	= empty($cartDetails->baseCouponDiscountNet) ? 0 : $cartDetails->baseCouponDiscountNet;

			$succ = $db->insertObject('#__cbcheckout_order_records', $orderRecord, 'id');

			// Just a double safety measure, insertObject would have thrown an exception already, this is just in case the method changes.
			if ($succ == false) {
				throw new Exception('Error on inserting order record. See error log file for more info');
			}

			/* COPY OVER THE USER GROUP INFORMATION - START */
			$query = "SELECT * FROM `#__configbox_groups`";
			$db->setQuery($query);
			$groups = $db->loadObjectList();

			$values = array();
			foreach ($groups as $group) {

				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 80, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 81, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 82, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 83, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 84, $group->id);

				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 85, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 86, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 87, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 88, $group->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 89, $group->id);


				$values[] = "(
					'".intval($orderRecord->id)."', 
					'".intval($group->id)."', 
					'".floatval($group->discount_start_1)."', 		'".floatval($group->discount_start_2)."', 		'".floatval($group->discount_start_3)."', 		'".floatval($group->discount_start_4)."', 		'".floatval($group->discount_start_5)."', 
					'".floatval($group->discount_factor_1)."', 		'".floatval($group->discount_factor_2)."', 		'".floatval($group->discount_factor_3)."', 		'".floatval($group->discount_factor_4)."', 		'".floatval($group->discount_factor_5)."',
					'".floatval($group->discount_amount_1)."', 		'".floatval($group->discount_amount_2)."', 		'".floatval($group->discount_amount_3)."', 		'".floatval($group->discount_amount_4)."', 		'".floatval($group->discount_amount_5)."',
					'".$db->getEscaped($group->discount_type_1)."', '".$db->getEscaped($group->discount_type_2)."', '".$db->getEscaped($group->discount_type_3)."', '".$db->getEscaped($group->discount_type_4)."', '".$db->getEscaped($group->discount_type_5)."',
					
					'".floatval($group->discount_recurring_start_1)."', 		'".floatval($group->discount_recurring_start_2)."', 		'".floatval($group->discount_recurring_start_3)."', 		'".floatval($group->discount_recurring_start_4)."', 		'".floatval($group->discount_recurring_start_5)."', 
					'".floatval($group->discount_recurring_factor_1)."', 		'".floatval($group->discount_recurring_factor_2)."', 		'".floatval($group->discount_recurring_factor_3)."', 		'".floatval($group->discount_recurring_factor_4)."', 		'".floatval($group->discount_recurring_factor_5)."',
					'".floatval($group->discount_recurring_amount_1)."', 		'".floatval($group->discount_recurring_amount_2)."', 		'".floatval($group->discount_recurring_amount_3)."', 		'".floatval($group->discount_recurring_amount_4)."', 		'".floatval($group->discount_recurring_amount_5)."',
					'".$db->getEscaped($group->discount_recurring_type_1)."', 	'".$db->getEscaped($group->discount_recurring_type_2)."', 	'".$db->getEscaped($group->discount_recurring_type_3)."', 	'".$db->getEscaped($group->discount_recurring_type_4)."', 	'".$db->getEscaped($group->discount_recurring_type_5)."',
					
					'".$db->getEscaped($group->title)."',
					
					'".$db->getEscaped($group->custom_1)."',
					'".$db->getEscaped($group->custom_2)."',
					'".$db->getEscaped($group->custom_3)."',
					'".$db->getEscaped($group->custom_4)."',
					
					'".$db->getEscaped($group->enable_checkout_order)."',
					'".$db->getEscaped($group->enable_see_pricing)."',
					'".$db->getEscaped($group->enable_save_order)."',
					'".$db->getEscaped($group->enable_request_quotation)."',
					
					'".$db->getEscaped($group->b2b_mode)."',
					'".$db->getEscaped($group->joomla_user_group_id)."',
					'".$db->getEscaped($group->quotation_download)."',
					'".$db->getEscaped($group->quotation_email)."'
			
				)";
			}

			if (count($values)) {

				$query = "
				INSERT INTO `#__cbcheckout_order_user_groups`
				(	`order_id`, 
					`group_id`, 
					`discount_start_1`, 	`discount_start_2`, 	`discount_start_3`, 	`discount_start_4`, 	`discount_start_5`, 
					`discount_factor_1`, 	`discount_factor_2`, 	`discount_factor_3`,	`discount_factor_4`, 	`discount_factor_5`, 
					`discount_amount_1`, 	`discount_amount_2`, 	`discount_amount_3`, 	`discount_amount_4`, 	`discount_amount_5`, 
					`discount_type_1`, 		`discount_type_2`, 		`discount_type_3`, 		`discount_type_4`, 		`discount_type_5`,
					
					`discount_recurring_start_1`, 	`discount_recurring_start_2`, 	`discount_recurring_start_3`, 	`discount_recurring_start_4`, 	`discount_recurring_start_5`, 
					`discount_recurring_factor_1`, 	`discount_recurring_factor_2`, 	`discount_recurring_factor_3`,	`discount_recurring_factor_4`, 	`discount_recurring_factor_5`, 
					`discount_recurring_amount_1`, 	`discount_recurring_amount_2`, 	`discount_recurring_amount_3`, 	`discount_recurring_amount_4`, 	`discount_recurring_amount_5`, 
					`discount_recurring_type_1`, 	`discount_recurring_type_2`, 	`discount_recurring_type_3`, 	`discount_recurring_type_4`, 	`discount_recurring_type_5`,
					
					`title`,
				
					`custom_1`,
					`custom_2`,
					`custom_3`,
					`custom_4`,
					
					`enable_checkout_order`,
					`enable_see_pricing`,
					`enable_save_order`,
					`enable_request_quotation`,
					
					`b2b_mode`,
					`joomla_user_group_id`,
					`quotation_download`,
					`quotation_email`
				
				)
				VALUES ".implode(",\n",$values);
				$db->setQuery($query);
				$db->query();

			}
			/* COPY OVER THE USER GROUP INFORMATION - END */



			/* STORE THE ORDER ADDRESS - START */
			$userData = ConfigboxUserHelper::getUser($cartDetails->user_id, true, true);

			// This method also adds copies of country/state/county/city/salutation data
			$success = ConfigboxUserHelper::setOrderAddress($orderRecord->id, $userData, false);

			if ($success == false) {
				throw new Exception ('Error inserting order address record. See error log file for more info');
			}
			/* STORE THE ORDER ADDRESS - END */



			/* COPY OVER POSITION DATA AND CONNECTED DATA - START */

			foreach ($cartDetails->positions as $cartPosition) {

				/* INSERT POSITION - START */
				/**
				 * @var ConfigboxOrderPositionData $position
				 */
				$position = new stdClass();
				$position->order_id 		= $orderRecord->id;
				$position->product_id 		= $cartPosition->productData->id;
				$position->product_sku 		= $cartPosition->productData->sku;
				$position->product_image 	= '';
				$position->quantity 		= $cartPosition->quantity;
				$position->weight 			= $cartPosition->weight;
				$position->dispatch_time 	= $cartPosition->productData->dispatch_time;

				$position->product_base_price_overrides = $cartPosition->productData->baseprice_overrides;
				$position->product_base_price_recurring_overrides = $cartPosition->productData->baseprice_recurring_overrides;

				$position->taxclass_id 				= $cartPosition->productData->taxclass_id;
				$position->taxclass_recurring_id 	= $cartPosition->productData->taxclass_recurring_id;

				$position->product_base_price_net 			= $cartPosition->productData->basePriceNet;
				$position->product_base_price_recurring_net = $cartPosition->productData->basePriceRecurringNet;

				$position->price_net 			= $cartPosition->baseTotalUnreducedNet;
				$position->price_recurring_net 	= $cartPosition->baseTotalUnreducedRecurringNet;

				if (isset($cartPosition->productData->product_custom_1)) {
					$position->product_custom_1 = $cartPosition->productData->product_custom_1;
					$position->product_custom_2 = $cartPosition->productData->product_custom_2;
					$position->product_custom_3 = $cartPosition->productData->product_custom_3;
					$position->product_custom_4 = $cartPosition->productData->product_custom_4;
				}
				else {
					$position->product_custom_1 = '';
					$position->product_custom_2 = '';
					$position->product_custom_3 = '';
					$position->product_custom_4 = '';
				}

				// Copy over translatable product data
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings',  1, $cartPosition->productData->id); // Product Title
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 11, $cartPosition->productData->id); // Product Description
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 25, $cartPosition->productData->id); // Interval
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 26, $cartPosition->productData->id); // Price Label
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 31, $cartPosition->productData->id); // Price Recurring Label
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 52, $cartPosition->productData->id); // Product Custom 5
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 53, $cartPosition->productData->id); // Product Custom 6

				// Insert the position
				$db->insertObject('#__cbcheckout_order_positions', $position, 'id');

				/* CREATE AND SET THE PRODUCT IMAGE - START */
				$fileName = '';
				try {
					$fileName = $this->createPositionImage($orderRecord->id, $position->id, $cartPosition);
				}
				catch(Exception $e) {
					KLog::log('Position image creation failed, see other error log messages for details.', 'error');
				}

				if ($fileName) {
					$position->product_image = $fileName;
					$success = $db->updateObject('#__cbcheckout_order_positions', $position, 'id');
					if ($success == false) {
						throw new Exception('Error on updating product image URL in order position record. See log for more info');
					}
				}

				/* CREATE AND SET THE PRODUCT IMAGE - END */






				/* INSERT POSITION - END */


				/* DEAL WITH CUSTOM PRODUCT TITLES - START */

				if ($position->id) {

					// See if we got elements, if not we can skip all that
					if (count($cartPosition->selections)) {

						// Get the languages
						$languages = KenedoLanguageHelper::getActiveLanguages();

						// Go through elements..
						foreach ($cartPosition->selections as $selection) {
							// ..get element data..
							$q = ConfigboxQuestion::getQuestion($selection->questionId);
							// .. see if we got one that has the right setting
							if ($q->asproducttitle) {

								// Prepare the translations that are to be copied
								$translations = array();

								// Loop through the languages
								foreach ($languages as $language) {

									// Elements with options take the option's title
									if (count($q->answers)) {
										$text = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $selection->selection, $language->tag);
									}
									// Others the output value (get rid of HTML tags)
									else {
										$text = strip_tags($selection->outputValue);
									}
									// Add it to the translations array
									$translations[$language->tag] = $text;

								}

								// Finally add the translations to the order's translations
								ConfigboxCacheHelper::addTranslationsToOrder($orderRecord->id, 'configbox_strings', 79, $position->id, $translations);

								// Skip searching through more elements
								break;

							}
							unset($q);

						}

						unset($languages, $selection);

					}

				}
				/* DEAL WITH CUSTOM PRODUCT TITLES - END */

				if (count($cartPosition->selections)) {

					foreach ($cartPosition->selections as $selection) {

						/**
						 * @var ConfigboxOrderPositionConfigurationData $configuration
						 */
						$configuration = new stdClass();
						$configuration->position_id 		= $position->id;
						$configuration->price_net 			= $selection->basePriceNet;
						$configuration->price_recurring_net = $selection->basePriceRecurringNet;
						$configuration->element_id 			= $selection->questionId;
						$configuration->element_type 		= $selection->type;
						$configuration->value 				= $selection->selection;
						$configuration->output_value 		= $selection->outputValue;
						$configuration->element_code 		= '';
						$configuration->weight 				= $selection->weight;

						// Mind that if we got calculation overrides, then we make a calculation for each of them and
						// store the results in the static price overrides. This is so that we keep on having no
						// connection to product data (and save us from making copies of all calculation data and
						// making a parallel system of calculating)

						// Prepare the array for price overrides
						$priceOverrides = array();

						// Prepare an array with static overrides (make the keys group ids to make things easier later)
						if ($selection->priceOverrides != '[]') {

							$staticOverrides = json_decode($selection->priceOverrides, true);

							foreach ($staticOverrides as $staticOverride) {
								$priceOverrides[$staticOverride['group_id']] = array(
									'group_id' => $staticOverride['group_id'],
									'price' => $staticOverride['price'],
								);
							}

						}

						// Now see if there are calculation overrides
						if ($selection->priceCalculationOverrides != '[]') {
							$overrides = json_decode($selection->priceCalculationOverrides, true);
							// For each calculation override, calculate the result and store it in the static price override field
							foreach ($overrides as $override) {
								if ($override['calculation_id'] != NULL) {
									$selections = ConfigboxConfiguration::getInstance($position->id)->getSelections(false);
									$priceOverrides[$override['group_id']]['group_id'] = $override['group_id'];
									$priceOverrides[$override['group_id']]['price'] = ConfigboxCalculation::calculate($override['calculation_id'], $selection->questionId, $selection->selection, $selections);
								}
							}
						}

						// Finally, store all that stuff as JSOn again
						$configuration->price_overrides				= json_encode($priceOverrides);



						// Prepare the array for recurring price overrides
						$priceOverrides = array();

						// Prepare an array with static overrides (make the keys group ids to make things easier later)
						if ($selection->priceRecurringOverrides != '[]') {

							$staticOverrides = json_decode($selection->priceRecurringOverrides, true);

							foreach ($staticOverrides as $staticOverride) {
								$priceOverrides[$staticOverride['group_id']] = array(
									'group_id' => $staticOverride['group_id'],
									'price' => $staticOverride['price'],
								);
							}

						}

						// Now see if there are calculation overrides
						if ($selection->priceRecurringCalculationOverrides != '[]') {
							$overrides = json_decode($selection->priceRecurringCalculationOverrides, true);
							// For each calculation override, calculate the result and store it in the static price override field
							foreach ($overrides as $override) {
								if ($override['calculation_id'] != NULL) {
									$selections = ConfigboxConfiguration::getInstance($position->id)->getSelections(false);
									$priceOverrides[$override['group_id']]['price'] = ConfigboxCalculation::calculate($override['calculation_id'], $selection->questionId, $selection->selection, $selections);
								}
							}
						}

						// Finally, store all that stuff as JSOn again
						$configuration->price_recurring_overrides 	= json_encode($priceOverrides);


						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings',  4, $configuration->element_id); // Element Title
						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 14, $configuration->element_id); // Element Description
						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 50, $configuration->element_id); // Element Translatable 1
						ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 51, $configuration->element_id); // Element Translatable 2

						$question = ConfigboxQuestion::getQuestion($selection->questionId);

						if (count($question->answers) && isset($question->answers[$selection->selection])) {

							$answer = $question->answers[$selection->selection];

							$configuration->xref_id = $selection->selection;
							$configuration->option_id = $answer->option_id;
							$configuration->option_sku = $answer->sku;
							$configuration->option_image = $answer->option_image;

							$configuration->option_custom_1 = $answer->option_custom_1;
							$configuration->option_custom_2 = $answer->option_custom_2;
							$configuration->option_custom_3 = $answer->option_custom_3;
							$configuration->option_custom_4 = $answer->option_custom_4;

							$configuration->assignment_custom_1 = $answer->assignment_custom_1;
							$configuration->assignment_custom_2 = $answer->assignment_custom_2;
							$configuration->assignment_custom_3 = $answer->assignment_custom_3;
							$configuration->assignment_custom_4 = $answer->assignment_custom_4;

							ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings',  5, $configuration->option_id); // Option Title
							ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 15, $configuration->option_id); // Option Description
							ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 28, $configuration->option_id); // Option Contract

							// Copy translatable custom option fields over
							ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 60, $configuration->option_id); // Option Custom 5
							ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 61, $configuration->option_id); // Option Custom 6

						}
						else {
							$configuration->xref_id = 0;
							$configuration->option_id = 0;
							$configuration->option_sku = '';
							$configuration->option_image = '';

							$configuration->option_custom_1 = '';
							$configuration->option_custom_2 = '';
							$configuration->option_custom_3 = '';
							$configuration->option_custom_4 = '';

							$configuration->assignment_custom_1 = '';
							$configuration->assignment_custom_2 = '';
							$configuration->assignment_custom_3 = '';
							$configuration->assignment_custom_4 = '';
						}

						$configuration->element_custom_1 = $question->element_custom_1;
						$configuration->element_custom_2 = $question->element_custom_2;
						$configuration->element_custom_3 = $question->element_custom_3;
						$configuration->element_custom_4 = $question->element_custom_4;

						$configuration->show_in_overviews = intval($selection->showInOverviews);

						$success = $db->insertObject('#__cbcheckout_order_configurations', $configuration, 'id');

						if ($success == false) {
							throw new Exception('Error on inserting order position configuration record. See log for more info');
						}

					}
					/* INSERT EACH ELEMENT - END */

				}

			}

			/* COPY OVER POSITION DATA AND CONNECTED DATA - END */


			/* COPY OVER THE CURRENCY RATE INFORMATION - START */

			$query = "SELECT * FROM `#__configbox_currencies` WHERE `published` = '1' ORDER BY `ordering`";
			$db->setQuery($query);
			$currencies = $db->loadObjectList();

			$values = array();
			foreach ($currencies as $item) {
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 6, $item->id);
				$values[] = "('".intval($orderRecord->id)."', '".intval($item->id)."', '".intval($item->base)."', '".floatval($item->multiplicator)."', '".$db->getEscaped($item->symbol)."', '".$db->getEscaped($item->code)."', '".intval($item->default)."', '".intval($item->ordering)."', '".intval($item->published)."' )";
			}

			if (count($values)) {
				$query = "
				INSERT INTO `#__cbcheckout_order_currencies`
				(`order_id`, `id`, `base`, `multiplicator`, `symbol`, `code`, `default`, `ordering`, `published`)
				VALUES ".implode(",\n",$values);
				$db->setQuery($query);
				$db->query();
			}
			/* COPY OVER THE CURRENCY RATE INFORMATION - END */



			/* COPY OVER THE TAX RATE INFORMATION - START */
			$query = "
				SELECT tc.default_tax_rate, tc.id AS `tax_class_id`, tcr.city_id, tcr.county_id, tcr.state_id, tcr.country_id, tcr.tax_rate, tcr.tax_code
				FROM `#__configbox_tax_classes` AS tc
				LEFT JOIN `#__configbox_tax_class_rates` AS tcr ON tc.id = tcr.tax_class_id";
			$db->setQuery($query);
			$taxClasses = $db->loadObjectList();

			$values = array();
			foreach ($taxClasses as $taxClass) {
				$values[] = "('".intval($orderRecord->id)."', '".intval($taxClass->tax_class_id)."', '".intval($taxClass->city_id)."', '".intval($taxClass->county_id)."', '".intval($taxClass->state_id)."', '".intval($taxClass->country_id)."', '".floatval($taxClass->tax_rate)."', '".floatval($taxClass->default_tax_rate)."', '".$db->getEscaped($taxClass->tax_code)."' )";
			}

			if (count($values)) {
				$query = "
				INSERT INTO `#__cbcheckout_order_tax_class_rates`
				(`order_id`, `tax_class_id`, `city_id`, `county_id`, `state_id`, `country_id`, `tax_rate`, `default_tax_rate`, `tax_code`)
				VALUES ".implode(",\n",$values);
				$db->setQuery($query);
				$db->query();
			}
			/* COPY OVER THE TAX RATE INFORMATION - END */

			/* COPY OVER THE DELIVERY RECORDS - START */
			$query = "SELECT * FROM `#__configbox_shipping_methods` WHERE `published` = '1'";
			$db->setQuery($query);
			$shippingRates = $db->loadObjectList();

			$values = array();

			foreach ($shippingRates as $item) {
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 44, $item->shipper_id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 45, $item->id);
				$values[] = "('".intval($orderRecord->id)."', '".intval($item->id)."', '".intval($item->shipper_id)."', '".intval($item->zone_id)."', '".floatval($item->minweight)."', '".floatval($item->maxweight)."', '".intval($item->deliverytime)."', '".floatval($item->price)."', '".intval($item->taxclass_id)."', '".$db->getEscaped($item->external_id)."', '".intval($item->ordering)."' )";
			}

			if (count($values)) {
				$query = "
				INSERT INTO `#__cbcheckout_order_shipping_methods`
				(`order_id`, `id`, `shipper_id`, `zone_id`, `minweight`, `maxweight`, `deliverytime`, `price`, `taxclass_id`, `external_id`, `ordering`)
				VALUES ".implode(",\n",$values);
				$db->setQuery($query);
				$db->query();
			}
			/* COPY OVER THE DELIVERY RECORDS - END */



			/* COPY OVER THE PAYMENT RECORDS - START */
			$query = "SELECT * FROM `#__configbox_payment_methods` WHERE `published` = '1'";
			$db->setQuery($query);
			$paymentOptions = $db->loadObjectList();

			$values = array();
			foreach ($paymentOptions as $item) {
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 46, $item->id);
				ConfigboxCacheHelper::copyTranslationToOrder($orderRecord->id, '#__configbox_strings', 47, $item->id);
				$values[] = "('".intval($orderRecord->id)."', '".intval($item->id)."', '".$db->getEscaped($item->connector_name)."', '".floatval($item->price)."', '".intval($item->taxclass_id)."', '".$db->getEscaped($item->params)."', '".intval($item->ordering)."', '".floatval($item->percentage)."', '".floatval($item->price_min)."', '".floatval($item->price_max)."' )";
			}

			if (count($values)) {
				$query = "
				INSERT INTO `#__cbcheckout_order_payment_methods`
				(`order_id`, `id`, `connector_name`, `price`, `taxclass_id`, `params`, `ordering`, `percentage`, `price_min`, `price_max`)
				VALUES ".implode(",\n",$values);
				$db->setQuery($query);
				$db->query();
			}
			/* COPY OVER THE PAYMENT RECORDS - END */


			// Store user, delivery and payment method id to order record
			$query = "
			UPDATE `#__cbcheckout_order_records` SET 
				`user_id` 		= ".intval($cartDetails->user_id).",
				`delivery_id` 	= ".(!empty($cartDetails->delivery->id) ? intval($cartDetails->delivery->id) : 'NULL').",
				`payment_id` 	= ".(!empty($cartDetails->payment->id) ? intval($cartDetails->payment->id) : 'NULL')."
			WHERE `id` = ".intval($orderRecord->id);

			$db->setQuery($query);
			$db->query();

		}
		catch(Exception $e) {
			KLog::logException($e);
			$db->rollbackTransaction();
			$this->setError($e->getMessage());
			return false;
		}

		$db->commitTransaction();
		return $orderRecord->id;
	}

	/**
	 * Writes (or overwrites) the position image and returns the file name (no full path).
	 * @param int $orderRecordId
	 * @param int $orderPositionId
	 * @param ConfigboxCartPositionData $cartPosition
	 * @return string $fileName File name (no full path)
	 * @throws Exception
	 */
	protected function createPositionImage($orderRecordId, $orderPositionId, $cartPosition) {

		$dirPositionImages = KenedoPlatform::p()->getDirDataCustomer().'/public/position_images';

		// Create the position images folder if necessary
		if (is_dir($dirPositionImages) == false) {
			$success = mkdir($dirPositionImages, 0755, true);
			if ($success == false) {
				KLog::log('Error copying product image. Could not create folder "'.$dirPositionImages.'". Make sure the containing folder is writable.', 'error');
				throw new Exception('Cannot create order, because the system cannot create the missing position image folder. See ConfigBox error log file for details.');
			}
		}

		switch ($cartPosition->productData->visualization_type) {

			case 'none':
			case 'shapediver':

				$sourcePath = $cartPosition->productData->prod_image_path;
				$ext = KenedoFileHelper::getExtension($sourcePath);
				$destFileName = $orderRecordId.'-'.$orderPositionId.'.'.$ext;
				$destPath = $dirPositionImages.'/'.$destFileName;
				$success = copy($sourcePath, $destPath);

				if ($success == false) {
					KLog::log('Error copying product image to order position image folder ("'.$sourcePath.'" to "'.$destPath.'".', 'error');
					throw new Exception('Could not copy product image for use as position image. See ConfigBox error log file for details.');
				}
				return $destFileName;

			case 'composite':

				$ext = 'png';
				$destFileName = $orderRecordId.'-'.$orderPositionId.'.'.$ext;
				$destPath = $dirPositionImages.'/'.$destFileName;

				$success = ConfigboxProductImageHelper::createMergedVisualizationImage($cartPosition, $destPath);

				// Use the product image as fallback if no image could be created
				if ($success === false) {
					$sourcePath = $cartPosition->productData->prod_image_path;
					$success = copy($sourcePath, $destPath);

					if ($success == false) {
						KLog::log('Error copying product image to order position image folder ("'.$sourcePath.'" to "'.$destPath.'".', 'error');
						throw new Exception('Could not copy product image for use as position image. See ConfigBox error log file for details.');
					}
				}
				return $destFileName;

		}

		return '';

	}

	/**
	 * Stores the Google Analytics Client ID (for transaction tracking in GA Enhanced Ecommerce and payment
	 * tracking in regular Ecommerce). Used on checkout page.
	 *
	 * @param int $orderId
	 * @param string $clientId
	 * @throws Exception In case the UPDATE query fails
	 */
	function storeGaClientId($orderId, $clientId) {

		$db = KenedoPlatform::getDb();
		$query = "UPDATE `#__cbcheckout_order_records` SET `ga_client_id` = '".$db->getEscaped($clientId)."' WHERE `id` = ".intval($orderId);
		$db->setQuery($query);
		$db->query();

	}

}
