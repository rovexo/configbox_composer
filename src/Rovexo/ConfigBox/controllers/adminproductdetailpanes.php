<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminproductdetailpanes extends KenedoController {

	/**
	 * @return ConfigboxModelAdminproductdetailpanes
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproductdetailpanes');
	}

	/**
	 * @return ConfigboxViewAdminproductdetailpanes
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminproductdetailpanes
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminproductdetailpanes');
	}

	/**
	 * @return ConfigboxViewAdminproductdetailpane
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminproductdetailpane');
	}

}
