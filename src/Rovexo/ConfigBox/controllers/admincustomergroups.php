<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincustomergroups extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincustomergroups
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincustomergroups');
	}

	/**
	 * @return ConfigboxViewAdmincustomergroups
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmincustomergroups
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincustomergroups');
	}

	/**
	 * @return ConfigboxViewAdmincustomergroup
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincustomergroup');
	}

}
