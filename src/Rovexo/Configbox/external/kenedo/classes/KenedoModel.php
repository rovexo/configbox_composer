<?php

/**
 * Class KenedoModel We all love KenedoModel
 */
class KenedoModel {

	/**
	 * @var string $componentName Component that handles tasks on the data of the model. Set it unless the model uses a KenedoEntity
	 * @see KenedoModel::getModel(), KenedoModel::__construct()
	 */
	public $componentName = '';

	/**
	 * @var KenedoModel[] $instances Array holding all singleton instances of the KenedoModels
	 * @see KenedoModel::getModel()
	 */
	static $instances;

	/**
	 * @var string[] $errors Array holding strings of error messages.
	 * @see KenedoModel::getErrors(), KenedoModel::setErrors() and similar
	 */
	protected $errors = array();

	/**
	 * Memo-cached values for getPropertyObject
	 * @see KenedoModel::getPropertyObject()
	 * @var string[]
	 */
	protected static $memoPropertyClassNames = array();

	protected $memoGetProperties = array();

	/**
	 * Memoized results of getRecord
	 * @see KenedoModel::getRecord
	 * @var object[]
	 */
	protected $memoGetRecord = array();

	/**
	 * Memoized results of getRecords with serialized function parameters as key
	 * @see KenedoModel::getRecord
	 * @var array
	 */
	protected $memoGetRecords = array();

	/**
	 * Will be set during getRecord and getRecords
	 * @see KenedoModel::getRecord, KenedoModel::getRecords
	 * @var string Language to use for getRecords and getRecord (format de-DE)
	 */
	public $languageTag = '';

