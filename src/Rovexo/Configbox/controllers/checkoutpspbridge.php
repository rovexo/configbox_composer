<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCheckoutpspbridge extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewCheckoutpspbridge
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewCheckoutpspbridge');
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

	function display() {
		KenedoView::getView('ConfigboxViewCheckoutpspbridge')->display();
	}
}