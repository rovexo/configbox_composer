<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmintaxClasses extends KenedoController {

	/**
	 * @return ConfigboxModelAdmintaxclasses
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmintaxclasses');
	}

	/**
	 * @return ConfigboxViewAdmintaxclasses
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmintaxclasses
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmintaxclasses');
	}

	/**
	 * @return ConfigboxViewAdmintaxclass
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmintaxclass');
	}

}
