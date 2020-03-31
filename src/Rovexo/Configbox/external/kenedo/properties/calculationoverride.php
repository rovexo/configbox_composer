<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyCalculationOverride extends KenedoProperty {

	/**
	 * Careful, not actually used - it's just for documentation.
	 * @var string Name of the property that gets overridden with this prop
	 */
	protected $overridePropertyName;

	/**
	 * In here we take apart the JSON we should receive and sanitize it (Force the structure we expect and normalize
	 * the price number to english decimal symbols)
	 * @param object $data
	 */
	function getDataFromRequest(&$data) {

        if ($this->applies($data) == false) {
			$data->{$this->propertyName} = json_encode(array());
			return;
        }

		$json = KRequest::getVar($this->propertyName, '[]', 'METHOD');
		$json = stripslashes($json);

		$overridesFromPost = json_decode($json, true);

		// Loop through the array from the POSTED JSON and build a fresh array structure as it's designed to be.
		$overrides = array();
		foreach ($overridesFromPost as &$override) {
			$overrides[] = array(
				'group_id' => intval($override['group_id']),
				'calculation_id' => $override['calculation_id'] ? intval($override['calculation_id']) : NULL,
			);
		}

		// Finally, add it to the data object
		$data->{$this->propertyName} = json_encode($overrides);

	}

	/**
	 * Simply outputs the amount of overrides (e.g. "2 overrides")
	 * @param object $record
	 * @return string
	 */
	function getOutputValueFromRecordData($record) {

		$count = count(json_decode($record->{$this->propertyName}));

		if ($count == 0) {
			return 'No overrides';
		}
		elseif ($count == 1) {
			return '1 override';
		}
		else {
			return $count . ' overrides';
		}

	}

	/**
	 * Takes the prop's JSON from the data it has and returns an array with overrides (weeds out overrides for groups
	 * that no longer exist).
	 * @return array
	 */
	public function getOverrides() {

		$overrides = json_decode($this->data->{$this->propertyName}, true);
		$groups = $this->getCustomerGroups();
		$calcs = $this->getCalculations();

		$overridesToKeep = array();
		foreach ($overrides as $override) {

			// This weeds out overrides for groups that do not exist anymore.
			if (empty($groups[$override['group_id']])) {
				continue;
			}

			// This weeds out overrides using calculations that do not exist anymore.
			if ($override['calculation_id'] && empty($calcs[$override['calculation_id']])) {
				continue;
			}

			$overridesToKeep[] = $override;

		}

		return $overridesToKeep;

	}

	/**
	 * Helper function for admin template. Gives you an array with all customer group records with IDs as keys.
	 * @return object[]
	 */
	protected function getCustomerGroups() {
		$groups = KenedoModel::getModel('ConfigboxModelAdmincustomergroups')->getRecords(array(), array(), array('propertyName'=>'title', 'direction'=>'ASC'));
		$response = array();
		foreach ($groups as $group) {
			$response[$group->id] = $group;
		}
		return $response;
	}

	/**
	 * Helper fucntion for admin template. Gives you the label of the property that gets overridden.
	 * @return string
	 */
	protected function getOverrideLabel() {

		$propName = $this->getPropertyDefinition('overridePropertyName');
		$props = $this->model->getPropertyDefinitions();
		if (!empty($props[$propName]['label'])) {
			return $props[$propName]['label'];
		}
		else {
			return '';
		}

	}

	protected function getCalculations($productId = NULL) {

		// If we got a product ID, we load only that product's calculations. Here we prepare the filter instruction
		$filter = ($productId) ? array('admincalculations.product_id' => $productId) : array();

		$model = KenedoModel::getModel('ConfigboxModelAdmincalculations');
		$calcs = $model->getRecords($filter, array(), array('propertyName' => 'name', 'direction' => 'ASC'));

		$response = array();
		foreach ($calcs as $calc) {
			$response[$calc->id] = $calc;
		}
		return $response;

	}

	protected function getCalculationsDropdownOptions($productId = NULL) {

		$options = array();

		$propName = $this->getPropertyDefinition('overridePropertyName');
		$props = $this->model->getPropertyDefinitions();
		if (!empty($props[$propName]['defaultlabel'])) {
			$options[0] = $props[$propName]['defaultlabel'];
		}
		else {
			$options[0] = KText::_('No calculation');
		}

		$calcs = $this->getCalculations($productId);

		foreach ($calcs as $calc) {
			$options[$calc->id] = $calc->name;
		}

		return $options;


	}

	function copyOverrides($record, $copyIds) {

		$overrides = $record->{$this->propertyName};

		// If we got no override, no copying needed
		if ($overrides == '' || $overrides === '[]') {
			return;
		}

		$overrides = json_decode($record->{$this->propertyName}, true);

		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');

		foreach ($overrides as &$override) {
			if ($override['calculation_id'] === NULL || $override['calculation_id'] === 0) {
				// Normalize any zeros to nulls
				$override['calculation_id'] = NULL;
				continue;
			}

			$override['calculation_id'] = $calcModel->copyAcrossProducts($override['calculation_id'], $copyIds);

		}

		$overrides = json_encode($overrides);

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
		SET `".$columnName."` = '".$db->getEscaped($overrides)."'
		WHERE `".$tableKeyCol."` = ".intval($record->{$this->model->getTableKey()})."
		";
		$db->setQuery($query);
		$db->query();

	}

}