<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerRecord extends KenedoController {

	/**
	 * @return ConfigboxModelOrderrecord
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelOrderrecord');
	}

	/**
	 * @return ConfigboxViewRecord
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewRecord');
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


		$view = $this->getDefaultView();
		$orderId = $this->getDefaultModel()->getId();
		$view->orderRecord = $this->getDefaultModel()->getOrderRecord($orderId);
		$view->showProductDetails = false;
		$view->hideSkus = true;

		echo $view->getHtml();

	}
}