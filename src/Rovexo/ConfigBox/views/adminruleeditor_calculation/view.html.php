<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminRuleeditor_calculation extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var object[] Array of calculation data objects
	 */
	public $calculations;

	/**
	 * @var int $productId
	 */
	public $productId;

	/**
	 * @var ConfigboxViewAdminRuleeditor
	 */
	public $ruleEditorView;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		// The product ID to use here
		$this->productId = $this->ruleEditorView->productId;

		// Prepare the calculations for conditions
		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');
		$this->calculations = $calcModel->getRecords(array('admincalculations.product_id' => $this->productId));

		$this->addViewCssClasses();

	}
	
}
