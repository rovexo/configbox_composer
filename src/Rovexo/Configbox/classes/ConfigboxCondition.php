<?php
defined('CB_VALID_ENTRY') or die();

abstract class ConfigboxCondition {

	/**
	 * @var ConfigboxCondition[] Holds the singleton pattern-like ConfigboxConditions
	 */
	static private $instances = array();

	/**
	 * @var string[] Cache for names of all condition classes (built-in and custom)
	 * @see ConfigboxRulesHelper::getConditionClassNames
	 */
	static protected $conditionClassNames = array();

	/**
	 * @var bool Just indicating if the condition classes are loaded already
	 * @see ConfigboxCondition::loadConditionClasses()
	 */
	static protected $conditionClassesLoaded = false;

	/**
	 * @var array Caches what class to use for each condition type
	 * @see ConfigboxCondition::getCondition
	 */
	static protected $typeToClassCache = array();

	/**
	 * @param string $type (Like 'Calculation')
	 * @return ConfigboxCondition actually sub-class of it
	 * @throws Exception when if type's class is not found or no $type was provided
	 */
	final static function getCondition($type) {

		// Load any condition classes
		self::loadConditionClasses();

		if (trim($type) == '') {
			throw new Exception('No type name provided.');
		}

		if (empty(self::$typeToClassCache[$type])) {
			$regularClass = 'ConfigboxCondition'.ucfirst($type);
			$customClass = 'CustomCondition'.ucfirst($type);

			if (class_exists($regularClass)) {
				$class = $regularClass;
			}
			elseif (class_exists($customClass)) {
				$class = $customClass;
			}
			else {
				throw new Exception('No class found for condition type "'.$type.'". Custom condition class should be called "'.$customClass.'"');
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
	private static function loadConditionClasses() {

		if (!self::$conditionClassesLoaded) {

			$path = KPATH_DIR_CB.DS.'classes'.DS.'rule_condition_types';
			$conditionFiles = KenedoFileHelper::getFiles($path, '.php$');
			foreach ($conditionFiles as $file) {
				include_once($path.DS.$file);
			}

			$path = KenedoPlatform::p()->getDirCustomization().DS.'rule_condition_types';
			if (is_dir($path)) {
				$customConditionFiles = KenedoFileHelper::getFiles($path, '.php$');
				foreach ($customConditionFiles as $file) {
					include_once($path.DS.$file);
				}
			}
			self::$conditionClassesLoaded = true;
		}

	}

	/**
	 * @return string[] All custom and built-in Condition class names
	 */
	public static function getConditionClassNames() {

		if (empty(self::$conditionClassNames)) {

			self::$conditionClassNames = array();

			$path = KPATH_DIR_CB.DS.'classes'.DS.'rule_condition_types';
			$conditionFiles = KenedoFileHelper::getFiles($path, '.php$');
			foreach ($conditionFiles as $file) {
				self::$conditionClassNames[] = str_replace('.php', '',$file);
			}

			$path = KenedoPlatform::p()->getDirCustomization().DS.'rule_condition_types';
			if (is_dir($path)) {
				$customConditionFiles = KenedoFileHelper::getFiles($path, '.php$');
				foreach ($customConditionFiles as $file) {
					self::$conditionClassNames[] = str_replace('.php', '',$file);
				}
			}

		}

		return self::$conditionClassNames;

	}

	/**
	 * @return string[] All custom and built-in Condition type names
	 */
	public static function getConditionTypeNames() {
		$classNames = self::getConditionClassNames();
		$typeNames = array();
		foreach ($classNames as $className) {
			$className = str_replace('ConfigboxCondition', '', $className);
			$className = str_replace('CustomCondition', '', $className);
			$typeNames[] = $className;
		}
		return $typeNames;
	}

	/**
	 * @return string Localized title of the condition type (i.e. to be displayed in rule editor tabs)
	 */
	function getTypeTitle() {
		return KText::_('CONDITION_TYPE_'.$this->getTypeName(), $this->getTypeName());
	}

	/**
	 * @return string The type name (e.g. Calculation, ElementAttribute etc)
	 */
	function getTypeName() {
		$className = str_replace('ConfigboxCondition', '', get_class($this));
		$className = str_replace('CustomCondition', '', $className);
		return $className;
	}

	/**
	 * Returns the text version of the relational operator ('==' becomes 'is')
	 * @param string $operator
	 * @return string
	 * @throws Exception if $operator does not exist
	 * @see ConfigboxCondition::getOperators
	 */
	function getOperatorText($operator) {
		$operators = $this->getOperators();
		if (empty($operators[$operator])) {
			throw new Exception('Operator "'.$operator.'" not found in Condition type "'.$this->getTypeName().'".');
		}
		else {
			return $operators[$operator];
		}
	}

	/**
	 * Returns the available relation operators for that condition type (Operators are what subject and value are compared with)
	 * @return string[] Key is the machine readable relation operator, the value is the readable text
	 */
	function getOperators() {
		return array(
			'<' => KText::_('is below'),
			'<=' => KText::_('is or below'),
			'==' => KText::_('is'),
			'!=' => KText::_('is not'),
			'>=' => KText::_('is or above'),
			'>' => KText::_('is above'),
		);
	}

	/**
	 * Called by ConfigboxRulesHelper::getConditionCode to compare condition with provided selections
	 *
	 * @param string[] $conditionData
	 * @param string[] $selections
	 * @return bool true if selections meet the condition
	 *
	 * @see ConfigboxRulesHelper::getConditionsCode, ConfigboxRulesHelper::getConditions, ConfigboxRulesHelper::getSelections
	 */
	abstract function getEvaluationResult($conditionData, $selections);

	/**
	 * @param ConfigboxViewAdminRuleeditor
	 * @return string The HTML for the type's panel in the rule editor
	 */
	abstract function getConditionsPanelHtml($ruleEditorView);

	/**
	 * Called by ConfigboxRulesHelper::getConditionHtml to display the condition (either for editing or display)
	 *
	 * @param string[] $conditionData
	 * @param bool $forEditing If edit controls or plain display should come out
	 * @return string HTML for that condition
	 * @see ConfigboxRulesHelper::getConditionsHtml
	 */
	abstract function getConditionHtml($conditionData, $forEditing = true);

    /**
     * @return bool Indicates if the condition type shows it's own panel
     */
	function showPanel() {
	   return true;
    }

	/**
	 * Should return true if the given element is contained in the condition, false otherwise. Used for finding out
	 * if the element can be deleted.
	 *
	 * @param string[] $conditionData
	 * @param int $questionId
	 * @return bool True if found, false otherwise
	 * @see ConfigboxRulesHelper::getConditions
	 */
	function containsQuestionId($conditionData, $questionId) {
		// Type does not deal with elements
		return false;
	}

	/**
	 * Should return true if the given element is contained in the condition, false otherwise. Used for finding out
	 * if the element can be deleted.
	 *
	 * @param string[] $conditionData
	 * @param int $answerId
	 * @return bool True if found, false otherwise
	 * @see ConfigboxRulesHelper::getConditions
	 */
	function containsAnswerId($conditionData, $answerId) {
		// Type does not deal with xrefs
		return false;
	}

	/**
	 * Should return true if the given calculation is contained in the condition, false otherwise. Used for finding out
	 * if the element can be deleted.
	 *
	 * @param string[] $conditionData
	 * @param int $calculationId
	 * @return bool True if found, false otherwise
	 * @see ConfigboxRulesHelper::getConditions
	 */
	function containsCalculationId($conditionData, $calculationId) {
		// Type does not deal with xrefs
		return false;
	}

}