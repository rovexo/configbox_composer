<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincities extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincities');
	}

	/**
	 * @return ConfigboxViewAdmincities
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdmincities');
	}

	/**
	 * @return ConfigboxViewAdmincities
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincities');
	}

	/**
	 * @return ConfigboxViewAdmincity
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincity');
	}

}
