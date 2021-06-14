<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAjaxapi extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAjaxapi
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAjaxapi');
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

	function getStateSelectOptions() {
		$this->display();
	}
	
	function getCountySelectOptions() {
		$this->display();
	}
	
	function getCityInput() {
		$this->display();
	}
	
	function validateRegex() {
		$this->display();
	}

	/**
	 * Takes a question_id and returns a JSON encoded array with hsc-encoded dropdown option data
	 */
	function getAnswerDropdownData() {

		$questionId = KRequest::getInt('question_id');

		$model = KenedoModel::getModel('ConfigboxModelAdmincalcmatrices');
		$answers = $model->getAnswerDropdownData($questionId);
		foreach ($answers as &$answer) {
			$answer = hsc($answer);
		}
		echo json_encode($answers);
	}

	function getNotificationUrl() {
		
		$paymentClassName = KRequest::getKeyword('payment_class');
		$url = 'index.php?option=com_configbox&controller=ipn&task=processipn&connector_name='.$paymentClassName;
		$route = KLink::getRoute($url, false);
		$url = KenedoPlatform::p()->getUrlBase() . $route;
		
		echo $url;
		
	}
	

}
