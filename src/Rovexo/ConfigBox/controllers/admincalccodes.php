<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincalccodes extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincalccodes
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalccodes');
	}

	/**
	 * @return ConfigboxViewAdmincalccode
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewForm();
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdmincalccode
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincalccode');
	}

	function edit() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$productId = KRequest::getInt('productId');

		$view = $this->getDefaultViewForm();
		$view->setProductId($productId)->display();

	}

}
