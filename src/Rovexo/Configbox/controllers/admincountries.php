<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincountries extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincountries
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincountries');
	}

	/**
	 * @return ConfigboxViewAdmincountries
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmincountries
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincountries');
	}

	/**
	 * @return ConfigboxViewAdmincountry
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincountry');
	}
	
}
