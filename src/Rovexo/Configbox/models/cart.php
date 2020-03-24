<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelCart extends KenedoModelLight {

	/**
	 * @var int $id ID of the cart to deal with.
	 * @deprecated and defunct. If you want the session cart id, use getSessionCartId
	 */
	public $id;

	/**
	 * @var object[] $cartData Memo-cache for getCartData()
	 * @see getCartData()
	 */
	public $cartData;

	/**
	 * @var ConfigboxCartData[] $cartDetails Memo-cache for getCartDetails()
	 * @see getCartDetails()
	 */
	public $cartDetails;

	/**
	 * @deprecated and defunct. If you want the session cart id, use getSessionCartId
	 * @throws Exception if used
	 */
	function getId() {
		throw new Exception('ConfigboxModelCart::setId is no longer in use. All cart methods take cart id as parameter now.');
	}

	/**
	 * @param int $id Cart ID
	 * @param bool $remember If true, ID will be stored in session and will be used for auto-determine in later reqs.
	 * @deprecated and defunct All cart methods take cart id now.
	 * @throws Exception if used
	 */
	function setId($id = 0, $remember = false) {
		if (!empty($id) && $remember == true) {
			KLog::log('ConfigboxModelCart::setId is deprecated. Use ::setSessionCartId instead (takes cart id as parameter)', 'deprecated');
			$this->setSessionCartId($id);
		}
		else {
			throw new Exception('ConfigboxModelCart::setId is no longer in use. All cart methods take cart id as parameter now.');
		}
	}

	function setSessionCartId($id) {
		KSession::set('cart_id', $id, 'com_configbox');
	}

	function getSessionCartId() {
		return KSession::get('cart_id',0,'com_configbox');
	}

	/**
	 * Creates a new cart record, returns the new cart ID.
	 * @return bool|int Cart Id
	 * @throws Exception if inserting cart record fails
	 */
	function createCart() {

		// Get the user ID
		$userId = ConfigboxUserHelper::getUserId();

		// Get the current date/time
		$time = KenedoTimeHelper::getFormattedOnly('NOW', 'datetime');

		// Insert the cart record
		$db = KenedoPlatform::getDb();
		$query = "INSERT INTO `#__configbox_carts` SET `user_id` = ". intval($userId).", `created_time` = '".$db->getEscaped($time)."'";
		$db->setQuery($query);
		$success = $db->query();

		if ($success == false) {
			KLog::log('SQL error when inserting cart. Error message was:'.$db->getErrorMsg(), 'error');
			throw new Exception('SQL error when inserting cart');
		}
		else {
			// Return the new cart ID
			return $db->insertid();
		}

	}

	/**
	 * Resets cart data and details cache and drops the cart id session var. Use it if you want subsequent cart actions
	 * to be in a new cart.
	 */
	function resetCart() {
		$this->forgetMemoizedData();
		$this->setSessionCartId(0);
	}

	/**
	 * Resets cart data and details cache, but does not drop the cart id in session or the set ID in model
	 * @deprecated Use forgetMemoizedData instead
	 */
	function resetCartDataCache() {
		$this->forgetMemoizedData();
	}

	function forgetMemoizedData() {
		$this->cartData = NULL;
		$this->cartDetails = NULL;
	}

	/**
	 * Checks if given cart ID belongs to given user ID
	 * @param int $cartId
	 * @param int|null $userId NULL for current user ID
	 * @return bool
	 */
	function cartBelongsToUser($cartId, $userId = NULL) {

		if ($userId == NULL) {
			$userId = ConfigboxUserHelper::getUserId();
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT `user_id` FROM `#__configbox_carts` WHERE `id` = ".intval($cartId);
		$db->setQuery($query);
		$cartUserId = $db->loadResult();

		KLog::log('Comparing user id for cart id "'.$cartId.'": User id is "'.$userId.'", cart user id is "'.$cartUserId.'".', 'debug');

		return ($userId == $cartUserId);
	}

	/**
	 * Tells if given cart ID exists
	 * @param int $cartId
	 * @return bool
	 */
	function cartExists($cartId) {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_carts` WHERE `id` = ".intval($cartId);
		$db->setQuery($query);
		$result = $db->loadResult();
		return ($result == $cartId);
	}

	/**
	 * Returns raw cart data from DB. Expects setId() with a proper id set first.
	 * @param int $cartId
	 * @return null|object Cart data
	 * @throws Exception if no cart id was set
	 */
	function getCartData($cartId = null) {

		if (empty($cartId)) {

			$cartId = $this->getSessionCartId();

			if (!$cartId) {
				KLog::log('getCartData() called without a ID. Set ID was "'.var_export($cartId).'"', 'error');
				KLog::log('Trace "'.var_export(debug_backtrace(false), true).'"', 'error');
				throw new Exception('getCartData() called without a ID. use setId first.');
			}

		}

		if (empty($this->cartData[$cartId])) {

			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_carts` WHERE `id` = ".intval($cartId);
			$db->setQuery($query);
			$cartData = $db->loadObject();

			if ($cartData) {
				$this->cartData[$cartId] = $cartData;
				KenedoObserver::triggerEvent('onConfigBoxGetCbOrderId',array(&$this->cartData[$cartId]));
				KenedoObserver::triggerEvent('onConfigBoxGetStatus',array(&$this->cartData[$cartId]));
			}
			else {
				KLog::log('Could not get cart object, id requested was "'.$cartId.'", query was "'.$db->getQuery().'"' , 'error', KText::_('A system error occured.'));
			}

		}

		return $this->cartData[$cartId];
	}

	/**
	 * @param int $cartId
	 * @return null|ConfigboxCartData
	 * @throws Exception If no $cartId provided (and none found in session data)
	 */
	function getCartDetails($cartId = null) {

		if (empty($cartId)) {

			$cartId = $this->getSessionCartId();

			if (!$cartId) {
				KLog::log('getCartData() called without a ID. Set ID was "'.var_export($cartId).'"', 'error');
				throw new Exception('getCartData() called without a ID. use setId first.');
			}

		}

		if (empty($this->cartDetails[$cartId])) {

			// Set the basic cart data
			$this->cartDetails[$cartId] = $this->getCartData($cartId);

			if (!$this->cartDetails[$cartId]) {
				return null;
			}

			// Get the user record
			$this->cartDetails[$cartId]->userInfo = ConfigboxUserHelper::getUser($this->cartDetails[$cartId]->user_id, true, true);

			// Set the group data
			$this->cartDetails[$cartId]->groupData = ConfigboxUserHelper::getGroupData($this->cartDetails[$cartId]->userInfo->group_id);

			// Set if the cart is a VAT free order (careful, does not mean all products have zero tax rate, means that the orders are VAT free because of reverse charge or EU VAT rules)
			$this->cartDetails[$cartId]->isVatFree = ConfigboxUserHelper::isVatFree($this->cartDetails[$cartId]->userInfo);

			// Add position details to cart details
			$this->addPositionDetails($this->cartDetails[$cartId]);

			// Update all unreduced totals
			$this->updateUnreducedTotals($this->cartDetails[$cartId]);

			// Trigger event for before processing discount
			KenedoObserver::triggerEvent('onConfigBoxCartProcessingBeforeDiscounts', array(&$this->cartDetails[$cartId]));

			// Get the discount object
			$this->cartDetails[$cartId]->discount 			= ConfigboxCustomerGroupHelper::getDiscount($this->cartDetails[$cartId]->groupData, $this->cartDetails[$cartId]->baseTotalUnreducedNet);
			$this->cartDetails[$cartId]->discountRecurring 	= ConfigboxCustomerGroupHelper::getDiscountRecurring($this->cartDetails[$cartId]->groupData, $this->cartDetails[$cartId]->baseTotalUnreducedRecurringNet);

			// Apply the discount to all positions
			$this->applyDiscountToPositions($this->cartDetails[$cartId]);

			// Update totals
			$this->updateTotalsExceptUnreduced($this->cartDetails[$cartId]);

			// Trigger event
			KenedoObserver::triggerEvent('onConfigBoxCartProcessingAfterPositions', array(&$this->cartDetails[$cartId]));

			// Set price labels (used in price overview) and flags about having recurring pricing etc.
			$this->addLabelsAndFlags($this->cartDetails[$cartId]);

			// Add delivery and payment info info
			$this->addDeliveryAndPayment($this->cartDetails[$cartId]);

			// Add array with taxes grouped by tax rate and price type
			$this->addGroupedTaxes($this->cartDetails[$cartId]);

			// Append currency prices to all cart prices
			ConfigboxCurrencyHelper::appendCurrencyPrices($this->cartDetails[$cartId]);

		}

		return $this->cartDetails[$cartId];

	}

	/**
	 * Method used by getCartDetails to add all position details to the cart details
	 *
	 * This is just a way to make getCartDetails more readable, better don't use it for any other purpose
	 * @param ConfigboxCartData $cartDetails
	 */
	protected function addPositionDetails($cartDetails) {

		// Get the basic position data for all finished positions
		// What is finished? Flag that tells if the configuration was finished. Each configuration adds a position flagged as unfinished, get's finished when clicking the finish
		// Configuration button (see base controller ->finishConfiguration() for reference)
		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__configbox_cart_positions` WHERE `cart_id` = ".intval($cartDetails->id). " AND `finished` = '1'";
		$db->setQuery($query);
		$basicPositionData = $db->loadObjectList('id');

		// Init the cart positions array
		$cartDetails->positions = array();

		// Load all position details from basic data (why? because we save a bunch of queries for single positions in getPositionDetails this way)
		if ($basicPositionData) {

			// Get the position details position model
			$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');

			// Overrides may still set the position id with the remember option, so remember the old one and restore it afterwards
			$positionIdBefore = $positionModel->getId();

			foreach ($basicPositionData as $positionId=>$basicPosition) {

				// Set the position id
				$positionModel->setId($positionId, false);

				// Replace the basic data with detail data
				$cartDetails->positions[$positionId] = $positionModel->getPositionDetails($basicPosition);

			}

			// Restore the position id like it was before scanning through the positions
			if ($positionIdBefore != $positionModel->getId()) {
				$positionModel->setId($positionIdBefore);
			}

		}
	}

	/**
	 * Method used by getCartDetails to update the unreduced totals (see updateTotalsExceptUnreduced() for reference)
	 *
	 * This is just a way to make getCartDetails more readable, better don't use it for any other purpose
	 * @param ConfigboxCartData $cartDetails
	 */
	protected function updateUnreducedTotals($cartDetails) {

		// These 3 blocks are added to the cart details:

		// Unreduced cart total - one-time
		$cartDetails->baseTotalUnreducedNet 	= 0;
		$cartDetails->baseTotalUnreducedGross 	= 0;
		$cartDetails->baseTotalUnreducedTax		= 0;

		// Unreduced cart total - recurring
		$cartDetails->baseTotalUnreducedRecurringNet 	= 0;
		$cartDetails->baseTotalUnreducedRecurringGross 	= 0;
		$cartDetails->baseTotalUnreducedRecurringTax 	= 0;

		// Total cart weight
		$cartDetails->weight = 0;

		foreach ($cartDetails->positions as $position) {

			$cartDetails->baseTotalUnreducedNet 	+= $position->baseTotalUnreducedNet;
			$cartDetails->baseTotalUnreducedGross 	+= $position->baseTotalUnreducedGross;
			$cartDetails->baseTotalUnreducedTax 	+= $position->baseTotalUnreducedTax;

			$cartDetails->baseTotalUnreducedRecurringNet 	+= $position->baseTotalUnreducedRecurringNet;
			$cartDetails->baseTotalUnreducedRecurringGross 	+= $position->baseTotalUnreducedRecurringGross;
			$cartDetails->baseTotalUnreducedRecurringTax 	+= $position->baseTotalUnreducedRecurringTax;

			$cartDetails->weight += $position->weight;

		}

	}

	/**
	 * Method used by getCartDetails to set the discount amounts and the reduced prices for all positions
	 * Does NOT alter the totals for the cart
	 * This is just a way to make getCartDetails more readable, better don't use it for any other purpose
	 * @param ConfigboxCartData $cartDetails
	 */
	protected function applyDiscountToPositions($cartDetails) {

		foreach ($cartDetails->positions as $position) {

			// These four blocks are added to each position:

			// Amount of discount for one-time price (negative numbers)
			$position->baseTotalDiscountNet = 0;
			$position->baseTotalDiscountTax = 0;
			$position->baseTotalDiscountGross = 0;

			// Amount of discount for recurring price (negative numbers)
			$position->baseTotalDiscountRecurringNet = 0;
			$position->baseTotalDiscountRecurringTax = 0;
			$position->baseTotalDiscountRecurringGross = 0;

			// Reduced position price - one-time
			$position->baseTotalReducedNet = 0;
			$position->baseTotalReducedTax = 0;
			$position->baseTotalReducedGross = 0;

			// Reduced position price - recurring
			$position->baseTotalReducedRecurringNet = 0;
			$position->baseTotalReducedRecurringTax = 0;
			$position->baseTotalReducedRecurringGross = 0;


			// Get the regular discount (it is a negative value)
			if ($cartDetails->discount->level && $position->baseTotalUnreducedNet != 0) {

				// For amounts, distribute the total discount over all positions
				if ($cartDetails->discount->type == 'amount') {

					// See how much percent that position makes to the total
					$percentageOfTotal = $position->baseTotalUnreducedNet / $cartDetails->baseTotalUnreducedNet * 100;

					// Do not round this amount, it used for summing up the total cart discount
					$position->baseTotalDiscountNet = $cartDetails->discount->amount / 100 * $percentageOfTotal;

				}
				// If type is 'percentage'
				else {
					$position->baseTotalDiscountNet = round($position->baseTotalUnreducedNet / 100 * $cartDetails->discount->percentage, 4);
				}

			}

			// Get the recurring discount (it is a negative value)
			if ($cartDetails->discountRecurring->level && $position->baseTotalUnreducedRecurringNet != 0) {

				// For amounts, distribute the total discount over all positions
				if ($cartDetails->discountRecurring->type == 'amount') {

					// See how much percent that position makes to the total
					$percentageOfTotal = $position->baseTotalUnreducedRecurringNet / $cartDetails->baseTotalUnreducedRecurringNet * 100;

					// Do not round this amount, it used for summing up the total cart discount
					$position->baseTotalDiscountRecurringNet = $cartDetails->discountRecurring->amount / 100 * $percentageOfTotal;

				}
				// If type is percentage
				else {
					$position->baseTotalDiscountRecurringNet = round($position->baseTotalUnreducedRecurringNet / 100 * $cartDetails->discountRecurring->percentage, 4);
				}

			}

			// Add discount tax and gross - regular
			if ($position->baseTotalUnreducedNet != 0) {
				$position->baseTotalDiscountTax 	= $position->baseTotalUnreducedTax * $position->baseTotalDiscountNet / $position->baseTotalUnreducedNet;
				$position->baseTotalDiscountGross 	= $position->baseTotalUnreducedGross * $position->baseTotalDiscountNet / $position->baseTotalUnreducedNet;
			}

			// Add discount tax and gross - recurring
			if ($position->baseTotalUnreducedRecurringNet != 0) {
				$position->baseTotalDiscountRecurringTax 	= $position->baseTotalUnreducedRecurringTax 	* $position->baseTotalDiscountRecurringNet / $position->baseTotalUnreducedRecurringNet;
				$position->baseTotalDiscountRecurringGross 	= $position->baseTotalUnreducedRecurringGross 	* $position->baseTotalDiscountRecurringNet / $position->baseTotalUnreducedRecurringNet;
			}

			// Add reduced totals to position - regular
			$position->baseTotalReducedNet 		= $position->baseTotalUnreducedNet 		+ $position->baseTotalDiscountNet;
			$position->baseTotalReducedTax 		= $position->baseTotalUnreducedTax 		+ $position->baseTotalDiscountTax;
			$position->baseTotalReducedGross 	= $position->baseTotalUnreducedGross 	+ $position->baseTotalDiscountGross;

			// Add reduced totals to position - recurring
			$position->baseTotalReducedRecurringNet 	= $position->baseTotalUnreducedRecurringNet 	+ $position->baseTotalDiscountRecurringNet;
			$position->baseTotalReducedRecurringTax 	= $position->baseTotalUnreducedRecurringTax 	+ $position->baseTotalDiscountRecurringTax;
			$position->baseTotalReducedRecurringGross 	= $position->baseTotalUnreducedRecurringGross 	+ $position->baseTotalDiscountRecurringGross;

			// Set the currency prices
			ConfigboxCurrencyHelper::appendCurrencyPrices($position);

		}

	}

	/**
	 * Method used by getCartDetails to update all totals
	 * This is just a way to make getCartDetails more readable, better don't use it for any other purpose
	 * @param ConfigboxCartData $cartDetails
	 */
	protected function updateTotalsExceptUnreduced($cartDetails) {

		// These 4 blocks are added to the cart details

		// Cart total discount one-time (negative number)
		$cartDetails->baseTotalDiscountNet 		= 0;
		$cartDetails->baseTotalDiscountTax 		= 0;
		$cartDetails->baseTotalDiscountGross 	= 0;

		// Cart total discount recurring (negative number)
		$cartDetails->baseTotalDiscountRecurringNet 	= 0;
		$cartDetails->baseTotalDiscountRecurringTax 	= 0;
		$cartDetails->baseTotalDiscountRecurringGross 	= 0;

		// Cart total reduced price
		$cartDetails->baseTotalNet				= 0;
		$cartDetails->baseTotalTax				= 0;
		$cartDetails->baseTotalGross			= 0;

		// Cart total reduced price
		$cartDetails->baseTotalRecurringNet		= 0;
		$cartDetails->baseTotalRecurringTax		= 0;
		$cartDetails->baseTotalRecurringGross	= 0;


		foreach ($cartDetails->positions as $position) {

			// Cart Discount Total
			$cartDetails->baseTotalDiscountNet 		+= $position->baseTotalDiscountNet;
			$cartDetails->baseTotalDiscountTax 		+= $position->baseTotalDiscountTax;
			$cartDetails->baseTotalDiscountGross 	+= $position->baseTotalDiscountGross;

			// Cart Discount Recurring
			$cartDetails->baseTotalDiscountRecurringNet 	+= $position->baseTotalDiscountRecurringNet;
			$cartDetails->baseTotalDiscountRecurringTax 	+= $position->baseTotalDiscountRecurringTax;
			$cartDetails->baseTotalDiscountRecurringGross 	+= $position->baseTotalDiscountRecurringGross;


			// Cart Reduced Total
			$cartDetails->baseTotalNet		+= round($position->baseTotalReducedNet, 2);
			$cartDetails->baseTotalTax		+= round($position->baseTotalReducedTax, 2);
			$cartDetails->baseTotalGross	+= round($position->baseTotalReducedGross, 2);

			// Cart Reduced Recurring
			$cartDetails->baseTotalRecurringNet		+= round($position->baseTotalReducedRecurringNet, 2);
			$cartDetails->baseTotalRecurringTax		+= round($position->baseTotalReducedRecurringTax, 2);
			$cartDetails->baseTotalRecurringGross	+= round($position->baseTotalReducedRecurringGross, 2);

		}

		ConfigboxCurrencyHelper::appendCurrencyPrices($cartDetails);

	}

	/**
	 * Adds the objects payment and delivery to the cartDetails object.
	 *
	 * This is just a helper method for getCartDetails, better don't use it for any other purpose
	 *
	 * @param ConfigboxCartData $cartDetails
	 * @see ObserverOrders::onConfigboxGetDeliveryOptions, ObserverOrders::onConfigboxGetPaymentOptions
	 */
	protected function addDeliveryAndPayment($cartDetails) {

		if (!empty($cartDetails->deliveryAndPaymentAdded)) {
			return;
		}

		// These are the variables set in this method
		$cartDetails->payment = NULL;
		$cartDetails->delivery = NULL;
		$cartDetails->deliveryAndPaymentAdded = true;

		// No positions, no payment/delivery
		if (count($cartDetails->positions) == 0) {
			return;
		}

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT `delivery_id`, `payment_id` 
		FROM `#__cbcheckout_order_records` 
		WHERE `cart_id` = ".intval($cartDetails->id)."
		ORDER BY `id` DESC
		LIMIT 1
		";
		$db->setQuery($query);
		$orderRecordData = $db->loadAssoc();

		// Get possible delivery methods for the cart contents
		$deliveryOptions = KenedoObserver::triggerEvent('onConfigboxGetDeliveryOptions', array($cartDetails->id, $cartDetails->userInfo, $cartDetails->weight, array()), true);

		// If preferredDeliveryId is set, prepare the right one to be the chosen one
		if (!empty($cartDetails->preferredDeliveryId)) {
			foreach ($deliveryOptions as $deliveryOption) {
				if ($cartDetails->preferredDeliveryId == $deliveryOption->id) {
					$cartDetails->delivery = $deliveryOption;
					break;
				}
			}
		}
		elseif (!empty($orderRecordData['delivery_id'])) {
			foreach ($deliveryOptions as $deliveryOption) {
				if ($orderRecordData['delivery_id'] == $deliveryOption->id) {
					$cartDetails->delivery = $deliveryOption;
					break;
				}
			}
		}

		// If preferred delivery id was no option, then go for the first one (which is the cheapest since the array items
		// are sorted by price)
		if (empty($cartDetails->delivery) && !empty($deliveryOptions[0])) {
			$cartDetails->delivery = $deliveryOptions[0];
		}

		// Now for payment method

		// Get the amount to be paid for getting payment options
		if ($cartDetails->isVatFree) {
			$basePaymentAmount = $cartDetails->baseTotalNet + (($cartDetails->delivery) ? $cartDetails->delivery->basePriceNet : 0);
		}
		else {
			$basePaymentAmount = $cartDetails->baseTotalGross + (($cartDetails->delivery) ? $cartDetails->delivery->basePriceGross : 0);
		}

		// Get all possible payment methods
		$paymentOptions = KenedoObserver::triggerEvent('onConfigboxGetPaymentOptions', array($cartDetails->userInfo, $basePaymentAmount), true);

		// If preferredPaymentId is set, use it
		if (!empty($cartDetails->preferredPaymentId)) {
			foreach ($paymentOptions as $paymentOption) {
				if ($paymentOption->id == $cartDetails->preferredPaymentId) {
					$cartDetails->payment = $paymentOption;
					unset($paymentOption);
					break;
				}
			}
		}
		elseif (!empty($orderRecordData['payment_id']) ) {
			foreach ($paymentOptions as $paymentOption) {
				if ($paymentOption->id == $orderRecordData['payment_id']) {
					$cartDetails->payment = $paymentOption;
					unset($paymentOption);
					break;
				}
			}
		}
		// Otherwise get the first one (which is the cheapest)
		elseif (!empty($paymentOptions)) {
			$cartDetails->payment = $paymentOptions[0];
		}

	}

	/**
	 * Adds usesRecurring, labelRegular and labelRecurring to the cartDetails object. Price labels (e.g. 'setup price',
	 * 'monthly service fee') can be chosen for each product. This method looks if we can use product specific labels
	 * for the cart overview.
	 *
	 * This is just a helper method for getCartDetails, better don't use it for any other purpose
	 *
	 * @param ConfigboxCartData $cartDetails
	 */
	protected function addLabelsAndFlags($cartDetails) {

		// These 3 variables are set in this method:

		// Indicates if cart got recurring pricing
		$cartDetails->usesRecurring = false;

		// Init price label (may get overwritten later in this method)
		$cartDetails->labelRegular = KText::_('Price');
		$cartDetails->labelRecurring = KText::_('Recurring Price');

		$labels = array(
			'regular' => array(),
			'recurring' => array(),
		);

		foreach ($cartDetails->positions as $position) {

			// Set flag for if cart uses recurring prices
			if ($position->usesRecurring == true) {
				$cartDetails->usesRecurring = true;
			}

			// Collect all unique price labels, used later on to see how many different labels we deal with
			$labels['regular'][$position->productData->priceLabel] = $position->productData->priceLabel;
			$labels['recurring'][$position->productData->priceLabelRecurring] = $position->productData->priceLabelRecurring;

		}

		// If we got a single price label for regular, use it for the price overview label
		if (count($labels['regular']) == 1) {
			$cartDetails->labelRegular= array_pop($labels['regular']);
		}

		// If we got a single price label for recurring, use it for the price overview label
		if (count($labels['recurring']) == 1) {
			$cartDetails->labelRecurring = array_pop($labels['recurring']);
		}

	}

	/**
	 * Adds a taxes array to the cart details. Contains tax amounts grouped by tax rate (each got the tax amount for
	 * regular and recurring).
	 *
	 * This is just a helper method for getCartDetails, better don't use it for any other purpose.
	 *
	 * @param ConfigboxCartData $cartDetails
	 */
	protected function addGroupedTaxes($cartDetails) {

		$taxes = array();

		// Add taxes for positions
		foreach ($cartDetails->positions as $position) {

			// Tax rate for one-time pricing
			$taxRate = number_format($position->productData->taxRate, 2, '.', '');

			if ($taxRate) {
				// Prime if not there
				if (!isset($taxes[$taxRate]['regular'])) {
					$taxes[$taxRate]['regular'] = 0;
				}
				// Add the tax amount
				$taxes[$taxRate]['regular'] += $position->totalReducedTax;
			}

			// Tax rate for recurring pricing
			$taxRateRecurring = strval($position->productData->taxRateRecurring);

			if ($taxRateRecurring) {
				// Prime if not there
				if (!isset($taxes[$taxRateRecurring]['recurring'])) {
					$taxes[$taxRateRecurring]['recurring'] = 0;
				}
				// Add the tax amount
				$taxes[$taxRateRecurring]['recurring'] 	+= $position->totalReducedRecurringTax;

			}

		}

		// Add taxes for delivery
		if ($cartDetails->delivery && $cartDetails->delivery->taxRate != 0) {
			// Numeric keys gave us some trouble, string works fine
			$taxRate = number_format($cartDetails->delivery->taxRate, 2, '.', '');
			// Prime if not there yet
			if (!isset($taxes[$taxRate]['regular'])) {
				$taxes[$taxRate]['regular'] = 0;
			}
			// Add the delivery tax
			$taxes[$taxRate]['regular'] += $cartDetails->delivery->priceTax;
		}

		// Add taxes for payment
		if ($cartDetails->payment && $cartDetails->payment->taxRate != 0) {
			// Numeric keys gave us some trouble, string works fine
			$taxRate = number_format($cartDetails->payment->taxRate, 2, '.', '');

			// Prime if not there yet
			if (!isset($taxes[$taxRate]['regular'])) {
				$taxes[$taxRate]['regular'] = 0;
			}
			// Add the payment tax
			$taxes[$taxRate]['regular'] += $cartDetails->payment->priceTax;
		}

		// Make sure each sub array has a value for convenience
		foreach ($taxes as &$tax) {
			if (!isset($tax['regular'])) 	$tax['regular'] = 0;
			if (!isset($tax['recurring'])) 	$tax['recurring'] = 0;
		}

		// Put the data in the cart details
		$cartDetails->taxes = $taxes;

	}

	/**
	 * Legacy method, removed in 2.7 or 3.0
	 *
	 *  @deprecated  2.7  Use self::getCartDetails instead.
	 */
	function getGrandOrderDetails() {
		KLog::logLegacyCall('Do not call getGrandOrderDetails, call getCartDetails() instead.');
		$cartId = $this->getSessionCartId();
		return $this->getCartDetails($cartId);
	}

	/**
	 * Use self::createCart instead.
	 *
	 * @deprecated  2.7  Use self::createCart instead.
	 */
	function createGrandOrder() {
		KLog::logLegacyCall('Do not call createGrandOrder, call createCart() instead.');
		return $this->createCart();
	}

	/**
	 * Use self::cartBelongsToCurrentUser instead.
	 *
	 * @param int $cartId Cart id
	 * @return bool
	 * @deprecated  2.7  Use self::cartBelongsToCurrentUser instead.
	 */
	function userOwnsGrandOrder($cartId) {
		KLog::logLegacyCall('Do not call userOwnsGrandOrder, call cartBelongsToCurrentUser() instead.');
		return $this->cartBelongsToUser($cartId);
	}

	/**
	 * No use anymore, remove if you got a call on it
	 *
	 * @deprecated  2.7  No use anymore
	 */
	function setUserId() {
		KLog::logLegacyCall('Do not call setUserId anymore, has no use. If you need to set the cart id on cart of different users, use setIdForced instead.');
	}

	/**
	 * Use self::resetCart instead.
	 *
	 * @deprecated  2.7  Use self::resetCart instead.
	 */
	function resetGrandOrder() {
		KLog::logLegacyCall('Do not call resetGrandOrder, call resetCart() instead.');
		$this->resetCart();
	}

	/**
	 * Use self::resetCartData instead.
	 *
	 * @deprecated  2.7  Use self::resetCartDataCache instead.
	 */
	function resetGrandOrderData() {
		KLog::logLegacyCall('Do not call resetGrandOrderData, call resetCartDataCache() instead.');
		$this->forgetMemoizedData();
	}

	/**
	 * Use self::getGrandOrder instead.
	 *
	 * @return object holding basic cart data
	 * @deprecated  2.7  Use self::getCart instead.
	 */
	function getGrandOrder() {
		KLog::logLegacyCall('Do not call getGrandOrder, call getCartData() instead.');
		$cartId = $this->getSessionCartId();
		return $this->getCartData($cartId);
	}

}
