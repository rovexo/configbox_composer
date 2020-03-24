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

    /**
     * @inheritDoc
     */
	function getJsInitCallsOnce() {
        $calls = parent::getJsInitCallsOnce();
        $calls[] = 'configbox/adminCalculation::initCalcViewOnce';
        return $calls;
    }

	/**
	 * @inheritDoc
	 */
	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/adminCalculation::initCalcViewEach';
		return $calls;
	}

    function prepareTemplateVars() {
		
		$model = $this->getDefaultModel();
		$id = KRequest::getInt('id');

		if ($id) {
			$record = $model->getRecord($id);
		}
		else {
			$record = $model->initData();
		}

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);
		$this->pageTitle = ($record->name) ? $record->name : $this->getPageTitle();
		$this->pageTasks = $model->getDetailsTasks();
        $this->record = $record;
        $this->properties = $model->getProperties();
        $this->recordUsage = $model->getRecordUsage($id);

		$this->addViewCssClasses();

	}
	
}
