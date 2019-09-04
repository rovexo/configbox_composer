<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewProductdetailpanes extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var int CB product ID
	 */
	public $productId;

	/**
	 * @var ConfigboxProductData $product CB product data
	 * @see ConfigboxCacheHelper::getProductDetailPanes()
	 */
	public $productDetailPanes;

	/**
	 * @var string 'configuratorPage'|'productPage' Indicates where the panes are rendered
	 */
	public $parentView;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function getJsInitCallsOnce() {
		return ['cbj.bootstrap'];
	}

	function prepareTemplateVars() {

		if ($this->productId === null) {
			if ($this->product) {
				KLog::log('Integrate product detail panes view different. Use setProductId() instead.');
				$this->productId = $this->product->id;
			}
			else {
				throw new Exception('Product detail panes view needs a product ID set. See setProductId() in view class.', 500);
			}
		}

		if ($this->productDetailPanes === null) {
			$this->productDetailPanes = ConfigboxCacheHelper::getProductDetailPanes($this->productId);
		}

	}

	/**
	 * @param int $id
	 * @return $this
	 */
	function setProductId($id) {
		$this->productId = $id;
		return $this;
	}

}
