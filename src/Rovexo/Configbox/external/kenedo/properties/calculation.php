<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyCalculation extends KenedoProperty {

	/**
	 * Joins that come in with 0 are regarded as NULL (and will be stored in the DB as such)
	 * @param $data
	 */
	function getDataFromRequest( &$data ) {

		$requestVar = KRequest::getString($this->propertyName, NULL);

		if ($requestVar === '0') {
			$data->{$this->propertyName} = NULL;
		}
		else {
			$data->{$this->propertyName} = $requestVar;
		}

	}

	function check( $data ) {
		$this->resetErrors();
		if ($this->isRequired() && $this->applies($data)) {
			if (empty($data->{$this->propertyName})) {
				$this->setError(KText::sprintf('Field %s cannot be empty.', $this->getPropertyDefinition('label')));
				return false;
			}
		}

		return true;
	}

    function getParentModelRecords() {

        $joinModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
        $joinModel->languageTag = $this->model->languageTag;

        // Prepare the method to get the joined data
        $method = $this->getPropertyDefinition('modelMethod');

        // Check if the method specified exists then call it
        if (method_exists($joinModel, $method)) {
            $items = $joinModel->$method();
        }
        else {
            return false;
        }
        // sort items
        $sortItemsBy = $this->getPropertyDefinition('dropdownOrdering');
        if(!empty($sortItemsBy)) {
            usort($items, $this->getSorter($sortItemsBy));
        }

        return $items;

    }

    protected function getSorter($key) {
        return function ($a, $b) use ($key) {
            return strnatcmp($a->$key, $b->$key);
        };
    }

	function groupItems(&$items, $groupKey) {

		$groupedItems = array();

		foreach ($items as &$item) {
			$groupedItems[$item->{$groupKey}][] = $item;
		}

		return $groupedItems;

	}

	function getOutputValueFromRecordData($record) {



		if (empty($record->{$this->propertyName})) {
			return '';
		}

		$calculations = $this->getCalculations();

		if (isset($calculations[$record->{$this->propertyName}])) {
			return $calculations[$record->{$this->propertyName}]->name;
		}
		else {
			return 'Calculation '.$record->{$this->propertyName}.' not found';
		}

	}

	protected $memoCalculations = null;

	protected function getCalculations() {

		if ($this->memoCalculations === null) {
			$model = KenedoModel::getModel('ConfigboxModelAdmincalculations');
			$calculations = $model->getRecords();
			foreach ($calculations as $calculation) {
				$this->memoCalculations[$calculation->id] = $calculation;
			}
		}

		return $this->memoCalculations;
	}

	/**
	 * The join property adds its column to the selects and in case the 'parent' def is 1, it does more:
	 *
	 * 1) Any join has a def called 'propNameDisplay' which is the joined table's property that names the record
	 * (e.g. a join for the product_id has the product's title prop name as 'propNameDisplay'). It adds this to the
	 * field list with a special alias, so we can show the joined record's title more easily in listings.
	 *
	 * 2) With the def 'joinAdditionalProps', more props of the joined table will be added. 'joinAdditionalProps' is
	 * an array of arrays with 2 key/value pairs. 'propertyName' and an optional 'selectAliasOverride'. First is the
	 * prop's name, the second forces the name of the select alias (prefix will still be in the name).
	 *
	 * 3) It will add all join props of the parents tables recursively, but only goes deeper if the 'parent' def is on.
	 *
	 * @param string $selectAliasPrefix
	 * @param string $selectAliasOverride
	 * @return array|string[]
	 * @throws Exception
	 */
	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {

		$selects = parent::getSelectsForGetRecord($selectAliasPrefix, $selectAliasOverride);

		if ($this->getPropertyDefinition('isPseudoJoin') == true) {
			return $selects;
		}

		$parentModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
		$parentModel->languageTag = $this->model->languageTag;

		$parentProps = $parentModel->getProperties();

		// Add selects for the 'display' property of the joined model (but only if it is a 'normal' model with a table)
		if ($this->getPropertyDefinition('propNameDisplay') && $parentModel->getTableName() != '') {

			$propName = $this->getPropertyDefinition('propNameDisplay');

			// In case that prop does not exist, throw an exception (must be a misconfiguration)
			if (empty($parentProps[$propName])) {
				throw new Exception('Property "'.$this->propertyName.'" in "'.$this->model->getModelName().'" has a propNameDisplay def with value "'.$propName.'", for model "'.$this->getPropertyDefinition('modelClass').'". Problem is that there is no such prop there.');
			}

			// Put the select alias override in place if there is one
			if ($selectAliasOverride) {
				$override = $selectAliasPrefix.$selectAliasOverride.'_display_value';
			}
			else {
				$override = $selectAliasPrefix.$this->propertyName.'_display_value';
			}

			// Now get the selects for that joined prop and merge them into the ones we've got already
			$joinDisplayPropSelects = $parentProps[$propName]->getSelectsForGetRecord('', $override);
			$selects = array_merge($selects, $joinDisplayPropSelects);

		}

		return $selects;


	}

	/**
	 * If the 'parent' def is on, it recurses into parent models and adds joins for certain props of that model. See
	 * infos for KenedoPropertyJoin::getSelectsForGetRecord() for details.
	 * @see KenedoPropertyJoin::getSelectsForGetRecord()
	 * @return string[]
	 * @throws Exception When propNameKey in propDefs does not exist in defined modelClass
	 */
	public function getJoinsForGetRecord() {

		$joins = parent::getJoinsForGetRecord();

		if ($this->getPropertyDefinition('isPseudoJoin') == true) {
			return $joins;
		}

		// Get some info about the parent model
		$parentModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
		$parentModel->languageTag = $this->model->languageTag;
		$parentProps = $parentModel->getProperties();

		if ($parentModel->getTableName() != '') {
			$parentKeyField = $this->getPropertyDefinition('propNameKey');

			// In case that prop does not exist, throw an exception (must be a misconfiguration)
			if (empty($parentProps[$parentKeyField])) {
				throw new Exception('Property "'.$this->propertyName.'" in "'.$this->model->getModelName().'" has a propNameKey def with value "'.$parentKeyField.'", for model "'.$this->getPropertyDefinition('modelClass').'". Problem is that there is no such prop there.');
			}

			$parentTableAlias = $parentProps[$parentKeyField]->getTableAlias();

			// Do the join statement for the parent model's table
			$joins[$parentTableAlias] = "LEFT JOIN `".$parentModel->getTableName()."` AS `".$parentTableAlias."`
					ON `".$parentTableAlias."`.`".$this->getPropertyDefinition('propNameKey')."` = `".$this->getTableAlias()."`.`".$this->getTableColumnName()."`";

			$parentDisplayPropName = $this->getPropertyDefinition('propNameDisplay');
			$parentPropJoins = $parentProps[$parentDisplayPropName]->getJoinsForGetRecord();
			$joins = array_merge($joins, $parentPropJoins);

		}

		return $joins;

	}

	protected function getPossibleFilterValues() {

		$joinModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
		$joinModel->languageTag = $this->model->languageTag;

		$method = $this->getPropertyDefinition('modelMethod');
		$records = $joinModel->$method();

		// Group if necessary
		if ($this->getPropertyDefinition('groupby')) {
			$groupKey = $this->getPropertyDefinition('groupby');
			$groupedRecords = array();
			foreach ($records as $record) {
				$groupedRecords[$record->{$groupKey}][] = $record;
			}
			$records = $groupedRecords;
		}

		// Make an array of select options
		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));

		// For convenience
		$keyNameParent = $this->getPropertyDefinition('propNameKey');
		$displayKey = $this->getPropertyDefinition('propNameDisplay');

		// Loop through the records and populate the options array
		foreach ($records as $record) {
			// Grouped records
			if (is_array($record)) {
				foreach ($record as $groupedRecord) {
					$options[$groupedRecord->{$groupKey}][$groupedRecord->{$keyNameParent}] = $groupedRecord->{$displayKey};
				}
			}
			// Normal records
			else {
				$options[$record->{$keyNameParent}] = $record->{$displayKey};
			}
		}

		return $options;

	}

	function copyCalculation($record, $copyIds) {

		$calculationId = $record->{$this->propertyName};

		// If we got no calculation, no copying needed
		if ($calculationId === null) {
			return;
		}

		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');
		$newCalculationId = $calcModel->copyAcrossProducts($calculationId, $copyIds);

		$db = KenedoPlatform::getDb();

		if ($this->getPropertyDefinition('storeExternally')) {
			$tableName = $this->getPropertyDefinition('foreignTableName');
			$tableKeyCol = $this->getPropertyDefinition('foreignTableKey');
		}
		else {
			$tableName = $this->model->getTableName();
			$tableKeyCol = $this->model->getTableKey();
		}

		$columnName = $this->getTableColumnName();

		$query = "
		UPDATE `".$tableName."`
		SET `".$columnName."` = ".intval($newCalculationId)."
		WHERE `".$tableKeyCol."` = ".intval($record->{$this->model->getTableKey()})."
		";
		$db->setQuery($query);
		$db->query();

	}

}