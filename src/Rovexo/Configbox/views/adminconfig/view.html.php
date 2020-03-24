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

		$this->assign('recordUsage', array());
		$this->assignRef('properties', $model->getProperties());
		$this->assignRef('pageTitle', $this->getPageTitle());
		$this->assignRef('pageTasks', $model->getDetailsTasks());
	}

}
