<?php
class ConfigboxQuestion {

	/**
	 * @var ConfigboxAnswer[] $answers
	 */
	public $answers = array();

	/**
	 * @var string $question_type Tells what type of question this is
	 */
	public $question_type;
	public $id;
	public $page_id;
	public $el_image;
	public $el_image_href;
	public $el_image_path;
	public $required;

	/**
	 * @var string hide|grey_out
	 */
	public $display_while_disabled;

	/**
	 * @var string none|select_default|select_any
	 */
	public $behavior_on_activation;

	/**
	 * @var string silent|confirm
	 */
	public $behavior_on_changes;

	/**
	 * @var string deselect|replace_with_default|replace_with_any
	 */
	public $behavior_on_inconsistency;
	public $validate;
	public $minval;
	public $maxval;
	public $calcmodel;
	public $calcmodel_recurring;
	public $multiplicator;
	public $published;
	public $ordering;
	public $asproducttitle;
	public $default_value;
	public $show_in_overview;
	public $text_calcmodel;
	public $element_custom_1;
	public $element_custom_2;
	public $element_custom_3;
	public $element_custom_4;
	public $element_custom_translatable_1;
	public $element_custom_translatable_2;
	public $rules;
	public $internal_name;
	public $element_css_classes;
	public $calcmodel_id_min_val;
	public $calcmodel_id_max_val;
	public $upload_extensions;
	public $upload_mime_types;
	public $upload_size_mb;
	public $slider_steps;
	public $unit;
	public $calcmodel_weight;
	public $choices;

	public $applies;
	public $cssClasses;
	public $extraAttributes;
	public $type;
	public $option;
	public $title;
	public $description;
	public $picker_table;
	public $prefill_on_init;
	/**
	 * @var int 0|1 If question controls a shapediver parameter
	 */
	public $is_shapediver_control;
	/**
	 * @var string Shapediver parameter ID. Can be set when question is not of type upload and user has set a parameter ID
	 */
	public $shapediver_parameter_id;

	/**
	 * @var string Shadpediver geometry name. Can be set when question is of type upload and user has set a texture name
	 */
	public $shapediver_geometry_name;

	/**
	 * @var string heading|label|none
	 */
	public $title_display;

	/**
	 * @var int $desc_display_method See setting in element for description.
	 */
	public $desc_display_method;

	/**
	 * @var string $elementImageCssClasses CSS classes to be set to the element image (see GUI for what that is).
	 * Set in ConfigboxViewConfiguratorpage::display()
	 * @see ConfigboxViewConfiguratorpage::display
	 */
	public $elementImageCssClasses;

	/**
	 * @var string $elementImagePreloadAttributes HTML attributes for the element image (used for smart preloading/delayed loading)
	 * ConfigboxViewConfiguratorpage::display()
	 * @see ConfigboxViewConfiguratorpage::display
	 */
	public $elementImagePreloadAttributes;

	/**
	 * @var string $elementImageSrc Full URL of the element image. Set in ConfigboxViewConfiguratorpage::display()
	 * @see ConfigboxViewConfiguratorpage::display
	 */
	public $elementImageSrc;

	/**
	 * @var string $disabled HTML attribute, used in input elements. Set in ConfigboxViewConfiguratorpage::prepareTemplateVars()
	 * @see ConfigboxViewConfiguratorpage::prepareTemplateVars
	 */
	public $disabled;

	/**
	 * @var string $templateFile Full path to the template file. Depends on element's settings and existing overrides.
	 * Figured out in ConfigboxViewConfiguratorpage::display()
	 * @see ConfigboxViewConfiguratorpage::display
	 */
	public $templateFile;

	/**
	 * @var bool $disableControl Indicates if the form control should be disabled
	 */
	public $disableControl;

	/**
	 * @var string $input_restriction Says what kind of selection you can make ('plaintext', 'integer', 'decimal')
	 */
	public $input_restriction;

	/**
	 * @var ConfigboxQuestion[] Holds the instances for getQuestion
	 * @see getQuestion
	 */
	static $questions;

