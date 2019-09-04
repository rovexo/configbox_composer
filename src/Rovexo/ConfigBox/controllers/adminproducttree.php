<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminproducttree extends KenedoController {

	/**
	 * @return ConfigboxModelAdminproducttree
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproducttree');
	}

	/**
	 * @return ConfigboxViewAdminproducttree
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdminproducttree');
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

}
