<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerQuotation extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	function display() {
		
		// Get user and group info
		$userId = ConfigboxUserHelper::getUserId();

		// Check if the user can actually request quotations
		if (ConfigboxPermissionHelper::canRequestQuotation($userId) == false) {
			KLog::log('Customer ID "'.$userId.'" tried to request a quotation, although his group permissions do not allow it.','permissions',KText::_('You cannot request quotations.'));
			return false;
		}
		
		// When the quote is requested via cart
		if (KRequest::getInt('from_cart')) {

			// Get the models
			$cartModel = KenedoModel::getModel('ConfigboxModelCart');
			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');

			// Get the cart ID from request
			$cartId = KRequest::getInt('cart_id');
			
			// Check if the cart actually belongs to the user
			$cartBelongs = $cartModel->cartBelongsToUser($cartId);
			if (!$cartBelongs) {
				KLog::log('Customer ID "'.$userId.'" tried to request another customer\'s (customer with ID "'.ConfigboxUserHelper::getUserId().'") quotation.','permissions',KText::_('Quotation not found.'));
				return false;
			}

			// Get the Cart details
			$cartDetails = $cartModel->getCartDetails($cartId);

			// Create the order record
			$checkoutRecordId = $orderModel->createOrderRecord($cartDetails, 11);
			
		}
		// When the quote is requested via configurator page
		elseif (KRequest::getInt('from_configurator')) {
			
			// Get params
			$cartId = KRequest::getInt('cart_id');
			$cartPositionId = KRequest::getInt('cart_position_id');
			
			// Get models
			$cartModel = KenedoModel::getModel('ConfigboxModelCart');
			$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
			$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
			
			// Check if the cart actually belongs to the user
			$cartBelongs = $cartModel->cartBelongsToUser($cartId);
			if (!$cartBelongs) {
				$platformUserId = KenedoPlatform::p()->getUserId();
				KLog::log('Platform user ID "'.$platformUserId.'" tried to request another customer\'s (customer with ID "'.ConfigboxUserHelper::getUserId().'") quotation.','permissions',KText::_('Quotation not found.'));
				return false;
			}
			
			// Set the position to finished (so that it gets included in order record creation)
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__configbox_cart_positions` SET `finished` = '1' WHERE `id` = ".intval($cartPositionId)." AND `cart_id` = ".intval($cartId);
			$db->setQuery($query);
			$db->query();
			
			// Reset the position and cart cache
			$cartModel->forgetMemoizedData();
			$positionModel->resetPositionDataCache();
			
			// Get the cart details to create the order record
			$cartDetails = $cartModel->getCartDetails($cartId);
			
			// Create the order record
			$checkoutRecordId = $orderModel->createOrderRecord($cartDetails, 11);
			
			// Change the finished flag back, to keep things normal on the configurator page
			$query = "UPDATE `#__configbox_cart_positions` SET `finished` = '0' WHERE `id` = ".intval($cartPositionId)." AND `cart_id` = ".intval($cartId);
			$db->setQuery($query);
			$db->query();
			
			// Reset the position and cart cache
			$cartModel->forgetMemoizedData();
			$positionModel->resetPositionDataCache();
			
			// Set IDs of both cart and position model (in case they got changed somewhere during order creation)
			$positionModel->setId($cartPositionId);
			
		}
		// When it is requested from within the order management
		else {
			$checkoutRecordId = KRequest::getInt('order_id');
		}
		
		// Set the quote comment here, because it is such a clean way to do it
		if (KSession::get('quote_comment')) {
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__cbcheckout_order_records` SET `comment` = '".$db->getEscaped(KSession::get('quote_comment'))."' WHERE `id` = ".(int)$checkoutRecordId;
			$db->setQuery($query);
			$db->query();
			KSession::delete('quote_comment');
		}
		
		// Alright, finally do what the controller is actually supposed to do (more or less at least)
		
		// Try to get the existing quote
		$quotationModel = KenedoModel::getModel('ConfigboxModelQuotation');
		$quotation = $quotationModel->getQuotation($checkoutRecordId);
		
		// Create the quotation if not already there
		if (!$quotation) {
			$quotation = $quotationModel->createQuotation($checkoutRecordId);
		}
		
		// If that did not work out, flee in panic
		if (!$quotation) {
			KLog::log('Could not create quotation for order ID "'.$checkoutRecordId.'".', 'error', KText::_('Could not create quotation.'));
			return false;
		}
		
		// Prepare the quotation path
		$quotationPath = $quotationModel->getQuotationsDir().'/'.$quotation->file;
		
		// If somehow the file is gone, flee in panic
		if(is_file($quotationPath) == false) {
			KLog::log('Quotation file for order id "'.$checkoutRecordId.'" was not found, it should be stored in "'.$quotationPath.'".', 'error', KText::_('Could not create quotation.'));
			return false;
		}

		$groupId = ConfigboxUserHelper::getGroupId();
		$group = ConfigboxUserHelper::getGroupData($groupId);

		// Send the quote though download
		if ($group->quotation_download == true) {
			
			// Set cache control
			header("Cache-Control: private",true);
			
			// Set content type
			header("Content-Type: application/pdf");
			
			// Set disposition and filename
			$fileNameDownload = KText::_('quotationfile').'.pdf';
			header('Content-Disposition: inline; filename="'.$fileNameDownload.'"', true);
			
			// Spit out the file contents
			readfile($quotationPath);
			
			// Say good bye
			header('Connection: close');
			header('Content-Length: '.filesize($quotationPath));
			
			// Trigger the status change event to send the email as well
			KenedoObserver::triggerEvent('onConfigBoxSetStatus', array($checkoutRecordId, 11));
			
			// Die so that the platform won't screw with our output
			die();
			
		}
		// Send the quote by email
		elseif( $group->quotation_email) {
			
			// Set the order record status, triggers an email with the quotation attached
			KenedoObserver::triggerEvent('onConfigBoxSetStatus', array($checkoutRecordId, 11));
			
			// Send feedback
			KenedoPlatform::p()->sendSystemMessage( KText::_('Thank you for your quotation request. We sent you an email with your quotation attached.'));
			
			// Redirect to the right place
			if (KRequest::getInt('from_cart')) {
				$link = KLink::getRoute('index.php?option=com_configbox&view=cart',false);
			}
			else {
				$link = KLink::getRoute('index.php?option=com_configbox&view=cart&task=editCartPosition&cart_position_id='.KRequest::getInt('cart_position_id'),false);
			}
			$this->setRedirect( $link );
			$this->redirect();
			
		}
		// Don't do anything, just set the status (shop managers get a notification with the pdf and deal with the RFQ manually)
		else {
			
			// Set the order record status, triggers an email with the quotation attached (goes to the shop manager only, if configured as instructed)
			KenedoObserver::triggerEvent('onConfigBoxSetStatus', array($checkoutRecordId, 14));
			
			// Send feedback
			KenedoPlatform::p()->sendSystemMessage( KText::_('Thank you for your quotation request. We will process your request and get back to you.'));
			
			// Redirect to the right place
			if (KRequest::getInt('from_cart')) {
				$link = KLink::getRoute('index.php?option=com_configbox&view=cart',false);
			}
			else {
				$link = KLink::getRoute('index.php?option=com_configbox&view=cart&task=editCartPosition&cart_position_id='.KRequest::getInt('cart_position_id'),false);
			}
			$this->setRedirect( $link );
			$this->redirect();
			
		}

		return true;

	}
	
}