	/**
	 * Factory pattern method to get the right question object
	 *
	 * @param int $questionId Question id
	 * @return ConfigboxQuestion (actually sub type of it)
	 * @throws Exception if question can't be found
	 */
	static function getQuestion($questionId) {

		if (!isset(self::$questions[$questionId])) {

			$questionData = ConfigboxCacheHelper::getElementData($questionId);

			if (!$questionData) {
				KLog::log('ConfigboxQuestion::getQuestion called with ID '. $questionId.', question does not exist. May have been deleted', 'error');
				throw new Exception('Question with ID "' . $questionId . '" requested, but this question does not exist.');
			}

			$className = 'ConfigboxQuestion'.ucfirst($questionData->question_type);

			if (class_exists($className) == false) {
				$filename = KPATH_DIR_CB.DS.'classes'.DS.'question_types'.DS.$className.'.php';
				if (is_file($filename)) {
					require_once($filename);
				}
			}

			if (class_exists($className) == false) {
				$filename = KenedoPlatform::p()->getDirCustomization().DS.'question_types'.DS.$className.'.php';
				if (is_file($filename)) {
					require_once($filename);
				}
			}

			if (class_exists($className) == true) {
				self::$questions[$questionId] = new $className($questionId);
			}
			else {
				self::$questions[$questionId] = new ConfigboxQuestion($questionId);
			}

		}

		return self::$questions[$questionId];

	}

	/**
	 * @param int $questionId
	 * @return bool
	 */
	static function questionExists($questionId) {
		$assignments = ConfigboxCacheHelper::getAssignments();
		return (isset($assignments['element_to_product'][$questionId]));
	}

	function __construct($id) {
		$this->id = $id;
		$this->loadData();
		$this->loadAnswers();
	}

	/**
	 * Runs before ConfigboxConfiguration makes a selection (not when doing sim selections, mind you)
	 * Mind you can alter the $selection here. ConfigboxConfiguration will use whatever you change it to
	 *
	 * @param string $selection
	 * @param string|null $prevSelection
	 * @param int $cartPositionId
	 *
	 * @see ConfigboxConfiguration::setSelection()
	 */
	function onBeforeSetSelection(&$selection, $prevSelection, $cartPositionId) {

		if ($this->getType() == 'upload') {

			if ($prevSelection) {

				$oldData = json_decode($prevSelection, true);

				KLog::log('Prev selection was: '. var_export($oldData, true), 'custom_uploads');

				if (isset($oldData['path']) && is_file($oldData['path'])) {
					KLog::log('Deleting old file', 'custom_uploads');
					unlink($oldData['path']);
				}

			}

			if ($selection != '') {

				$data = json_decode($selection, true);

				$newFilename = $cartPositionId.'-'.$this->id.'-'.rand(0,1000).'.'.KenedoFileHelper::getExtension($data['name']);

				$data['path'] = CONFIGBOX_DIR_CONFIGURATOR_FILEUPLOADS .DS. $newFilename;
				$data['url'] = CONFIGBOX_URL_CONFIGURATOR_FILEUPLOADS .DS. $newFilename;

				if (!empty($_FILES['file'])) {
					KLog::log('Storing uploaded file', 'custom_uploads');
					move_uploaded_file($_FILES['file']['tmp_name'], $data['path']);
				}

				KLog::log('New selection selection data will be: '. var_export($data, true), 'custom_uploads');

				$selection = json_encode($data);


			}


		}

	}

	/**
	 * Runs after ConfigboxConfiguration makes a selection (not when doing sim selections, mind you)
	 * @param string $selection
	 * @param string|null $prevSelection
	 * @param int $cartPositionId
	 *
	 * @see ConfigboxConfiguration::setSelection()
	 */
	function onAfterSetSelection($selection, $prevSelection, $cartPositionId) {

	}

	/**
	 * Gets you the current price of the question as float
	 *
	 * @param bool $getNet
	 * @param bool $inBaseCurrency
	 *
	 * @return float
	 */
	function getPrice($getNet = NULL, $inBaseCurrency = false) {
		return ConfigboxPrices::getElementPrice($this->id, $getNet, $inBaseCurrency);
	}

