<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxCalcTermFunction extends ConfigboxCalcTerm {

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
	 * @throws Exception If function was not found
	 *
	 * @see ConfigboxCalculation::getTermsCode, ConfigboxCalculation::getTerms, ConfigboxConfiguration::getSelections
	 */
	function getTermResult($termData, $selections, $regardingQuestionId = NULL, $regardingAnswerId = NULL, $allowNonNumeric = false) {

		$function = $this->getFunction($termData['name']);

		// Eval all parameters (if any) to have parameters for the function call
		$parameters = array();
		if ($termData['parameters']) {
			foreach ($termData['parameters'] as $key=>$parameterData) {
				if (!empty($parameterData)) {

					// Check if the parameter for that function allows non-numeric input
					$allowNonNumericForParameter = false;
					if (!empty($function['parametersAllowNonNumeric'][$key])) {
						$allowNonNumeric = $function['parametersAllowNonNumeric'][$key];
					}

					$code = trim(ConfigboxCalculation::getTermsCode($parameterData, $selections, $regardingQuestionId, $regardingAnswerId, $allowNonNumericForParameter));
					$code = 'return '.$code.';';
					$function = create_function('', $code);
					$parameters[] = $function();
				}
			}
		}

		// Check if the function exists..
		if (strstr($termData['name'], '::')) {
			$ex = explode('::', $termData['name']);
			$functionExists = (method_exists($ex[0], $ex[1]));
		}
		else {
			$functionExists = (function_exists($termData['name']));
		}

		// ..and throw an Exception if not
		if ($functionExists == false) {
			throw new Exception('Function "'.$termData['name'].'" was not found.');
		}

		// Call the function..
		$return = call_user_func_array($termData['name'], $parameters);

		// Sanitize the result
		if ($allowNonNumeric == false && is_numeric($return) == false) {
			$return = floatval($return);
		}

		return $return;

	}

	function getTermsPanelHtml($calculationId, $productId) {

		$functions = $this->getAllowedFunctions();

		ob_start();
		?>
		<ul class="conditions-list">
		<?php
		foreach ($functions as $functionName=>$functionData) {

			$termData = array(
				'type'=>'Function',
				'name'=>$functionName,
			);

			echo '<li>';
			echo $this->getTermHtml($termData);
			echo '</li>';
		}
		?>
		</ul>
		<?php
		return ob_get_clean();
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

		$function = $this->getFunction($termData['name']);
		$i = 0;
		ob_start();
		?>

		<span class="item term function" data-type="function" data-name="<?php echo hsc($termData['name']);?>">
			<span class="function-name"><?php echo hsc($function['title']);?></span>
			(
			<span class="parameters parameters-required">
				<?php if (!empty($function['parametersRequired'])) { ?>
					<?php for ($i = 0; $i < $function['parametersRequired']; $i++) { ?>
						<span class="parameter parameter-required" data-parameter-name="<?php echo (!empty($function['parameterNames'][$i])) ? $function['parameterNames'][$i] : KText::sprintf('Parameter %s',$i+1) ;?>">
							<?php if ($forEditing && (empty($termData['parameters'][$i]) || empty($termData['parameters'][$i][0]))) { ?>
								<span class="parameter-drop-area"><?php echo (!empty($function['parameterNames'][$i])) ? $function['parameterNames'][$i] : KText::sprintf('Parameter %s',$i+1) ;?></span>
							<?php } else { ?>
								<?php echo ConfigboxCalculation::getTermsHtml($termData['parameters'][$i], $forEditing);?>
							<?php } ?>
						</span>
					<?php } ?>
				<?php } ?>

				<?php if (!empty($function['parametersOptional'])) { ?>
					<?php for ($j = $i; $j < $function['parametersRequired'] + $function['parametersOptional']; $j++) { ?>
						<span class="parameter parameter-optional" data-parameter-name="<?php echo (!empty($function['parameterNames'][$j])) ? $function['parameterNames'][$j] : KText::sprintf('Parameter %s',$j+1) ;?>">
							<?php if ($forEditing && (empty($termData['parameters'][$j]) || empty($termData['parameters'][$j][0]))) { ?>
								<span class="parameter-drop-area"><?php echo (!empty($function['parameterNames'][$j])) ? $function['parameterNames'][$j] : KText::sprintf('Parameter %s',$j+1) ;?></span>
							<?php } else { ?>
								<?php echo ConfigboxCalculation::getTermsHtml($termData['parameters'][$j], $forEditing);?>
							<?php } ?>
						</span>
					<?php } ?>
				<?php } ?>
			</span>
			)
		</span>

		<?php
		return ob_get_clean();
	}

	function getTypeTitle() {
		return KText::_('Functions');
	}

	/**
	 * @param string $functionName
	 * @return array[]
	 * @see ConfigboxCalcTermFunction::getAllowedFunctions
	 */
	function getFunction($functionName) {
		$functions = $this->getAllowedFunctions();
		return $functions[$functionName];
	}

	/**
	 * @return array[]
	 */
	function getAllowedFunctions() {

		$functions = array(
			'round'	=> array(
				'title'=>KText::_('Round'),
				'parametersRequired'=>1,
				'parametersOptional'=>1,
				'parameterNames'=>array(
					KText::_('Number'),
					KText::_('Decimal places'),
				),
				'parametersAllowNonNumeric'=>array(
					false,
					false,
				),
			),
			'min' => array(
				'title'=>KText::_('Lowest Value'),
				'parametersRequired'=>2,
				'parametersOptional'=>2,
				'parameterNames'=>array(
					KText::_('First value'),
					KText::_('Second value'),
					KText::_('Third value'),
					KText::_('Fourth value'),
				),
				'parametersAllowNonNumeric'=>array(
					false,
					false,
					false,
					false,
				),
			),
			'max' => array(
				'title'=>KText::_('Highest Value'),
				'parametersRequired'=>2,
				'parametersOptional'=>2,
				'parameterNames'=>array(
					KText::_('First value'),
					KText::_('Second value'),
					KText::_('Third value'),
					KText::_('Fourth value'),
				),
				'parametersAllowNonNumeric'=>array(
					false,
					false,
					false,
					false,
				),
			),
		);

		if (function_exists('getAdditionalCalcFunctions')) {
			$additional = getAdditionalCalcFunctions();

			if (is_array($additional)) {
				$functions = array_merge($functions, $additional);
			}
		}

		return $functions;

	}

}