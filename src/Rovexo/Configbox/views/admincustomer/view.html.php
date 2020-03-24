<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincustomer extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincustomers';

	/**
	 * @var string $customerFormHtml
	 * @see ConfigboxViewCustomerform::getViewOutput
	 */
	public $customerFormHtml;

	/**
	 * @var ConfigboxUserData
	 */
	public $record;

	/**
	 * @return ConfigboxModelAdmincustomers
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincustomers');
	}

	function getPageTitle() {
		return KText::_('Customer');
	}

	function prepareTemplateVarsForm() {

		$id = KRequest::getInt('id');
		$model = $this->getDefaultModel();

		if ($id) {
			$this->record = $model->getRecord($id);
		}
		else {
			$this->record = $model->initData();
		}

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);

		$view = KenedoView::getView('ConfigboxViewCustomerform');
		$view->customerFields = ConfigboxUserHelper::getUserFields();
		$view->customerData = $this->record;
		$view->formType = 'profile';
		$view->useLoginForm = false;
		$view->prepareTemplateVars();
		$this->customerFormHtml = $view->getViewOutput();

		$this->recordUsage = $model->getRecordUsage($id);

		if (!empty($this->record->title)) {
			$this->pageTitle = $this->getPageTitle() . ': ' . $this->record->title;
		} elseif (!empty($this->record->name)) {
			$this->pageTitle = $this->record->name;
		} else {
			$this->pageTitle = $this->getPageTitle();
		}

		$this->pageTasks = $model->getDetailsTasks();

	}

}
