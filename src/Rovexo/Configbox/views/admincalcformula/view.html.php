<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalcformula extends KenedoView {

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @var string[] Tab info for the condition panels (key is condition type, value is the condition type's title)
	 * @see ConfigboxCondition::getTermTypeNames, ConfigboxCalcTerm::getTypeTitle
	 */
	public $termTabs;

	/**
	 * @var string[] HTML of the condition type's panel, will show up when the user clicks on the type's tab
	 * @see ConfigboxCalcTerm::getTermsPanelHtml
	 */
	public $termPanels;

	/**
	 * @var string The selected panel (fixed)
	 */
	public $selectedTypeName;

	/**
	 * @var string The calculation JSON
	 */
	public $calcJson;

	/**
	 * @var array[] Holding the operator terms data
	 */
	public $operatorData;

	function getJsInitCallsOnce() {
        $calls = parent::getJsInitCallsOnce();
        $calls[] = 'configbox/calcEditor::initCalcEditorOnce';
        return $calls;
    }

    function getJsInitCallsEach() {
        $calls = parent::getJsInitCallsEach();
        $calls[] = 'configbox/calcEditor::initCalcEditorEach';
        return $calls;
    }

    function getStyleSheetUrls() {
        $urls = parent::getStyleSheetUrls();
        $urls[] = KenedoPlatform::p()->getUrlAssets().'/css/calc-editor.css';
        return $urls;
    }

    function prepareTemplateVars() {

		// Get the id of the requested calculation
		if (empty($this->id)) {
			$this->id = KRequest::getInt('id', 0);
		}

		// Get the calculation json string
		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalcformulas');
		$this->calcJson = $calcModel->getCalculationJson($this->id);

		$this->operatorData = array(
			array('type'=>'Operator', 'value'=>'+'),
			array('type'=>'Operator', 'value'=>'-'),
			array('type'=>'Operator', 'value'=>'*'),
			array('type'=>'Operator', 'value'=>'/'),
		);

		// Get all available condition type names
		$conditionTypeNames = ConfigboxCalcTerm::getTermTypeNames();

		// The intended ordering for tabs (other types will be appended after those)
		$ordering = array(
			'ElementAttribute',
			'Calculations',
			'Functions',
			'CustomerGroups',
		);

		// Go through that list and add the real
		$orderedTypeNames = array();
		foreach ($ordering as $typeName) {
			$key = array_search($typeName, $conditionTypeNames);
			if ($key) {
				$orderedTypeNames[] = $conditionTypeNames[$key];
				unset($conditionTypeNames[$key]);
			}
		}
		foreach ($conditionTypeNames as $typeName) {
			$orderedTypeNames[] = $typeName;
		}
		unset($conditionTypeNames);

		// Set up all panels for available conditions
		$tabs = array();
		$panels = array();
		foreach ($orderedTypeNames as $typeName) {
			$term = ConfigboxCalcTerm::getTerm($typeName);
			$tabs[$typeName] = $term->getTypeTitle();
			$panels[$typeName] = $term->getTermsPanelHtml($this->id, $this->productId);
			if (empty($panels[$typeName])) {
				unset($panels[$typeName], $tabs[$typeName]);
			}
		}

		$this->selectedTypeName = $ordering[0];
		$this->termTabs = $tabs;
		$this->termPanels = $panels;

		$this->addViewCssClasses();

	}

	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}

	function setFormulaId($id) {
		$this->id = $id;
		return $this;
	}
	
}
