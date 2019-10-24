<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminreviews extends KenedoController {

	/**
	 * @return ConfigboxModelAdminreviews
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminreviews');
	}

	/**
	 * @return ConfigboxViewAdminreviews
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminreviews
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminreviews');
	}

	/**
	 * @return ConfigboxViewAdminreview
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminreview');
	}

}