	/**
	 * Holds data about old and new IDs during the copy process
	 * @var array
	 */
	static public $copyIds = array();

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return '';
	}

	/**
	 * Returns a nested array with infos about where records of that type are used
	 *
	 * Example:
	 *
	 * $usageInfo = array(
	 *		'componentName' => array(
	 *			'modelName'=> array(
	 *				array(
	 *					'titleField'	=> 'Name of the property that names the record',
	 *					'fkField'		=> 'Name of the property that holds the foreign key value',
	 *					'controller'	=> 'Name of the controller dealing with the type of record',
	 *					'name'			=> 'Description of the field using the entry. E.g. Element weight calculation',
	 *				),
	 *			),
	 *		),
	 *	);
	 *
	 * @return array
	 */
	function getRecordUsageInfo() {
		return array();
	}

	/**
	 *
	 * Get's you a singleton instance of a model. $className follows a convention that indicates the location of the
	 * model's class file (location can be specified with $path if necessary).
	 *
	 * Convention:
	 * ConfigboxModelName - Configbox brings you in the right component's model folder, Model does nothing, Name get's
	 * the filename. Looks into the custom model folder when a system model is not found.
	 *
	 * @param string $className
	 * @param string $path
	 *
	 * @throws Exception if $className is empty or just containing whitespace
	 * @throws Exception if the requested model cannot be found
	 *
	 * @return KenedoModel $model A KenedoModel subclass object
	 *
	 * @see InterfaceKenedoPlatform::getComponentDir(), KenedoPlatform::p()->getDirCustomization()
	 *
	 */
	static function getModel($className, $path = '') {

		// Legacy, remove with 2.7 for old client method (frontend made it load from components frontend folder, backend from backend - ignored in 2.6.0)
		if ($path == 'frontend' || $path == 'backend') {
			KLog::logLegacyCall('Method no longer uses frontend and backend as $path parameter. Use path to model file instead if necessary.');
			$path = '';
		}

		// Legacy, remove with 2.7
		if ($className == 'CbcheckoutModelOrder') {
			KLog::logLegacyCall('Model CbcheckoutModelOrder is now called ConfigboxModelOrderrecord');
			$className = 'ConfigboxModelOrderrecord';
		}
		// Legacy, remove with 2.7
		if ($className == 'ConfigboxModelGrandorder') {
			KLog::logLegacyCall('Model ConfigboxModelGrandorder is now called ConfigboxModelCart');
			$className = 'ConfigboxModelCart';
		}
		// Legacy, remove with 2.7
		if ($className == 'ConfigboxModelOrder') {
			KLog::logLegacyCall('Model ConfigboxModelOrder is now called ConfigboxModelCartposition');
			$className = 'ConfigboxModelCartposition';
		}

		// Abort in case $className is empty
		if (trim($className) == '') {
			$logMessage = 'KenedoModel::getModel called with empty className parameter';
			$identifier = KLog::log($logMessage, 'error');
			$publicMessage = 'KenedoModel::getModel called with empty className parameter. See error log file (Identifier: '.$identifier.').';
			throw new Exception($publicMessage);
		}

		if (!isset(self::$instances[$className])) {

			// Figure out component name and file name
			$component = 'com_' . strtolower( substr($className, 0, strpos($className, 'Model') ) );
			$modelFileName = strtolower( substr($className, strpos($className, 'Model') + 5 ) ) . '.php';

			// MERGELECACY - any com_cbcheckout is overwritten to com_configbox - logging is done later in this method
			if ($component == 'com_cbcheckout') {
				$component = 'com_configbox';
			}

			// Get the complete path to the regular and custom model
			$regularPath = KenedoPlatform::p()->getComponentDir($component) .'/models/'. $modelFileName;
			$customPath = KenedoPlatform::p()->getDirCustomization() .'/models/'. $modelFileName;

			// Overwrite $path to get the file from either customization or system
			if ($path == '') {

				if (is_file($regularPath)) {
					$path = $regularPath;
				}
				elseif (is_file($customPath)) {
					$path = $customPath;
				}

			}

			// Abort if the model file cannot be found anywhere
			if (!is_file($path)) {
				$logMessage = 'Model file for class "'.$className.'" not found in path "'.$path.'".';
				$identifier = KLog::log($logMessage, 'error');
				$publicMessage = 'Model file for class "'.$className.'" not found. See error log file (Identifier: '.$identifier.').';
				throw new Exception($publicMessage);
			}

			// Load the model file
			require_once($path);

			// MERGELECACY - The model's class name may still use CbcheckoutModel, offer temporary backwards compatibility and log
			if (class_exists($className) == false) {
				$legacyClassName = str_replace('ConfigboxModel','CbcheckoutModel',$className);
				if (class_exists($legacyClassName)) {
					$className = $legacyClassName;
					KLog::logLegacyCall('Change custom class names in customization/models from CbcheckoutModel... into ConfigboxModel.. and update any KModel::getModel calls');
				}
			}

			// Finally store the object
			self::$instances[$className] = new $className($component);

		}

		return self::$instances[$className];

	}

	/**
	 * @param string $component
	 *
	 * @throws Exception if $component parameter is empty
	 */
	function __construct($component = '') {

		if (empty($component)) {
			$logMessage = 'Model constructor called without an empty $component parameter.';
			$identifier = KLog::log($logMessage, 'error');
			$publicMessage = 'A system error occured. (Identifier: '.$identifier.').';
			throw new Exception($publicMessage);
		}

		// Set the component name
		$this->componentName = $component;

	}

	/**
	 * Returns the class name of the child model (if the model actually has children)
	 * @return string
	 */
	function getChildModel() {
		return '';
	}

	/**
	 * Returns the name of the foreign key property (connecting the child model with the parent)
	 * @return string
	 */
	function getChildModelForeignKey() {
		return '';
	}

	/**
	 * Returns a normalized object with the relevant record data coming in via a HTTP request. Each model's property
	 * reads from the HTTP request and adds the sanitized value/values to the right object properties.
	 *
	 * @return object Record data collected by KenedoProperties
	 */
	function getDataFromRequest() {

		$data = new stdClass();

		$properties = $this->getProperties();

		foreach ($properties as $property) {
			$property->getDataFromRequest($data);
		}

		return $data;
	}

	/**
	 * Used for appending or auto-filling data before validation and storing
	 * @param object $data
	 * @return boolean if anything reported an issue
	 */
	function prepareForStorage($data) {

		$properties = $this->getProperties();
		foreach ($properties as $property) {
			$response = $property->prepareForStorage($data);
			if ($response === false) {
				$this->setErrors($property->getErrors());
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true|false if data is good. In case it's not, you get localized error messages via getErrors()
	 * @see getErrors
	 *
	 * @param object $data The normalized data object
	 * @param string $context A string you can use in overrides that you can use to validate differently. OFC only useful if your code calls this method directly.
	 * @return boolean
	 */
	function validateData($data, $context = '') {

		$properties = $this->getProperties();
		$finalResponse = true;
		foreach ($properties as $property) {

			if ($property->applies($data) == false) {
				continue;
			}
			$response = $property->check($data);

			if ($response !== true) {
				$errors = $property->getErrors();
				if (count($errors) == 0) {
					$errors[] = 'Field "'. $property->getPropertyDefinition('label').'" reported failed validation but gives no error message.';
				}

				$this->setErrors($errors);
				$finalResponse = false;
			}

		}

		return $finalResponse;

	}

	function isInsert($data) {
		return ( empty($data->{$this->getTableKey()}) );
	}

	/**
	 * Stores the base record of the model and its properties. It will set $data's primary key value if it was an insert
	 * @param object $data
	 * @return bool true on success, false otherwise
	 */
	function store($data) {

		try {

			$db = KenedoPlatform::getDb();

			$db->startTransaction();

			// Figure out if we're dealing with an insert or an update
			$isInsert = $this->isInsert($data);

			// Prepare the base data
			$baseData = new stdClass();
			$properties = $this->getProperties();
			foreach ($properties as $property) {
				$keys = $property->getDataKeysForBaseTable($data);
				foreach ($keys as $key) {

					if (!property_exists($data, $key)) {
						$id = KLog::log('Property '.$this->getModelName().':'.$property->propertyName.' says key "'.$key.'" is for base table, but is not set. Full data object is '.var_export($data, true), 'error');
						throw new Exception('Model misconfiguration detected. See identifier "'.$id.'" in ConfigBox error log');
					}

					$baseData->{$key} = $data->{$key};
				}
			}

			$db->insertObject($this->getTableName(), $baseData, $this->getTableKey());

			$id = $baseData->{$this->getTableKey()};
			$data->{$this->getTableKey()} = $id;
			
			// Loop through the entity fields and call their store method
			$properties = $this->getProperties();
			foreach ($properties as $property) {
				$response = $property->store($data);
				if ($response === false) {
					$db->rollbackTransaction();
					$this->setErrors($property->getErrors());
					return false;
				}
			}
			
			$this->forgetRecord($id);

			$this->afterStore($id, $isInsert);

			$db->commitTransaction();

			KenedoObserver::triggerEvent('onAfterStoreRecord', array($this->getModelName(), $data));

			return true;

		}
		catch (Exception $e) {
			KLog::logException($e);
			$db = KenedoPlatform::getDb();
			$db->rollbackTransaction();
			$this->setError($e->getMessage());
			return false;
		}

	}

	/**
	 * "Soft abstract" function that you can override to do further processing after regular storage completed.
	 * Has to return true or false. False will make the store method revert the insert (but not an update).
	 * You can rely on that the "base" method does no processing, so no need for any parent::afterStore() stuff.
	 *
	 * Use ->setError() to send user feedback if you need it
	 *
	 * @param int $id ID of the record that was updated
	 * @param boolean $wasInsert Whether the storage was an update or insert
	 * @return bool true if all good, false on errors
	 */
	function afterStore($id, $wasInsert) {
		return true;
	}

	/**
	 * @param object $data model data object
	 * @return bool|int false on failure, ID of new record on success
	 */
	public function copy($data) {

		// Start a timer if there is none already (may be set in a recursion-parent or in the controller)
		if (KLog::timerExists('ModelCopyMethod') == false) {
			KLog::start('ModelCopyMethod');
		}

		$db = KenedoPlatform::getDb();

		KLog::log('Starting copying data for model "' . get_class($this) . '". ID '.$data->{$this->getTableKey()}.' - ' . KLog::time('ModelCopyMethod'), 'custom_copying');
		KLog::log('Model '.get_class($this). ' starts its transaction.', 'custom_copying');
		$db->startTransaction();

		try {

			$newData = clone $data;

			$oldId = $data->{$this->getTableKey()};

			if (empty($oldId)) {
				$logMsg = 'Data for model "' . get_class($this) . '" has no primary key value. Data was '.var_export($data, true).'. Aborting';
				KLog::log($logMsg, 'error');
				KLog::log($logMsg, 'custom_copying');
				throw new Exception($logMsg);
			}

			// Unset the primary key value prior storing
			$newData->{$this->getTableKey()} = null;

			// Prepare the base data
			$baseData = new stdClass();
			$properties = $this->getProperties();
			foreach ($properties as $property) {
				$keys = $property->getDataKeysForBaseTable($newData);
				foreach ($keys as $key) {

					if (!property_exists($newData, $key)) {
						$id = KLog::log('Property '.$this->getModelName().':'.$property->propertyName.' says key "'.$key.'" is for base table, but is not set. Full data object is '.var_export($newData, true), 'error');
						throw new Exception('Model misconfiguration detected. See identifier "'.$id.'" in ConfigBox error log');
					}

					$baseData->{$key} = $newData->{$key};
				}
			}

			// Copy the record's base table row..
			$db->insertObject($this->getTableName(), $baseData, $this->getTableKey());
			// and get the primary key ID of the new record
			$newId = $baseData->{$this->getTableKey()};
			$newData->{$this->getTableKey()} = $newId;

			// Hardly possible, but if we somehow didn't get an ID, abort all
			if (empty($newId)) {
				$msg = 'After copying the model\'s base record, we got no new primary key ID. Model class was '.get_class($this).', ID was '.$data->{$this->getTableKey()}.'. Copied data was "'.var_export($baseData, true).'". Aborting.';
				throw new Exception($msg);
			}

			self::$copyIds[$this->getModelName()][$oldId] = $newId;

			// Get the model's properties
			$properties = $this->getProperties();

			// Loop through for copying property-specific data
			foreach ($properties as $property) {

				//KLog::log('Copying property ' . get_class($this) . '\\' . $property->propertyName . '. Property type is ' . get_class($property) . ' - ' . KLog::time('ModelCopyMethod'), 'custom_copying');

				$success = $property->copy($data, $newId, $oldId);

				// If things go bad, properties do not rollback on their own and we do not trust that they log errors. So we do all here ourselves (possibly redundant)
				if ($success === false) {

					$logMsg = 'Copying property '.get_class($this).'\\'.$property->propertyName.' failed.';
					$errors = $property->getErrors();
					if (count($errors)) {
						$logMsg .= 'Property reports these errors: '.implode(', ', $errors);
						// Add property errors to model errors so that they bubble up from any possible recursion-children up to the controller
						$this->setErrors($errors);
					}
					else {
						$logMsg .= 'Property reports no specific errors. It may have made log messages on its own (see above in log file).';
						// Add property errors to model errors so that they bubble up from any possible recursion-children up to the controller
						$this->setError(KText::_('Copying failed, property "'.$property->propertyName.'" reported an unspecified error'));
					}

					// Log everything in error and copy log
					KLog::log($logMsg, 'error');
					KLog::log($logMsg, 'custom_copying');

					// Rollback and return false
					KLog::log('Model '.get_class($this). ' rolls back its transaction.', 'custom_copying');
					$db->rollbackTransaction();
					return false;

				}

			}

			// copy child models
			$childModelName = $this->getChildModel();

			if ($childModelName) {

				// Get the child model and foreign key property name
				$childModel = KenedoModel::getModel($childModelName);
				$fkPropName = $this->getChildModelForeignKey();

				// Make sure the foreign key property name is set in child model
				if (empty($fkPropName)) {
					throw new Exception('Copying child model data for model '.get_class($this). ' failed due to a model misconfiguration. It has a child model class "'.$childModelName.'", but no child model foreign key. Check method getChildModelForeignKey');
				}

				// Make sure the foreign key property exists child model
				$childProperties = $childModel->getProperties();
				if (!isset($childProperties[$fkPropName])) {
					throw new Exception('Copying child model data for model '.get_class($this). ' failed due to a model misconfiguration. child model foreign key "'.$fkPropName.'" does not exist. Available property names are "'.implode(',', array_keys($childProperties)).'" Check method getChildModelForeignKey');
				}

				// We are about to search for child records using the child model's getRecords method. We prepare the filter name of the child model's fk property
				$fkProperty = $childProperties[$fkPropName];
				$filterName = $fkProperty->getFilterName();
				if (is_array($filterName)) {
					$filterName = $filterName[0];
				}

				KLog::log('Figured out the filter name for child model\'s foreign key prop "'.$fkProperty->propertyName.'". It is "'.$filterName.'" - '.KLog::time('ModelCopyMethod'),'custom_copying');

				KLog::log('Getting child records from '.get_class($childModel).' with parent ID '.$oldId.'" - '.KLog::time('ModelCopyMethod'), 'custom_copying');
				// Now we get the child records
				$childRecords = $childModel->getRecords(array($filterName => $oldId));

				KLog::log('Got '.count($childRecords).' child records. - '.KLog::time('ModelCopyMethod'),'custom_copying');

				foreach ($childRecords as $childRecord) {

					// We clone the child record here because we manipulate the FK prop's value (because objects from getRecords come referenced, so we do not want to mess up the data for the next guy in the same runtime)
					$childRecordCopy = clone $childRecord;

					// Get the old child record FK value
					$oldChildFkId = $childRecordCopy->{$fkPropName};
					$oldChildPrimaryId = $childRecordCopy->{$childModel->getTableKey()};

					// Set the foreign key value to the new ID of the parent record (MIND WE DO NOT SET THE PRIMARY KEY VALUE OF CHILD RECORD HERE)
					$childRecordCopy->{$fkPropName} = $newId;

					KLog::log('Starting to copy child record ID '.$oldChildPrimaryId.' of '.get_class($this).' - (New fk ID: '.$newId.', Old fk ID: '.$oldChildFkId.') - '.KLog::time('ModelCopyMethod'),'custom_copying');

					// Dare to recurse into the child's copy method
					$response = $childModel->copy($childRecordCopy);

					// If things went bad in the child model, we can trust that it rolled back its own transaction. Also
					// it catches its own Exceptions, so we can be sure that we don't slip into our catch block as well.
					// So we got to rollback our own model's transaction and return false as well, so that the process
					// bubbles up nicely the recursion chain.
					// Also we take the model's errors and set them in our model, so that those bubble up as well.
					if ($response === false) {

						// We don't trust child models to log their errors. So we log them here (and eat up the possible redundancy)
						$logMsg = 'Copying child model record ID '.$oldChildPrimaryId.' of '.get_class($childModel).' failed.';
						$errors = $childModel->getErrors();
						if (count($errors)) {
							$logMsg .= 'Child model reports these errors: '.implode(', ', $errors);

						}
						else {
							$logMsg .= 'Child model reports no specific errors. It may have made log messages on its own (see above in log file).';

						}
						KLog::log($logMsg, 'error');
						KLog::log($logMsg, 'custom_copying');

						// Add reported errors from the child model in our model
						if (count($errors)) {
							$this->setErrors($errors);
						}
						else {
							$this->setError(KText::_('Copying failed, model "'.$childModel->getModelName().'" reported an unspecified error'));
						}

						KLog::log('Model '.get_class($this). ' rolls back its transaction.', 'custom_copying');
						$db->rollbackTransaction();
						return false;

					}

					KLog::log('Copying child model record was successful. - '.KLog::time('ModelCopyMethod'),'custom_copying');

				}

				KLog::log('Model '.get_class($this). ' finished copying all child records - '.KLog::time('ModelCopyMethod'),'custom_copying');

			}

			KLog::log('Model '.get_class($this). ' commits its transaction.', 'custom_copying');

			KenedoObserver::triggerEvent('onAfterCopyRecord', array($this->getModelName(), $newData));

			$db->commitTransaction();

			return $newId;

		}
		catch (Exception $e) {
			KLog::log($e->getMessage(), 'error');
			KLog::log($e->getMessage(), 'custom_copying');
			KLog::log('Model '.get_class($this). ' rolls back its transaction.', 'custom_copying');
			$db->rollbackTransaction();
			$this->setError(KText::_('A system error occured during copying. Please notify your service provider.'));
			return false;
		}

	}

	function copyRulesAndCalculations($recordId, $copyIds) {

		// Get the model's properties
		$properties = $this->getProperties();

		foreach ($properties as $property) {
			if (is_a($property, 'KenedoPropertyRule')) {
				$record = $this->getRecord($recordId);
				$property->copyRule($record, $copyIds);
			}
			elseif (is_a($property, 'KenedoPropertyCalculation')) {
				$record = $this->getRecord($recordId);
				$property->copyCalculation($record, $copyIds);
			}
			elseif (is_a($property, 'KenedoPropertyCalculationOverride')) {
				$record = $this->getRecord($recordId);
				$property->copyOverrides($record, $copyIds);
			}
			elseif(is_a($property, 'KenedoPropertyChildentries')) {
				$record = $this->getRecord($recordId);
				if ($record) {
					$view = KenedoView::getView($property->getPropertyDefinition('viewClass'));
					$childEntriesModel = $view->getDefaultModel();
					$viewFilters = $property->getPropertyDefinition('viewFilters');

					// Prepare the filter array for loading the child records
					$filters = array();
					foreach ($viewFilters as $viewFilter) {
						$filters[$viewFilter['filterName']] = $record->{$viewFilter['filterValueKey']};
					}

					// This should give you all the child records to copy
					$childEntriesRecords = $childEntriesModel->getRecords($filters);
					foreach ($childEntriesRecords as $childEntriesRecord) {
						$childEntriesModel->copyRulesAndCalculations($childEntriesRecord->{$childEntriesModel->getTableKey()}, $copyIds);
					}
				}

			}
		}

		$childModelName = $this->getChildModel();
		$foreignKeyPropName = $this->getChildModelForeignKey();

		if ($childModelName) {
			$childModel = KenedoModel::getModel($childModelName);

			$childProperties = $childModel->getProperties();
			$fkProperty = $childProperties[$foreignKeyPropName];
			$filterName = $fkProperty->getFilterName();
			if (is_array($filterName)) {
				$filterName = $filterName[0];
			}

			$childRecords = $childModel->getRecords([$filterName=>$recordId]);
			foreach ($childRecords as $childRecord) {
				$childModel->copyRulesAndCalculations($childRecord->{$childModel->getTableKey()}, $copyIds);
			}

		}

	}

	/**
	 * Remove memo-cache entries of records for the given id (removes all memoizations of getRecords)
	 * @see getRecord, getRecords
	 *
	 * @param $id
	 */
	public function forgetRecord($id) {
		if (!empty($this->memoGetRecord[$id])) {
			unset($this->memoGetRecord[$id]);
		}
		$this->memoGetRecords = array();
	}

	/**
	 * Remove any memoized results of getRecord and getRecords
	 * @see getRecord, getRecords
	 */
	public function forgetRecords() {
		$this->memoGetRecord = array();
		$this->memoGetRecords = array();
	}

	/**
	 * In case the model implements CRUD method itself, just return an array.
	 * @return array[]|array Array of the model's property definitions.
	 */
	function getPropertyDefinitions() {
		return array();
	}

	/**
	 * Gets custom property definitions from the customization folder to be merged with the regular ones.
	 * @return array[]|array Array of the model's property definitions.
	 */
	function getCustomPropertyDefinitions() {

		$customPropDefs = array();

		$dir = KenedoPlatform::p()->getDirCustomization().'/model_property_customization';
		$fileBase = strtolower( substr(get_class($this), strpos(get_class($this), 'Model') + 5 ) );
		$file = $fileBase . '.php';
		$path = $dir.'/'.$file;
		if (is_file($path) == false) {
			return $customPropDefs;
		}

		include_once($path);

		$function = 'customPropertyDefinitions'.ucfirst($fileBase);

		if (function_exists($function) == false) {
			return $customPropDefs;
		}

		return $function();

	}

	/**
	 * @return KenedoProperty[] Keys are the names of the properties
	 */
	function getProperties() {
		if (empty($this->memoGetProperties)) {
			// Get the regular defs
			$propDefs = $this->getPropertyDefinitions();
			// Get the custom defs
			$customPropDefs = $this->getCustomPropertyDefinitions();
			// Merge them together
			$propDefs = array_merge($propDefs, $customPropDefs);
			// Sort them by positionForm
			uasort($propDefs, array('KenedoModel', 'sortProperties'));
			// Loop through and make property objects
			$this->memoGetProperties = array();
			foreach ($propDefs as $propDef) {
				$this->memoGetProperties[$propDef['name']] = $this->getPropertyObject($propDef['type'], $propDef);
			}
		}

		return $this->memoGetProperties;
	}

	/**
	 * Sorting callable, used in KenedoModel::getProperties()
	 * @see KenedoModel::getProperties
	 * @param $a
	 * @param $b
	 * @return int
	 */
	function sortProperties($a, $b) {
		if ($a['positionForm'] < $b['positionForm']) {
			return -1;
		}
		elseif ($a['positionForm'] == $b['positionForm']) {
			return 0;
		}
		else {
			return 1;
		}
	}

	/**
	 * @param string $propertyType
	 * @param array $propertyDefinition
	 * @return KenedoProperty (Sub-class of it)
	 * @throws Exception if property type is unknown
	 */
	protected function getPropertyObject($propertyType, $propertyDefinition) {

		if (empty(self::$memoPropertyClassNames[$propertyType])) {

			$regularFolder = KenedoPlatform::p()->getComponentDir('com_configbox').'/external/kenedo/properties';
			$customFolder = KenedoPlatform::p()->getDirCustomization().'/properties';

			$fileName = strtolower($propertyType).'.php';

			$pathCustom = $customFolder.'/'.$fileName;
			$pathSystem = $regularFolder.'/'.$fileName;

			// Check in customization folder
			if (file_exists($pathCustom)) {
				$classFile = $pathCustom;
			}
			// Check in system folder
			elseif (file_exists($pathSystem)) {
				$classFile = $pathSystem;
			}
			// If needed, deal with non-existent prop templates
			else {
				KLog::log('Class file not found for property type "'.$propertyType.'" not found. Looked in "'.$pathCustom.'" and "'.$pathSystem.'" Model was "'.$this->getModelName().'", search prop defs were "'.var_export($propertyDefinition, true).'"', 'error');
				throw new Exception('Property type "'.$propertyType.'" not found.');
			}

			$className = 'KenedoProperty'.ucfirst(strtolower($propertyType));

			require_once($classFile);

			self::$memoPropertyClassNames[$propertyType] = $className;

		}

		$className = self::$memoPropertyClassNames[$propertyType];

		return new $className($propertyDefinition, $this);

	}

	/**
	 * @return KenedoProperty[]
	 */
	function getPropertiesForListing() {

		$properties = $this->getProperties();
		$returnProperties = array();
		foreach ($properties as $property) {
			if ($property->isInListing()) {
				$returnProperties[$property->getListingPosition()] = $property;
			}
		}
		ksort($returnProperties);
		return $returnProperties;

	}

	/**
	 * Holds the definition of tasks available for listings of that type. Data is used by KenedoViewHelper::renderTaskItems in Admin templates
	 *
	 * @see KenedoViewHelper::renderTaskItems
	 * @return array
	 */
	function getListingTasks() {
		$tasks = array(
			array('title'=>KText::_('Add'), 	'task'=>'add', 'primary'=>true),
			array('title'=>KText::_('Remove'), 'task'=>'remove'),
			array('title'=>KText::_('Copy'), 	'task'=>'copy'),
		);
		return $tasks;
	}

	/**
	 * Holds the definition of tasks available for edit forms of that type. Data is used by KenedoViewHelper::renderTaskItems in Admin templates
	 *
	 * @see KenedoViewHelper::renderTaskItems
	 * @return array
	 */
	function getDetailsTasks() {
		$tasks = array(
			array('title'=>KText::_('Help'), 			'task'=>'toggle-help'),
			array('title'=>KText::_('Save and Close'), 	'task'=>'store'),
			array('title'=>KText::_('Save'), 			'task'=>'apply', 'primary' => true),
			array('title'=>KText::_('Cancel'), 			'task'=>'cancel'),
		);
		return $tasks;
	}

	/**
	 * Returns the model's name (e.g. 'ConfigboxModelName' becomes 'name')
	 * @param KenedoModel|NULL $model Model object or use NULL to use $this
	 * @return string
	 */
	function getModelName($model = NULL) {
		if ($model === NULL) {
			$model = $this;
		}
		$className = get_class($model);
		$modelName = substr($className, strpos($className, 'Model') + 5 );
		return strtolower($modelName);
	}


	/**
	 * @return object A record with all properties set to default values or empty
	 */
	function initData() {

		$data = $this->getDataFromRequest();

		$props = $this->getProperties();
		foreach ($data as $key=>$value) {
			if (empty($value) && !empty($props[$key])) {
				if ($props[$key]->getPropertyDefinition('default', NULL) !== NULL) {
					$data->$key = $props[$key]->getPropertyDefinition('default');
				}
			}
		}

		foreach ($props as $property) {
			if (empty($data->{$property->propertyName})) {

				// If request data has a key named 'prefill_<property_name>', then use it
				if (KRequest::getString('prefill_'.$property->propertyName)) {
					$data->{$property->propertyName} = KRequest::getString('prefill_'.$property->propertyName);
				}
				elseif ($property->getPropertyDefinition('default', NULL) !== NULL)  {
					$data->{$property->propertyName} = $property->getPropertyDefinition('default');
				}
				else {
					$data->{$property->propertyName} = NULL;
				}

				try {
					if ($data->{$property->propertyName} && $property->getType() == 'join') {
						/**
						 * @var KenedoPropertyJoin $property
						 */
						$parentModelClass = $property->getPropertyDefinition('modelClass');
						$parentModel = KenedoModel::getModel($parentModelClass);
						$parentProps = $parentModel->getProperties();
						$parentIdProp = $parentProps[$parentModel->getTableKey()];

						$selects = $property->getSelectsForGetRecord();
						array_shift($selects);
						$joins = $property->getJoinsForGetRecord();
						array_shift($joins);

						$query = "
						SELECT ".implode(",\n", $selects)."
						FROM ".$parentModel->getTableName()." AS ".$parentIdProp->getTableAlias()."
						".implode("\n", $joins)."
						WHERE ".$parentIdProp->getTableAlias().'.'.$parentIdProp->getSelectAlias()." = ".$data->{$property->propertyName}."
						";
						$db = KenedoPlatform::getDb();
						$db->setQuery($query);
						$parentData = $db->loadObject();

						foreach ($parentData as $key=>$value) {
							$data->{$key} = $value;
						}

						unset($parentModel, $parentIdProp);
					}
				}
				catch (Exception $e) {
					KLog::log('Experimental adding of parent data to initialized data failed. With error "'.$e->getMessage().'"', 'warning');
					KLog::log('stack trace was '.$e->getTraceAsString(), 'warning');
				}

			}
		}

		return $data;
	}

	/**
	 * @param int $id Record ID
	 * @param string   $languageTag Language for translatable fields (format de-DE), leave empty to use current system language
	 * @return object
	 * @throws Exception if a bad language tag was selected
	 */
	function getRecord($id, $languageTag = '') {

		// Default to current system language
		if ($languageTag == '') {
			$languageTag = KText::getLanguageTag();
		}

		// Throw and exception if a bad language was selected
		if (!in_array($languageTag, KenedoLanguageHelper::getActiveLanguageTags())) {
			$logMsg = 'Requested records with language "'.$languageTag.'", but it is not an active language.';
			KLog::log($logMsg, 'error');
			throw new Exception('Requested records with language "'.$languageTag.'", but it is not an active language.', 500);
		}

		$this->languageTag = $languageTag;

		if (empty($this->memoGetRecord[$id][$languageTag])) {
			$props = $this->getProperties();
			$selects = array();
			$joins = array();
			foreach ($props as $prop) {
				$selects = array_merge($selects, $prop->getSelectsForGetRecord());
				$joins = array_merge($joins, $prop->getJoinsForGetRecord());
			}

			asort($selects);

			$db = KenedoPlatform::getDb();

			$query = "SELECT
				".implode(", \n", $selects)."

				FROM ".$db->getQuoted($this->getTableName())." AS ".$this->getModelName()."

				".implode("\n\n", $joins);

			$query .= "\n\nWHERE ".$db->getQuoted($this->getModelName()).".".$db->getQuoted($this->getTableKey())." = ".intval($id);

			$db->setQuery($query);
			$record = $db->loadObject();

			if (!$record || $record->{$this->getTableKey()} === null) {
				$record = null;
			}

			if ($record) {
				foreach ($props as $prop) {
					$prop->appendDataForGetRecord($record);
				}
			}

			$this->memoGetRecord[$id][$languageTag] = $record;
		}

		return $this->memoGetRecord[$id][$languageTag];

	}

	/**
	 * Returns records of the model's type.
	 * @param string[] 	$filters 		See KenedoView::getFiltersFromUpdatedState()
	 * @param string[] 	$pagination 	See KenedoView::getPaginationFromUpdatedState()
	 * @param array[] 	$sortSpecs 		See KenedoView::getOrderingFromUpdatedState()
	 * @param string   	$languageTag 	Language for translatable fields (format de-DE), leave empty to use current system language
	 * @param boolean 	$countOnly 		If you simply want to get the count of records for the given $filters
	 *
	 * @return object[]|int Array with objects or the number of records if $countOnly == true
	 * @throws Exception
	 * @see KenedoView::getFiltersFromUpdatedState(), KenedoView::getPaginationFromUpdatedState(), KenedoView::getOrderingFromUpdatedState()
	 *
	 */
	function getRecords($filters = array(), $pagination = array(), $sortSpecs = array(), $languageTag = '', $countOnly = false) {

		// If we got an empty $languageTag parameter, fall back to current system language
		if ($languageTag == '') {
			$languageTag = KText::getLanguageTag();
		}

		// Throw and exception if a bad language was selected
		if (!in_array($languageTag, KenedoLanguageHelper::getActiveLanguageTags())) {
			$logMsg = 'Requested records with language "'.$languageTag.'", but it is not an active language.';
			KLog::log($logMsg, 'error');
			throw new Exception('Requested records with language "'.$languageTag.'", but it is not an active language', 500);
		}

		$signature = serialize(array(func_get_args(), $languageTag));

		if (empty($this->memoGetRecords[$signature])) {

			// Set the language tag
			$this->languageTag = $languageTag;

			$props = $this->getProperties();
			$selects = array();
			$joins = array();
			$wheres = array();
			$groupByCols = array();

			// Prepare selects, joins and group by items
			foreach ($props as $prop) {
				$selects = array_merge($selects, $prop->getSelectsForGetRecord());
				$joins = array_merge($joins, $prop->getJoinsForGetRecord());
				$groupByCols = array_merge($groupByCols, $prop->getGroupingColumnsForGetRecord());
				$wheres = array_merge($wheres, $prop->getWheres($filters));
			}
			unset($prop);

			asort($selects);

			$db = KenedoPlatform::getDb();

			// Prepare ORDER BYs
			// Will be a array of strings with col reference and direction (e.g. array('column_1 ASC', 'column_2 DESC)
			$orderBys = array();
			if (!empty($sortSpecs)) {

				// We used to have $ordering as flat array with a single ordering instruction, now it's an array of
				// ordering instructions. Here we 'upgrade' any legacy flat ones.
				if ( isset( $sortSpecs['propertyName'] ) ) {
					$sortSpecs = array(
						$sortSpecs,
					);
				}

				foreach ($sortSpecs as $sortSpec ) {

					if (is_array($sortSpec) == false || empty($sortSpec['propertyName'])) {
						KLog::log('Misconfigured ordering instructions found. Model is '.get_class($this).'. Ordering instructions were '.var_export($sortSpecs, true), 'error');
					}

					$direction = strtoupper( (!empty($sortSpec['direction'])) ? $sortSpec['direction'] : 'ASC' );

					// property name is either a base model's property name or a reference to a joined model's property
					// table alias and column name
					// This is the joined model's reference. We trust the caller that he writes it right
					// Structure is prop_table_alias.prop_table_column_name
					if ( strstr( $sortSpec['propertyName'], '.' ) ) {
						// Put table and column name/aliases in quotes
						$split = explode('.', $sortSpec['propertyName']);
						$columnReference = $db->getQuoted($db->getEscaped($split[0])).'.'.$db->getQuoted($db->getEscaped($split[1]));
					} else {

						if (empty($props[ $sortSpec['propertyName'] ])) {
							continue;
						}

						$prop = $props[ $sortSpec['propertyName'] ];

						// Join props get special treatment, if you use a join, it will sort by the joined model's prop
						// defined in propNameDisplay
						if ( $prop->getType() == 'join' ) {
							$parentModelName = $prop->getPropertyDefinition( 'modelClass' );
							$parentModel     = KenedoModel::getModel( $parentModelName );
							// If we deal with a join on a non-table-based model, leave all that be.
							if ($parentModel->getTableName() == '') {
								continue;
							}
							$parentProps     = $parentModel->getProperties();
							$displayProp     = $parentProps[ $prop->getPropertyDefinition( 'propNameDisplay' ) ];
							$columnReference = $displayProp->getTableAlias() . '.' . $displayProp->getTableColumnName();
						} else {
							$columnReference = $prop->getTableAlias() . '.' . $prop->getTableColumnName();
						}
					}

					$orderBys[] = $columnReference . ' ' . $db->getEscaped($direction);

				}
			}


			// If we do $countOnly drop ORDER BY for a bit of performance gain
			if ($countOnly == true) {
				$orderBys = array();
			}

			// Start the query with the SELECTs
			$query = "SELECT \n".implode(", \n", $selects)."\n\n";

			// Do the FROM
			$query .= "FROM ".$db->getQuoted($this->getTableName())." AS ".$db->getQuoted($this->getModelName());

			// Put in the JOINs
			if (count($joins)) {
				$query .= "\n\n".implode("\n\n", $joins);
			}

			// Add any WHEREs
			if (count($wheres)) {
				$query .= "\n\nWHERE ".implode(' AND ', $wheres);
			}

			// Do GROUP BYs
			if (count($groupByCols)) {
				$query .= "\n\n GROUP BY ".implode(', ', array_unique($groupByCols));
			}

			// And now ORDER BYs
			if (count($orderBys)) {
				$query .= "\n\n ORDER BY ".implode(', ', $orderBys);
			}

			// If it's about counting only, skip all else and count up
			if ($countOnly == true) {
				$db->setQuery($query);
				$records = $db->loadObjectList();
				return count($records);
			}

			// As per pagination, do LIMIT
			if (!empty($pagination) && $pagination['limit'] != 0) {
				$query .= "\n\n LIMIT ".intval($pagination['start']).", ".intval($pagination['limit']);
			}

			// Set the query and get the data
			$db->setQuery($query);

			$records = $db->loadObjectList();

			// If things when bad..
			if (!$records) {
				$records = array();
			}

			// Let the props to the data appending
			foreach ($records as $record) {
				foreach ($props as $prop) {
					$prop->appendDataForGetRecord($record);
				}
			}

			$this->memoGetRecords[$signature] = $records;

		}

		return $this->memoGetRecords[$signature];

	}

	/**
	 * Returns an array with applicable filter names
	 * @return string[]
	 */
	function getFilterNames() {
		$filterNames = array();
		$props = $this->getProperties();
		foreach ($props as $prop) {
			$propFilterName = $prop->getFilterName();
			if (is_array($propFilterName)) {
				$filterNames = array_merge($filterNames, $propFilterName);
			}
			elseif(!empty($propFilterName)) {
				$filterNames[] = $propFilterName;
			}
		}
		return $filterNames;
	}

	/**
	 * Returns the table alias and column name for the given property in this model.
	 * E.g. property 'published' in model ConfigBoxModelAdminproducts gives you 'adminproducts.published'
	 * Or property 'title' (translatable) gives you 'translation_adminproducts_title.text'
	 * @param string $propertyName
	 * @return string
	 * @throws Exception if the propertyName wasn't found in the model
	 */
	function getTableReferenceForProperty($propertyName) {
		$props = $this->getProperties();
		foreach ($props as $propName => $prop) {
			if ($propName == $propertyName) {
				return $prop->getTableAlias().'.'.$prop->getTableColumnName();
			}
		}

		return $propertyName;

//		$msg = 'Property "'.$propertyName.'" not found in model "'.$this->getModelName().'"';
//		KLog::log($msg, 'error');
//		throw new Exception('Encountered an error during loading model data. Please check CB error log file');
	}

	/**
	 * Makes the property 'published' (called Active in the GUI nowadays) 1 or 0
	 * Looks for a GET/POST parameter 'ids', it is supposed to be a comma separated list of record ids
	 *
	 * @param int[] $ids Ids of records to active/deactivate
	 * @param boolean $publish True for publish, False for unpublish
	 * @return boolean
	 */
	function publish($ids, $publish = true) {

		// Check if $ids is an array
		if (is_array($ids) == false) {
			KLog::log('Non array passed to method. No changes made. Got this: '.var_export($ids,true), 'error');
			$this->setError('Activation/Deactivation of records failed. See error log file for details.');
			return false;
		}

		// Done
		if (count($ids) == 0) {
			return true;
		}

		$db = KenedoPlatform::getDb();

		// Sanitize ids
		foreach ($ids as &$id) {

			trim($id);

			if (!is_numeric($id)) {
				$id = "'".$db->getEscaped($id)."'";
			}
			else {
				$id = intval($id);
			}
		}

		$properties = $this->getProperties();
		$columnName = '';
		foreach ($properties as $property) {
			if ($property->getType() == 'published') {
				$columnName = $property->propertyName;
			}
		}

		if (empty($columnName)) {
			throw new Exception('Cannot find property of type "published" in model "'.$this->getModelName().'"');
		}

		$query = "UPDATE `".$this->getTableName()."` SET `".$columnName."` = '".intval($publish)."' WHERE `".$this->getTableKey()."` IN (".implode(',', $ids).")";
		$db->setQuery($query);
		$success = $db->query();

		$this->forgetRecords();

		return ($success === true) ? true : false;

	}

	/**
	 * @param int|int[] $ids ID or array of IDs
	 * @return bool
	 */
	function delete($ids) {

		// Trying to track down an elusive case where NULL gets passed - logging a backtrace and normalizing $ids to empty array
		if ($ids === NULL) {

			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$trace = ob_get_contents();
			ob_end_clean();

			KLog::log('NULL passed as parameter $ids to KenedoModel::delete(). Model name was "'.$this->getModelName().'". Backtrace was "'.$trace.'"', 'warning');
			$ids = array();

		}

		if (is_numeric($ids)) {
			$ids = array($ids);
		}

		// First check if all records can be removed
		// Otherwise, error messages will be set and false returned
		$allGood = true;
		foreach ($ids as $id) {
			if ($this->canDelete($id) == false) {
				$allGood = false;
			}
		}
		// That is because KenedoModel::canDelete() sets error messages as feedback. We want to see all messages, not
		// just the first.
		if ($allGood == false) {
			return false;
		}

		$db = KenedoPlatform::getDb();

		try {

			$db->startTransaction();

			foreach ($ids as $id) {

				// Go trough properties and run their delete method
				$properties = $this->getProperties();
				foreach ($properties as $property) {
					$property->delete($id, $this->getTableName());
				}

				$query = 'DELETE FROM `'.$this->getTableName().'` WHERE `'.$this->getTableKey().'` = '.intval($id);
				$db->setQuery($query);
				$success = $db->query();

				if ($success == false) {
					$this->setError($db->getErrorMsg());
					return false;
				}
				else {
					$this->afterDelete($id);
					KenedoObserver::triggerEvent('onAfterDeleteRecord', array($this->getModelName(), $id));
				}

			}

			$db->commitTransaction();

		}
		catch (Exception $exception) {

			$db = KenedoPlatform::getDb();
			$db->rollbackTransaction();

			if ($exception->getCode() == '1451') {
				$errorMsg = KText::_('Cannot delete this record because it is linked with other records.');
			}
			else {
				$errorMsg = $exception->getMessage();
			}
			$this->setError($errorMsg);
			return false;
		}

		return true;

	}

	/**
	 * Soft-abstract method to use for some post-delete processing
	 * @param int $id ID of the record that was deleted
	 */
	function afterDelete($id) {

	}

	function canDelete($id) {

		$properties = $this->getProperties();
		foreach ($properties as $property) {
			$can = $property->canDelete($id);
			if ($can === false) {
				$this->setErrors($property->getErrors());
				return false;
			}
		}

		$usage = $this->getRecordUsage($id);

		if (count($usage) != 0) {
			$feedbackItems = array();
			foreach ($usage as $propertyLabel=>$records) {
				foreach ($records as $record) {
					$feedbackItems[] = $propertyLabel . ' in '.$record->title .' (ID '.$record->id.')';
				}
			}

			$errorMsg = KText::sprintf('Cannot delete this record because it is linked with these records. %s', implode(', ', $feedbackItems));
			$this->setError($errorMsg);
			return false;
		}

		return true;
	}

	/**
	 * @param int[] $ordering Key/value pairs (key is record id, value is position)
	 * @return bool
	 */
	function storeOrdering($ordering) {

		if (count($ordering) == 0) {
			return true;
		}

		$db = KenedoPlatform::getDb();

		foreach ($ordering as $id=>$position) {
			$query = "UPDATE `".$this->getTableName()."` SET `ordering` = ".intval($position)." WHERE `".$this->getTableKey()."` = ".intval($id);
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

	/**
	 * Returns an array with all info regarding where the record is being used (determined by KenedoEntity join fields)
	 * Used for displaying item usage in most edit forms.
	 *
	 * @param int $recordId ID of record to check
	 * @see KenedoEntity::getItemUsage
	 * @return array
	 */
	function getRecordUsage($recordId) {

		$return = array();

		$usageInfo = $this->getRecordUsageInfo();

		if (empty($recordId) || count($usageInfo) == 0) {
			return $return;
		}

		$db = KenedoPlatform::getDb();
		foreach ($usageInfo as $component=>$items) {

			foreach ($items as $modelName=>$propertyInfos) {
				foreach ($propertyInfos as $propertyInfo) {
					$model = KenedoModel::getModel($modelName);
					$propertyDefinitions = $model->getPropertyDefinitions();

					$titleField = $propertyDefinitions[$propertyInfo['titleField']];
					if ($titleField['type'] == 'translatable') {
						$select = 'a.id, str_title.text AS title';
						$join = "LEFT JOIN `#__configbox_strings` AS str_title ON str_title.key = a.id AND str_title.type = ".(int)$titleField['langType']." AND str_title.language_tag = '".$db->getEscaped(KText::getLanguageTag())."'";
					}
					else {
						$select = 'a.id, a.'.$propertyInfo['titleField'].' AS title';
						$join = '';
					}

					$query = "
					SELECT ".$select."
					FROM `".$model->getTableName()."` AS a
					".$join."
					WHERE `".$propertyInfo['fkField']."` = ".(int)$recordId;

					if (!empty($propertyInfo['filterField'])) {
						$query .= " AND `".$propertyInfo['filterField']."` = '".$db->getEscaped($propertyInfo['filterValue'])."'";
					}

					$db->setQuery($query);
					$usages = $db->loadObjectList();

					if ($usages) {
						foreach ($usages as $usage) {
							$usage->link = KLink::getRoute('index.php?option='.$component.'&controller='.$propertyInfo['controller'].'&id='.$usage->id.'&task=edit', false);
						}

						$return[$propertyInfo['name']] = $usages;
					}
				}
			}

		}

		return $return;

	}

	function resetErrors() {
		$this->errors = array();
	}

	function setError($error) {
		$this->errors[] = $error;
	}

	function setErrors($errors) {
		if (is_array($errors) && count($errors)) {
			$this->errors = array_merge((array)$this->errors,$errors);
		}
	}

	function getErrors() {
		return $this->errors;
	}

	function getError() {
		if (is_array($this->errors) && count($this->errors)) {
			return end($this->errors);
		}
		else {
			return '';
		}
	}

}
