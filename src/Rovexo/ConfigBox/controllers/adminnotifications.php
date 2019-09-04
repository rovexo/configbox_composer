<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminnotifications extends KenedoController {

	/**
	 * @return ConfigboxModelAdminnotifications
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminnotifications');
	}

	/**
	 * @return ConfigboxViewAdminnotifications
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminnotifications
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminnotifications');
	}

	/**
	 * @return ConfigboxViewAdminnotification
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminnotification');
	}

}
