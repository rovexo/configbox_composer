<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCheckoutpayment extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewCheckoutpayment
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewCheckoutpayment');
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
		KenedoView::getView('ConfigboxViewCheckoutpayment')->display();
	}
}