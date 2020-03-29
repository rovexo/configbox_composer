<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincalculations extends KenedoModel {

	function getTableName() {
		return '#__configbox_calculations';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listing'=>1,
			'order'=>100,
			'positionForm'=>100,
		);

		$propDefs['name'] = array(
			'name'=>'name',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'allow'=>'[A-Za-z0-9_]',
			'required'=>1,
			'listing'=>10,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincalculations',
			'search'=>1,
			'filter'=>1,
			'order'=>1,
			'positionForm'=>200,
		);

		$propDefs['product_id'] = array(
			'name'=>'product_id',
			'label'=>KText::_('Product'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Product'),
			'tooltip'=>KText::_('PROP_TOOLTIP_CALCULATION_PRODUCT_ID'),

			'modelClass'=>'ConfigboxModelAdminproducts',
			'modelMethod'=>'getFilterSelectData',

			'lockedAfterStore'=>true,
			'parent'=>1,
			'required'=>1,
			'listing'=>20,
			'order'=>2,
			'filter'=>2,
			'listingwidth'=>'200px',
			'positionForm'=>300,
		);

		$propDefs['type'] = array(
			'name'=>'type',
			'label'=>KText::_('Type'),
			'type'=>'radio',
			'default'=>'matrix',
			'choices'=>array('matrix'=>KText::_('Matrix'), 'formula'=>KText::_('Formula'), 'code'=>KText::_('Code')),
			'required'=>1,
			'listing'=>30,
			'order'=>3,
			'positionForm'=>400,
		);

		return $propDefs;

	}

	function getDetailsTasks() {

		$tasks = array(
			array('title'=>KText::_('Save and close'), 'task'=>'store', 'primary' => true),
			array('title'=>KText::_('Save'), 			'task'=>'apply'),
			array('title'=>KText::_('Cancel'), 		'task'=>'cancel'),
		);

		return $tasks;

	}


	/**
	 * Stores the data of the selected calculation type
	 *
	 * @param int $id id of the record
	 * @param bool $wasInsert if it was inserted or updated
	 * @return bool true if all good, false if insertion should be reverted
	 *
	 */
	function afterStore($id, $wasInsert) {

		$query = "SELECT `type` FROM `#__configbox_calculations` WHERE `id` = ".intval($id);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$type = $db->loadResult();

		// CAREFUL: This is the calculation type's model, not this model
		$model = $this->getModelForCalcType($type);

		// Get the request data for matrices
		$data = $model->getDataFromRequest();

		// Set the ID to be the one from the the calculation record
		$data->id = $id;

		// Prepare it all
		$model->prepareForStorage($data);

		// Check if the data validates
		$checkResult = $model->validateData($data);

		if ($checkResult === false) {
			$this->setErrors($model->getErrors());
			return false;
		}

		$success = $model->store($data);

		// Bounce on issues
		if ($success === false) {
			$this->setErrors($model->getErrors());
			return false;
		}

		$types = $this->getPossibleTypes();

		// Delete data from other types (Say user changed calculation from code to formula, delete any data from code
		// (and matrix just because it's easier to delete any other))
		foreach ($types as $possibleType) {
			if ($type != $possibleType) {
				$model = $this->getModelForCalcType($possibleType);
				$model->delete($data->id);
			}
		}

		return true;
	}

	/**
	 * @return string[] The calculation types we have got
	 */
	protected function getPossibleTypes() {
		return array('matrix', 'formula', 'code');
	}

	/**
	 * @param string $type (matrix, formula, code)
	 * @return ConfigboxModelAdmincalccodes|ConfigboxModelAdmincalcformulas|ConfigboxModelAdmincalcmatrices
	 * @throws Exception if type does not exist
	 */
	protected function getModelForCalcType($type) {

		switch ($type) {

			case 'matrix':
				$model = KenedoModel::getModel('ConfigboxModelAdmincalcmatrices');
				break;

			case 'formula':
				$model = KenedoModel::getModel('ConfigboxModelAdmincalcformulas');
				break;

			case 'code':
				$model = KenedoModel::getModel('ConfigboxModelAdmincalccodes');
				break;

			default:
				throw new Exception('Model for calculation type "'.$type.'" requested, but does not exist');
				break;

		}

		return $model;

	}

	/**
	 * @var int[] Holds IDs of already copied calculations (key is old id, value is new id)
	 */
	static $alreadyCopiedCalculations = array();

	/**
	 * @param int $id Calculation ID
	 * @param array $copyIds Infos about old and new IDs
	 * @return int ID of copied calculation
	 */
	function copyAcrossProducts($id, $copyIds) {

		if (isset(self::$alreadyCopiedCalculations[$id])) {
			return self::$alreadyCopiedCalculations[$id];
		}

		$calculation = $this->getRecord($id);

		if (!$calculation) {
			KLog::log('Requested a calculation copy of ID '.$id.', but calculation does not exist', 'error');
			throw new Exception('Requested a calculation copy of ID '.$id.', but calculation does not exist');
		}

		$db = KenedoPlatform::getDb();

		$data = new stdClass();
		$data->id = NULL;
		$data->name = $calculation->name;
		$data->type = $calculation->type;
		$data->product_id = $copyIds['adminproducts'][$calculation->product_id];

		$db->insertObject($this->getTableName(), $data, $this->getTableKey());
		$calculationCopyId = $data->id;
		self::$alreadyCopiedCalculations[$id] = $calculationCopyId;


		$model = $this->getModelForCalcType($calculation->type);
		$model->copyAcrossProducts($id, $calculationCopyId, $copyIds);

		return $calculationCopyId;
	}

	/**
	 * Additionally to the regular check this one looks for calculation overrides.
	 * @param int $id
	 * @return bool
	 */
	function canDelete($id) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT * FROM `#__configbox_xref_element_option` 
		WHERE `price_calculation_overrides` != '[]' OR `price_recurring_calculation_overrides` != '[]'
		";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$problemItems = array();

		foreach ($items as $item) {
			$overrides = json_decode($item->price_calculation_overrides, true);
			foreach ($overrides as $override) {
				if ($override['calculation_id'] == $id) {
					$problemItems[] = $item;
				}
			}
			$overrides = json_decode($item->price_recurring_calculation_overrides, true);
			foreach ($overrides as $override) {
				if ($override['calculation_id'] == $id) {
					$problemItems[] = $item;
				}
			}
		}

		if (count($problemItems)) {
			$ass = ConfigboxCacheHelper::getAssignments();
			foreach ($problemItems as $problemItem) {
				$productId = $ass['xref_to_product'][$problemItem->id];
				$productTitle = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 1, $productId);
				$elementTitle = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $problemItem->element_id);
				$optionTitle = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $problemItem->option_id);
				$this->setError(KText::sprintf('Cannot delete calculation %s because it is used in a calculation override in product %s, question %s, answer %s', $id, $productTitle, $elementTitle, $optionTitle));
			}
			// Let the parent class add any issues, just in case, then return false
			parent::canDelete($id);
			return false;

		}
		else {
			return parent::canDelete($id);
		}

	}

	/**
	 * By now the foreign key constraints (they use CASCADE) will do that for us, but anyways.
	 * @param int $id
	 * @return bool
	 */
	function afterDelete($id) {

		$db = KenedoPlatform::getDb();

		$query = "DELETE FROM `#__configbox_calculation_matrices` WHERE `id` = ".(int)$id;
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__configbox_calculation_matrices_data` WHERE `id` = ".(int)$id;
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__configbox_calculation_formulas` WHERE `id` = ".(int)$id;
		$db->setQuery($query);
		$db->query();

		$query = "DELETE FROM `#__configbox_calculation_codes` WHERE `id` = ".(int)$id;
		$db->setQuery($query);
		$db->query();

		return true;

	}

	function getRecordUsageInfo() {

		$usage = array(

			'com_configbox'=> array(

				'ConfigboxModelAdminelements'=> array(
					array(
						'titleField'=>'title',
						'fkField'=>'calcmodel_id_min_val',
						'controller'=>'adminelements',
						'name'=>KText::_('Question validation minimum value'),
					),
					array(
						'titleField'=>'title',
						'fkField'=>'calcmodel_id_max_val',
						'controller'=>'adminelements',
						'name'=>KText::_('Question validation maximum value'),
					),
					array(
						'titleField'=>'title',
						'fkField'=>'calcmodel_weight',
						'controller'=>'adminelements',
						'name'=>KText::_('Question weight calculation'),
					),
					array(
						'titleField'=>'title',
						'fkField'=>'calcmodel',
						'controller'=>'adminelements',
						'name'=>KText::_('Question price calculation'),
					),
					array(
						'titleField'=>'title',
						'fkField'=>'calcmodel_recurring',
						'controller'=>'adminelements',
						'name'=>KText::_('Question Recurring price calculation'),
					),

				),

				'ConfigboxModelAdminxrefelementoptions'=> array(
					array(
						'titleField'=>'id',
						'fkField'=>'calcmodel',
						'controller'=>'adminoptionassignments',
						'name'=>KText::_('Answer price calculation'),
					),
					array(
						'titleField'=>'id',
						'fkField'=>'calcmodel_recurring',
						'controller'=>'adminoptionassignments',
						'name'=>KText::_('Answer recurring price calculations'),
					),
					array(
						'titleField'=>'id',
						'fkField'=>'calcmodel_weight',
						'controller'=>'adminoptionassignments',
						'name'=>KText::_('Answer weight calculation'),
					),

				),

				'ConfigboxModelAdmincalcmatrices'=> array(
					array(
						'titleField'=>'id',
						'fkField'=>'column_calc_id',
						'controller'=>'admincalculations',
						'name'=>KText::_('Calculation matrix, Column parameter'),
					),
					array(
						'titleField'=>'id',
						'fkField'=>'row_calc_id',
						'controller'=>'admincalculations',
						'name'=>KText::_('Calculation matrix, Row parameter'),
					),
					array(
						'titleField'=>'id',
						'fkField'=>'calcmodel_id_multi',
						'controller'=>'admincalculations',
						'name'=>KText::_('Calculation matrix, Calculated Multiplier'),
					),
				),

			),

		);

		return $usage;

	}

}