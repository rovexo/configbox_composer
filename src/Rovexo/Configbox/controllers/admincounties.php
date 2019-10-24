<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincounties extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincounties
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincounties');
	}

	/**
	 * @return ConfigboxViewAdmincounties
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmincounties
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincounties');
	}

	/**
	 * @return ConfigboxViewAdmincounty
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincounty');
	}

}
