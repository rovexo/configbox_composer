<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCustomerform extends KenedoController {

	/**
	 * @return ConfigboxModelCustomerForm
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelCustomerForm');
	}

	/**
	 * @return ConfigboxViewCustomerform
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewForm();
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewCustomerform
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewCustomerform');
	}

	/**
	 * Takes country_id as request parameter and renders the list of states in JSON
	 */
	function getStates() {
		$countryId = KRequest::getInt('country_id');

		if ($countryId) {
			$model = $this->getDefaultModel();
			$states = $model->getStates($countryId);
		}
		else {
			$states = array();
		}

		echo json_encode($states);

	}

	/**
	 * Takes state_id as request parameter and renders the list of counties in JSON
	 */
	function getCounties() {
		$stateId = KRequest::getInt('state_id');

		if ($stateId) {
			$model = $this->getDefaultModel();
			$counties = $model->getCounties($stateId);
		}
		else {
			$counties = array();
		}

		echo json_encode($counties);

	}

	/**
	 * Takes county_id as request parameter and renders the list of cities in JSON
	 */
	function getCities() {
		$countyId = KRequest::getInt('county_id');

		if ($countyId) {
			$model = $this->getDefaultModel();
			$cities = $model->getCities($countyId);
		}
		else {
			$cities = array();
		}

		echo json_encode($cities);

	}

}
