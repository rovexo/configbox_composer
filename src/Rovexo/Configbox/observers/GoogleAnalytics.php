<?php
defined('CB_VALID_ENTRY') or die();

class ObserverGoogleAnalytics {

	static protected $endpointUrl = 'https://www.google-analytics.com/collect';
//	static protected $endpointUrl = 'https://www.google-analytics.com/debug/collect';

	/**
	 * Schedules a payment tracking
	 * @param int $orderId
	 * @param int $status
	 */
	function onConfigBoxSetStatus($orderId, $status) {

		$settings = CbSettings::getInstance();

		// Let it all be if GA tracking is disabled in the settings
		if ($settings->get('use_ga_ecommerce') == '0') {
			return;
		}

		// Track goal conversion in any case (will send events for quote request, ordered and paid
		$this->trackEvent($orderId, $status);

		// Tracking depends on payment method, status and settings
		if ($this->transactionShouldBeTrackedNow($orderId, $status) == false) {
			return;
		}

		if ($settings->get('use_ga_enhanced_ecommerce') == '1') {
			$this->trackTransactionEnhanced($orderId);
		}
		else {
			$this->trackTransactionBasic($orderId);
		}

	}

	/**
	 * Checks if transaction/conversion should be tracked now.
	 * Orders with offline payment (like bank or cash on delivery) get tracked depending on settings and status (see
	 * setting 'ga_behavior_offline_psps' for details.
	 *
	 * @param int $orderId
	 * @param int $newStatus
	 * @return bool
	 * @throws Exception
	 */
	protected function transactionShouldBeTrackedNow($orderId, $newStatus) {

		$orderRecord =  KenedoModel::getModel('ConfigboxModelOrderrecord')->getOrderRecord($orderId);

		// We get the status for 'paid' and 'ordered'
		$paidStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('paid'), true);
		$orderedStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('ordered'), true);

		// Prepare some variables to make the logic later more concise
		$paymentIsOffline = ($orderRecord->payment && $orderRecord->payment->connector_name && ConfigboxPspHelper::pspSupportsIpn($orderRecord->payment->connector_name) == false);
		$trackOfflineOn = (CbSettings::getInstance()->get('ga_behavior_offline_psps') == 'conversion_when_ordered') ? 'ordered':'paid';

		// For offline payment orders
		if ($paymentIsOffline == true) {

			// When order status is 'ordered'
			if ($newStatus == $orderedStatus) {
				$trackTransaction = ($trackOfflineOn == 'ordered');
			}
			// When order status is 'paid'
			elseif($newStatus == $paidStatus) {
				$trackTransaction = ($trackOfflineOn == 'paid');
			}
			// If status is anything else, we do not track for sure
			else {
				$trackTransaction = false;
			}

			return $trackTransaction;

		}
		// For IPN payments, track if the order is paid
		else {
			return ($newStatus == $paidStatus);
		}

	}

	/**
	 * Tracks a transaction for GA Enhanced Ecommerce tracking (using Measurement Protocol V1)
	 * @param int $orderId
	 * @throws Exception
	 */
	public function trackTransactionEnhanced($orderId) {

		// Just a check if the license actually has ec
		if (ConfigboxAddonHelper::hasAddon('ga_enhanced_ecommerce') == false) {
			KLog::log('Installation has enhanced ecommerce tracking enabled, but the license does not have the ga_enhanced_ecommerce addon. Aborting transaction tracking.', 'error');
			return;
		}

		// Collect various models, settings and data for later
		$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$settings = CbSettings::getInstance();
		$orderRecord = $model->getOrderRecord($orderId);
		$propertyId = $settings->get('ga_property_id');

		// Sanity check: See if there is a GA property ID set actually
		if (empty($propertyId)) {
			KLog::log('GA tracking is enabled, but there is no GA Property Tracking ID set, CB settings must be corrected. Aborting transaction tracking.', 'error');
			return;
		}

		// Sanity check: See if we got a client ID
		if (empty($orderRecord->ga_client_id)) {
			KLog::log('GA tracking is enabled, but there is no GA client ID. The customer may have opted out of tracking.', 'warning');
			return;
		}

		// Prepare delivery and payment amounts (since delivery and payment object in $orderRecord can be NULL)
		if ($orderRecord->delivery) {
			$deliveryGross = $orderRecord->delivery->priceGross;
			$deliveryTax = $orderRecord->delivery->priceTax;
			$paymentTax = $orderRecord->payment->priceTax;
		}
		else {
			$deliveryGross = 0;
			$deliveryTax = 0;
			$paymentTax = 0;
		}

		// Prepare the payload
		$payload = array(

			'v' => '1',
			'tid' => $propertyId,
			'cid' => $orderRecord->ga_client_id,

			't' => 'event',
			'ec' => 'configbox',
			'ea' => 'transaction',

			'ti' => $orderRecord->id,
			'tr' => $orderRecord->payableAmount,
			'tt' => $orderRecord->totalTax + $deliveryTax + $paymentTax,
			'ts' => $deliveryGross,
			'tcc' => '',
			'cu' => $orderRecord->currency->code,

			'pa' => 'purchase',

		);

		$i = 0;
		foreach ($orderRecord->positions as $position) {
			$i++;
			$prefix = 'pr'.$i;

			$payload[$prefix.'id'] = ($position->product_sku) ? $position->product_sku : $position->product_id;
			$payload[$prefix.'nm'] = $position->productTitle;
			$payload[$prefix.'pr'] = $position->totalReducedNet;
			$payload[$prefix.'qt'] = $position->quantity;

		}

		$this->sendRequest($payload);

	}

	/**
	 * Tracks a transaction for 'Basic' GA Ecommerce tracking (using Measurement Protocol V1)
	 * @param int $orderId
	 * @throws Exception
	 */
	public function trackTransactionBasic($orderId) {

		// Collect various models, settings and data for later
		$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$settings = CbSettings::getInstance();
		$orderRecord = $model->getOrderRecord($orderId);
		$propertyId = $settings->get('ga_property_id');

		if ($settings->get('use_ga_enhanced_ecommerce') == '1') {
			KLog::log('Basic GA ecommerce tracking was called, but settings say that Enhanced Ecommerce tracking is turned on. Aborting transaction tracking.', 'error');
			return;
		}

		// Sanity check: See if there is a GA property ID set actually
		if (empty($propertyId)) {
			KLog::log('GA tracking is enabled, but there is no GA Property Tracking ID set, CB settings must be corrected. Aborting transaction tracking.', 'error');
			return;
		}

		// Sanity check: See if we got a client ID
		if (empty($orderRecord->ga_client_id)) {
			KLog::log('GA tracking is enabled, but there is no GA client ID. The customer may have opted out of tracking.', 'warning');
			return;
		}

		// Prepare delivery and payment amounts (since delivery and payment object in $orderRecord can be NULL)
		if ($orderRecord->delivery) {
			$deliveryGross = $orderRecord->delivery->priceGross;
			$deliveryTax = $orderRecord->delivery->priceTax;
			$paymentTax = $orderRecord->payment->priceTax;
		}
		else {
			$deliveryGross = 0;
			$deliveryTax = 0;
			$paymentTax = 0;
		}

		// Do the overall transaction
		$payload = array(

			'v' => '1',
			'tid' => $propertyId,
			'cid' => $orderRecord->ga_client_id,

			't' => 'transaction',

			'ti' => $orderRecord->id,
			'tr' => $orderRecord->payableAmount,
			'ts' => $deliveryGross,
			'tt' => $orderRecord->totalTax + $deliveryTax + $paymentTax,
			'cu' => $orderRecord->currency->code,

		);

		$this->sendRequest($payload);

		// Do each position
		foreach ($orderRecord->positions as $position) {

			$payload = array(

				'v' => '1',
				'tid' => $propertyId,
				'cid' => $orderRecord->ga_client_id,

				't' => 'item',

				'ti' => $orderRecord->id,
				'in' => $position->productTitle,
				'ip' => $position->totalReducedNet,
				'iq' => $position->quantity,
				'ic' => ($position->product_sku) ? $position->product_sku : $position->product_id,
				'cu' => $orderRecord->currency->code,

			);

			$this->sendRequest($payload);

		}

	}

	/**
	 * Sends an event for goal conversion (using Measurement Protocol V1). Event value is the base total price for the
	 * order (since there is no currency setting for events).
	 * @param int $orderId
	 * @param int $status New order record status
	 * @throws Exception
	 */
	public function trackEvent($orderId, $status) {

		// Here we act only if the order status is 'paid'
		$orderedStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('ordered'), true);
		$paidStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('paid'), true);
		$rfqStatus = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodeForType', array('quote requested'), true);

		$actionsForStatuses = array(
			$orderedStatus => 'ordered',
			$paidStatus => 'order_payment',
			$rfqStatus => 'quote_requested',
		);

		if (!isset($actionsForStatuses[$status])) {
			return;
		}

		$action = $actionsForStatuses[$status];

		// Collect various models, settings and data for later
		$model = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$settings = CbSettings::getInstance();
		$orderRecord = $model->getOrderRecord($orderId);
		$propertyId = $settings->get('ga_property_id');

		// Sanity check: See if there is a GA property ID set actually
		if (empty($propertyId)) {
			KLog::log('GA tracking is enabled, but there is no GA Property Tracking ID set, CB settings must be corrected. Aborting transaction tracking.', 'error');
			return;
		}

		// Sanity check: See if we got a client ID
		if (empty($orderRecord->ga_client_id)) {
			KLog::log('GA tracking is enabled, but there is no GA client ID. The customer may have opted out of tracking.', 'warning');
			return;
		}

		// Prepare delivery and payment price for convenience
		$delivery = ($orderRecord->delivery) ? $orderRecord->delivery->basePriceGross : 0;
		$payment = ($orderRecord->payment) ? $orderRecord->payment->basePriceGross : 0;

		// Send the event
		$payload = array(

			'v' => '1',
			'tid' => $propertyId,
			'cid' => $orderRecord->ga_client_id,

			't' => 'event',

			'ec' => 'configbox_goals',
			'ea' => $action,
			'el' => '',
			'ev' => $orderRecord->baseTotalGross + $payment + $delivery,

		);

		$this->sendRequest($payload);

	}

	/**
	 * Sends the GA signal using CURL.
	 * @param string[] $payload
	 * @return bool true on success, false otherwise. Logs to payment_tracking and/or CB error log on either success or failure
	 */
	protected function sendRequest($payload) {

		KLog::log('Sending purchase data to GA (URL: '.self::$endpointUrl.'). Payload is '. "\n".var_export($payload, true), 'payment_tracking');

		$request = curl_init(self::$endpointUrl);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($request, CURLOPT_HEADER, false);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POST, true);
		curl_setopt($request, CURLOPT_TIMEOUT, 3);
		curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 3);

		$postArray = http_build_query($payload);

		if ($postArray) {
			curl_setopt($request, CURLOPT_POSTFIELDS, $postArray);
		}

		$response = curl_exec($request);
		$info = curl_getinfo($request);
		curl_close($request);

		KLog::log('Response from GA is '."\n".var_export($response, true), 'payment_tracking');

		if ($response === false || $info['http_code'] != 200) {
			$msg = 'Request to GA failed. CURL info was '."\n".var_export($info, true);
			KLog::log($msg, 'payment_tracking');
			KLog::log($msg, 'error');
			return false;
		}
		else {
			return true;
		}

	}

}