<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminelements extends KenedoController {

	/**
	 * @return ConfigboxModelAdminelements
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminelements');
	}

	/**
	 * @return ConfigboxViewAdminelements
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminelements
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminelements');
	}

	/**
	 * @return ConfigboxViewAdminelement
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminelement');
	}
	
}
