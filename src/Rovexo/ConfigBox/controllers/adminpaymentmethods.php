<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminpaymentmethods extends KenedoController {

	/**
	 * @return ConfigboxModelAdminpaymentmethods
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpaymentmethods');
	}

	/**
	 * @return ConfigboxViewAdminpaymentmethods
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminpaymentmethods
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminpaymentmethods');
	}

	/**
	 * @return ConfigboxViewAdminpaymentmethod
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminpaymentmethod');
	}

}
