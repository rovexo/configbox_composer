<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincustomers extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincustomers
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincustomers');
	}

	/**
	 * @return ConfigboxViewAdmincustomers
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmincustomers
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincustomers');
	}

	/**
	 * @return ConfigboxViewAdmincustomer
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincustomer');
	}

}
