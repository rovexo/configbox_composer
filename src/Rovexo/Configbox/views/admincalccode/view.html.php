<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalccode extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincalccodes';

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @return ConfigboxModelAdmincalccodes
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalccodes');
	}

    /**
     * @inheritDoc
     */
	function getJsInitCallsEach() {
        $calls = parent::getJsInitCallsEach();
        $calls[] = 'configbox/calcCode::initCalcCodeViewEach';
        return $calls;
    }

    function prepareTemplateVars() {
		
		$model 	= KenedoModel::getModel('ConfigboxModelAdmincalccodes');
		$id = KRequest::getInt('id');
		$this->record = $model->getRecord($id);
		$this->properties = $model->getProperties();

		$this->addViewCssClasses();
		
	}

	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}
	
}
