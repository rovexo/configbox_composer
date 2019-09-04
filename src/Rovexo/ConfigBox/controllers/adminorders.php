<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminorders extends KenedoController {

	/**
	 * @return ConfigboxModelAdminorders
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminorders');
	}

	/**
	 * @return ConfigboxViewAdminorders
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminorders
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminorders');
	}

	/**
	 * @return ConfigboxViewAdminorder
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminorder');
	}

	public function isAuthorized($task = '') {
		
		if (ConfigboxPermissionHelper::canSeeOrders() !== true) {
			KenedoPlatform::p()->sendSystemMessage('You do not have access to this information.');
			$this->setRedirect( KenedoPlatform::p()->getPlatformLoginLink());
			return false;
		}
		else {
			return true;
		}
	}
	
	function getData() {
		
		// Get and sanitize record ids
		$cids = KRequest::getString('ids');
		$cids = explode(',',$cids);
		foreach ($cids as &$id) {
			$id = (int)$id;
		}
		
		// Bounce if no cids where found
		if (count($cids) == 0) {
			return false;
		}
		
		$storeId = ConfigboxStoreHelper::getStoreId();
		
		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__cbcheckout_order_records` WHERE `id` IN (".implode(',',$cids).")";
		
		if ($storeId != 1) {
			$query .= " AND store_id = ".(int)$storeId;
		}
		
		$query .= " ORDER BY `id`";
		
		$db->setQuery($query);
		
		$items = $db->loadObjectList('id');
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		
		$lines = array();
		
		$path = CONFIGBOX_DIR_CUSTOMIZATION .DS.'notification_elements'.DS.'helpers'.DS.'generate_configuration_code.php';
		if (is_file($path)) {
			require_once($path);
		}
			
		foreach ($items as $recordId => $record) {
			
			$orderRecord = $orderModel->getOrderRecord($recordId);
			
			$line = array();
			$line['id'] = $orderRecord->id;
			$line['First Name'] = $orderRecord->orderAddress->billingfirstname;
			$line['Last Name'] = $orderRecord->orderAddress->billinglastname;
			$line['Title'] = $orderRecord->orderAddress->billingsalutation;
			$line['Company'] = $orderRecord->orderAddress->billingcompany;
			$line['Address 1'] = $orderRecord->orderAddress->billingaddress1;
			$line['Address 2'] = $orderRecord->orderAddress->billingaddress2;
			$line['City'] = $orderRecord->orderAddress->billingcity;
			$line['State'] = $orderRecord->orderAddress->billingstatename;
			$line['Zip Code'] = $orderRecord->orderAddress->billingzipcode;
			$line['Country'] = $orderRecord->orderAddress->billingcountryname;
			$line['Phone'] = $orderRecord->orderAddress->billingphone;
			$line['Email'] = $orderRecord->orderAddress->billingemail;
			$line['Comment'] = $orderRecord->comment;
			$line['Status'] = $orderRecord->status;
			$line['Date'] = KenedoTimeHelper::getFormatted($orderRecord->created_on, 'datetime');
			
			foreach ($orderRecord->positions as $position) {
				$line['Product Title'] = $position->productTitle;
				$line['Product Code'] = (function_exists('generateConfigurationCode')) ? generateConfigurationCode($position) : '';
				$line['Product Custom 1'] = $position->product_custom_1;
				$line['Product Custom 2'] = $position->product_custom_2;
				$line['Product Custom 3'] = $position->product_custom_3;
				$line['Product Custom 4'] = $position->product_custom_4;
				
				$line['Configuration'] = '';
				foreach ($position->configuration as $element) {
					$line['Configuration'] .= $element->elementTitle .': '.strip_tags($element->output_value).',';
				}
				
				$lines[] = $line;
			}
			
			
			$orderModel->unsetOrderRecord($recordId);
			unset($orderRecord);
		}
		
		$written = '';
		
		$keys = array_keys($lines[0]);
		foreach ($keys as $key) {
			$written .= '"'.$key.'",';
		}
		$written .= "\n";
		
		foreach ($lines as $line) {
			foreach ($line as $key=>$value) {
				$value = str_replace(',',' ',$value);
				$written .= '"'.addslashes($value).'",';
			}
			$written .= "\n";
		}
		
		header('Content-type: text/csv');
		header('Content-disposition: attachment;filename=orders.csv');
		echo utf8_decode($written);
		die();
		
	}
	
	function cancel() {
		$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&controller=adminorders',false));
	}
	
	function remove() {
		
		// Get and sanitize record ids
		$cids = KRequest::getString('ids');
		$cids = explode(',',$cids);
		foreach ($cids as &$id) {
			$id = (int)$id;
		}

		// Bounce if no cids where found
		if (count($cids) == 0) {
			return;
		}

		$storeId = ConfigboxStoreHelper::getStoreId();

		$db = KenedoPlatform::getDb();
		$query = "SELECT `id`, `user_id`, `status` FROM `#__cbcheckout_order_records` WHERE `id` IN (".implode(',',$cids).")";

		if ($storeId != 1) {
			$query .= " AND store_id = ".(int)$storeId;
		}

		$db->setQuery($query);

		$removalItems = $db->loadObjectList('id');

		if (!$removalItems) {
			return;
		}
		
		$userId = ConfigboxUserHelper::getUserId();

		$platformUserId = KenedoPlatform::p()->getUserId();

		foreach ($removalItems as $item) {

			if ($item->user_id != $userId) {
				if (ConfigboxPermissionHelper::canEditOrders($platformUserId) == false) {
					KenedoPlatform::p()->sendSystemMessage( KText::_('You cannot remove orders of other customers.') );
					return;
				}
			}

			if (ConfigboxPermissionHelper::isPermittedAction('removeOrderRecord',$item) == false) {
				if (ConfigboxPermissionHelper::canEditOrders($platformUserId) == false) {
					KenedoPlatform::p()->sendSystemMessage( KText::_('You cannot remove order %s because if its status.',$item->id) );
					return;
				}
			}

		}

		$model = KenedoModel::getModel('ConfigboxModelAdminorders');
		$succ = $model->delete( array_keys($removalItems) );

		if (!$succ) {
			$errors = $model->getErrors();
			foreach ($errors as $error) {
				KenedoPlatform::p()->sendSystemMessage($error);
			}
		}
		
		$this->display();

		return;
		
	}
	
	function release_invoice() {
		
		$orderId = KRequest::getInt('order_id');
		$userId = KenedoPlatform::p()->getUserId();

		if (CbSettings::getInstance()->get('enable_invoicing') && CbSettings::getInstance()->get('invoice_generation') == 1) {
			
			$model = KenedoModel::getModel('ConfigboxModelInvoice');

			$succ = $model->generateInvoice($orderId, $userId);
			if (!$succ) {
				foreach ($model->getErrors() as $error) {
					KenedoPlatform::p()->sendSystemMessage($error,'error');
				}
				$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.KRequest::getInt('order_id'), false) );
				$this->redirect();
			}
			else {
				$db = KenedoPlatform::getDb();
				$query = "UPDATE `#__cbcheckout_order_records` SET `invoice_released` = '1' WHERE `id` = ".(int)$orderId;
				$db->setQuery($query);
				$db->query();
			}
			KenedoPlatform::p()->sendSystemMessage(KText::_('Invoice was generated and released.'));
			
			$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.KRequest::getInt('order_id'), false) );
			$this->redirect();
		}
	}
	
	function insert_invoice() {
		
		$orderId = KRequest::getInt('order_id');
		$invoiceNumberPrefix = KRequest::getString('invoice_number_prefix','');
		$invoiceNumberSerial = KRequest::getString('invoice_number_serial','');
		$userId = KenedoPlatform::p()->getUserId();

		if (CbSettings::getInstance()->get('enable_invoicing') && CbSettings::getInstance()->get('invoice_generation') == 2) {
			
			$file = KRequest::getFile('invoice_file');
			$customInvoicePath = KenedoPlatform::p()->getTmpPath().DS.$file['name'];

			if (!empty($file['tmp_name'])) {

				$succ = move_uploaded_file($file['tmp_name'], $customInvoicePath);
				if (!$succ) {
					KenedoPlatform::p()->sendSystemMessage(KText::_('Could not upload invoice file. Check folder permissions for tmp folder.'),'error');
					$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.KRequest::getInt('order_id'), false) );
					$this->redirect();
				}

			}
			else {
				KenedoPlatform::p()->sendSystemMessage(KText::_('No invoice file uploaded.'),'error');
				$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.KRequest::getInt('order_id'), false) );
				$this->redirect();
			}
			
			$model = KenedoModel::getModel('ConfigboxModelInvoice');
			$succ = $model->insertInvoice($orderId, $userId, $customInvoicePath, $invoiceNumberPrefix, $invoiceNumberSerial);

			if (!$succ) {
				foreach ($model->getErrors() as $error) {
					KenedoPlatform::p()->sendSystemMessage($error,'error');
				}
				$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.KRequest::getInt('order_id'), false) );
				$this->redirect();
			}
			
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__cbcheckout_order_records` SET `invoice_released` = '1' WHERE `id` = ".(int)$orderId;
			$db->setQuery($query);
			$db->query();
			
			KenedoPlatform::p()->sendSystemMessage(KText::_('Invoice was inserted and released.'));
		}
		
		$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.KRequest::getInt('order_id'), false) );
		$this->redirect();
		
	}
	
	function update_status() {
		$orderId = KRequest::getInt('order_id');
		KRequest::setVar('view','order');
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		$succ = $orderModel->setStatus(KRequest::getInt('status'), $orderId);
		
		if (!$succ) {
			foreach ($orderModel->getErrors() as $error) {
				KenedoPlatform::p()->sendSystemMessage($error,'error');
			}
		}
		else {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Order Status updated.'));
		}
		
		$this->setRedirect( KLink::getRoute('index.php?option=com_configbox&controller=adminorders&task=edit&cid[]='.$orderId, false) );
		$this->redirect();

	}
	
}
