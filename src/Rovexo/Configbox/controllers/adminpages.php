<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminpages extends KenedoController {

	/**
	 * @return ConfigboxModelAdminpages
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpages');
	}

	/**
	 * @return ConfigboxViewAdminpages
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminpages
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminpages');
	}

	/**
	 * @return ConfigboxViewAdminpage
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminpage');
	}
	
}
