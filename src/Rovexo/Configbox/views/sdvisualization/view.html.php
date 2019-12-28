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
	 * @var float Height of the iframe as percent of the current width
	 */
	public $relativeIframeHeight = 60;

	/**
	 * @var string[][] Array with image data. Keys are question_id, url, geometry_name
	 */
	public $currentImageUploads;

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();

		$calls[] = 'configbox/shapediver::initShapeDiverVisOnce';

		return $calls;
	}

	function getJsInitCallsEach() {
		$calls = [];
		$calls[] = 'configbox/shapediver::initShapeDiverVisEach';
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

		if (empty($modelData['iframeUrl'])) {
			KLog::log('Product ID '.$this->productId.' uses ShapeDiver, but the URL is not set', 'error');
			return;
		}

		if (filter_var($modelData['iframeUrl'], FILTER_VALIDATE_URL) == false) {
			KLog::log('Product ID '.$this->productId.' seems to have an invalid iframe URL. URL is "'.$modelData['iframeUrl'].'"', 'error');
			return;
		}

		// iframe width is responsive and width/height ratio should be constant. So we made a width to height ratio setting.
		$ratio = !empty($modelData['ratioDimensions']) ? $modelData['ratioDimensions'] : '4:3';
		$exp = explode(':', $ratio);
		$this->relativeIframeHeight = number_format($exp[1] / $exp[0] * 100, 3, '.', '');

		$this->iframeUrl = ConfigboxShapediverHelper::getVisualizationUrl($product->id, ConfigboxConfiguration::getInstance());

		// Here we prepare the image stash (image data used for reapplying textures after a geometry update)
		$configuration = ConfigboxConfiguration::getInstance($this->positionId);
		$selections = $configuration->getSelections(false);

		$this->currentImageUploads = array();

		foreach ($selections as $questionId => $selection) {

			if (ConfigboxQuestion::questionExists($questionId) == false) {
				continue;
			}

			$question = ConfigboxQuestion::getQuestion($questionId);

			if ($question->is_shapediver_control == '0' || $question->shapediver_geometry_name == '') {
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
					'question_id' => $question->id,
					'url' => $imgData['url'],
					'geometry_name' => $question->shapediver_geometry_name,
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