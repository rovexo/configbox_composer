<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerM2configurator extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	function getPricing() {

		$positionId = KRequest::getInt('cartPositionId');

		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$positionModel->setId($positionId, false);
		$response = $positionModel->getPricing();

		echo json_encode($response);

	}

	function getConfiguratorHtml() {

		$configInfo = KRequest::getArray('configInfo');
		$taxRate = KRequest::getFloat('taxRate');
		KSession::set('cbtaxrate', $taxRate);

		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$productModel = KenedoModel::getModel('ConfigboxModelProduct');
		$pageModel = KenedoModel::getModel('ConfigboxModelConfiguratorpage');

		$positionId = isset($configInfo['position_id']) ? $configInfo['position_id'] : null;

		if ($positionId) {
			$position = $positionModel->getPosition($positionId);
		}
		else {
			$position = null;
		}

		// If the position is not there, create a position and set the selections
		if ($position) {

			$positionModel->setId($positionId);
			$data = array('finished' => 0);
			$positionModel->editPosition($positionId, $data);

		} else {

			$positionId = $pageModel->ensureProperCartEnvironment($configInfo['prod_id']);

			// If the config info came with selections, then unset defaults and set them
			if (!empty($configInfo['selections'])) {

				$configuration = ConfigboxConfiguration::getInstance($positionId);

				// Remove any defaults..
				$configurationSelections = $configuration->getSelections(false);
				foreach ($configurationSelections as $questionId => $selection) {
					$configuration->setSelection($questionId, null);
				}

				// ..then set the selections from the config info
				if (!empty($configInfo['selections'])) {
					foreach ($configInfo['selections'] as $questionId => $selection) {
						$configuration->setSelection($questionId, $selection);
					}
				}

			}

		}

		$product = $productModel->getProduct($configInfo['prod_id']);

		KenedoView::getView('ConfigboxViewConfiguratorpage')
			->setCartPositionId($positionId)
			->setPageId($product->firstPageId)
			->display();

	}

	function getVisualizationHtml() {

		$positionId = KRequest::getInt('positionId');
		$productId = KRequest::getInt('productId');
		$pageId = KRequest::getInt('pageId');

		$productModel = KenedoModel::getModel('ConfigboxModelProduct');

		// Get the product
		$product = $productModel->getProduct($productId);

		if ($product->visualization_type == 'shapediver') {
			$shapeDiverView = KenedoView::getView('ConfigboxViewSdvisualization');
			$shapeDiverView->setProductId($productId)->setPositionId($positionId);
			echo $shapeDiverView->getHtml();
		} elseif ($product->visualization_type == 'composite' && ConfigboxProductImageHelper::hasProductImage($positionId)) {
			$visualizationView = KenedoView::getView('ConfigboxViewBlockvisualization');
			$visualizationView->setPositionId($positionId);
			echo $visualizationView->getHtml();
		}

	}

}