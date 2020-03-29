<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxConditionCalculation extends ConfigboxCondition {

	function containsQuestionId($conditionData, $questionId) {
		return false;
	}

	function containsAnswerId($conditionData, $answerId) {
		return false;
	}

	function containsCalculationId($conditionData, $calculationId) {
		return ($calculationId == $conditionData['calcId']);
	}

	/**
	 * @inheritDoc
	 */
	function getCopiedConditionData($conditionData, $copyIds) {

		$oldCalcId = $conditionData['calcId'];
		if ($oldCalcId) {
			$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');
			$conditionData['calcId'] = $calcModel->copyAcrossProducts($oldCalcId);
		}

		return $conditionData;
	}


	/**
	 * Called by ConfigboxRulesHelper::getConditionCode to compare condition with provided selections
	 *
	 * @param string[] $conditionData
	 * @param string[] $selections current selections
	 * @return bool true if selections meet the condition
	 *
	 * @see ConfigboxRulesHelper::getConditionsCode, ConfigboxRulesHelper::getConditions, ConfigboxConfiguration::getSelections
	 */
	function getEvaluationResult($conditionData, $selections) {
		
		$calcId = $conditionData['calcId'];
		$operator = $conditionData['operator'];
		$shouldValue = $conditionData['value'];

		$isValue = ConfigboxCalculation::calculate($calcId, NULL, NULL, $selections);
		
		if (is_numeric($isValue)) {
			return version_compare( (float)$isValue, $shouldValue, $operator);
		}
		else {
			if ($operator == "==") {
				return (strcmp($isValue,$shouldValue) == 0);
			}
			if ($operator == "!=") {
				return (strcmp($isValue,$shouldValue) != 0);
			}
			else {
				return version_compare( (float)$isValue, $shouldValue, $operator);
			}
		}
		
	}

	/**
	 * Called by ConfigboxRulesHelper::getConditionHtml to display the condition (either for editing or display)
	 *
	 * @param string[] $conditionData
	 * @param bool $forEditing If edit controls or plain display should come out
	 * @return string HTML for that condition
	 * @see ConfigboxRulesHelper::getConditionsHtml
	 */
	function getConditionHtml($conditionData, $forEditing = true) {

		$calculation = ConfigboxCacheHelper::getCalculation($conditionData['calcId']);
		$conditionName = KText::sprintf('Result of Calculation %s', $calculation->name);

		// Localize the condition value if numeric
		$conditionValue = $conditionData['value'];
		if (is_numeric($conditionValue)) {
			$conditionValue = str_replace('.', KText::_('DECIMAL_MARK','.'), $conditionValue);
		}

		ob_start();

		?>
		<span
			class="item condition calculation"
			data-type="<?php echo $conditionData['type'];?>"
			data-calc-id="<?php echo $conditionData['calcId'];?>"
			data-operator="<?php echo $conditionData['operator'];?>"
			>

			<span class="condition-name"><?php echo $conditionName;?></span>

			<span class="condition-operator"><?php echo $this->getOperatorText($conditionData['operator']);?></span>

			<?php if ($forEditing) { ?>
				<input class="input" data-data-key="value" type="text" value="<?php echo hsc($conditionValue);?>" />
			<?php } else { ?>
				<span class="condition-value"><?php echo hsc($conditionValue);?></span>
			<?php } ?>

		</span>
		<?php

		return ob_get_clean();

	}

	function getTypeTitle() {
		return KText::_('Results of calculations');
	}

	function getConditionsPanelHtml($ruleEditorView) {
		$view = KenedoView::getView('ConfigboxViewAdminruleeditor_calculation');
		$view->ruleEditorView = $ruleEditorView;
		return $view->getHtml();
	}

}