<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewReviews extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'reviews';

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @var ConfigboxProductData Product data
	 */
	public $product;

	/**
	 * @var object[] Reviews for the product
	 */
	public $reviews;

	/**
	 * @var int Number of reviews to be shown initially (rest shows up on click on 'more')
	 */
	public $countVisibleReviewsInitial = 3;

	/**
	 * @var bool Indicates if the user can add a review (always true, just for template forward compat)
	 */
	public $canAddReview = true;

	/**
	 * @var string HTML with the review form
	 * @see ConfigboxViewReviewform
	 */
	public $formHtml;

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
		$calls[] = 'configbox/reviews::initReviewsPage';
		return $calls;
	}

	function prepareTemplateVars() {

		if (empty($this->productId)) {
			$this->productId = KRequest::getInt('product_id');
		}

		$this->reviews = $this->getDefaultModel()->getReviews($this->productId);
		$this->product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($this->productId);

		$this->formHtml = self::getView('ConfigboxViewReviewform')->setProductId($this->productId)->getHtml();

		$i = 1;
		foreach ($this->reviews as $review) {

			// Trim any leading or trailing line breaks from comment
			$review->comment = trim($review->comment);

			if ($i > $this->countVisibleReviewsInitial) {
				$review->wrapperClass = 'review review-excess';
			}
			else {
				$review->wrapperClass = 'review';
			}
			$i++;
		}

	}

	/**
	 * @param int $productId
	 * @return ConfigboxViewReviews
	 */
	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}
	
}