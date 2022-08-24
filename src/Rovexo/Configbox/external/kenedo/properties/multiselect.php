<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyMultiselect extends KenedoProperty {

	/**
	 * @var string The model class you connect via multiselect
	 */
	protected $modelClass;

	/**
	 * @var string The model class' method that gives you the possible records to connect
	 */
	protected $modelMethod;

	/**
	 * @var string The name of the xref table (using #__ as table prefix placeholder)
	 */
	protected $xrefTable;
	/**
	 * @var string The foreign key column name in the xref table for the other table's rows (other table = table of the model you connect)
	 */
	protected $fkOther;

	/**
	 * @var string The foreign key column name in the xref table for the base table's rows (base table = table of that property's model)
	 */
	protected $fkOwn;

	/**
	 * @var string Primary key column name of the base table
	 */
	protected $keyOwn;

	/**
	 * @var string The primary key column name of the other table
	 */
	protected $keyOther;

	/**
	 * @var string The name of the other model's property you want to use for displaying records in the multi-select
	 */
	protected $displayColumnOther;

	/**
	 * @var string The name of the other table (using #__ as table prefix placeholder)
	 */
	protected $tableOther;

	/**
	 * @var boolean Indicates if the xref table supports ordering (the xref table needs a column called 'ordering')
	 */
	protected $usesOrdering;

	/**
	 * @var boolean Indicates if the multi-select edit form shall show checkboxes or an HTML select with multiple choice
	 */
	protected $asCheckboxes;

	/**
	 * @var boolean Indicates if the activeLanguage hack is needed.
	 */
	protected $activeLanguageHack;

	/**
	 * This method takes in the selected records from the request (the prop's store method will remove it again after
	 * storing)
	 * @see KenedoPropertyMultiselect::store()
	 * @param object $data
	 */
	function getDataFromRequest( &$data) {
		$data->{$this->propertyName} = KRequest::getArray($this->propertyName);
	}

	function getDataKeysForBaseTable($data) {
		return array();
	}

	/**
	 * This method takes the object var (called after the property name) that came in through getDataFromRequest
	 * and deals with storing itself (it removes the
	 * object var afterwards)
	 * @see KenedoPropertyMultiselect::getDataFromRequest()
	 *
	 * @param object $data
	 *
	 * @return bool
	 */
	function store(&$data) {

		// Get the xref values for convenience
		$fieldValues = $data->{$this->propertyName};

		$db = KenedoPlatform::getDb();

		// No fkOwn means that we don't have a true xref table, just a table that stores a bunch of values just for that record
		// See #__configbox_active_languages as example.
		// Simple delete all and insert is enough.
		if ($this->getPropertyDefinition('fkOwn', '') == '') {
			$db = KenedoPlatform::getDb();
			$query = "DELETE FROM `".$this->getPropertyDefinition('xrefTable')."`";
			$db->setQuery($query);
			$db->query();

			foreach ($fieldValues as $fieldValue) {
				$query = "INSERT INTO `".$this->getPropertyDefinition('xrefTable')."` SET `".$this->getPropertyDefinition('fkOther')."` = '".$db->getEscaped($fieldValue)."'";
				$db->setQuery($query);
				$db->query();
			}
			return true;
		}

		// Make variables with relevant field data for simplicity
		$xrefTable = $this->getPropertyDefinition('xrefTable');
		$fkOwn = $this->getPropertyDefinition('fkOwn');
		$fkOther = $this->getPropertyDefinition('fkOther');
		$keyOwn = $this->getPropertyDefinition('keyOwn');

		// We can't just drop all regarding xref records, since some contain additional data like ordering, which
		// would get lost. So we load the existing ones and put them in $newXrefRecords if they are to stay. Later
		// we delete all regarding ones and insert those records.

		// Get the existing xref records
		$query = "SELECT * FROM `".$xrefTable."` WHERE `".$fkOwn."` = '".$db->getEscaped($data->$keyOwn)."'";
		$db->setQuery($query);
		$existingXrefRecords = $db->loadObjectList($fkOther);

		// Prepare an array with new xref records (bound to replace the existing ones)
		$newXrefRecords = array();
		foreach ($fieldValues as $fieldValue) {

			// If an xref record for that value exists, use it's existing data
			if (!empty($existingXrefRecords[$fieldValue])) {
				$newXrefRecord = $existingXrefRecords[$fieldValue];
			}
			// Otherwise create a new one (add the ordering field in case the xref table uses ordering)
			else {
				$newXrefRecord = new stdClass();
				$newXrefRecord->$fkOwn = intval($data->$keyOwn);
				$newXrefRecord->$fkOther = $fieldValue;

				if ($this->getPropertyDefinition('usesOrdering', false)) {
					$newXrefRecord->ordering = 0;
				}

			}

			$newXrefRecords[] = $newXrefRecord;
			unset($newXrefRecord);

		}

		// Remove all existing xref records
		$query = "DELETE FROM `".$xrefTable."` WHERE `".$fkOwn."` = '".$db->getEscaped($data->$keyOwn)."'";
		$db->setQuery($query);
		$db->query();

		// Add the new set of xref records
		foreach ($newXrefRecords as $newXrefRecord) {
			$db->insertObject($xrefTable, $newXrefRecord);
		}

		// Set sorting numbers for all xref records that are involved in the second table values
		if ($this->getPropertyDefinition('usesOrdering', false)) {

			foreach ($fieldValues as $fieldValue) {
				$query = "SELECT MAX(ordering) AS `highest_ordering` FROM `".$xrefTable."` WHERE `".$fkOther."` = '".$db->getEscaped($fieldValue)."'";
				$db->setQuery($query);
				$currentHighestOrderNumber = $db->loadResult();

				$query = "SELECT * FROM `".$xrefTable."` WHERE `ordering` = 0 AND `".$fkOther."` = '".$db->getEscaped($fieldValue)."'";
				$db->setQuery($query);
				$xrefsToUpdate = $db->loadObjectList();

				foreach ($xrefsToUpdate as $xrefToUpdate) {
					$currentHighestOrderNumber++;
					$query = "UPDATE `".$xrefTable."` SET `ordering` = ".intval($currentHighestOrderNumber)." WHERE `".$fkOther."` = '".$db->getEscaped($fieldValue)."' AND `".$fkOwn."` = '".$db->getEscaped($xrefToUpdate->$fkOwn)."'";
					$db->setQuery($query);
					$db->query();
				}

			}
		}

		return true;

	}

    /**
     * Copy Method
     * @param object $data model data object
     * @param int $newId
     * @param int $oldId
     * @return bool
	 * @throws Exception if property configuration or data appears invalid or a row insert failed
     */
    function copy($data, $newId, $oldId) {

		$logPrefix = get_class($this->model).'\\'.$this->propertyName.'. Type "'.$this->getType().'": ';
		KLog::log($logPrefix.'Searching for xref records. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

        $fieldValues = $data->{$this->propertyName};

        $db = KenedoPlatform::getDb();

        // Make variables with relevant field data for simplicity
        $xrefTable = $this->getPropertyDefinition('xrefTable');
        $fkOwn = $this->getPropertyDefinition('fkOwn');
        $fkOther = $this->getPropertyDefinition('fkOther');
        $keyOwn = $this->getPropertyDefinition('keyOwn');

        // if empty value then continue with next record
        if(empty($data->$keyOwn)){
        	$logMsg = $logPrefix.'Settings for multiselect appear invalid. Cannot find $keyOwn ("'.$keyOwn.'") in record data (or is empty). Data was '.var_export($data, true);
			KLog::log($logMsg, 'error');
			KLog::log($logMsg, 'custom_copying');
			throw new Exception($logMsg);
        }

        // Get the existing xref records
        $query = "SELECT * FROM `".$xrefTable."` WHERE `".$fkOwn."` = '".$db->getEscaped($data->$keyOwn)."'";
        $db->setQuery($query);
        $existingXrefRecords = $db->loadObjectList($fkOther);

        // Prepare an array with new xref records (bound to replace the existing ones)
        $newXrefRecords = array();
        foreach ($fieldValues as $fieldValue) {

            // if empty value then continue with next record
            if(empty($fieldValue)) {
				$logMsg = $logPrefix.'One of the xref values appear invalid. xref value was '.var_export($fieldValue, true).'. All values were '.var_export($fieldValues, true);
				KLog::log($logMsg, 'error');
				KLog::log($logMsg, 'custom_copying');
				throw new Exception($logMsg);
            }

            // If an xref record for that value exists, use it's existing data
            if (!empty($existingXrefRecords[$fieldValue])) {
                $newXrefRecord = $existingXrefRecords[$fieldValue];
                $newXrefRecord->$fkOwn = intval($newId);
            }
            // Otherwise create a new one (add the ordering field in case the xref table uses ordering)
            else {
                $newXrefRecord = new stdClass();
                $newXrefRecord->$fkOwn = intval($newId);
                $newXrefRecord->$fkOther = $fieldValue;

                if ($this->getPropertyDefinition('usesOrdering', false)) {
                    $newXrefRecord->ordering = 0;
                }
            }

            $newXrefRecords[] = $newXrefRecord;
            unset($newXrefRecord);

        }

		KLog::log($logPrefix.'Found '.count($newXrefRecords).' xref records. Starting to copy. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

		// Add the new set of xref records
        foreach ($newXrefRecords as $newXrefRecord) {
            $saveResult = $db->insertObject($xrefTable, $newXrefRecord);
            if ($saveResult === false) {
            	$logMsg = $logPrefix.'Copying xref record failed. SQL error was "'.$db->getErrorMsg().'".';
				KLog::log($logMsg, 'error');
				KLog::log($logMsg, 'custom_copying');
				throw new Exception($logMsg);
			}
        }

		KLog::log($logPrefix.'Successfully copied all '.count($newXrefRecords).' xref records. Elapsed time: '.KLog::time('ModelCopyMethod').'ms', 'custom_copying');

        return true;
    }

	/**
	 * Delete what needs deleting for that prop specifically. Model's delete method does the rest.
	 *
	 * @param int $id Record ID
	 * @param string $tableName (Probably phased out already)
	 *
	 * @return bool
	 */
	function delete ($id, $tableName) {

		$xrefTable = $this->getPropertyDefinition('xrefTable');
		$fkOwn = $this->getPropertyDefinition('fkOwn');

		if ($xrefTable && $fkOwn) {
			// Remove all existing xref records
			$db = KenedoPlatform::getDb();
			$query = "DELETE FROM `".$xrefTable."` WHERE `".$fkOwn."` = '".$db->getEscaped($id)."'";
			$db->setQuery($query);
			$db->query();
		}

		return true;

	}

	function check($data) {
		return true;
	}

	/**
	 * Values for this prop instead come from appendDataForGetRecord
	 * @see appendDataForGetRecord
	 * @param string $selectAliasPrefix
	 * @param string $selectAliasOverride
	 *
	 * @return string[]
	 */
	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getWheres($filters) {

		$a = $this->propertyName;
		$db = KenedoPlatform::getDb();
		$wheres = [];
		foreach ($filters as $filterName=>$searchValues) {

			if ($this->filterNameApplies($filterName) == false) {
				continue;
			}

			// Normalize search values to an array
			if (!is_array($searchValues)) {
				$searchValues = [$searchValues];
			}

			// Escape and quote the search values
			foreach ($searchValues as &$searchValue) {
				$searchValue = (is_numeric($searchValue)) ? $searchValue : "'".$db->getEscaped($searchValue)."'";
			}

			// Prepare the IN() part as string
			if (count($searchValues) != 0) {
				$inPart = implode(',', $searchValues);
			}
			else {
				$inPart = 'NULL';
			}

			$subQuery = "
			SELECT `".$this->getPropertyDefinition('fkOwn')."` 
			FROM `".$this->getPropertyDefinition('xrefTable')."` 
			WHERE `".$this->getPropertyDefinition('fkOther')."` IN (".$inPart.")";

			$keyPropName = $this->model->getTableKey();
			$props = $this->model->getProperties();
			$aliasBase = $props[$keyPropName]->getTableAlias();

			$wheres[] = "`".$aliasBase."`.`".$this->getPropertyDefinition('keyOwn')."` IN (".$subQuery.")";

		}

		return $wheres;
	}

	/**
	 * Adds an array with the xref values to the record (from tye mysql-concatenated string)
	 * @param $data
	 */
	public function appendDataForGetRecord(&$data) {

		// Safeguard, just in case there is a chance for having the method run twice
		if (empty($data->{$this->propertyName})) {
			$colKey = $this->getPropertyDefinition('keyOwn');
			$recordId = $data->{$colKey};
			$data->{$this->getSelectAlias()} = $this->getAssignments($recordId);
		}
		parent::appendDataForGetRecord($data);
	}

	/**
	 * @return string[]
	 */
	public function getJoinsForGetRecord() {

		$joins = array();

		// This is for adminconfig.active_languages: in this setup we just have an xref table with a column tag, we join in all of them
		// When it comes to load possible values, they come from a separate method, not dealing with the xref or the (non-existant) other table
		// If this sounds confusing, just pretend this block isn't there - it won't bite you later
		if ($this->getPropertyDefinition('activeLanguageHack', false) == true) {
			$joins[$this->getTableAlias()] = "LEFT JOIN `".$this->getPropertyDefinition('xrefTable')."` AS ".$this->getTableAlias()." ON 1 = 1";
			return $joins;
		}

		return $joins;
	}

	/**
	 * This one makes a custom table alias. Format is xref_(base model's name)_(joined model's name)
	 * @return string
	 */
	public function getTableAlias() {

		$joinedModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'), '');
		$joinedModel->languageTag = $this->model->languageTag;

		return 'xref_'.$this->model->getModelName().'_'.$joinedModel->getModelName();
	}

	/**
	 * This makes the prop's table column the foreign key field name of the 'other table'. It's used for searching and
	 * filtering, this way we get the system to search at the joined xref table's fk other. See getTableAlias to lift
	 * some confusion.
	 *
	 * @return string
	 */
	public function getTableColumnName() {
		return $this->getPropertyDefinition('fkOther');
	}

	/**
	 * Helper for making filter boxes
	 * @return string[] Key's are the 'other' table's primary key prop name, values the 'other' table's prim. key prop value
	 */
	function getPossibleFilterValues() {

		if ($this->getPropertyDefinition('modelClass')) {
			$selectModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
			$method = $this->getPropertyDefinition('modelMethod');
			$selectData = $selectModel->$method();
		}
		else {
			$db = KenedoPlatform::getDb();
			$query = "
			SELECT
				`".$this->getPropertyDefinition('keyOther')."` AS `id`,
				`".$this->getPropertyDefinition('displayColumnOther')."` AS `title`
			FROM `".$this->getPropertyDefinition('tableOther')."`
			";
			$db->setQuery($query);
			$selectData = $db->loadObjectList();
		}

		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));

		if (is_array($selectData) && count($selectData) > 0) {
			foreach ($selectData as $selectOption) {
				$options[$selectOption->{$this->getPropertyDefinition('keyOther')}] = $selectOption->{$this->getPropertyDefinition('displayColumnOther')};
			}
		}

		return $options;
	}

	/**
	 * Returns the assignments for given record
	 * @param int $id record ID
	 * @return int[]
	 */
	function getAssignments($id) {

		if ($this->getPropertyDefinition('activeLanguageHack', false) == true) {

			$db = KenedoPlatform::getDb();
			$query = "
			SELECT `".$this->getPropertyDefinition('fkOther')."` 
			FROM `".$this->getPropertyDefinition('xrefTable')."`
			";
			$db->setQuery($query);
			return $db->loadResultList();

		}
		else {
			$db = KenedoPlatform::getDb();
			$query = "
			SELECT `".$this->getPropertyDefinition('fkOther')."` 
			FROM `".$this->getPropertyDefinition('xrefTable')."`
			WHERE `".$this->getPropertyDefinition('fkOwn')."` = ".intval($id)."
			";
			$db->setQuery($query);
			return $db->loadResultList();
		}

	}

	/**
	 * Helper function for the admin template. Returns all possible selectable values, formatted for KenedoHtml.
	 * @return string[]|int[]
	 */
	protected function getSelectableValues() {
		// Get all values possible for the multi-select
		if ($this->getPropertyDefinition('modelClass')) {
			$selectModel              = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
			$selectModel->languageTag = $this->model->languageTag;
			$method                   = $this->getPropertyDefinition('modelMethod');
			$allValues                = $selectModel->$method();
		}
		else {
			$db = KenedoPlatform::getDb();
			$query = "
				SELECT `".$this->getPropertyDefinition('keyOther')."` AS id, `".$this->getPropertyDefinition('displayColumnOther')."` AS `title`
				FROM `".$this->getPropertyDefinition('tableOther')."`";
			$db->setQuery($query);
			$allValues = $db->loadObjectList();
		}

		// Make the possible values grouped well for KenedoHtml functions
		$options = array();
		if (is_array($allValues) && count($allValues) > 0) {
			foreach ($allValues as $selectOption) {
				$options[$selectOption->{$this->getPropertyDefinition('keyOther')}] = $selectOption->{$this->getPropertyDefinition('displayColumnOther')};
			}
		}

		asort($options, SORT_NATURAL);

		return $options;
	}

	/**
	 * Gives you a list of titles (up to 2) of assigned records
	 * @param object $record
	 *
	 * @return string
	 */
	function getOutputValueFromRecordData($record) {

		$assignmentIds = $record->{$this->propertyName};

		// Prepare the array for titles to show
		$titles = array();
		// Define max amount to show
		$max = 2;

		if (is_array($assignmentIds)) {

			// Get the model that gets us the records
			$model = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
			if (method_exists($model, 'getRecord') == false) {
				return '';
			}

			// Prime the counter for limiting records to show
			$i = 0;
			foreach ($assignmentIds as $assignmentId) {

				// Get the record
				$record = $model->getRecord($assignmentId);

				// See if we actually get something
				if ($record && !empty($record->{$this->getPropertyDefinition('displayColumnOther')})) {
					// Collect the title
					$titles[] = $record->{$this->getPropertyDefinition('displayColumnOther')};
					// Dial up the counter and break if we're over
					$i++;
					if ($i >= $max) {
						break;
					}
				}
			}

		}

		// Prepare the output
		$output = implode(', ', $titles);

		// Append the 'and x more' if there's more than we collected
		if (count($titles) < count($assignmentIds)) {
			$excess = count($assignmentIds) - count($titles);
			$output .= ' '.KText::sprintf('and %s more', $excess);
		}

		// Send it off
		return $output;

	}

}