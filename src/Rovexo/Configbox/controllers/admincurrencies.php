<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincurrencies extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincurrencies
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincurrencies');
	}

	/**
	 * @return ConfigboxViewAdmincurrencies
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmincurrencies
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincurrencies');
	}

	/**
	 * @return ConfigboxViewAdmincurrency
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincurrency');
	}
	
	function makeBase() {
		$model = $this->getDefaultModel();
		$id = KRequest::getInt('ids');
		$model->makeBase($id);
		$this->purgeCache();
		parent::display();
	}
	
	function makeDefault() {
		$model = $this->getDefaultModel();
		$id = KRequest::getInt('ids');
		$model->makeDefault($id);
		$this->purgeCache();
		parent::display();
	}
	
}
