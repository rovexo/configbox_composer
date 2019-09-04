<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminxrefelementoptions extends KenedoController {

	/**
	 * @return ConfigboxModelAdminxrefelementoptions
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
	}

	/**
	 * @return ConfigboxViewAdminxrefelementoptions
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminxrefelementoptions
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminxrefelementoptions');
	}

	/**
	 * @return ConfigboxViewAdminxrefelementoption
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminxrefelementoption');
	}

}
