<?php
defined('CB_VALID_ENTRY') or die();

class ObserverPaymentTracking {

	/**
	 * Schedules a payment tracking
	 * @param int $orderId
	 * @param int $status
	 */
	function onConfigBoxSetStatus($orderId, $status) {

	}

}