<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxCalcTermCustomerGroup extends ConfigboxCalcTerm {

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
		$fieldName = $termData['fieldName'];

		$groupId = ConfigboxUserHelper::getGroupId();
		$groupData = ConfigboxUserHelper::getGroupData($groupId);

		$value = (!empty($groupData->$fieldName)) ? $groupData->$fieldName : 0;
		if ($allowNonNumeric == false && is_numeric($value) == false) {
			$value = floatval($value);
		}
		return $value;
	}

	function getTermsPanelHtml($calculationId, $productId) {
		ob_start();
		?>
		<ul class="conditions-list">
			<?php
			for ($i = 1; $i <= 4; $i++) {
				$conditionData = array(
					'type'=>'CustomerGroup',
					'fieldName' => 'custom_'.$i,
					'operator' => '==',
					'value' => '',
				);

				echo '<li>';
				echo ConfigboxCalcTerm::getTerm('CustomerGroup')->getTermHtml($conditionData);
				echo '</li>';
			}
			?>
		</ul>
		<?php
		return ob_get_clean();

	}

	/**
	 * @inheritDoc
	 */
	function getTermHtml($termData, $forEditing = true) {
		$termName = KText::sprintf('Customer Group Field %s', str_replace('custom_','', $termData['fieldName']));
		ob_start();
		?>
		<span class="item term"
			  data-type="CustomerGroup"
			  data-field-name="<?php echo hsc($termData['fieldName']);?>"
			  data-value="<?php echo hsc($termData['value']);?>">
			<?php echo hsc($termName);?>
		</span>
		<?php
		return ob_get_clean();
	}

	function getTypeTitle() {
		return KText::_('Customer');
	}

}