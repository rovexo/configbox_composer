<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxConditionCustomerGroup extends ConfigboxCondition {

	function containsQuestionId($conditionData, $questionId) {
		return false;
	}

	function containsAnswerId($conditionData, $answerId) {
		return false;
	}

	/**
	 * Called by ConfigboxRulesHelper::getConditionCode to compare condition with provided selections
	 *
	 * @param string[] $conditionData
	 * @param string[] $selections
	 * @return bool true if selections meet the condition
	 *
	 * @see ConfigboxRulesHelper::getConditionsCode, ConfigboxRulesHelper::getConditions, ConfigboxRulesHelper::getSelections
	 */
	function getEvaluationResult($conditionData, $selections) {
		
		$fieldName = $conditionData['fieldName'];
		$operator = $conditionData['operator'];
		$shouldValue = $conditionData['value'];

		$groupId = ConfigboxUserHelper::getGroupId();
		$groupData = ConfigboxUserHelper::getGroupData($groupId);
		
		$isValue = (isset($groupData->$fieldName)) ? $groupData->$fieldName : NULL;
		
		if (is_numeric($isValue)) {
			$return = version_compare( (float)$isValue,$shouldValue,$operator);
			
			return $return;
		}
		else {
			if ($operator == "==") {
				$return = (strcmp($isValue,$shouldValue) == 0);
				
				return $return;
			}
			if ($operator == "!=") {
				$return = (strcmp($isValue,$shouldValue) != 0);
				
				return $return;
			}
			else {
				$isValue = floatval($isValue);
				$return = version_compare( $isValue,$shouldValue,$operator);
				return $return;
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

		$conditionName = KText::sprintf('Customer Group Field %s', str_replace('custom_','', $conditionData['fieldName']));

		$operatorHtml = $this->getOperatorText($conditionData['operator']);

		$conditionValue = $conditionData['value'];
		if (is_numeric($conditionValue)) {
			$conditionValue = str_replace('.', KText::_('DECIMAL_MARK','.'), $conditionValue);
		}

		ob_start();

		?>
		<span
			class="item condition customer-group"
			data-type="<?php echo $conditionData['type'];?>"
			data-field-name="<?php echo $conditionData['fieldName'];?>"
			data-operator="<?php echo $conditionData['operator'];?>"
			>

			<span class="condition-name"><?php echo $conditionName;?></span>

			<span class="condition-operator"><?php echo $operatorHtml;?></span>

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
		return KText::_('Customer');
	}

	function getConditionsPanelHtml($ruleEditorView) {
		$view = KenedoView::getView('ConfigboxViewAdminruleeditor_customergroup');
		$view->ruleEditorView = $ruleEditorView;
		return $view->getHtml();
	}
}