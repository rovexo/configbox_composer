<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincalculations extends KenedoController {

	/**
	 * Returns the model to be used for standard tasks
	 * @see KenedoModel::getModel()
	 * @return ConfigboxModelAdminCalculations
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalculations');
	}

	/**
	 * Returns the KenedoView subclass for displaying arbitrary content
	 * @return ConfigboxViewAdminCalculations
	 * @see KenedoView::getView()
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * Returns the KenedoView subclass for displaying a list of records
	 * @return ConfigboxViewAdminCalculations
	 * @see KenedoView::getView()
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincalculations');
	}

	/**
	 * Returns the KenedoView subclass for editing the record
	 * @return ConfigboxViewAdmincalculation
	 * @see KenedoView::getView()
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincalculation');
	}

}
