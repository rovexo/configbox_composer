<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminzones extends KenedoController {

	/**
	 * @return ConfigboxModelAdminzones
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminzones');
	}

	/**
	 * @return ConfigboxViewAdminzones
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminzones
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminzones');
	}

	/**
	 * @return ConfigboxViewAdminzone
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminzone');
	}

}
