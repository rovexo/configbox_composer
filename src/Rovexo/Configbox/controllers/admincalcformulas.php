<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincalcformulas extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincalcformulas
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalcformulas');
	}

	/**
	 * @return ConfigboxViewAdmincalcformula
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
	 * @return ConfigboxViewAdmincalcformula
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincalcformula');
	}

	function edit() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$productId = KRequest::getInt('productId');

		$view = $this->getDefaultViewForm();
		$view->setProductId($productId)->display();

	}

}
