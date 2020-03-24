<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminoptionassignment extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminoptionassignments';

	/**
	 * @var object $option Holds the option's data object
	 * @see ConfigboxModelAdminoptions::getRecord()
	 */
	public $option;

	/**
	 * @var KenedoProperty[] Properties of the Option Type
	 * @see ConfigboxModelAdminoptions::getProperties
	 */
	public $optionProperties;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function getPageTitle() {
		return KText::_('Answer');
	}

	function getJsInitCallsOnce() {
        $calls = parent::getJsInitCallsOnce();
        $calls[] = 'configbox/adminAnswer::initAnswerViewOnce';
        return $calls;
    }

    function prepareTemplateVars() {

		$id = KRequest::getInt('id');

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);

		$model = KenedoModel::getModel('ConfigboxModelAdminoptionassignments');

		$this->pageTitle = $this->getPageTitle();
		$this->pageTasks = $model->getDetailsTasks();
		$this->recordUsage = $model->getRecordUsage($id);

		$xrefModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$optionModel = KenedoModel::getModel('ConfigboxModelAdminoptions');

		// Get the xref data
		if ($id) {
			$xref = $xrefModel->getRecord($id);
		}
		else {
			$xref = $xrefModel->initData();

			// Fill in data that we normally have on edit only (not new), we need it to show the right properties
			if (isset($xref->element_id)) {
				$ass = ConfigboxCacheHelper::getAssignments();
				$productId = $ass['element_to_product'][$xref->element_id];

				$product = KenedoModel::getModel('ConfigboxModelAdminproducts')->getRecord($productId);
				$element = KenedoModel::getModel('ConfigboxModelAdminelements')->getRecord($xref->element_id);

				$xref->is_shapediver_control = $element->is_shapediver_control;
				$xref->joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_visualization_type = $product->visualization_type;
				$xref->question_type = $element->question_type;
			}

		}

		if ($xref->option_id) {
			$option = $optionModel->getRecord($xref->option_id);
		}
		else {
			$option = $optionModel->initData();
		}

		$this->record = $xref;
		$this->option = $option;
		$this->properties = $xrefModel->getProperties();
        $this->optionProperties = $optionModel->getProperties();

		$this->addViewCssClasses();

	}
}