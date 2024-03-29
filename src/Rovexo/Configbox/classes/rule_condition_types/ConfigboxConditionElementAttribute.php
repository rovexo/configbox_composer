<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxConditionElementAttribute extends ConfigboxCondition {

	function containsQuestionId($conditionData, $questionId) {
		return ($questionId == $conditionData['elementId']);
	}

	function containsAnswerId($conditionData, $answerId) {

		if ($conditionData['field'] == 'selectedOption.id') {

			$db = KenedoPlatform::getDb();
			$query = "
			SELECT `id`
			FROM `#__configbox_xref_element_option`
			WHERE `id` = ".intval($conditionData['value'])." AND `element_id` = ".intval($conditionData['elementId']);
			$db->setQuery($query);
			$isActualXref = intval($db->loadResult());

			if ($isActualXref) {
				return ($answerId == $conditionData['value']);
			}

		}

		return false;

	}

	/**
	 * @inheritDoc
	 */
	function getCopiedConditionData($conditionData, $copyIds) {

		$oldQuestionId = $conditionData['elementId'];
		if ($oldQuestionId) {
			$hasNewId = isset($copyIds['adminelements'][$conditionData['elementId']]);
			if ($hasNewId) {
				$conditionData['elementId'] = $copyIds['adminelements'][$conditionData['elementId']];
			}
		}

		if ($conditionData['field'] == 'selectedOption.id') {
			$oldAnswerId = $conditionData['value'];
			if ($oldAnswerId) {
				$hasNewAnswerId = isset($copyIds['adminoptionassignments'][$conditionData['value']]);
				if ($hasNewAnswerId) {
					$conditionData['value'] = $copyIds['adminoptionassignments'][$conditionData['value']];
				}
			}

		}

		return $conditionData;
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

		$questionId = $conditionData['elementId'];
		$operator = $conditionData['operator'];
		$shouldValue = $conditionData['value'];

		// Get the isValue
		switch ($conditionData['field']) {

			case 'price':
				$isValue = ConfigboxPrices::getElementPrice($questionId);
				break;

			case 'priceRecurring':
				$isValue = ConfigboxPrices::getElementPriceRecurring($questionId);
				break;

			case 'selectedOption.id':
			case 'selected':
			case 'selection':
				$selection = (isset($selections[$questionId])) ? $selections[$questionId] : null;
				if (ConfigboxQuestion::questionExists($questionId) == false) {
					$isValue = null;
				}
				else {
					$isValue = ConfigboxQuestion::getQuestion($questionId)->getComparableValue($selection);
				}
				break;

			default:

				if (ConfigboxQuestion::questionExists($questionId) == false) {
					$isValue = null;
				}
				else {
					$isValue = ConfigboxQuestion::getQuestion($questionId)->getField($conditionData['field']);
				}

				break;

		}

		if (is_null($isValue)) {

			if ($operator == "==") {
				if ($shouldValue == '') {
					$return = true;
				}
				else {
					$return = false;
				}
			}
			elseif ($operator == "!=") {
				if ($shouldValue == '') {
					$return = false;
				}
				else {
					$return = true;
				}
			}
			else {
				$return = false;
			}

		}
		elseif (is_numeric($isValue)) {
			$return = version_compare($isValue, $shouldValue, $operator);
		}
		else {

			if ($operator == "==") {
				$return = (strcmp($isValue, $shouldValue) == 0);
			}
			elseif ($operator == "!=") {
				$return = (strcmp($isValue, $shouldValue) != 0);
			}
			else {
				$return = version_compare(floatval($isValue), $shouldValue, $operator);
			}

		}

		return $return;

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

		$questionTitle = ConfigboxRulesHelper::getQuestionTitle($conditionData['elementId']);
		$attributes = $this->getElementAttributes();

		$conditionName = KText::sprintf($attributes[$conditionData['field']]['text'], $questionTitle);

		$operatorHtml = $this->getOperatorText($conditionData['operator']);

		// We prep shouldValue (what we use for processing)..
		$shouldValue = $conditionData['value'];
		$outputValue = $shouldValue;

		// .. and outputValue (more expressive value for users)
		switch ($conditionData['field']) {
			case 'selected':
			case 'selection':
				if ($shouldValue === '') {
					$outputValue = 'empty';
				}
				elseif(is_numeric($shouldValue)) {
					$outputValue = str_replace('.', KText::_('DECIMAL_MARK','.'), $shouldValue);
				}
				break;

			case 'selectedOption.id':
				if ($shouldValue === '') {
					$outputValue = KText::_('not answered');
				}
				else {
					$outputValue = ConfigboxRulesHelper::getAnswerTitle($conditionData['value']);
				}
				break;

		}

		ob_start();

		?>
		<span
			class="item condition elementattribute"
			data-type="<?php echo hsc($conditionData['type']);?>"
			data-element-id="<?php echo hsc($conditionData['elementId']);?>"
			data-field="<?php echo hsc($conditionData['field']);?>"
			data-operator="<?php echo hsc($conditionData['operator']);?>"
			data-value="<?php echo hsc($conditionData['value']);?>"
			>

			<span class="condition-name"><?php echo hsc($conditionName);?></span>

			<span class="condition-operator"><?php echo $operatorHtml;?></span>

			<?php if ($conditionData['field'] == 'selectedOption.id') { ?>
				<span class="condition-value"><?php echo hsc($outputValue);?></span>
			<?php } else { ?>

				<?php if ($forEditing) { ?>
					<input class="input" data-data-key="value" type="text" value="<?php echo hsc($shouldValue);?>" />
				<?php } else { ?>
					<span class="condition-value"><?php echo hsc($outputValue);?></span>
				<?php } ?>

			<?php } ?>

		</span>
		<?php

		return ob_get_clean();

	}

	function getTypeTitle() {
		return KText::_('Answers to questions');
	}

	function getConditionsPanelHtml($ruleEditorView) {
		$view = KenedoView::getView('ConfigboxViewAdminruleeditor_elementattribute');
		$view->ruleEditorView = $ruleEditorView;
		return $view->getHtml();
	}

	/**
	 * Returns the possible element subjects for elementattribute conditions
	 * @return array[]
	 */
	function getElementAttributes() {

		$attributes = array(
			'selectedOption.id' => array ('text'=>KText::_('Answer in %s') ) ,
			'selected'          => array ('text'=>KText::_('Answer in %s') ) ,
			'price'             => array ('text'=>KText::_('Price of %s') ) ,
			'priceRecurring'    => array ('text'=>KText::_('Recurring Price of %s') ) ,
		);

		// Add the answer custom fields
		for ($i = 1; $i <= 4; $i++) {
			$fieldPath = 'selectedOption.assignment_custom_'.$i;

			$label = CbSettings::getInstance()->get('label_assignment_custom_'.$i);
			if (trim((string)$label) == '') {
				$label = KText::sprintf('Field %s', $i);
			}

			$attributes[$fieldPath] = array(
				'text'=> $label .' '. KText::_('in answer for %s'),
			);

		}

		// Add the component custom fields
		for ($i = 1; $i <= 4; $i++) {
			$fieldPath = 'selectedOption.option_custom_'.$i;

			$label = CbSettings::getInstance()->get('label_option_custom_'.$i);
			if (trim((string)$label) == '') {
				$label = KText::sprintf('Field %s', $i);
			}

			$attributes[$fieldPath] = array(
				'text'=> $label .' '. KText::_('in global answer for %s'),
			);

		}

		return $attributes;

	}

}