<?php
defined('CB_VALID_ENTRY') or die();

/**
 * This model deals with crudding answer (xref) and component (option) data together. There is a view
 * that let's users edit both xref data and option data at once.
 *
 * @see ConfigboxViewAdminoptionassignment::display, ConfigboxModelAdminxrefelementoptions, ConfigboxModelAdminoptions
 *
 * Class ConfigboxModelAdminoptionassignments
 */
class ConfigboxModelAdminoptionassignments extends KenedoModel {

	function getPropertyDefinitions() {
		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		return $xrefsModel->getPropertyDefinitions();
	}

	function getTableName() {
		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		return $xrefsModel->getTableName();
	}

	function getTableKey() {
		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		return $xrefsModel->getTableKey();
	}

	function getDetailsTasks() {
        $tasks = array(
            array('title'=>KText::_('Save and Close'), 'task'=>'store', 'primary' => true),
            array('title'=>KText::_('Save and New'), 	'task'=>'storeAndNew'),
            array('title'=>KText::_('Save'), 			'task'=>'apply'),
            array('title'=>KText::_('Cancel'), 		'task'=>'cancel'),
        );
        return $tasks;
    }

	/**
	 * Takes both xref's and option's data from the request and returns an array with both objects
	 * @return object[]
	 * @throws Exception
	 */
	function getDataFromRequest() {

		$data = array();

		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$optionsModel = KenedoModel::getModel('ConfigboxModelAdminoptions');

		$data['xref'] = $xrefsModel->getDataFromRequest();
		$data['option'] = $optionsModel->getDataFromRequest();
		$data['option']->id = $data['xref']->option_id;

		return $data;
	}

	function isInsert($data) {
		return (empty($data['xref']->id));
	}

	/**
	 * Prepares both xref and option data. Takes an array of both data objects (as opposed to the usual object).
	 *
	 * @see ConfigboxModelAdminoptionassignments::getDataFromRequest
	 * @param object[] $data
	 * @return bool
	 * @throws Exception
	 */
	function prepareForStorage($data) {
		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$optionsModel = KenedoModel::getModel('ConfigboxModelAdminoptions');

		// Fill out the answer's internal name if empty (use the option title)
		if (empty($data['xref']->internal_name)) {
			$key = 'title-'.KText::getLanguageTag();
			$data['xref']->internal_name = $data['option']->{$key};
		}

		$xrefPreparation = $xrefsModel->prepareForStorage($data['xref']);
		$optionPreparation = $optionsModel->prepareForStorage($data['option']);

		if ($xrefPreparation == false) {
			$this->setErrors($xrefsModel->getErrors());
		}
		if ($optionPreparation == false) {
			$this->setErrors($optionsModel->getErrors());
		}

		return ($xrefPreparation && $optionPreparation);
	}

	/**
	 * Validates both xref and option data. Takes an array of both data objects (as opposed to the usual object).
	 *
	 * @see ConfigboxModelAdminoptionassignments::getDataFromRequest
	 * @param object[] $data
	 * @param string $context Has no meaning for this here
	 * @return bool
	 * @throws Exception
	 */
	function validateData($data, $context = '') {

		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$optionsModel = KenedoModel::getModel('ConfigboxModelAdminoptions');

		$xrefValidation = $xrefsModel->validateData($data['xref']);
		$optionValidation = $optionsModel->validateData($data['option']);

		if ($xrefValidation == false) {
			$this->setErrors($xrefsModel->getErrors());
		}
		if ($optionValidation == false) {
			$this->setErrors($optionsModel->getErrors());
		}

		return ($xrefValidation && $optionValidation);

	}

	/**
	 * Stores both xref and option data. Takes an array of both data objects (as opposed to the usual object).
	 * @see ConfigboxModelAdminoptionassignments::getDataFromRequest
	 * @param object[] $data
	 * @return bool
	 * @throws Exception
	 */
	function store($data) {

		// Figure out if we're dealing with an insert or an update
		$isInsert = $this->isInsert($data);

		$isNewOption = (empty($data['option']->id));

		// Get the models
		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$optionsModel = KenedoModel::getModel('ConfigboxModelAdminoptions');

		// Run their store methods
		$optionStorage = $optionsModel->store($data['option']);

		if ($isNewOption) {
			$optionId = $data['option']->id;
			$data['xref']->option_id = $optionId;
		}

		$xrefStorage = $xrefsModel->store($data['xref']);


		// Set errors on issues
		if ($xrefStorage == false) {
			$this->setErrors($xrefsModel->getErrors());
		}
		if ($optionStorage == false) {
			$this->setErrors($optionsModel->getErrors());
		}

		// How did it go?
		$success = ($xrefStorage && $optionStorage);

		if ($success == false) {

			// Revert insertion on base record and any properties
			if ($isInsert) {
				$this->delete($data['xref']->id);
			}

			return false;
		}

		$success = $this->afterStore($data['xref']->id, $isInsert);

		if ($success === false) {

			if ($isInsert) {
				$this->delete($data['xref']->id);
			}

			return false;
		}

		return true;

	}

	/**
	 * @param int  $id
	 * @param bool $wasInsert
	 *
	 * @return bool
	 * @throws Exception
	 */
	function afterStore($id, $wasInsert) {

		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
		$optionsModel = KenedoModel::getModel('ConfigboxModelAdminoptions');

		$db = KenedoPlatform::getDb();
		$query = "SELECT `option_id` FROM `#__configbox_xref_element_option` WHERE `id` = ".intval($id);
		$db->setQuery($query);
		$optionId = $db->loadResult();

		$xrefStorage = $xrefsModel->afterStore($id, $wasInsert);
		$optionStorage = $optionsModel->afterStore($optionId, $wasInsert);

		if ($xrefStorage == false) {
			$this->setErrors($xrefsModel->getErrors());
		}
		if ($optionStorage == false) {
			$this->setErrors($optionsModel->getErrors());
		}

		return ($xrefStorage && $optionStorage);

	}

	/**
	 * IDs refer to the element-option-xref, not the option. Options are not deleted along with the xref.
	 * @param int|int[] $ids
	 * @return bool
	 * @throws Exception
	 */
	function delete($ids) {

		if (is_numeric($ids)) {
			$ids = array($ids);
		}

		$xrefsModel = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');

		$success = $xrefsModel->delete($ids);

		if ($success == false) {
			$this->setErrors($xrefsModel->getErrors());
			return false;
		}
		else {
			return true;
		}
	}

//	function copy($data) {
//
//		KLog::log('Unsetting calcs and rules in data', 'custom_copying');
//
//		$data->calcmodel = NULL;
//		$data->calcmodel_recurring = NULL;
//
//		$data->calcmodel_weight = NULL;
//		$data->rules = '';
//
//		$data->price_calculation_overrides = '[]';
//		$data->price_recurring_calculation_overrides = '[]';
//
//		return parent::copy($data);
//
//	}
}