	/**
	 * Gets you the current recurring price of the question as float
	 *
	 * @param bool $getNet
	 * @param bool $inBaseCurrency
	 *
	 * @return float
	 */
	function getPriceRecurring($getNet = NULL, $inBaseCurrency = false) {
		return ConfigboxPrices::getElementPriceRecurring($this->id, $getNet, $inBaseCurrency);
	}

	/**
	 * Gets you the current weight of the question as float
	 *
	 * @return float
	 */
	function getWeight() {
		return ConfigboxPrices::getElementWeight($this->id);
	}

	function getStorableValue($selection) {

		switch ($this->getType()) {

			case 'upload':

				return json_encode($selection);

			default:

				return $selection;

		}

	}

	function getComparableValue($selection) {

		if ($selection === null) {
			return null;
		}

		switch ($this->getType()) {

			case 'calendar':

				$timestamp = strtotime($selection);
				if (is_int($timestamp)) {
					return $timestamp;
				}
				else {
					throw new Exception('Dealing with a bad date time "'.$selection.'".');
				}

			case 'upload':

				return ($selection) ? 1 : 0;


			default:

				if (in_array($this->input_restriction, array('integer', 'decimal'))) {
					return floatval($selection);
				}
				else {
					return strval($selection);
				}

		}

	}

	/**
	 *
	 * This method outputs the selection text for the element as it should be displayed in overviews Can be HTML
	 * This can be the selected option's title, a localized and formatted date, the link to the file upload etc.
	 *
	 * @param string $selection
	 * @return string Output value of the question's current selection
	 */
	function getOutputValue($selection = null) {

		if ($selection === null) {
			$selection = ConfigboxConfiguration::getInstance()->getSelection($this->id);
		}

		switch ($this->getType()) {

			case 'calendar':

				if ($selection) {
					return KenedoTimeHelper::getFormattedOnly($selection, KText::_('CALENDAR_DATE_FORMAT_PHP'));
				}
				else {
					return KText::_('No date selected');
				}
				break;

			case 'upload':

				if ($selection) {
					$fileInfo = json_decode($selection, true);
					return $fileInfo['name'];
				}
				else {
					return KText::_('No file uploaded');
				}
				break;


			default:

				if ($selection === null) {
					return '';
				}

				if (count($this->answers) && !empty($this->answers[$selection])) {
					return $this->answers[$selection]->title;
				}

				if (in_array($this->input_restriction, array('integer', 'decimal'))) {
					$selection = str_replace('.', KText::_('DECIMAL_MARK','.'), $selection);
					if ($this->unit) {
						$selection .= $this->unit;
					}
				}

				return $selection;

		}

	}

	/**
	 * @return string
	 */
	function getInitialValue() {

		if (count($this->answers)) {
			foreach ($this->answers as $answer) {
				if ($answer->default == 1 && $answer->published == 1) {
					return $answer->id;
				}
			}
		}

		if ($this->prefill_on_init == 1) {
			if ($this->default_value) {
				if ($this->getType() == 'calendar') {
					return KenedoTimeHelper::getNormalizedTime($this->default_value, 'datetime');
				}
				else {
					return $this->default_value;
				}
			}
		}

		return null;

	}

