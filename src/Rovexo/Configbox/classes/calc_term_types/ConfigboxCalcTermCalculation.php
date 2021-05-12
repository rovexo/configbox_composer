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

	function getCopiedTermData($termData, $copyIds) {

		$oldCalcId = $termData['value'];
		if ($oldCalcId) {
			$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');
			$termData['value'] = $calcModel->copyAcrossProducts($oldCalcId);
		}

		return $termData;
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
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