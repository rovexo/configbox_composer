<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminstates extends KenedoController {

	/**
	 * @return ConfigboxModelAdminstates
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminstates');
	}

	/**
	 * @return ConfigboxViewAdminstates
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminstates
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminstates');
	}

	/**
	 * @return ConfigboxViewAdminstate
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminstate');
	}

}
