<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalcmatrix extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admincalcmatrices';

	/**
	 * @var int
	 */
	public $matrixId;

	/**
	 * @var boolean Tells if the view shows a table from initialized data
	 */
	public $isNew;

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @var string[] Names of all calculations (IDs as key), suitable for dropdowns
	 */
	public $calculations;

	/**
	 * @var string[] Titles of all questions (IDs as key), suitable for dropdowns
	 */
	public $questionTitles;

	/**
	 * @var boolean Indicates if the rows use questions that have answers
	 */
	public $rowUsesAnswers;

	/**
	 * @var boolean Indicates if the columns use questions that have answers
	 */
	public $columnUsesAnswers;

	/**
	 * @var object[] Answers for the row's question (if row uses question and has answers)
	 */
	public $rowAnswers;

	/**
	 * @var object[] Answers for the column's question (if column uses question and has answers)
	 */
	public $columnAnswers;

	/**
	 * @var string[][] Holds matrix data. First array keys are column value, second are row values
	 */
	public $matrixValues;

	/**
	 * @var string question|calculation
	 */
	public $columnType;

	/**
	 * @var int
	 */
	public $columnQuestionId = -1;

	/**
	 * @var int
	 */
	public $columnCalculationId = -1;

	/**
	 * @var string question|calculation
	 */
	public $rowType;

	/**
	 * @var int
	 */
	public $rowQuestionId = -1;

	/**
	 * @var int
	 */
	public $rowCalculationId = -1;

	/**
	 * @return ConfigboxModelAdmincalcmatrices
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalcmatrices');
	}

	function getJsInitCallsOnce() {
		$js = parent::getJsInitCallsOnce();

		$js[] = 'configbox/calcmatrix::initMatrixViewOnce';

		return $js;
	}

	function getJsInitCallsEach() {
		$js = parent::getJsInitCallsEach();

		$js[] = 'configbox/calcmatrix::initMatrixViewEach';

		return $js;
	}

	function prepareTemplateVars() {

		// Get and prepare the id
		if (empty($this->matrixId)) {
			$this->matrixId = KRequest::getInt('id',0);
		}

		// Load the matrix values
		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__configbox_calculation_matrices_data` WHERE `id` = ".intval($this->matrixId)." ORDER BY `ordering`, `y`, `x`";
		$db->setQuery($query);
		$values = $db->loadAssocList();

		// Group them
		$this->matrixValues = array();
		foreach ($values as $value ) {
			$this->matrixValues[$value['y']][$value['x']] = $value['value'];
		}

		// Set init values for matrix values (So that a table with a few empty rows and columns appear
		// Makes it easier to understand the table for a first-time user
		if (count($this->matrixValues) == 0) {
			$this->matrixValues[0] = array('','','','');
			$this->matrixValues[1] = array('','','','');
			$this->matrixValues[2] = array('','','','');
		}

		// Prepare the record data
		$model = $this->getDefaultModel();
		$this->record = $model->getRecord($this->matrixId);
		if (!$this->record) {
			$this->record = $model->initData();
			$this->isNew = true;
		}
		else {
			$this->isNew = false;
		}

		$this->columnType = $this->record->column_type;
		// It should init to 'none', but just in case
		if ($this->columnType == '') {
			$this->columnType = 'none';
		}
		$this->columnQuestionId = $this->record->column_element_id;
		$this->columnCalculationId = $this->record->column_calc_id;

		if ($this->columnQuestionId) {
			$this->columnAnswers = $model->getAnswerDropdownData($this->columnQuestionId);
		}
		else {
			$this->columnAnswers = array();
		}

		$this->rowType = $this->record->row_type;
		// It should init to 'none', but just in case
		if ($this->rowType == '') {
			$this->rowType = 'none';
		}
		$this->rowQuestionId = $this->record->row_element_id;
		$this->rowCalculationId = $this->record->row_calc_id;

		if ($this->rowQuestionId) {
			$this->rowAnswers = $model->getAnswerDropdownData($this->rowQuestionId);
		}
		else {
			$this->rowAnswers = array();
		}

		$this->columnUsesAnswers = ($this->columnType == 'question' && count($this->columnAnswers) > 1);
		$this->rowUsesAnswers = ($this->rowType == 'question' && count($this->rowAnswers) > 1);

		$this->properties = $model->getProperties();

		// Get the calculations for the calculation axis picker
		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');
		$calculations = $calcModel->getRecords(array('admincalculations.product_id'=>$this->productId));

		// Prepare the calculations for the axis picker
		$this->calculations = array(KText::_('Select a calculation'));
		foreach ($calculations as $calculation) {

			// Skip the calculation that is the one we load right now
			if ($calculation->id == $this->matrixId) {
				continue;
			}

			$this->calculations[$calculation->id] = $calculation->name;

		}

		// Prepare the questions for the axis picker
		$questionModel = KenedoModel::getModel('ConfigboxModelAdminelements');
		$questions = $questionModel->getRecords(array('adminpages.product_id'=>$this->productId));

		$this->questionTitles = array(KText::_('Select a question'));
		foreach ($questions as $id => $question) {
			if (CbSettings::getInstance()->get('use_internal_question_names')) {
				$title = (!empty($question->internal_name)) ? $question->internal_name : $question->title;
			}
			else {
				$title = $question->title;
			}

			$this->questionTitles[$question->id] = $title;
		}
		unset($question);
		unset($questions);

		// Add view CSS classes
		$this->addViewCssClasses();

	}

	/**
	 * Set the matrix ID used in the view (always the same as the parent calculation ID)
	 * @param int $matrixId
	 * @return $this
	 */
	function setMatrixId($matrixId) {
		$this->matrixId = $matrixId;
		return $this;
	}

	/**
	 * Set the product id used for this matrix
	 * @param int $productId
	 * @return $this
	 */
	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}

}
