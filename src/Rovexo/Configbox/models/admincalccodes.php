<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincalccodes extends KenedoModel {

	function getTableName() {
		return '#__configbox_calculation_codes';
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
			'positionForm'=>10,
		);

		$propDefs['code'] = array(
			'name'=>'code',
			'label'=>KText::_('Code'),
			'type'=>'string',
			'options'=>'USE_TEXTAREA ALLOW_HTML ALLOW_RAW',
			'style'=>'width:410px;height:200px;padding:5px',
			'required'=>0,
			'positionForm'=>20,
		);

		$propDefs['element_id_a'] = array(
			'name'=>'element_id_a',
			'label'=>KText::_('Element for placeholder A'),
			'tooltip'=>KText::_('For convenience you can use the placeholders A-D. They represent the value the customer has entered in this element. Always keep a space before and after the placeholder in your formula.'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Not used'),
			'groupby'=>'joinedby_page_id_to_adminpages_product_id_display_value',
			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',
			'required'=>0,
			'positionForm'=>30,
		);

		$propDefs['element_id_b'] = array(
			'name'=>'element_id_b',
			'label'=>KText::_('Element for placeholder B'),
			'tooltip'=>KText::_('For convenience you can use the placeholders A-D. They represent the value the customer has entered in this element. Always keep a space before and after the placeholder in your formula.'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Not used'),
			'groupby'=>'joinedby_page_id_to_adminpages_product_id_display_value',
			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',
			'required'=>0,
			'positionForm'=>40,
		);

		$propDefs['element_id_c'] = array(
			'name'=>'element_id_c',
			'label'=>KText::_('Element for placeholder C'),
			'tooltip'=>KText::_('For convenience you can use the placeholders A-D. They represent the value the customer has entered in this element. Always keep a space before and after the placeholder in your formula.'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Not used'),
			'groupby'=>'joinedby_page_id_to_adminpages_product_id_display_value',
			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',
			'required'=>0,
			'positionForm'=>50,
		);

		$propDefs['element_id_d'] = array(
			'name'=>'element_id_d',
			'label'=>KText::_('Element for placeholder D'),
			'tooltip'=>KText::_('For convenience you can use the placeholders A-D. They represent the value the customer has entered in this element. Always keep a space before and after the placeholder in your formula.'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Not used'),
			'groupby'=>'joinedby_page_id_to_adminpages_product_id_display_value',
			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getRecords',
			'required'=>0,
			'positionForm'=>60,
		);

		// Overwrite prop defs to get internal element names to show
		$goInternal = CbSettings::getInstance()->get('use_internal_question_names');
		if ($goInternal) {
			$propDefs['element_id_a']['propNameDisplay'] = 'internal_name';
			$propDefs['element_id_b']['propNameDisplay'] = 'internal_name';
			$propDefs['element_id_c']['propNameDisplay'] = 'internal_name';
			$propDefs['element_id_d']['propNameDisplay'] = 'internal_name';
		}

		return $propDefs;

	}

	function copyAcrossProducts($sourceCalcId, $copyCalcId, $copyIds) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM #__configbox_calculation_codes WHERE `id` = ".$sourceCalcId;
		$db->setQuery($query);
		$codeRow = $db->loadObject();

		$codeRow->id = $copyCalcId;

		$calcModel = KenedoModel::getModel('ConfigboxModelAdmincalculations');

		if ($codeRow->element_id_a != null) {
			$codeRow->element_id_a = $copyIds['adminelements'][$codeRow->element_id_a];
		}

		if ($codeRow->element_id_b != null) {
			$codeRow->element_id_b = $copyIds['adminelements'][$codeRow->element_id_b];
		}

		if ($codeRow->element_id_c != null) {
			$codeRow->element_id_c = $copyIds['adminelements'][$codeRow->element_id_c];
		}

		if ($codeRow->element_id_d != null) {
			$codeRow->element_id_d = $copyIds['adminelements'][$codeRow->element_id_d];
		}

		$rawCode = $codeRow->code;

		if (stristr($rawCode,'ElementEntry') ) {
			preg_match_all("/ElementEntry\((.*?)\)/i", $rawCode, $matches);
			if (isset($matches[0])) {
				$questionId = $matches[1][0];
				$newQuestionId = $copyIds['adminelements'][$questionId];
				$rawCode = str_ireplace($matches[0][0], 'ElementEntry('.$newQuestionId.')', $rawCode);
			}
		}

		if (stristr($rawCode,'ElementPrice') ) {
			preg_match_all("/ElementPrice\((.*?)\)/i", $rawCode, $matches);
			if (isset($matches[0])) {
				$questionId = $matches[1][0];
				$newQuestionId = $copyIds['adminelements'][$questionId];
				$rawCode = str_ireplace($matches[0][0], 'ElementPrice('.$newQuestionId.')', $rawCode);
			}
		}

		if (stristr($rawCode,'ElementPriceRecurring') ) {
			preg_match_all("/ElementPriceRecurring\((.*?)\)/i", $rawCode, $matches);
			if (isset($matches[0])) {
				$questionId = $matches[1][0];
				$newQuestionId = $copyIds['adminelements'][$questionId];
				$rawCode = str_ireplace($matches[0][0], 'ElementPriceRecurring('.$newQuestionId.')', $rawCode);
			}
		}

		if (stristr($rawCode,'Calculation') ) {
			preg_match_all("/Calculation\((.*?)\)/i", $rawCode, $matches);
			if (isset($matches[0])) {
				$calcId = $matches[1][0];
				$newCalcId = $calcModel->copyAcrossProducts($calcId, $copyIds);
				$rawCode = str_ireplace($matches[0][0], 'Calculation('.$newCalcId.')', $rawCode);
			}
		}

		$codeRow->code = $rawCode;

		$db->insertObject('#__configbox_calculation_codes', $codeRow, 'id');


	}

}
