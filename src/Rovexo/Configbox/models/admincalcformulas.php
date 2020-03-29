<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincalcformulas extends KenedoModel {

	function getTableName() {
		return '#__configbox_calculation_formulas';
	}

	function getTableKey() {
		return 'id';
	}

	function getDataFromRequest() {
		$data = new stdClass;
		$data->id = KRequest::getInt('id');
		$data->calc = KRequest::getString('calc','');
		return $data;
	}

	function store($data) {

		$db = KenedoPlatform::getDb();
		$success = $db->insertObject('#__configbox_calculation_formulas', $data);

		if ($success === false) {
			$this->setError(KText::sprintf('A database error occured while saving the calculation.'));
			return false;
		}
		else {
			return true;
		}

	}

	function getCalculationJson($id) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `calc` FROM `#__configbox_calculation_formulas` WHERE `id` = ".intval($id);
		$db->setQuery($query);
		return $db->loadResult();

	}

	function copyAcrossProducts($sourceCalcId, $copyCalcId, $copyIds) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM #__configbox_calculation_formulas WHERE `id` = ".$sourceCalcId;
		$db->setQuery($query);
		$formulaData = $db->loadObject();

		$formulaData->id = $copyCalcId;
		$formulaData->calc = ConfigboxCalculation::getFormulaCopy($formulaData->calc, $copyIds);

		$db->insertObject($this->getTableName(), $formulaData, $this->getTableKey());

	}

}