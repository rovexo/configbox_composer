<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmin extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdmin
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdmin');
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
