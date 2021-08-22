<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincalcmatrices extends KenedoModel {

	function getTableName() {
		return '#__configbox_calculation_matrices';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'canSortBy'=>true,
			'positionList'=>1,
			'positionForm'=>10,
		);

		$propDefs['column_element_id'] = array(
			'name'=>'column_element_id',
			'label'=>KText::_('Question for columns'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Question'),
			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',

			'required'=>0,

			'positionForm'=>20,
		);

		$propDefs['row_element_id'] = array(
			'name'=>'row_element_id',
			'label'=>KText::_('Question for Rows'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Question'),
			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',

			'required'=>0,

			'positionForm'=>30,
		);

		$goInternal = CbSettings::getInstance()->get('use_internal_question_names');
		if ($goInternal) {
			$propDefs['column_element_id']['propNameDisplay'] = 'internal_name';
			$propDefs['row_element_id']['propNameDisplay'] = 'internal_name';
		}

		$propDefs['column_calc_id'] = array(
			'name'=>'column_calc_id',
			'label'=>KText::_('Calculation for columns'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',
			'positionForm'=>40,

		);

		$propDefs['row_calc_id'] = array(
			'name'=>'row_calc_id',
			'label'=>KText::_('Calculation for rows'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',
			'positionForm'=>50,

		);

		$propDefs['row_type'] = array(
			'name'=>'row_type',
			'label'=>'Row parameter type',
			'type'=>'string',
			'default'=>'none',
			'positionForm'=>60,
		);

		$propDefs['column_type'] = array(
			'name'=>'column_type',
			'label'=>'Column parameter type',
			'type'=>'string',
			'default'=>'none',
			'positionForm'=>70,
		);

		$propDefs['advanced_start'] = array(
			'name'=>'advanced_start',
			'type'=>'groupstart',
			'toggle'=>true,
			'defaultState'=>'closed',
			'title'=>KText::_('Advanced Settings'),
			'positionForm'=>80,
		);

		$propDefs['lookup_value'] = array(
			'name'=>'lookup_value',
			'label'=>KText::_('Lookup Value'),
			'type'=>'dropdown',
			'choices'=> array(0=>KText::_('Exact Value'),1=>KText::_('Next higher value'), 2=>KText::_('Next lower value')),
			'default'=>1,
			'tooltip'=>KText::_("This setting is used when a value lies between two rows or columns. With Next higher value, an customer entry of 20 will make the table look for the exact value in a column or row, if not found it looks up the next higher value - e.g. 30. The setting Round Values is ignored unless you use exact value."),
			'positionForm'=>90,
		);

		$propDefs['round'] = array(
			'name'=>'round',
			'label'=>KText::_('Round Values to'),
			'type'=>'string',
			'stringType'=>'number',
			'size'=>'50',
			'default'=>1,
			'required'=>0,
			'tooltip'=>KText::_("This setting rounds the customer values up. Use 1 for rounding to integers, 10 for rounding to full tens and so forth."),
			'positionForm'=>100,
		);

		$propDefs['multiplicator'] = array(
			'name'=>'multiplicator',
			'label'=>KText::_('Multiplicator'),
			'type'=>'string',
			'stringType'=>'number',
			'default'=>1,
			'tooltip'=>KText::_('The output of the calculation model will be multiplied by this value. With this feature you can enter your supplier prices and automaticly add your margin. Please use no separator for thousands.'),
			'positionForm'=>110,
		);

		$propDefs['multielementid'] = array(
			'name'=>'multielementid',
			'label'=>KText::_('Question Selection Multiplier'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_SELECTION_MULTIPLIER'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('No multiplier'),
			'groupby'=>'joinedby_page_id_to_adminpages_product_id_display_value',

			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',

			'required'=>0,

			'positionForm'=>120,
		);

		$propDefs['calcmodel_id_multi'] = array(
			'name'=>'calcmodel_id_multi',
			'label'=>KText::_('Calculated multiplier'),
			'type'=>'join',
			'tooltip'=>KText::_('When you set a calculation here, the calculated value will be used as multiplicator.'),

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>130,

		);

		$propDefs['advanced_end'] = array(
			'name'=>'advanced_end',
			'type'=>'groupend',
			'positionForm'=>140,
		);

		return $propDefs;

	}

	function afterStore($id, $wasInsert) {
		$success = $this->storeMatrixValues($id, KRequest::getString('matrix'));
		return $success;
	}

	function afterDelete($id) {
		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_calculation_matrices_data` WHERE `id` = ".intval($id);
		$db->setQuery($query);
		$db->query();
		return true;
	}

	protected function storeMatrixValues($id, $valuesJson) {

		$rows = json_decode($valuesJson);

		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_calculation_matrices_data` WHERE `id` = ".intval($id);
		$db->setQuery($query);
		$db->query();

		if (count($rows) == 0) {
			return true;
		}

		$values = array();

		foreach ($rows as $row) {
			$values[] = "(".intval($id) .", ". floatval($row->x).", ".floatval($row->y).", ".floatval($row->value).", ".intval($row->ordering).")";
		}

		$query = "INSERT INTO `#__configbox_calculation_matrices_data` (`id`,`x`,`y`,`value`,`ordering`) VALUES ".implode(', ',$values);
		$db->setQuery($query);
		$success = $db->query();

		if (!$success) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		else {
			return true;
		}

	}

	/**
	 * Gets you answer data for a question (array, key as answer id, value title)
	 * @param int $id Question ID
	 * @param string $languageTag optional, falls back to current user language
	 * @return string[]
	 */
	function getAnswerDropdownData($id, $languageTag = NULL) {

		if ($languageTag == null) {
			$languageTag = KText::getLanguageTag();
		}

		$db = KenedoPlatform::getDb();

		if (CbSettings::getInstance()->get('use_internal_answer_names')) {
			$query = "	
			SELECT `id`, `internal_name` AS title
			FROM `#__configbox_xref_element_option`
			WHERE `element_id` = ".intval($id)."
			ORDER BY `ordering`";
			$db->setQuery($query);
			$options = $db->loadResultList('id', 'title');
		}
		else {
			$query = "	
			SELECT answer.id, s.text AS title
			FROM `#__configbox_xref_element_option` AS answer
			LEFT JOIN `#__configbox_strings` AS s ON s.key = answer.option_id AND s.type = 5 AND s.language_tag = '".$db->getEscaped($languageTag)."'
			WHERE answer.element_id = ".intval($id)."
			ORDER BY answer.ordering";
			$db->setQuery($query);
			$options = $db->loadResultList('id', 'title');
		}

		// Put a null option as first in the array
		$dropdownItems = array();
		$dropdownItems['0'] = KText::_('No selection');

		foreach ($options as $key=>$value) {
			$dropdownItems[$key] = $value;
		}

		return $dropdownItems;

	}

	function copyAcrossProducts($sourceCalcId, $copyCalcId, $copyIds) {
		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM #__configbox_calculation_matrices WHERE `id` = ".$sourceCalcId;
		$db->setQuery($query);
		$matrixData = $db->loadObject();

		$matrixData->id = $copyCalcId;

		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');

		if ($matrixData->column_element_id != null) {
			$matrixData->column_element_id = $copyIds['adminelements'][$matrixData->column_element_id];
		}

		if ($matrixData->row_element_id != null) {
			$matrixData->row_element_id = $copyIds['adminelements'][$matrixData->row_element_id];
		}

		if ($matrixData->multielementid != null) {
			$matrixData->multielementid = $copyIds['adminelements'][$matrixData->multielementid];
		}

		if ($matrixData->column_calc_id != null) {
			$matrixData->column_calc_id = $calcModel->copyAcrossProducts($matrixData->column_calc_id, $copyIds);
		}

		if ($matrixData->row_calc_id != null) {
			$matrixData->row_calc_id = $calcModel->copyAcrossProducts($matrixData->row_calc_id, $copyIds);
		}

		if ($matrixData->calcmodel_id_multi != null) {
			$matrixData->calcmodel_id_multi = $calcModel->copyAcrossProducts($matrixData->calcmodel_id_multi, $copyIds);
		}

		$db->insertObject($this->getTableName(), $matrixData, $this->getTableKey());

		$query = "SELECT * FROM `#__configbox_calculation_matrices_data` WHERE `id` = ".$sourceCalcId;
		$db->setQuery($query);
		$matrixValueRows = $db->loadAssocList();

		foreach ($matrixValueRows as &$matrixValueRow) {

			$matrixValueRow['id'] = $copyCalcId;

			if ($matrixValueRow['y'] != 0) {
				if ($matrixData->row_type == 'question') {
					if ($this->questionHasAnswers($matrixData->row_element_id)) {
						$matrixValueRow['y'] = $copyIds['adminoptionassignments'][$matrixValueRow['y']];
					}
				}
			}

			if ($matrixValueRow['x'] != 0) {
				if ($matrixData->column_type == 'question') {
					if ($this->questionHasAnswers($matrixData->column_element_id)) {
						$matrixValueRow['x'] = $copyIds['adminoptionassignments'][$matrixValueRow['x']];
					}
				}
			}

		}

		foreach ($matrixValueRows as $row) {
			$object = (object)$row;
			$db->insertObject('#__configbox_calculation_matrices_data', $object, 'id');
		}

	}

	protected static $memoHasAnswers = array();

	protected function questionHasAnswers($questionId) {
		if (!isset(self::$memoHasAnswers[$questionId])) {

			$db = KenedoPlatform::getDb();
			$query = "SELECT COUNT(*) FROM `#__configbox_xref_element_option` WHERE `element_id` = ".intval($questionId);
			$db->setQuery($query);
			self::$memoHasAnswers[$questionId] = $db->loadResult() != 0;
		}

		return self::$memoHasAnswers[$questionId];
	}
}