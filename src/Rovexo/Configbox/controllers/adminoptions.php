<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminoptions extends KenedoController {

	/**
	 * @return ConfigboxModelAdminoptions
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminoptions');
	}

	/**
	 * @return ConfigboxViewAdminoptions
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminoptions
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminoptions');
	}

	/**
	 * @return ConfigboxViewAdminoption
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminoption');
	}

}
