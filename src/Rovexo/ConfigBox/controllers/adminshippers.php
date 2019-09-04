<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminShippers extends KenedoController {

	/**
	 * @return ConfigboxModelAdminshippers
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminshippers');
	}

	/**
	 * @return ConfigboxViewAdminshippers
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminshippers
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminshippers');
	}

	/**
	 * @return ConfigboxViewAdminshipper
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminshipper');
	}

}
