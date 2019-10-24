<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalculation extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincalculations';

	/**
	 * @return ConfigboxModelAdmincalculations
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalculations');
	}

	function getPageTitle() {
		return KText::_('Calculation');
	}

	function prepareTemplateVars() {
		
		$model = KenedoModel::getModel('ConfigboxModelAdmincalculations');
		$id = KRequest::getInt('id');
		if ($id) {
			$record = $model->getRecord($id);
		}
		else {
			$record = $model->initData();
		}

		$properties = $model->getProperties();

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=raw', false);

		$this->assignRef('pageTitle',	($record->name) ? $record->name : $this->getPageTitle());
		$this->assignRef('pageTasks',	$model->getDetailsTasks());
		$this->assignRef('record', 		$record);
		$this->assignRef('properties',	$properties);
		$this->assignRef('itemUsage', 	$model->getRecordUsage($id));

		$this->addViewCssClasses();

	}
	
}
