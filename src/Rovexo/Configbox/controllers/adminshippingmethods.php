<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminshippingmethods extends KenedoController {

	/**
	 * @return ConfigboxModelAdminshippingmethods
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminshippingmethods');
	}

	/**
	 * @return ConfigboxViewAdminshippingmethods
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminshippingmethods
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminshippingmethods');
	}

	/**
	 * @return ConfigboxViewAdminshippingmethod
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminshippingmethod');
	}

}
