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

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=raw', false);

		$view = KenedoView::getView('ConfigboxViewCustomerform');
		$view->customerFields = ConfigboxUserHelper::getUserFields();
		$view->customerData = $this->record;
		$view->formType = 'profile';
		$view->useLoginForm = false;
		$view->prepareTemplateVars();
		$formHtml = $view->getViewOutput();

		$this->assign('customerFormHtml', $formHtml);

		$this->assignRef('recordUsage', $model->getRecordUsage($id));

		if (!empty($this->record->title)) {
			$this->assignRef('pageTitle', $this->getPageTitle() . ': ' . $this->record->title);
		} elseif (!empty($this->record->name)) {
			$this->assignRef('pageTitle', $this->record->name);
		} else {
			$this->assignRef('pageTitle', $this->getPageTitle());
		}

		$this->assignRef('pageTasks', $model->getDetailsTasks());

	}

}
