<?php
class ConfigboxControllerReviewform extends KenedoController {

	/**
	 * @return ConfigboxModelReviews $model
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelReviews');
	}

	/**
	 * @return ConfigboxViewReviewform
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewReviewform');
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

}