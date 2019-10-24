<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalcformula_calculation extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var object[] Array of calculation data objects
	 */
	public $calculations;

	/**
	 * @var int The calculation ID that loads this panel
	 */
	public $calculationId;

	/**
	 * @var int Product ID to filter calculations for
	 */
	public $productId;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		// Prepare the calculations for conditions
		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');
		$this->calculations = $calcModel->getRecords(array('admincalculations.product_id'=>$this->productId));

		$this->addViewCssClasses();

	}

	function setCalculationId($calculationId) {
		$this->calculationId = $calculationId;
		return $this;
	}

	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}

}
