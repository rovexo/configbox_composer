<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyGroupPrice extends KenedoProperty {

	/**
	 * Careful, not actually used - it's just for documentation.
	 * @var string Name of the property that gets overridden with this prop
	 */
	protected $overridePropertyName;

	/**
	 * Careful, not actually used - it's just for documentation.
	 * @var string Used for showing the base currency
	 */
	protected $unit;

	/**
	 * In here we take apart the JSON we should receive and sanitize it (Force the structure we expect and normalize
	 * the price number to english decimal symbols)
	 * @param object $data
	 */
	function getDataFromRequest(&$data) {

		$json = KRequest::getVar($this->propertyName, '[]', 'METHOD');
		$json = stripslashes($json);

		$overridesFromPost = json_decode($json, true);

		// Loop through the array from the POSTED JSON and build a fresh array structure as it's designed to be.
		$overrides = array();
		foreach ($overridesFromPost as &$override) {
			$overrides[] = array(
				'group_id' => intval($override['group_id']),
				'price' => str_replace(KText::_('DECIMAL_MARK', '.'), '.', floatval($override['price'])),
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

		$overridesToKeep = array();
		foreach ($overrides as $override) {
			// This weeds out overrides for groups that do not exist anymore.
			if (empty($groups[$override['group_id']])) {
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

}