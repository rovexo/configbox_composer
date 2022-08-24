<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyJoin extends KenedoProperty {

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
        $sortProperty = $this->getPropertyDefinition('dropdownOrdering');
        if(!empty($sortProperty)) {
            usort($items, $this->getSorter($sortProperty));
        }
        else {
			$sortProperty = $this->getPropertyDefinition('propNameDisplay');
			usort($items, $this->getSorter($sortProperty));
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

		if (isset($record->{$this->propertyName . '_display_value'})) {
			$value = $record->{$this->propertyName . '_display_value'};
		}
		elseif(!empty($record->{$this->propertyName})) {
			$value = $record->{$this->propertyName};
		}
		else {
			if ($this->getPropertyDefinition('defaultlabel')) {
				$value = $this->getPropertyDefinition('defaultlabel');
			}
			else {
				$value = KText::_('None selected');
			}

		}

		return $value;

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

		// This is for the case when the prop defs say we want additional props from the joined model
		if ($this->getPropertyDefinition('joinAdditionalProps')) {
			$addProps = $this->getPropertyDefinition('joinAdditionalProps');
			foreach ($addProps as $addProp) {
				// If there is an alias defined
				if (!empty($addProp['selectAliasOverride'])) {
					$prefix = $selectAliasPrefix;
					$override = $addProp['selectAliasOverride'];
				}
				else {
					$prefix = $selectAliasPrefix .'joinedby_'.$this->propertyName.'_to_'.$parentModel->getModelName().'_';
					$override = '';
				}

				if (empty($parentProps[$addProp['propertyName']])) {
					KLog::log('Property: '.$this->model->getModelName().':'.$this->propertyName.' has joinAdditionalProps, but parent prop "'.$addProp['propertyName'].'", does not exist', 'error');
					continue;
				}

				$additionalSelects = $parentProps[$addProp['propertyName']]->getSelectsForGetRecord($prefix, $override);
				$selects = array_merge($selects, $additionalSelects);
			}
		}

		// If the prop states that we deal with a parent model, join it (will recurse as deep as it goes with parents)
		if ($this->getPropertyDefinition('parent', 0) == 1) {

			foreach ($parentProps as $parentProp) {

				if ($parentProp->getPropertyDefinition('type') == 'join' && $parentProp->getPropertyDefinition('parent') == true) {
					$prefix = $selectAliasPrefix . 'joinedby_'.$this->propertyName.'_to_'.$parentModel->getModelName().'_';
					$parentSelects = $parentProp->getSelectsForGetRecord($prefix);
					$selects = array_merge($selects, $parentSelects);
				}
			}

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

			// Go through the additional props that shall be in the record
			if ($this->getPropertyDefinition('joinAdditionalProps')) {
				$addProps = $this->getPropertyDefinition('joinAdditionalProps');
				foreach ($addProps as $addProp) {

					if (empty($parentProps[$addProp['propertyName']])) {
						KLog::log('Property: '.$this->model->getModelName().':'.$this->propertyName.' has joinAdditionalProps, but parent prop "'.$addProp['propertyName'].'", does not exist', 'error');
						continue;
					}

					$additionalJoins = $parentProps[$addProp['propertyName']]->getJoinsForGetRecord();
					$joins = array_merge($joins, $additionalJoins);
				}
			}
		}

		$parentDef = $this->getPropertyDefinition('parent');
		if (empty($parentDef)) {
			return $joins;
		}

		// Join any parent props that are joins (This will go all the way up recursively, the check for the parent def
		// happens at the start of the method.
		foreach ($parentProps as $parentProp) {
			if ($parentProp->getPropertyDefinition('type') == 'join') {
				$parentJoins = $parentProp->getJoinsForGetRecord();
				$joins = array_merge($joins, $parentJoins);
			}

			if ($parentProp->getPropertyDefinition('type') == 'multiselect' && $parentProp->getPropertyDefinition('addDropdownFilter')) {
				$parentJoins = $parentProp->getJoinsForGetRecord();
				$joins = array_merge($joins, $parentJoins);
			}
		}

		return $joins;

	}

	public function getWheres($filters) {
		$wheres = parent::getWheres($filters);

		$parentDef = $this->getPropertyDefinition('parent');
		if (empty($parentDef)) {
			return $wheres;
		}

		$parentModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
		$parentModel->languageTag = $this->model->languageTag;
		$parentProps = $parentModel->getProperties();
		foreach ($parentProps as $parentProp) {
			$parentWheres = $parentProp->getWheres($filters);
			$wheres = array_merge($wheres, $parentWheres);
		}

		return $wheres;

	}

	/**
	 * @return string|string[]
	 */
	public function getFilterName() {

		if ($this->getPropertyDefinition('addDropdownFilter', false) == false && $this->getPropertyDefinition('addSearchBox', false) == false) {
			return '';
		}

		$filterNames = array();

		if ($this->getPropertyDefinition('addDropdownFilter')) {
			$filterNames[] = parent::getFilterName();
		}

		if ($this->getPropertyDefinition('filterparents')) {
			$parentModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
			$parentModel->languageTag = $this->model->languageTag;

			$parentProps = $parentModel->getProperties();
			foreach ($parentProps as $parentProp) {
				$parentFilterName = $parentProp->getFilterName();
				if (is_array($parentFilterName)) {
					$filterNames = array_merge($filterNames, $parentFilterName);
				}
				elseif(!empty($parentFilterName)) {
					$filterNames[] = $parentFilterName;
				}

			}
		}

		return $filterNames;

	}

	public function getFilterInput(KenedoView $view, $filters) {

		if (!$this->getPropertyDefinition('addSearchBox') && !$this->getPropertyDefinition('addDropdownFilter')) {
			return '';
		}

		$filterInputs = array();

		$filterNames = $this->getFilterName();
		$filterName = $filterNames[0];
		$filterNameRequest = $this->getFilterNameRequest();
		$filterHtmlName = str_replace('.', '_', $filterName);

		if (is_array($filterNameRequest)) {
			$filterNameRequest = $filterNameRequest[0];
		}

		$options = $this->getPossibleFilterValues();
		$chosenValue = !empty($filters[$filterName]) ? $filters[$filterName] : NULL;

		$html = KenedoHtml::getSelectField($filterNameRequest, $options, $chosenValue, 'all', false, 'listing-filter', $filterHtmlName);

		$filterInputs[$this->getTableAlias().'.'.$this->getTableColumnName()] = $html;

		if ($this->getPropertyDefinition('filterparents')) {

			$parentModel = KenedoModel::getModel($this->getPropertyDefinition('modelClass'));
			$parentModel->languageTag = $this->model->languageTag;

			$parentProps = $parentModel->getProperties();
			foreach ($parentProps as $parentProp) {
				$parentFilterInputs = $parentProp->getFilterInput($view, $filters);
				if (is_array($parentFilterInputs)) {
					$filterInputs = array_merge($filterInputs, $parentFilterInputs);
				}
				elseif(!empty($parentFilterInputs)) {
					$filterInputs[$parentProp->getTableAlias().'.'.$parentProp->getTableColumnName()] = $parentFilterInputs;
				}

			}

		}

		return $filterInputs;

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

}