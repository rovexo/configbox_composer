<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminproducts extends KenedoController {

	/**
	 * @return ConfigboxModelAdminproducts
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproducts');
	}

	/**
	 * @return ConfigboxViewAdminproducts
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminproducts
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminproducts');
	}

	/**
	 * @return ConfigboxViewAdminproduct
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminproduct');
	}

}
