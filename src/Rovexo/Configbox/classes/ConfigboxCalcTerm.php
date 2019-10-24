<?php
defined('CB_VALID_ENTRY') or die();

abstract class ConfigboxCalcTerm {

	/**
	 * @var ConfigboxCalcTerm[] Holds the singleton pattern-like ConfigboxCalcTerms
	 */
	static private $instances = array();

	/**
	 * @var string[] Cache for names of all term classes (built-in and custom)
	 * @see ConfigboxCalculation::getTermClassNames
	 */
	static protected $termClassNames = array();

	/**
	 * @var bool Just indicating if the term classes are loaded already
	 * @see ConfigboxCalcTerm::loadTermClasses()
	 */
	static protected $termClassesLoaded = false;

	/**
	 * @var array Caches what class to use for each term type
	 * @see ConfigboxCalcTerm::getTerm
	 */
	static protected $typeToClassCache = array();

	/**
	 * @param string $type (Like 'Calculation')
	 * @return ConfigboxCalcTerm actually sub-class of it
	 * @throws Exception when if type's class is not found or no $type was provided
	 */
	final static function getTerm($type) {

		// Load any condition classes
		self::loadTermClasses();

		if (trim($type) == '') {
			throw new Exception('No type name provided.');
		}

		if (empty(self::$typeToClassCache[$type])) {
			$regularClass = 'ConfigboxCalcTerm'.ucfirst($type);
			$customClass = 'CustomCalcTerm'.ucfirst($type);

			if (class_exists($regularClass)) {
				$class = $regularClass;
			}
			elseif (class_exists($customClass)) {
				$class = $customClass;
			}
			else {
				throw new Exception('No class found for calculation term type "'.$type.'". Custom term class should be called "'.$customClass.'"');
			}

			self::$typeToClassCache[$type] = $class;
		}

		$className = self::$typeToClassCache[$type];

		// Instantiate in case that type isn't there yet
		if (empty(self::$instances[$className])) {
			self::$instances[$className] = new $className;
		}

		return self::$instances[$className];

	}

	/**
	 * Loads the PHP class files dealing with condition types.
	 * There are the system conditions and optionally custom ones.
	 */
	private static function loadTermClasses() {

		if (!self::$termClassesLoaded) {

			$path = KPATH_DIR_CB.DS.'classes'.DS.'calc_term_types';
			$conditionFiles = KenedoFileHelper::getFiles($path, '.php$');

			foreach ($conditionFiles as $file) {
				include_once($path.DS.$file);
			}

			$path = CONFIGBOX_DIR_CUSTOMIZATION.DS.'calc_term_types';
			if (is_dir($path)) {
				$customConditionFiles = KenedoFileHelper::getFiles($path, '.php$');
				foreach ($customConditionFiles as $file) {
					include_once($path.DS.$file);
				}
			}
			self::$termClassesLoaded = true;
		}

	}

	/**
	 * @return string[] All custom and built-in Condition class names
	 */
	public static function getTermClassNames() {

		if (empty(self::$termClassNames)) {

			self::$termClassNames = array();

			$path = KPATH_DIR_CB.DS.'classes'.DS.'calc_term_types';
			$conditionFiles = KenedoFileHelper::getFiles($path, '.php$');
			foreach ($conditionFiles as $file) {
				self::$termClassNames[] = str_replace('.php', '',$file);
			}

			$path = CONFIGBOX_DIR_CUSTOMIZATION.DS.'calc_term_types';
			if (is_dir($path)) {
				$customConditionFiles = KenedoFileHelper::getFiles($path, '.php$');
				foreach ($customConditionFiles as $file) {
					self::$termClassNames[] = str_replace('.php', '',$file);
				}
			}

		}

		return self::$termClassNames;

	}

	/**
	 * @return string[] All custom and built-in Term type names
	 */
	public static function getTermTypeNames() {
		$classNames = self::getTermClassNames();
		$typeNames = array();
		foreach ($classNames as $className) {
			$className = str_replace('ConfigboxCalcTerm', '', $className);
			$className = str_replace('CustomCalcTerm', '', $className);
			$typeNames[] = $className;
		}
		return $typeNames;
	}

	/**
	 * @return string Localized title of the term type (i.e. to be displayed in calculation formula editor tabs)
	 */
	function getTypeTitle() {
		return KText::_('TERM_TYPE_'.$this->getTypeName(), $this->getTypeName());
	}

	/**
	 * @return string The type name (e.g. Calculation, ElementAttribute etc)
	 */
	function getTypeName() {
		$className = str_replace('ConfigboxCalcTerm', '', get_class($this));
		$className = str_replace('CustomCalcTerm', '', $className);
		return $className;
	}

	/**
	 * Called by ConfigboxRulesHelper::getTermCode to get the term result
	 *
	 * @param string[] $termData
	 * @param string[] $selections
	 * @param int|NULL $regardingQuestionId The ID of the question the calculation is assigned to
	 * @param int|NULL $regardingAnswerId The ID of the answer the calculation is assigned to
	 * @param boolean $allowNonNumeric If the result can be non-numeric
	 * @return float The calculated result
	 *
	 * @see ConfigboxCalculation::getCalculationResult, ConfigboxCalculation::getTerms, ConfigboxConfiguration::getSelections
	 */
	abstract function getTermResult($termData, $selections, $regardingQuestionId = NULL, $regardingAnswerId = NULL, $allowNonNumeric = false);

	/**
	 * @param int $calculationId The ID of the calculation that loads the panel
	 * @param int $productId To filter possible terms to a certain product
	 * @return string The HTML for the type's panel in the calculation formula editor
	 */
	abstract function getTermsPanelHtml($calculationId, $productId);

	/**
	 * Called by ConfigboxCalculation::getTermHtml to display the condition (either for editing or display)
	 *
	 * @param string[] $termData
	 * @param bool $forEditing If edit controls or plain display should come out
	 * @return string HTML for that term
	 * @see ConfigboxCalculation::getTermHtml
	 */
	abstract function getTermHtml($termData, $forEditing = true);

	/**
	 * Should return true if the given element is contained in the term, false otherwise. Used for finding out
	 * if the element can be deleted.
	 *
	 * @param string[] $termData
	 * @param int $questionId
	 * @return bool True if found, false otherwise
	 * @see ConfigboxCalculation::getTerms
	 */
	function containsQuestionId($termData, $questionId) {
		// Type does not deal with elements
		return false;
	}

	/**
	 * Should return true if the given element is contained in the term, false otherwise. Used for finding out
	 * if the element can be deleted.
	 *
	 * @param string[] $termData
	 * @param int $answerId
	 * @return bool True if found, false otherwise
	 * @see ConfigboxCalculation::getTerms
	 */
	function containsAnswerId($termData, $answerId) {
		// Type does not deal with xrefs
		return false;
	}

	/**
	 * Should return true if the given calculation is contained in the term, false otherwise. Used for finding out
	 * if the element can be deleted.
	 *
	 * @param string[] $termData
	 * @param int $calculationId
	 * @return bool True if found, false otherwise
	 * @see ConfigboxCalculation::getTerms
	 */
	function containsCalculationId($termData, $calculationId) {
		return false;
	}

}