<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminshopdata extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminshopdata';

	/**
	 * @return ConfigboxModelAdminshopdata
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminshopdata');
	}

	function getPageTitle() {
		return KText::_('Store Information');
	}

	/**
	 * This adds the customer form module and runs initCustomerForm. For having the country dropdown change states
	 * @return string[]
	 */
	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/customerform::initCustomerForm';
		return $calls;
	}

	protected function prepareTemplateVarsForm() {

		$model = $this->getDefaultModel();
		$this->record = $model->getRecord(1);
		$this->properties = $model->getProperties();
		$this->pageTitle = $this->getPageTitle();
		$this->pageTasks = $model->getDetailsTasks();
		$this->recordUsage = array();

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);

	}

}
