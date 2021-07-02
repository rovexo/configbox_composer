<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerExamples extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewExamples
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewExamples');
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

	public function getAsJson() {
		echo json_encode(KenedoModel::getModel('ConfigboxModelAdminexamples')->getRecords());
	}
}
