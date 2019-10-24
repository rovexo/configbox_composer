<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminshopdata extends KenedoController {

	/**
	 * @return ConfigboxModelAdminshopdata
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminshopdata');
	}

	/**
	 * @return ConfigboxViewAdminshopdata
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewForm();
	}

	/**
	 * @return NULL;
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdminshopdata
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminshopdata');
	}

	function display() {
		$this->edit();
	}
}
