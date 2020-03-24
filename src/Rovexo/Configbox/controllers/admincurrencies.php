<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincurrencies extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincurrencies
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincurrencies');
	}

	/**
	 * @return ConfigboxViewAdmincurrencies
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmincurrencies
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmincurrencies');
	}

	/**
	 * @return ConfigboxViewAdmincurrency
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincurrency');
	}
	
	function makeBase() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		KenedoPlatform::p()->setDocumentMimeType('application/json');

		$model = $this->getDefaultModel();
		$id = KRequest::getInt('ids');

		if ($id == 0) {
			echo ConfigboxJsonResponse::makeOne()
				->setSuccess(false)
				->setErrors([KText::_('CURRENCIES_FAILURE_MSG_MAKE_BASE_NO_RECORD_CHOSEN')])
				->toJson();
			return;
		}

		$success = $model->makeBase($id);

		if ($success == true) {
			$this->purgeCache();

			echo ConfigboxJsonResponse::makeOne()
				->setSuccess($success)
				->setCustomData('messages', [KText::_('CURRENCIES_SUCCESS_MSG_MAKE_BASE')])
				->toJson();

		}
		else {
			echo ConfigboxJsonResponse::makeOne()
				->setSuccess($success)
				->setCustomData('messages', [KText::_('CURRENCIES_FAILURE_MSG_MAKE_BASE')])
				->toJson();

		}

	}
	
	function makeDefault() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();
		$id = KRequest::getInt('ids');

		KenedoPlatform::p()->setDocumentMimeType('application/json');

		if ($id == 0) {
			echo ConfigboxJsonResponse::makeOne()
				->setSuccess(false)
				->setErrors([KText::_('CURRENCIES_FAILURE_MSG_MAKE_DEFAULT_NO_RECORD_CHOSEN')])
				->toJson();
			return;
		}

		$success = $model->makeDefault($id);
		if ($success == true) {
			$this->purgeCache();

			echo ConfigboxJsonResponse::makeOne()
				->setSuccess($success)
				->setCustomData('messages', [KText::_('CURRENCIES_SUCCESS_MSG_MAKE_DEFAULT')])
				->toJson();

		}
		else {
			echo ConfigboxJsonResponse::makeOne()
				->setSuccess($success)
				->setCustomData('messages', [KText::_('CURRENCIES_FAILURE_MSG_MAKE_DEFAULT')])
				->toJson();

		}
	}
	
}
