<?php
class ConfigboxShapediverHelper {

	/**
	 * @return string The API version CB will work with
	 */
	static function getApiVersion() {
		return 'master_current';
	}

	/**
	 * @param int $productId
	 * @param ConfigboxConfiguration $configuration
	 * @return string Complete URL for the iframe
	 * @throws Exception in case something is wrong with the ShapeDiver settings
	 */
	static function getVisualizationUrl($productId, $configuration) {

		$product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($productId);

		if ($product->visualization_type != 'shapediver') {
			KLog::log('Shapediver view loaded for product ID '.$productId.', but product does not use Shapediver', 'error');
			throw new Exception('Product does not use ShapeDiver. See error log for details.');
		}

		ConfigboxRulesHelper::checkLicense('shapediver');

		$modelData = json_decode($product->shapediver_model_data, true);

		if (empty($modelData['iframeUrl'])) {
			KLog::log('Product ID '.$productId.' uses ShapeDiver, but the URL is not set', 'error');
			throw new Exception('Product data has no model URL. See error log for details.');
		}

		if (filter_var($modelData['iframeUrl'], FILTER_VALIDATE_URL) == false) {
			KLog::log('Product ID '.$productId.' seems to have an invalid iframe URL. URL is "'.$modelData['iframeUrl'].'"', 'error');
			throw new Exception('Shapediver model URL appears invalid. See error log for details.');
		}

		$viewerSettings = array(
			'showControls' => false,
			'allowFullscreen' => true,
			'showZoomControl' => false,
			'cameraRevertAtMouseUp' => false,
			'cameraAutoAdjust' => true,
			'showMessages' => false,
			'staticControls' => false,
		);

		if (function_exists('overrideSdViewerSettings')) {
			overrideSdViewerSettings($viewerSettings);
		}

		$parameterOverrides = self::getParameterValues($configuration);

		$query = array(
			'version'=>self::getApiVersion(),
			'mode'=>'full',
			'branding'=>'false',
			'brandedMode'=>'false',
			'viewerSettings'=>json_encode($viewerSettings),
			'overrideParams'=> json_encode($parameterOverrides),
		);

		$url = $modelData['iframeUrl'] .'?'.http_build_query($query);

		return $url;

	}

	/**
	 * @param ConfigboxConfiguration $configuration
	 * @return string[] keys are parameter IDs
	 */
	static function getParameterValues($configuration) {

		$questionIds = $configuration->getQuestionIdsWithSelection();

		$parameters = array();
		foreach ($questionIds as $questionId) {

			$question = ConfigboxQuestion::getQuestion($questionId);

			if ($question->is_shapediver_control && !empty($question->shapediver_parameter_id)) {

				$selection = $configuration->getSelection($questionId);
				$shapeDiverValue = null;

				// If we got a question with answers, look for the SD choice value
				if (!empty($question->answers) && !empty($question->answers[$selection])) {
					$shapeDiverValue = $question->answers[$selection]->shapediver_choice_value;
				}
				// If neither of those, use the selection as is
				elseif($selection !== null) {
					$shapeDiverValue = $selection;
				}

				// If we got a selection, use it as SD value. Otherwise
				if ($shapeDiverValue !== null) {
					$parameters[$question->shapediver_parameter_id] = $shapeDiverValue;
				}

			}
		}

		return $parameters;

	}


	/**
	 * @param ConfigboxConfiguration $configuration
	 * @return string[] keys are parameter IDs
	 */
	static function getParameterValuesV2($configuration) {

		$questionIds = $configuration->getQuestionIdsWithSelection();

		$parameters = array();
		foreach ($questionIds as $questionId) {

			$question = ConfigboxQuestion::getQuestion($questionId);

			if ($question->getType() == 'upload') {
				continue;
			}

			if ($question->is_shapediver_control && !empty($question->shapediver_parameter_id)) {

				$selection = $configuration->getSelection($questionId);
				$shapeDiverValue = null;
				$selectionHint = '';

				// If we got a question with answers, look for the SD choice value
				if (!empty($question->answers) && !empty($question->answers[$selection])) {
					$shapeDiverValue = $question->answers[$selection]->shapediver_choice_value;
					$selectionHint = $question->answers[$selection]->title;
				}
				// If neither of those, use the selection as is
				elseif($selection !== null) {
					$shapeDiverValue = $selection;
					$selectionHint = 'non-answer';
				}

				// If we got a selection, use it as SD value. Otherwise
				if ($shapeDiverValue !== null) {

					$parameter = [
						'id' => $question->shapediver_parameter_id,
						'value' => $shapeDiverValue,
						'questionType'=>$question->question_type,
						'questionTitle' => $question->title,
						'selectionHint' => $selectionHint,
					];

					$parameters[] = $parameter;
				}

			}
		}

		return $parameters;

	}

}