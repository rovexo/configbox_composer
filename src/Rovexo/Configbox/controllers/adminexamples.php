<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminexamples extends KenedoController {

	/**
	 * @return ConfigboxModelAdminexamples
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminexamples');
	}

	/**
	 * @return ConfigboxViewAdminexamples
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminexamples
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminexamples');
	}

	/**
	 * @return ConfigboxViewAdminexample
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminexample');
	}
	
}
