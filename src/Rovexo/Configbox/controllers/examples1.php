<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerExamples1 extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewExamples1
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewExamples1');
	}

	/**
	 * @return null
	 */
	protected function getDefaultViewList() {
		return null;
	}

	/**
	 * @return null
	 */
	protected function getDefaultViewForm() {
		return null;
	}

}
