<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminconfig extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminconfig';

	/**
	 * @return ConfigboxModelAdminconfig
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminconfig');
	}

	function getPageTitle() {
		return KText::_('Settings');
	}

	protected function prepareTemplateVarsForm() {

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);

		$model = $this->getDefaultModel();
		$this->record = $model->getRecord(1);

		$this->recordUsage = array();
		$this->properties = $model->getProperties();
		$this->pageTitle = $this->getPageTitle();
		$this->pageTasks = $model->getDetailsTasks();

	}

}
