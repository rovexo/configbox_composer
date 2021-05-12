<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxCalcTermElementAttribute extends ConfigboxCalcTerm {

	function containsQuestionId($termData, $questionId) {
		return ($questionId == $termData['elementId']);
	}

	function containsAnswerId($termData, $answerId) {
		return false;
	}

	function getCopiedTermData($termData, $copyIds) {

		if ($termData['elementId'] == 'regarding') {
			return $termData;
		}

		$oldQuestionId = $termData['elementId'];
		if ($oldQuestionId) {
			$termData['elementId'] = $copyIds['adminelements'][$oldQuestionId];
		}
		return $termData;
	}

	/**
	 * @inheritDoc
	 */
	function getTermResult($termData, $selections, $regardingQuestionId = NULL, $regardingAnswerId = NULL, $allowNonNumeric = false) {

		if ($termData['elementId'] == 'regarding') {
			$termData['elementId'] = $regardingQuestionId;
			$termData['fieldPath'] = str_replace('selectedOption.', 'regardingOption.', $termData['fieldPath']);
		}

		switch ($termData['fieldPath']) {

			case 'price':
				$isValue = ConfigboxPrices::getElementPrice($termData['elementId']);
				break;

			case 'priceRecurring':
				$isValue = ConfigboxPrices::getElementPriceRecurring($termData['elementId']);
				break;

			case 'selected':
				$selection = (isset($selections[$termData['elementId']])) ? $selections[$termData['elementId']] : null;
				$question = ConfigboxQuestion::getQuestion($termData['elementId']);
				$isValue = $question->getComparableValue($selection);
				break;

			default:
				$element = ConfigboxQuestion::getQuestion($termData['elementId']);
				$isValue = $element->getField($termData['fieldPath'], $regardingAnswerId, $termData['fallbackValue']);
				break;

		}

		// Get the found value, the fallback value or 0
		if (!empty($isValue)) {
			$return = $isValue;
		}
		elseif(!empty($termData['fallbackValue'])) {
			$return = $termData['fallbackValue'];
		}
		else {
			$return = 0;
		}

		// Sanitize the value
		if ($allowNonNumeric == false && is_numeric($return) == false) {
			$return = floatval($return);
		}

		return $return;

	}

	function getTermsPanelHtml($calculationId, $productId) {
		$view = KenedoView::getView('ConfigboxViewAdmincalcformula_elementattribute');
		$view->setProductId($productId);
		return $view->getHtml();
	}

	/**
	 * @inheritDoc
	 */
	function getTermHtml($termData, $forEditing = true) {
		$attributes = $this->getElementAttributes();
		if (strtolower($termData['elementId']) == 'regarding') {
			$termName = KText::sprintf($attributes[$termData['fieldPath']]['text'], KText::_('Regarding Question'));
		}
		else {
			$termName = KText::sprintf($attributes[$termData['fieldPath']]['text'], hsc(ConfigboxRulesHelper::getQuestionTitle($termData['elementId'])));
		}

		ob_start();
		?>
		<span class="item term element-field-value"
			  data-type="ElementAttribute"
			  data-element-id="<?php echo hsc($termData['elementId']);?>"
			  data-field-path="<?php echo hsc($termData['fieldPath']);?>">

			<?php echo hsc($termName); ?>

			<?php if ($forEditing) { ?>
				<?php echo KText::_('or');?>
				<input type="text" class="input fallback-value" data-data-key="fallbackValue" value="<?php echo hsc($termData['fallbackValue']);?>" />
			<?php } else { ?>
				<?php echo ($termData['fallbackValue']) ? KText::_('or') : '';?>
				<span class="term-value"><?php echo hsc($termData['fallbackValue']);?></span>
			<?php } ?>

		</span>
		<?php
		return ob_get_clean();
	}

	function getTypeTitle() {
		return KText::_('Questions');
	}

	/**
	 * Returns the possible element subjects for elementattribute conditions
	 * @return array[]
	 */
	function getElementAttributes() {

		$settings = CbSettings::getInstance();

		$label1 = $settings->get('label_assignment_custom_1');
		$label2 = $settings->get('label_assignment_custom_2');
		$label3 = $settings->get('label_assignment_custom_3');
		$label4 = $settings->get('label_assignment_custom_4');

		$assignmentCustom1 = (trim($label1)) ? $label1 : KText::sprintf('Answer Custom %s',1);
		$assignmentCustom2 = (trim($label2)) ? $label2 : KText::sprintf('Answer Custom %s',2);
		$assignmentCustom3 = (trim($label3)) ? $label3 : KText::sprintf('Answer Custom %s',3);
		$assignmentCustom4 = (trim($label4)) ? $label4 : KText::sprintf('Answer Custom %s',4);

		$label1 = $settings->get('label_option_custom_1');
		$label2 = $settings->get('label_option_custom_2');
		$label3 = $settings->get('label_option_custom_3');
		$label4 = $settings->get('label_option_custom_4');

		$optionCustom1 = (trim($label1)) ? $label1 : KText::sprintf('Global Answer Custom %s',1);
		$optionCustom2 = (trim($label2)) ? $label2 : KText::sprintf('Global Answer Custom %s',2);
		$optionCustom3 = (trim($label3)) ? $label3 : KText::sprintf('Global Answer Custom %s',3);
		$optionCustom4 = (trim($label4)) ? $label4 : KText::sprintf('Global Answer Custom %s',4);

		$label1 = $settings->get('label_element_custom_1');
		$label2 = $settings->get('label_element_custom_2');
		$label3 = $settings->get('label_element_custom_3');
		$label4 = $settings->get('label_element_custom_4');

		$elementCustom1 = (trim($label1)) ? $label1 : KText::sprintf('Question Custom %s',1);
		$elementCustom2 = (trim($label2)) ? $label2 : KText::sprintf('Question Custom %s',2);
		$elementCustom3 = (trim($label3)) ? $label3 : KText::sprintf('Question Custom %s',3);
		$elementCustom4 = (trim($label4)) ? $label4 : KText::sprintf('Question Custom %s',4);

		return array(

			'selected' => array ('text'=>KText::_('Entry in %s') ) ,
			'price' => array ('text'=>KText::_('Price of %s') ) ,
			'priceRecurring' => array ('text'=>KText::_('Recurring Price of %s') ) ,

			'element_custom_1' => array ('text'=>KText::_("$elementCustom1 in %s") ) ,
			'element_custom_2' => array ('text'=>KText::_("$elementCustom2 in %s") ) ,
			'element_custom_3' => array ('text'=>KText::_("$elementCustom3 in %s") ) ,
			'element_custom_4' => array ('text'=>KText::_("$elementCustom4 in %s") ) ,

			'selectedOption.assignment_custom_1' => array ('text'=>KText::_("$assignmentCustom1 in %s") ) ,
			'selectedOption.assignment_custom_2' => array ('text'=>KText::_("$assignmentCustom2 in %s") ) ,
			'selectedOption.assignment_custom_3' => array ('text'=>KText::_("$assignmentCustom3 in %s") ) ,
			'selectedOption.assignment_custom_4' => array ('text'=>KText::_("$assignmentCustom4 in %s") ) ,

			'selectedOption.option_custom_1' => array ('text'=>KText::_("$optionCustom1 in %s") ) ,
			'selectedOption.option_custom_2' => array ('text'=>KText::_("$optionCustom2 in %s") ) ,
			'selectedOption.option_custom_3' => array ('text'=>KText::_("$optionCustom3 in %s") ) ,
			'selectedOption.option_custom_4' => array ('text'=>KText::_("$optionCustom4 in %s") ) ,

		);

	}

}