<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerPaymentresult extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewPaymentresult
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewPaymentresult');
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

	function save_payment_info() {
		$connectorName = KRequest::getString('connector_name');
		
		if (!$connectorName) {
			$connectorName = KRequest::getString('system');
		}
		
		if ($connectorName) {
			$folder = ConfigboxPspHelper::getPspConnectorFolder($connectorName);
			$file = $folder.'/save_payment_info.php';
			
			if (file_exists($file)) {
				include($file);
			}
		}
		
	}
	
	function display() {
		
		$orderModel = KenedoModel::getModel('ConfigboxModelOrderrecord');
		
		if (!$orderModel->getId()) {
			echo '<p>'.KText::_('Nothing to checkout').'</p>';
			return;
		}
		
		parent::display();
	}
	
}
