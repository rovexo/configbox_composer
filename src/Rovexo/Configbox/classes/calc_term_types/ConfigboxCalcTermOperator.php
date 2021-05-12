<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxCalcTermOperator extends ConfigboxCalcTerm {

	function containsQuestionId($termData, $questionId) {
		return false;
	}

	function containsAnswerId($termData, $answerId) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	function getTermResult($termData, $selections, $regardingQuestionId = NULL, $regardingAnswerId = NULL, $allowNonNumeric = false) {
		return $termData['value'];
	}

	function getTermsPanelHtml($calculationId, $productId) {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	function getTermHtml($termData, $forEditing = true) {
		ob_start();
		?>
		<span class="item operator"
			  data-type="operator"
			  data-value="<?php echo hsc($termData['value']);?>">
			<?php echo hsc($termData['value']);?>
		</span>
		<?php
		return ob_get_clean();
	}

	function getTypeTitle() {
		return KText::_('Calculations');
	}

}