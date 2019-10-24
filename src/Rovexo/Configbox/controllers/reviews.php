<?php
class ConfigboxControllerReviews extends KenedoController {

	/**
	 * @return ConfigboxModelReviews $model
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelReviews');
	}

	/**
	 * @return ConfigboxViewReviews
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewReviews');
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

	/**
	 * Stores a review. Form data normally comes from a form in ConfigboxViewReview and sent via an XHR.
	 * See CSS class .trigger-send-review for event handler that does the request.
	 *
	 * @throws Exception
	 */
	public function storeReview() {

		$model = KenedoModel::getModel('ConfigboxModelReviews');

		$productId = KRequest::getInt('productId');
		$name = KRequest::getString('name');
		$comment = KRequest::getString('comment');
		$rating = KRequest::getFloat('rating');

		$storeSuccess = $model->storeReview($productId, $name, $comment, $rating);

		if ($storeSuccess) {

			$model->notifyOnReview($productId, 'product', $name, $comment, $rating);

			echo json_encode(array(
				'success' => true,
				'feedback' => KText::_('Thank you for your review. Please allow some time for us to process your review.'),
			));

		}
		else {

			echo json_encode(array(
				'success' => true,
				'feedback' => KText::_('An error has occured. Please try again later!'),
			));

		}

	}

}