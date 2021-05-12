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
	 * @inheritDoc
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
	 * @inheritDoc
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