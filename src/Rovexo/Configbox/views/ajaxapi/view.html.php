<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAjaxapi extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'ajaxapi';

	/**
	 * @var object[]|array[] For 'getstateselectoptions' and 'getcountyselectoptions' task. Holds the data for the select options.
	 */
	public $data;

	/**
	 * @var int For 'getstateselectoptions' and 'getcountyselectoptions' task. Value of the option that should be marked as selected.
	 */
	public $selectedId;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function display() {
		
		$task = strtolower(KRequest::getKeyword('task',''));

		switch ($task) {
			
			case 'getstateselectoptions':
				$this->selectedId = KRequest::getString('selected_id');
				$this->data = ConfigboxCountryHelper::getStateSelectOptions($this->selectedId);
				$template = 'stateselectoptions';
				$this->renderView($template);
				break;
			
			case 'getcountyselectoptions':
				$this->selectedId = KRequest::getString('selected_id');
				$this->data = ConfigboxCountryHelper::getCountySelectOptions($this->selectedId);
				$template = 'stateselectoptions';
				$this->renderView($template);
				break;
			
			case 'validateregex':
				$this->renderView('validateregex');
				break;

			case 'getcityinput':
				echo ConfigboxCountryHelper::getCityInputField( KRequest::getString('name'), KRequest::getString('selected_id'), KText::_('Select City'), KRequest::getInt('county_id'), KRequest::getString('city_name') );
				break;
			
		}
		
	}
	
}