	/**
	 * Returns if the provided value can be set for the element
	 * On file uploads, the info is taken from $_FILES
	 *
	 * @param mixed $value Raw value as sent via XHR
	 * @return boolean|string true or a localized user message containing the reason.
	 */
	function isValidValue($value) {

		if ($this->getType() == 'upload') {

			$extensions = strtolower($this->upload_extensions);
			$extensions = str_replace(',', ' ', $extensions);
			$extensions = str_replace('.', '', $extensions);
			$extensions = str_replace('  ', ' ', $extensions);
			$extensions = explode(' ',$extensions);
			$validExtensions = array_map('trim',$extensions);

			if ($key = array_search('php',$validExtensions)) {
				unset($validExtensions[$key]);
			}

			$mimeTypes = strtolower($this->upload_mime_types);
			$mimeTypes = str_replace(',', ' ', $mimeTypes);
			$mimeTypes = str_replace('.', '', $mimeTypes);
			$mimeTypes = str_replace('  ', ' ', $mimeTypes);
			$mimeTypes = explode(' ',$mimeTypes);
			$validMimeTypes = array_map('trim',$mimeTypes);

			$file = !empty($_FILES['file']) ? $_FILES['file'] : NULL;

			if ($file == NULL) {
				return true;
			}

			$fileExtension = substr(strrchr($file['name'],'.'),1);
			if (!in_array($fileExtension,$validExtensions)) {
				$response = KText::sprintf('Files with extension %s are not allowed.',$fileExtension);
				return $response;
			}

			$fileMimeType = KenedoFileHelper::getMimeType($file['tmp_name']);
			if ($fileMimeType) {
				if (!in_array($fileMimeType,$validMimeTypes)) {
					$response = KText::sprintf('Files with MIME type %s are not allowed.',$fileMimeType);
					return $response;
				}
			}

			if ($this->upload_size_mb != 0) {
				$validFilesizeBytes = $this->upload_size_mb * 1024 * 1024;
				if ( filesize($file['tmp_name']) > $validFilesizeBytes ) {
					$response = KText::sprintf('File size is over the maximum of %s MB.', $this->upload_size_mb);
					return $response;
				}
			}

			return true;
		}

		if ($this->isValueTooLow($value)) {
			return $this->getValidationMessage($this->getMinimumValue(), false);
		}

		if ($this->isValueTooHigh($value)) {
			return $this->getValidationMessage($this->getMaximumValue(), true);
		}

		return true;

	}

	/**
	 * @return float|null A number with the minimum or NULL if there is no minimum
	 */
	function getMinimumValue() {

		if ($this->calcmodel_id_min_val) {
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$minVal = ConfigboxCalculation::calculate($this->calcmodel_id_min_val, $this->id, NULL, $selections);

			if ($minVal === NULL or $minVal === '') {
				$minVal = NULL;
			}
		}
		else {
			$minVal = $this->minval;
			if ($minVal === NULL or $minVal === '') {
				$minVal = NULL;
			}
		}

		return $minVal;

	}

	/**
	 * @return float|null A number with the maximum or NULL if there is no maximum
	 */
	function getMaximumValue() {

		if ($this->calcmodel_id_max_val) {
			$selections = ConfigboxConfiguration::getInstance()->getSelections();
			$maxVal = ConfigboxCalculation::calculate($this->calcmodel_id_max_val, $this->id, NULL, $selections);
			if ($maxVal === NULL or $maxVal === '') {
				$maxVal = NULL;
			}
		}
		else {
			$maxVal = $this->maxval;
			if ($maxVal === NULL or $maxVal === '') {
				$maxVal = NULL;
			}
		}

		return $maxVal;

	}

	function isValueTooLow($value) {

		if ($this->validate == false) {
			return false;
		}

		$minimumValue = $this->getMinimumValue();

		if ($minimumValue === NULL) {
			return false;
		}

		$value = $this->getComparableValue($value);
		$minimumValue = $this->getComparableValue($minimumValue);

		if ($value < $minimumValue) {
			return true;
		}
		else {
			return false;
		}

	}

	function isValueTooHigh($value) {

		if ($this->validate == false) {
			return false;
		}

		$maximumValue = $this->getMaximumValue();

		if ($maximumValue === NULL) {
			return false;
		}

		$value = $this->getComparableValue($value);
		$maximumValue = $this->getComparableValue($maximumValue);

		if ($value > $maximumValue) {
			return true;
		}
		else {
			return false;
		}
	}

	function getValidationMessage($limitValue, $tooHigh = true) {

		if ($this->getType() == 'calendar') {
			if ($tooHigh) {
				$response = KText::sprintf('The latest date is %s.', $this->getOutputValue($limitValue));
			}
			else {
				$response = KText::sprintf('The earliest date is %s.', $this->getOutputValue($limitValue));
			}
		}
		else {
			if ($tooHigh) {
				$response = KText::sprintf('Value must be %s or less.', $this->getOutputValue($limitValue));
			}
			else {
				$response = KText::sprintf('Value must be %s or more.', $this->getOutputValue($limitValue));
			}
		}

		return $response;
	}

