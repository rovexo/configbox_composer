<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxCalcTermCalculation extends ConfigboxCalcTerm {

	function containsQuestionId($termData, $questionId) {
		return false;
	}

	function containsAnswerId($termData, $answerId) {
		return false;
	}

	function containsCalculationId($termData, $calculationId) {
		return ($calculationId == $termData['value']);
	}

	/**
	 * Called by ConfigboxRulesHelper::getTermCode to get the term result
	 *
	 * @param string[] $termData
	 * @param string[] $selections
	 * @param int|NULL $regardingQuestionId The ID of the question the calculation is assigned to
	 * @param int|NULL $regardingAnswerId The ID of the answer the calculation is assigned to
	 * @param boolean $allowNonNumeric If the result can be non-numeric
	 * @return float The calculated result
	 *
	 * @see ConfigboxCalculation::getTermsCode, ConfigboxCalculation::getTerms, ConfigboxConfiguration::getSelections
	 */
	function getTermResult($termData, $selections, $regardingQuestionId = NULL, $regardingAnswerId = NULL, $allowNonNumeric = false) {
		$result = ConfigboxCalculation::calculate($termData['value'], $regardingQuestionId, $regardingAnswerId, $selections);
		if ($allowNonNumeric == false && is_numeric($result) == false) {
			$result = floatval($result);
		}
		return $result;
	}

	function getTermsPanelHtml($calculationId, $productId) {
		$view = KenedoView::getView('ConfigboxViewAdmincalcformula_calculation');
		$view->setProductId($productId);
		$view->setCalculationId($calculationId);
		return $view->getHtml();
	}

	/**
	 * Called by ConfigboxCalculation::getTermHtml to display the term (either for editing or display)
	 *
	 * @param string[] $termData
	 * @param bool $forEditing If edit controls or plain display should come out
	 * @return string HTML for that term
	 * @see ConfigboxCalculation::getTermHtml
	 */
	function getTermHtml($termData, $forEditing = true) {
		$calc = ConfigboxCacheHelper::getCalculation($termData['value']);
		ob_start();
		?>
		<span class="item term calculation"
			  data-type="Calculation"
			  data-value="<?php echo hsc($termData['value']);?>">
			<?php echo hsc($calc->name);?>
		</span>
		<?php
		return ob_get_clean();
	}

	function getTypeTitle() {
		return KText::_('Calculations');
	}

}