<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminlistings extends KenedoController {

	/**
	 * @return ConfigboxModelAdminlistings
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminlistings');
	}

	/**
	 * @return ConfigboxViewAdminlistings
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminlistings
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminlistings');
	}

	/**
	 * @return ConfigboxViewAdminlisting
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminlisting');
	}

}
