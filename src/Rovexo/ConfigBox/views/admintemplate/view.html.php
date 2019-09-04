<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmintemplate extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admintemplates';

	/**
	 * @return ConfigboxModelAdmintemplates
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmintemplates');
	}

	function getPageTitle() {
		return KText::_('Template');
	}

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/codemirror-5.30.0/lib/codemirror.css';
		return $urls;
	}

	function prepareTemplateVars() {

		$model = $this->getDefaultModel();
		$id = KRequest::getString('id');

		if ($id) {
			$record = $model->getRecord($id);
		}
		else {
			$record = $model->initData();
		}

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=json', false);


		$this->assignRef('record', $record);
		$this->assignRef('properties', $model->getProperties());

		$this->assign('isNew', (KRequest::getString('task') == 'edit'));

		$this->addViewCssClasses();
		$this->assignRef('pageTasks', $model->getDetailsTasks());

	}
	
}
