<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminruleeditor extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdminruleeditor
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdminruleeditor');
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

}
