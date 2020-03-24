<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminorders extends KenedoModel {

	/**
	 * Memo-caches getOrders results
	 * @var object[]
	 */
	var $memoOrders = array();

	function getListingTasks() {
		$tasks = array(
				array('title'=>KText::_('Remove'), 		'task'=>'remove'),
// 				array('title'=>KText::_('Download'), 	'task'=>'getData', 'non-ajax'=>true),
		);
		return $tasks;
	}
	
	function getDetailsTasks() {
		$tasks = array(
				array('title'=>KText::_('Save'), 	'task'=>'store', 'primary' => true),
				array('title'=>KText::_('Apply'), 	'task'=>'apply'),
				array('title'=>KText::_('Cancel'), 	'task'=>'cancel'),
		);
		return $tasks;
	}

	/**
	 * @param int[] $orderIds
	 * @return bool
	 * @throws Exception
	 */
	function delete($orderIds) {

		if (is_int($orderIds)) {
			$orderIds = array($orderIds);
		}

		if ($orderIds && is_array($orderIds)) {
			foreach ($orderIds as &$id) {
				$id = intval($id);
			}
		}
		else {
			return true;
		}
		unset($id);
		
		$db = KenedoPlatform::getDb();
		
		clearstatcache();
		
		foreach ($orderIds as $orderId) {

			try {

				$db->startTransaction();

				$query = "DELETE FROM `#__cbcheckout_order_tax_class_rates` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();


				$query = "DELETE FROM `#__cbcheckout_order_cities` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_counties` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_states` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_countries` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_payment_trackings` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_invoices` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "SELECT `id`,`product_image` FROM `#__cbcheckout_order_positions` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$positions = $db->loadAssocList('id');

				if ($positions) {

					// Remove configuration entries
					$positionIds = array_keys($positions);
					$query = "DELETE FROM `#__cbcheckout_order_configurations` WHERE `position_id` IN (" . implode(',', $positionIds) . ")";
					$db->setQuery($query);
					$db->query();

					// Delete position images, if there are any
					foreach ($positions as $position) {
						if ($position['product_image']) {
							$path = CONFIGBOX_DIR_POSITION_IMAGES . DS . $position['product_image'];
							if (is_file($path) && is_writable($path) && is_writable(CONFIGBOX_DIR_POSITION_IMAGES)) {
								$succ = unlink($path);
								// If deleting failed, log it, but continue
								if (!$succ) {
									KLog::log('Could not remove position image in "' . $path . '" for order id "' . $orderId . '"', 'error');
								}
							}
						}
					}

				}

				$query = "DELETE FROM `#__cbcheckout_order_positions` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_shipping_methods` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_payment_methods` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_currencies` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_users` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_user_groups` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_salutations` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__cbcheckout_order_strings` WHERE `order_id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();


				$quoteModel = KenedoModel::getModel('ConfigboxModelQuotation');
				$quotation = $quoteModel->getQuotation($orderId);

				if ($quotation) {

					if ($quoteModel->isQuotationFileRemovable($orderId) == false) {
						$msg = KText::sprintf('The quotation file for order id %s is write-protected and cannot be removed. Order was not deleted. Please check file permissions of the quotation folder and its files.', $orderId);
						throw new Exception($msg);
					}

					$success = $quoteModel->removeQuotation($orderId);
					if ($success == false) {
						throw new Exception('Quotation for order id "' . $orderId . '" could not be removed.');
					}

				}

				$query = "DELETE FROM `#__cbcheckout_order_records` WHERE `id` = " . intval($orderId);
				$db->setQuery($query);
				$db->query();

			}
			catch (Exception $e) {
				$db->rollbackTransaction();
				$logMsg = 'SQL error during order deletion. Exception message was '.$e->getMessage().'. File: '.$e->getFile().':'.$e->getLine();
				KLog::log($logMsg, 'error');
				$this->setError($e->getMessage());
				return false;
			}

			$db->commitTransaction();

		}
		
		return true;
		
	}
	
	function getOrders($filters = array(), $paginationInfo = array(), $orderingInfo = array()) {
		
		$selectQuery = $this->_buildQuery();

		$queryWhere = $this->_buildQueryWhere($filters);

		$orderBys = array();
		$allowedCustomColRefs = array('o.user_id','o.created_on','o.status');

		foreach ($orderingInfo as $orderingInfoItem) {

			$columnReference = $orderingInfoItem['propertyName'];

			if ($columnReference == 'o.user_id') {
				$orderBys[] 	= 'o.user_id '.$orderingInfoItem['direction'];
			}
			elseif ($columnReference == 'o.created_on') {
				$orderBys[] 	= 'o.created_on '.$orderingInfoItem['direction'];
			}
			elseif ($columnReference == 'a.lastname') {
				$orderBys[] 	= 'a.lastname '.$orderingInfoItem['direction'];
			}
			elseif ($columnReference == 'a.billinglastname') {
				$orderBys[] 	= 'a.billinglastname '.$orderingInfoItem['direction'];
			}
			elseif ($columnReference == 'o.status') {
				$orderBys[] 	= 'o.status '.$orderingInfoItem['direction'];
			}
			elseif ($columnReference == 'o.id') {
				$orderBys[] 	= 'o.id '.$orderingInfoItem['direction'];
			}
			elseif (in_array($columnReference, $allowedCustomColRefs)) {
				$orderBys[] 	= $columnReference.' '.$orderingInfoItem['direction'];
			}
			else {
				$orderBys[] 	= 'o.created_on '.$orderingInfoItem['direction'];
			}

		}

		if (count($orderBys)) {
			$queryOrder = ' ORDER BY '.implode(', ', $orderBys);
		}
		else {
			$queryOrder = '';
		}

		$db = KenedoPlatform::getDb();
		$query = $selectQuery.$queryWhere.$queryOrder;
		$db->setQuery($query, $paginationInfo['start'], $paginationInfo['limit']);
		$query = $db->getQuery();
		$orders = $db->loadObjectList();

		$statusCodes = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodes', array(), true);

		foreach ($orders as $order) {
			if (isset($statusCodes[$order->status])) {
				$order->statusCodeString = $statusCodes[$order->status];
			}
			else {
				$order->statusCodeString = $statusCodes[0];
			}
		}
		return $orders;
	}
	
	function _buildQuery() {
		
		$query = "	SELECT 	o.*,
							a.billingfirstname,
							a.billinglastname,
							a.billingcompanyname
					FROM `#__cbcheckout_order_records` AS o
					LEFT JOIN `#__cbcheckout_order_users` AS a ON a.order_id = o.id";

		return $query;
	}

	function _buildQueryWhere($filters) {
	    
		$db = KenedoPlatform::getDb();
	    
	    // prepare the WHERE clause 
		$where = array();

		if ($filters['filter_status'] != '' && $filters['filter_status'] != -1) {
			$where[] = 'o.status = '.intval($filters['filter_status']);
		}

		if ($filters['filter_nameorder']) {

			$filterValue = mb_strtolower($filters['filter_nameorder']);
			$filterWords = explode(' ', $filterValue);
			$orWheres = [];
			foreach ($filterWords as $filterWord) {
				$orWheres[] = "LOWER(CONCAT_WS(' ', a.firstname, a.lastname, a.companyname, a.email)) LIKE '%".$db->getEscaped($filterWord)."%'";
				$orWheres[] = "LOWER(CONCAT_WS(' ', a.billingfirstname, a.billinglastname, a.billingcompanyname, a.billingemail)) LIKE '%".$db->getEscaped($filterWord)."%'";
			}
			if (count($orWheres)) {
				$where[] = "(".implode(" OR \n", $orWheres).")";
			}

		}

		if ($filters['filter_startdate']) {
			$filterStartDate = KenedoTimeHelper::getNormalizedTime($filters['filter_startdate'],'datetime');
			$where[] = "o.created_on > '".$db->getEscaped($filterStartDate)."'";
		}

		if ($filters['filter_enddate']) {
			$filterEndDate = $filters['filter_enddate'];
			if (strlen($filterEndDate) <= 10) {
				$filterEndDate .= ' 23:59';
			}
			$filterEndDate = KenedoTimeHelper::getNormalizedTime($filterEndDate,'datetime');

			$where[] = "o.created_on <= '".$db->getEscaped($filterEndDate)."'";
		}

		if ($filters['filter_user_id']) {
			$where[] = "o.user_id = ".intval($filters['filter_user_id']);
		}

		$storeId = ConfigboxStoreHelper::getStoreId();
		if ($storeId != 1) {
			$where[] = "o.store_id = ".intval($storeId);
		}

		// return the WHERE clause
		return (count($where)) ? ' WHERE '.implode(" AND \n", $where) : '';

	}

	function getTotalRecords($filters) {
		$query = $this->_buildQuery();
		$query.= $this->_buildQueryWhere($filters);

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$db->query();

		$total = $db->getReturnedRows();
		return $total;
	}

	function getStatusDropdown($filters) {

		$statusCodes = KenedoObserver::triggerEvent('onConfigBoxGetStatusCodes', array(), true);

		$options = array();
		$options[-1] = KText::_('Select Status');
			
		foreach ($statusCodes as $key=>$code) {
			$options[$key] = $code;
		}
		
		$selectedStatusId = $filters['filter_status'];
		$selectBox = KenedoHtml::getSelectField('filter_status', $options, $selectedStatusId, -1, false, 'listing-filter');
		
		return $selectBox;
	}
	
	function getUserOrders() {
		
		$userId = ConfigboxUserHelper::getUserId();
		if (!$userId) {
			return array();
		}
			
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__cbcheckout_order_records` WHERE `user_id` = ".(int)$userId." ORDER BY `created_on` DESC";
		$db->setQuery($query);
		$orders = $db->loadAssocList();
			
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$cbOrders = array();
			
		foreach ($orders as $order) {
			$cbOrders[] = $orderModel->getOrderRecord($order['id']);
		}
			
		return $cbOrders;
		
	}

}
