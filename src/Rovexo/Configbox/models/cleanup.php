<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelCleanup extends KenedoModelLight {

	protected $tsNow;

	function cleanUp() {

		$this->tsNow = KenedoTimeHelper::getFormattedOnly('NOW', 'timestamp');

		if ($this->isTimeForCleanup()) {

			KLog::log('Current time is '. KenedoTimeHelper::getFormattedOnly($this->tsNow, 'datetime').'. All times UTC.', 'cleanup');

			try {

				$this->setLastCleanupTs($this->tsNow);
				$this->cleanupTemporaryUsers();
				$this->cleanupUnorderedOrderRecords();
				$this->deleteExpiredLabels();

			}
			catch (Exception $e) {

				$msg = 'Cleanup failed. Exception message was "'.$e->getMessage().'", on line '.$e->getLine();
				KLog::log($msg, 'cleanup');
				KLog::log($msg, 'error');

			}

		}
	}

	public function getLastCleanupTs() {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `value` FROM `#__configbox_system_vars` WHERE `key` = 'last_cleanup'";
		$db->setQuery($query);
		$lastCleanup = $db->loadResult();
		return $lastCleanup;
	}

	protected function setLastCleanupTs($timestamp) {

		KLog::log('Setting last clean up time to '.KenedoTimeHelper::getFormattedOnly($timestamp, 'datetime'), 'cleanup');

		$db = KenedoPlatform::getDb();
		$query = "REPLACE INTO `#__configbox_system_vars` SET `key` = 'last_cleanup', `value` = '".intval($timestamp)."'";
		$db->setQuery($query);
		$db->query();
	}

	private function isTimeForCleanup() {

		$lastCleanup = $this->getLastCleanupTs();
		$tsNextCleanup = $lastCleanup + (CbSettings::getInstance()->get('intervals', 0) * 3600);
		if ($this->tsNow > $tsNextCleanup) {
			$logMessage  = 'Time for cleanup. ';
			$logMessage .= 'Last cleanup was '.KenedoTimeHelper::getFormattedOnly($lastCleanup, 'datetime').'. ';
			$logMessage .= 'Interval is '.CbSettings::getInstance()->get('intervals', 0).' hours. All times GMT.';
			KLog::log($logMessage, 'cleanup');
			return true;
		}
		else {
			$logMessage  = 'Too early for cleanup. Next time will be '. KenedoTimeHelper::getFormattedOnly($tsNextCleanup, 'datetime').'. ';
			$logMessage .= 'Last cleanup was '.KenedoTimeHelper::getFormattedOnly($lastCleanup, 'datetime').'. ';
			$logMessage .= 'Interval is '.CbSettings::getInstance()->get('intervals', 0).' hours. All times GMT.';
			KLog::log($logMessage, 'debug');
			return false;
		}
	}

	private function cleanupUnorderedOrderRecords() {

		$minimumTimestamp = $this->tsNow - (CbSettings::getInstance()->get('unorderedtime', 0) * 3600);

		$logMessage  = 'Unorderedtime says '. CbSettings::getInstance()->get('unorderedtime', 0).' hours.';
		KLog::log($logMessage, 'cleanup');

		$logMessage  = 'Searching for order records with status 0 or 1 older than '. KenedoTimeHelper::getFormattedOnly($minimumTimestamp, 'datetime').'. That would be '.CbSettings::getInstance()->get('unorderedtime', 0).' hours old.';
		KLog::log($logMessage, 'cleanup');
		
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM #__cbcheckout_order_records WHERE `status` IN (0, 1) AND UNIX_TIMESTAMP(`created_on`) < ".intval($minimumTimestamp);
		$db->setQuery($query);
		$orderIdsToDelete = $db->loadResultList();

		if (count($orderIdsToDelete) == 0) {
			KLog::log('No order records to delete.', 'cleanup');
			return true;
		}

		KLog::log('Deleting '.intval(count($orderIdsToDelete)).' order records with status 0 or 1 (Unordered and in Checkout).', 'cleanup');

		$success = $this->deleteOrders($orderIdsToDelete);

		if (!$success) {
			KLog::log('Deleting order in checkout failed.', 'cleanup');
			return false;
		}

		return true;

	}

	/**
	 * Removes all temporary users (along with their carts, orders and connected data)
	 * @return bool true if no errors occurred, false otherwise
	 */
	private function cleanupTemporaryUsers() {

		$minimumTimestampUser = $this->tsNow - (CbSettings::getInstance()->get('usertime', 0) * 3600);

		$logMessage  = 'Usertime says '. CbSettings::getInstance()->get('usertime', 0). ' hours.';
		KLog::log($logMessage, 'cleanup');

		$logMessage  = 'Searching for temporary users older than '. KenedoTimeHelper::getFormattedOnly($minimumTimestampUser, 'datetime').'. That would be '.CbSettings::getInstance()->get('usertime', 0).' hours old.';
		KLog::log($logMessage, 'cleanup');
		
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_users` WHERE `is_temporary` = '1' AND UNIX_TIMESTAMP(`created`) < ".intval($minimumTimestampUser);
		$db->setQuery($query);
		$userIdsToDelete = $db->loadResultList();

		if (count($userIdsToDelete) == 0) {
			KLog::log('No users to delete.', 'cleanup');
			return true;
		}

		KLog::log('Going to delete '.intval(count($userIdsToDelete)).' users.', 'cleanup');

		KLog::log('Checking those users\' carts. Dealing with 100 user carts at a time.', 'cleanup');

		while (count($userIdsToDelete)) {

			$usersToDeletePortion = array_splice($userIdsToDelete, 0, 100);

			if (is_array($usersToDeletePortion) && count($usersToDeletePortion)) {

				$query = "SELECT `id` FROM `#__configbox_carts` WHERE `user_id` IN (".implode(',', $usersToDeletePortion).")";
				$db->setQuery($query);
				$cartIds = $db->loadResultList();

				KLog::log('Got '.intval(count($cartIds)).' carts to delete for this user delete portion.', 'cleanup');

				$success = $this->deleteCarts($cartIds);
				if (!$success) {
					KLog::log('Deleting carts failed.', 'cleanup');
					return false;
				}

				$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `user_id` IN (".implode(',', $usersToDeletePortion).")";
				$db->setQuery($query);
				$orderIdsToDelete = $db->loadResultList();
				KLog::log('Got '.intval(count($orderIdsToDelete)).' order records to delete for this user delete portion.', 'cleanup');
				$success = $this->deleteOrders($orderIdsToDelete);

				if (!$success) {
					KLog::log('Deleting orders failed.', 'cleanup');
					return false;
				}

				KLog::log('Got '.intval(count($usersToDeletePortion)).' users to delete for this user delete portion.', 'cleanup');

				$query = "DELETE FROM `#__configbox_users` WHERE `id` IN (".implode(',', $usersToDeletePortion).")";
				$db->setQuery($query);
				$success = $db->query();
				if (!$success) {
					KLog::log('Deleting users failed.', 'cleanup');
					return false;
				}

			}

		}

		return true;

	}

	private function deleteOrders($orderIds) {
		$model = KenedoModel::getModel('ConfigboxModelAdminorders');
		$success = $model->delete($orderIds);
		return $success;
	}

	private function deleteCarts($cartIds) {

		while (count($cartIds)) {
			$cartIdsToDelete = array_splice($cartIds, 0, 100);

			$db = KenedoPlatform::getDb();
			$query = "SELECT `id` FROM `#__configbox_cart_positions` WHERE `cart_id` IN (".implode(',', $cartIdsToDelete).")";
			$db->setQuery($query);
			$positionIds = $db->loadResultList();

			if (count($positionIds)) {
				$query = "DELETE FROM `#__configbox_cart_position_configurations` WHERE `cart_position_id` IN (".implode(', ',$positionIds).")";
				$db->setQuery($query);
				$success = $db->query();

				if (!$success) {
					KLog::log('Removing cart position configurations failed.', 'cleanup');
					return false;
				}
			}

			$query = "DELETE FROM `#__configbox_cart_positions` WHERE `cart_id` IN (".implode(',', $cartIdsToDelete).")";
			$db->setQuery($query);
			$success = $db->query();

			if (!$success) {
				KLog::log('Removing cart positions failed.', 'cleanup');
				return false;
			}

			$query = "DELETE FROM `#__configbox_carts` WHERE `id` IN (".implode(',', $cartIdsToDelete).")";
			$db->setQuery($query);
			$success = $db->query();

			if (!$success) {
				KLog::log('Removing carts failed.', 'cleanup');
				return false;
			}

		}

		return true;

	}

	private function deleteExpiredLabels() {

		$mimimumTimestamp = $this->tsNow - (CbSettings::getInstance()->get('labelexpiry', 0) * 86400);

		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_oldlabels` WHERE `created` < ".$mimimumTimestamp;
		$db->setQuery($query);
		$db->query();

	}

}
