<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminRuleeditor extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var bool $ruleIsSet Indicates if the editor already got a rule loaded or if the rule is empty
	 */
	public $ruleIsSet;

	/**
	 * @var string The HTML of the editable rule
	 */
	public $ruleHtml;

	/**
	 * @var string The ID of the parent property that holds to rule value. On store the editor will put the rule JSON there
	 */
	public $returnFieldId;

	/**
	 * @var string[] Tab info for the condition panels (key is condition type, value is the condition type's title)
	 * @see ConfigboxCondition::getTypeName, ConfigboxCondition::getTypeTitle
	 */
	public $conditionTabs;

	/**
	 * @var string[] HTML of the condition type's panel, will show up when the user clicks on the type's tab
	 * @see ConfigboxCondition::getConditionsPanelHtml
	 */
	public $conditionPanels;

	/**
	 * @var string The selected panel (fixed)
	 */
	public $selectedTypeName;

	/**
	 * @var int $productId
	 */
	public $productId;

	/**
	 * @var int $pageId
	 */
	public $pageId;

	/**
	 * @var string $usageIn Either question or answer. Comes from kenedo prop through usageIn parameter
	 */
	public $usageIn;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function getStyleSheetUrls() {
		$css = parent::getStyleSheetUrls();
		$css[] = KenedoPlatform::p()->getUrlAssets().'/css/rule-editor.css';
		return $css;
	}

	function getJsInitCallsEach() {
		$js = parent::getJsInitCallsEach();
		$js[] = 'configbox/ruleEditor::initRuleEditor';
		return $js;
	}

	function prepareTemplateVars() {

		$rule = KRequest::getVar('rule','');
		$this->productId = KRequest::getInt('productId');
		$this->pageId = KRequest::getInt('pageId');
		$this->returnFieldId = KRequest::getString('returnFieldId', '');
		$this->usageIn = KRequest::getString('usageIn');

		if ($this->usageIn == 'question') {
			$this->pageTitle = KText::_('Show the question if these conditions are met:');
		}
		else {
			$this->pageTitle = KText::_('Show the answer if these conditions are met:');
		}

		// Get the rule HTML and assign it
		if ($rule) {
			$this->ruleHtml = ConfigboxRulesHelper::getRuleHtml($rule);
			$this->ruleIsSet = true;
		}
		else {
			$this->ruleHtml = '';
			$this->ruleIsSet = false;
		}

		// Get all available condition type names
		$conditionTypeNames = ConfigboxCondition::getConditionTypeNames();

		// The intended ordering for tabs (other types will be appended after those)
		$ordering = array(
			'ElementAttribute',
			'Calculations',
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
		$this->conditionTabs = array();
		$this->conditionPanels = array();

		foreach ($orderedTypeNames as $typeName) {
			$condition = ConfigboxCondition::getCondition($typeName);
			$this->conditionTabs[$typeName] = $condition->getTypeTitle();
			$this->conditionPanels[$typeName] = $condition->getConditionsPanelHtml($this);
		}

		$this->selectedTypeName = $ordering[0];

		$this->addViewCssClasses();

	}
	
}
