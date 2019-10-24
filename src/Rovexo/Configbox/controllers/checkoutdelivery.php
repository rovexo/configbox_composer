<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCheckoutdelivery extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewCheckoutdelivery
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewCheckoutdelivery');
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
		KenedoView::getView('ConfigboxViewCheckoutdelivery')->display();
	}
}