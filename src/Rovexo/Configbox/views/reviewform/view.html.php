<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewReviewform extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'reviewform';

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @var ConfigboxProductData Product data
	 */
	public $product;

	/**
	 * @var string Name of the user (if user data contains anything yet)
	 */
	public $name;

	/**
	 * @return ConfigboxModelReviews
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelReviews');
	}

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/reviews.css';
		return $urls;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/reviews::initReviewForm';
		return $calls;
	}

	function prepareTemplateVars() {

		if (empty($this->productId)) {
			$this->productId = KRequest::getInt('product_id');
		}

		$this->name = ConfigboxUserHelper::getUser()->billingfirstname . ' '.ConfigboxUserHelper::getUser()->billinglastname;

		$this->product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($this->productId);

	}

	/**
	 * @param int $productId
	 * @return ConfigboxViewReviewform
	 */
	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}
	
}