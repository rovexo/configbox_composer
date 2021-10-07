<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminconfig extends KenedoController {

	/**
	 * @return ConfigboxModelAdminconfig
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminconfig');
	}

	/**
	 * @return ConfigboxViewAdminconfig
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
	 * @return ConfigboxViewAdminconfig
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminconfig');
	}
	
	function display() {
		$this->edit();
	}

	function renewWordpressPages() {
		try {
			ConfigboxWordpressHelper::renewPages();
		}
		catch (Exception $e) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors([$e->getMessage()])->toJson();
			return;
		}

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}
	
}
