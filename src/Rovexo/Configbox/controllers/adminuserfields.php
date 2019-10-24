<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminuserfields extends KenedoController {

	/**
	 * @return ConfigboxModelAdminuserfields
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminuserfields');
	}

	/**
	 * @return ConfigboxViewAdminuserfields
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewForm();
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdminuserfields
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminuserfields');
	}

}