	function loadData() {

		$data = ConfigboxCacheHelper::getElementData($this->id);

		if (!$data) {
			KLog::log('Element with ID "'.$this->id.'" (Type of ID: '.var_export($this->id, true).') not found. Backtrace: '.var_export(debug_backtrace(false), true), 'error');
			return false;
		}

		foreach ($data as $k => &$v) {
			$this->$k = $v;
		}
		return true;

	}

	function loadAnswers() {

		// Get answer ids that belong to the question
		$assignments = ConfigboxCacheHelper::getAssignments();
		$answerIds = isset($assignments['element_to_xref'][$this->id]) ? $assignments['element_to_xref'][$this->id] : array();

		// Put the anwser objects in the answers var
		$this->answers = array();
		foreach ($answerIds as $answerId) {
			$answerData = ConfigboxCacheHelper::getAnswerData($answerId);
			$this->answers[$answerId] = new ConfigboxAnswer($answerData);
		}

	}

	function getType() {
		return $this->question_type;
	}

	function applies() {
		return ConfigboxRulesHelper::ruleIsFollowed($this->rules,'element',$this->id);
	}

	function getField($path, $regardingAnswerId = NULL, $default = NULL) {

		KLog::log('Getting element field for element "'.$this->id.'". Path is "'.$path.'"');

		if ($default === NULL) {
			$default = 0;
		}

		if (strstr($path,'.')) {
			$attributePath = explode('.',$path);
			$obj = $this;

			foreach ($attributePath as $attributeCrumb) {

				if (strtolower($attributeCrumb) == 'regardingoption' && $regardingAnswerId) {
					unset($obj); // Unset to avoid overwriting question or answer data
					$obj = $this->answers[$regardingAnswerId];
					continue;
				}

				if (strtolower($attributeCrumb) == 'selectedoption') {

					$selection = ConfigboxConfiguration::getInstance()->getSelection($this->id);

					if ($selection && isset($this->answers[$selection])) {
						unset($obj); // Unset to avoid overwriting question or answer data
						$obj = $this->answers[$selection];
						continue;
					}

				}

				if (!empty($obj->$attributeCrumb) && (is_string($obj->$attributeCrumb) || $obj->$attributeCrumb != 0)) {
					// Replace the object (if the looping is ongoing, this will be replaced again until we reached the last one).
					$newObj = $obj->$attributeCrumb;
					unset($obj); // Unset to avoid overwriting question or answer data
					$obj = $newObj;
				}
				else {
					KLog::log('Attribute "'.$attributeCrumb.'" not found in question with ID "'.$this->id.'" or the attribute has no value. Attribute path was "'.$path.'". Using "'.$default.'" as fallback value.', 'debug');
					unset($obj); // Unset to avoid overwriting question or answer data
					$obj = $default;
					break;
				}
			}
			return $obj;
		}
		else {

			if (strtolower($path) == 'entry') {
				$entry = ConfigboxConfiguration::getInstance()->getSelection($this->id);
				return $entry;
			}

			if (isset($this->$path)) $value = $this->$path;
			else $value = $default;

			return $value;
		}

	}

	function addCssClass($class) {

		$class = trim(str_replace('  ', ' ', $class));
		$classes = explode(' ', $class);

		foreach ($classes as $class) {
			$this->cssClasses[$class] = hsc($class);
		}

	}

	function removeCssClass($class) {

		if (isset($this->cssClasses[$class])) {
			unset($this->cssClasses[$class]);
		}

	}

	function getCssClasses() {
		return filter_var(implode(' ',$this->cssClasses), FILTER_SANITIZE_SPECIAL_CHARS);
	}

	/**
	 * @deprecated Use ConfigboxConfiguration::getSelection($questionId) instead
	 * @return int|null|string
	 */
	function getRawValue() {
		$configuration = ConfigboxConfiguration::getInstance();
		$rawValue = $configuration->getSelection($this->id);
		return $rawValue;
	}

}