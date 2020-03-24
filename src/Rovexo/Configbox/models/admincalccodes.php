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

}
