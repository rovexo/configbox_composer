<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminsalutations extends KenedoController {

	/**
	 * @return ConfigboxModelAdminsalutations
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminsalutations');
	}

	/**
	 * @return ConfigboxViewAdminsalutations
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminsalutations
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminsalutations');
	}

	/**
	 * @return ConfigboxViewAdminsalutation
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminsalutation');
	}
	
}
