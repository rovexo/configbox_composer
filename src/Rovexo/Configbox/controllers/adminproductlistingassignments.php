<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminproductlistingassignments extends KenedoController {

	/**
	 * @return ConfigboxModelAdminproductlistingassignments
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproductlistingassignments');
	}

	/**
	 * @return ConfigboxViewAdminproductlistingassignments
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminproductlistingassignments
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminproductlistingassignments');
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

}
