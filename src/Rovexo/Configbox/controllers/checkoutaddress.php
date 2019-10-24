<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCheckoutaddress extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewCheckoutaddress
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewCheckoutaddress');
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
		KenedoView::getView('ConfigboxViewCheckoutaddress')->display();
	}
}