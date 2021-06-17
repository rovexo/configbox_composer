<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewSdvisualization extends KenedoView {

	/**
	 * @var int CB product ID
	 */
	public $productId;

	/**
	 * @var int CB position Id
	 */
	public $positionId;

	/**
	 * @var string URL for the visualization iframe
	 */
	public $iframeUrl;

	/**
	 * @var string[][] Array with image data. Keys are question_id, url, geometry_name
	 */
	public $currentImageUploads;

	/**
	 * @var string ShapeDiver Ticket
	 */
	public $ticket;

	/**
	 * @var string Override for the SD setting modelViewUrl
	 */
	public $modelViewUrl;

	/**
	 * @var string[] Shapediver parameter settings (key is parameter id, value is parameter value)
	 */
	public $parameters;

	/**
	 * JSON made from $this->parameters
	 * @see parameters
	 * @var string
	 */
	public $parameterJson;

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/shapediverV2::initFrontendOnce';
		return $calls;
	}

	function prepareTemplateVars() {

		if (empty($this->productId)) {
			KLog::log('No product ID set for Shapediver iframe.', 'error');
			return;
		}

		$product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($this->productId);

		if ($product->visualization_type != 'shapediver') {
			KLog::log('Shapediver view loaded for product ID '.$this->productId.', but product does not use Shapediver', 'error');
			return;
		}

		if (ConfigboxAddonHelper::hasAddon('shapediver') == false) {
			echo KText::_('SHAPEDIVER_ADDON_EXPIRATION_INFO');
			return;
		}

		$modelData = json_decode($product->shapediver_model_data, true);

		if (empty($modelData['ticket'])) {
			KLog::log('Product ID '.$this->productId.' uses ShapeDiver, but the Ticket is not set', 'error');
			return;
		}

		$this->ticket = $modelData['ticket'];

		$configuration = ConfigboxConfiguration::getInstance($this->positionId);
		$this->parameters = ConfigboxShapediverHelper::getParameterValuesV2($configuration);
		$this->parameterJson = json_encode($this->parameters);

		$standardUrl = 'https://sdeuc1.eu-central-1.shapediver.com';
		$this->modelViewUrl = !empty($modelData['modelViewUrlOverride']) ? $modelData['modelViewUrlOverride'] : $standardUrl;

		// Here we prepare the image stash (image data used for reapplying textures after a geometry update)
		$configuration = ConfigboxConfiguration::getInstance($this->positionId);
		$selections = $configuration->getSelections(false);

		$this->currentImageUploads = array();

		foreach ($selections as $questionId => $selection) {

			if (ConfigboxQuestion::questionExists($questionId) == false) {
				continue;
			}

			$question = ConfigboxQuestion::getQuestion($questionId);

			if ($question->is_shapediver_control == '0' || $question->shapediver_parameter_id == '') {
				continue;
			}

			if ($question->getType() == 'upload') {

				$imgData = json_decode($selection, true);

				// Shouldn't be possible, but well.. skip over it if data seems wrong
				if (!is_array($imgData)) {
					continue;
				}

				// Skip non-images
				if (strpos($imgData['type'], 'image/') !== 0) {
					continue;
				}

				$this->currentImageUploads[] = array(
					'parameter_id' => $question->shapediver_parameter_id,
					'question_id' => $question->id,
					'url' => $imgData['url'],
				);

			}

		}

	}

	/**
	 * @param int $productId
	 * @return ConfigboxViewSdvisualization
	 */
	function setProductId($productId) {
		$this->productId = intval($productId);
		return $this;
	}

	/**
	 * @param int $positionId
	 * @return ConfigboxViewSdvisualization
	 */
	function setPositionId($positionId) {
		$this->positionId = intval($positionId);
		return $this;
	}
}