<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxCalcTermNumber extends ConfigboxCalcTerm {

	function containsQuestionId($termData, $questionId) {
		return false;
	}

	function containsAnswerId($termData, $answerId) {
		return false;
	}

	/**
	 * Called by ConfigboxRulesHelper::getTermsCode to get the term result
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
		$return = $termData['value'];
		if ($allowNonNumeric == false && is_numeric($return) == false) {
			$return = floatval($return);
		}
		return $return;
	}

	function getTermsPanelHtml($calculationId, $productId) {
		return '';
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
		ob_start();
		?>
		<span class="item term number" data-type="number" data-value="<?php echo hsc($termData['value']);?>">
			<?php if ($forEditing) { ?>
				<input class="input" data-data-key="value" type="text" value="<?php echo hsc($termData['value']);?>" placeholder="<?php echo KText::_('Number');?>" />
			<?php } else { ?>
				<span class="term-value"><?php echo hsc($termData['value']);?></span>
			<?php } ?>
		</span>
		<?php
		return ob_get_clean();
	}

}