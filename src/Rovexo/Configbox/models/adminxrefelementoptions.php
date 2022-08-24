<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminxrefelementoptions extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_xref_element_option';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'positionList'=>10,
			'canSortBy'=>true,
			'label'=>KText::_('ID'),
			'positionForm'=>10000,
		);

		$propDefs['element_id'] = array(
			'name'=>'element_id',
			'label'=>KText::_('Question'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Question'),

			'modelClass'=>'ConfigboxModelAdminelements',
			'modelMethod'=>'getFilterSelectData',

			'required'=>1,

			'joinAdditionalProps'=>array(
				array('propertyName'=>'question_type', 				'selectAliasOverride'=>'question_type'),
				array('propertyName'=>'is_shapediver_control', 		'selectAliasOverride'=>'is_shapediver_control'),
				array('propertyName'=>'shapediver_parameter_id', 	'selectAliasOverride'=>'shapediver_parameter_id'),
			),

			'parent'=>1,
			'canSortBy'=>true,
			'addDropdownFilter'=>true,
			'invisible'=>true,
			'positionForm'=>20000,
		);

		$propDefs['option_id'] = array(
			'name'=>'option_id',
			'label'=>KText::_('Reused Answer'),
			'tooltip'=>KText::_('You can choose an existing answer or create a new one. All data on the left will shared wherever you use this answer.'),
			'defaultlabel'=>KText::_('Make a new answer'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdminoptions',
			'modelMethod'=>'getFilterSelectData',
			'required'=>0,
			'positionList'=>50,
			'canSortBy'=>true,
			'invisible'=>false,
			'listCellWidth'=>'150px',
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminoptionassignments',
			'positionForm'=>30000,
		);

		$propDefs['default'] = array(
			'name'=>'default',
			'label'=>KText::_('FIELD_LABEL_ANSWER_DEFAULT'),
			'labelList'=>KText::_('LISTING_LABEL_ANSWER_DEFAULT'),
			'tooltip'=>KText::_('TOOLTIP_ANSWER_DEFAULT'),
			'type'=>'boolean',
			'positionList'=>77,
			'positionForm'=>40000,
		);


		$propDefs['internal_name'] = array(
			'name'=>'internal_name',
			'label'=>KText::_('FIELD_LABEL_COMPONENT_INTERNAL_NAME'),
			'type'=>'string',
			'required'=>0,
			'canSortBy'=>true,
			'invisible'=>true,
			'listCellWidth'=>'150px',
			'positionForm'=>41000,
		);

		if (CbSettings::getInstance()->get('use_internal_answer_names')) {
			$propDefs['internal_name']['invisible'] = false;
			$propDefs['internal_name']['positionList'] = $propDefs['option_id']['positionList'];
			$propDefs['internal_name']['makeEditLink'] = true;
			$propDefs['internal_name']['component'] = $propDefs['option_id']['component'];
			$propDefs['internal_name']['controller'] = $propDefs['option_id']['controller'];
			unset($propDefs['option_id']['makeEditLink']);
			$propDefs['option_id']['positionList'] = 0;
		}


		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Enable this answer?'),
			'labelList'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'positionList'=>80,
			'tooltip'=>KText::_('You can disable this answer temporarily.'),
			'listCellWidth'=>'60px',
			'positionForm'=>45000,
		);

		$propDefs['option_picker_image'] = array(
			'name'=>'option_picker_image',
			'label'=>KText::_('Picker Image'),
			'type'=>'image',
			'appendSerial'=>1,
			'allowedExtensions'=>array('jpg','jpeg','gif','tif','bmp','png'),
			'allowedMimeTypes'=>array('image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png'),
			'maxFileSizeKb'=>'2000',
			'dirBase'=>KenedoPlatform::p()->getDirDataStore().'/public/answer_picker_images',
			'urlBase'=>KenedoPlatform::p()->getUrlDataStore().'/public/answer_picker_images',
			'required'=>0,
			'tooltip'=>KText::_('Upload an image here and it will be shown instead of the radio button/checkbox. No effect on drop-down fields.'),
			'options'=>'FILENAME_TO_RECORD_ID SAVE_FILENAME',
			'positionForm'=>45500,
			'appliesWhen'=>array(
				'question_type' => 'images',
			),

		);

		$propDefs['rule_start'] = array(
			'name'=>'rule_start',
			'type'=>'groupstart',
			'title'=>KText::_('Rule'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>50000,
		);

		$propDefs['rules'] = array(
			'name'=>'rules',
			'label'=>KText::_('FIELD_LABEL_ANSWER_RULE'),
			'labelList'=>KText::_('Rule'),
			'tooltip'=>KText::_('TOOLTIP_ANSWER_RULE'),
			'type'=>'rule',
			'textWhenNoRule'=>KText::_('Show the answer in any case.'),
			'required'=>0,
			'options'=>'ALLOW_RAW',
			'positionList'=>70,
			'positionForm'=>60000,
		);

		$propDefs['display_while_disabled'] = array(
			'name'=>'display_while_disabled',
			'label'=>KText::_('FIELD_LABEL_ANSWER_DISPLAY_WHILE_DISABLED'),
			'tooltip'=>KText::_('TOOLTIP_ANSWER_DISPLAY_WHILE_DISABLED'),
			'type'=>'dropdown',
			'choices'=>array(
				'like_question' => KText::_('TOOLTIP_ANSWER_DISPLAY_WHILE_DISABLED_CHOICE_LIKE_QUESTION'),
				'hide' => KText::_('Hide the answer'),
				'grey_out' => KText::_('Grey out the answer'),
			),
			'default'=>'like_question',
			'positionForm'=>70000,
			'appliesWhen' => array(
				'rules'=>array('*'),
			)
		);

		$propDefs['rule_end'] = array(
			'name'=>'rule_end',
			'type'=>'groupend',
			'positionForm'=>80000,
		);

		$propDefs['calc_start'] = array(
			'name'=>'calc_start',
			'type'=>'groupstart',
			'title'=>KText::_('Calculations'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>90000,
		);

		$propDefs['calcmodel'] = array(
			'name'=>'calcmodel',
			'labelList'=>KText::_('Calculation'),
			'label'=>KText::_('Price Calculation'),
			'tooltip'=>KText::_('If you choose a price calculation, it will override the component price.'),
			'type'=>'calculation',
			'showLinks'=>true,

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionList'=>75,
			'positionForm'=>100000,
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['price_calculation_overrides'] = array(
				'name' => 'price_calculation_overrides',
				'label' => KText::_('Price Calculation Overrides'),
				'type' => 'calculationOverride',
				'overridePropertyName' => 'calcmodel',
				'positionForm' => 100100,
			);

		}

		$propDefs['calcmodel_recurring'] = array(
			'name'=>'calcmodel_recurring',
			'label'=>KText::_('Recurring Price Calculation'),
			'tooltip'=>KText::_('If you choose a price calculation, it will override the component price.'),

			'type'=>'calculation',
			'showLinks'=>true,

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>110000,
			'appliesWhen'=>array(
				'joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_joinedby_product_id_to_adminproducts_use_recurring_pricing'=>'1',
			),
		);

		if (KenedoPlatform::getName() != 'magento2' && ConfigboxWordpressHelper::isWcIntegration() == false) {

			$propDefs['price_recurring_calculation_overrides'] = array(
				'name' => 'price_recurring_calculation_overrides',
				'label' => KText::_('Recurring Price Calculation Overrides'),
				'type' => 'calculationOverride',
				'overridePropertyName' => 'calcmodel_recurring',
				'positionForm' => 110100,
				'appliesWhen'=>array(
					'joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_joinedby_product_id_to_adminproducts_use_recurring_pricing'=>'1',
				),
			);

		}

		$propDefs['calcmodel_weight'] = array(
			'name'=>'calcmodel_weight',
			'label'=>KText::_('Weight Calculation'),
			'tooltip'=>KText::_('If you set a calculation, the weight will come from the calculation.'),

			'type'=>'calculation',
			'showLinks'=>true,

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>120000,
		);

		$propDefs['calc_end'] = array(
			'name'=>'calc_end',
			'type'=>'groupend',
			'positionForm'=>130000,
		);

		$propDefs['visualization_start'] = array(
			'name'=>'visualization_start',
			'type'=>'groupstart',
			'title'=>KText::_('Visualization'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>140000,
			'appliesWhen'=>array(
				'joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_visualization_type'=>array('shapediver', 'composite'),
			),
		);



		$propDefs['visualization_image'] = array(
			'name'=>'visualization_image',
			'label'=>KText::_('Visualization Image'),
			'type'=>'image',
			'appendSerial'=>1,
			'allowedExtensions'=>array('jpg','jpeg','png'),
			'allowedMimeTypes'=>array('image/pjpeg','image/jpg','image/jpeg','image/png'),
			'maxFileSizeKb'=>'2000',
			'dirBase'=>KenedoPlatform::p()->getDirDataStore().'/public/vis_answer_images',
			'urlBase'=>KenedoPlatform::p()->getUrlDataStore().'/public/vis_answer_images',
			'required'=>0,
			'tooltip'=>KText::_('This image will be inserted into the composite image once this answer gets selected. Please mind the stacking order value below.'),
			'options'=>'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
			'positionForm'=>160000,
			'appliesWhen'=>array(
				'joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_visualization_type'=>'composite',
			),
		);

		$propDefs['visualization_stacking'] = array(
			'name'=>'visualization_stacking',
			'label'=>KText::_('Visualization Stacking Order'),
			'type'=>'string',
			'size'=>'50',
			'default'=>'1',
			'required'=>0,
			'tooltip'=>KText::_('Here you specify, where the image will be positioned in the stack. Higher numbers are layered higher.'),
			'positionForm'=>170000,
			'appliesWhen'=>array(
				'joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_visualization_type'=>'composite',
			),
		);

		$propDefs['shapediver_choice_value'] = array(
			'name'=>'shapediver_choice_value',
			'label'=>KText::_('Shapediver choice value'),
			'tooltip' => KText::_('If the question is used for controlling a ShapeDiver parameter, enter the choice value for this answer.'),
			'type'=>'shapediverparametervalue',

			'required'=>0,

			'positionForm'=>180500,

			'appliesWhen'=>array(
				'joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_visualization_type'=>'shapediver',
				'is_shapediver_control'=>'1'
			),

		);

		$propDefs['visualization_end'] = array(
			'name'=>'visualization_end',
			'type'=>'groupend',
			'positionForm'=>190000,
		);

		$propDefs['custom_start'] = array(
			'name'=>'custom_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Answer Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>200000,
		);

		$label = CbSettings::getInstance()->get('label_assignment_custom_1');
		if (!$label) {
			$label = KText::_('Custom Field 1');
		}

		$propDefs['assignment_custom_1'] = array(
			'name'=>'assignment_custom_1',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','assignment_custom_1'),
			'positionForm'=>210000,
		);

		$label = CbSettings::getInstance()->get('label_assignment_custom_2');
		if (!$label) {
			$label = KText::_('Custom Field 2');
		}

		$propDefs['assignment_custom_2'] = array(
			'name'=>'assignment_custom_2',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','assignment_custom_2'),
			'positionForm'=>220000,
		);

		$label = CbSettings::getInstance()->get('label_assignment_custom_3');
		if (!$label) {
			$label = KText::_('Custom Field 3');
		}

		$propDefs['assignment_custom_3'] = array(
			'name'=>'assignment_custom_3',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','assignment_custom_3'),
			'positionForm'=>230000,
		);

		$label = CbSettings::getInstance()->get('label_assignment_custom_4');
		if (!$label) {
			$label = KText::_('Custom Field 4');
		}

		$propDefs['assignment_custom_4'] = array(
			'name'=>'assignment_custom_4',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','assignment_custom_4'),
			'positionForm'=>240000,
		);

		$propDefs['custom_end'] = array(
			'name'=>'custom_end',
			'type'=>'groupend',
			'positionForm'=>250000,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'group'=>'element_id',
			'canSortBy'=>true,
			'positionList'=>15,
			'positionForm'=>260000,
		);

//		$propDefs['misc_start'] = array(
//			'name'=>'misc_start',
//			'type'=>'groupstart',
//			'title'=>KText::_('Misc'),
//			'toggle'=>true,
//			'defaultState'=>'closed',
//			'positionForm'=>270000,
//		);
//
//
//
//		$propDefs['misc_end'] = array(
//			'name'=>'misc_end',
//			'type'=>'groupend',
//			'positionForm'=>290000,
//		);

		return $propDefs;
	}

	function canDelete($id) {

		$db = KenedoPlatform::getDb();

		// Find elements with rules containing the xref id in the string
		$query = "SELECT `id` AS `element_id`, `rules` FROM `#__configbox_elements` WHERE `rules` LIKE '%".intval($id) . "%'";
		$db->setQuery($query);
		$potentials = $db->loadAssocList();

		// Loop through and scan the rule for the xref id
		if ($potentials) {
			foreach ($potentials as $potential) {

				$ruleString = $potential['rules'];
				$ruleQuestionId = $potential['element_id'];

				$result = ConfigboxRulesHelper::ruleContainsAnswer($ruleString, $id);

				if ($result == true) {

					// Get more info about the xref to be deleted
					$query = "SELECT `element_id`, `option_id` FROM `#__configbox_xref_element_option` WHERE `id` = ".intval($id);
					$db->setQuery($query);
					$meta = $db->loadAssoc();

					// Get the titles for the error message
					$elementTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $meta['element_id']);
					$optionTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $meta['option_id']);
					$ruleElementTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $ruleQuestionId);

					$message = KText::sprintf('You cannot remove option assignment %s for element %s, it is referenced in the rule for element %s, ID %s. Please change the rule first.', $optionTitle, $elementTitle, $ruleElementTitle, $ruleQuestionId);
					$this->setError($message);
					return false;
				}
			}
		}

		// Find elements with rules containing the xref id in the string
		$query = "SELECT `id` AS `xref_id` ,`element_id`, `option_id`, `rules` FROM `#__configbox_xref_element_option` WHERE `rules` LIKE '%".intval($id) . "%'";
		$db->setQuery($query);
		$potentials = $db->loadAssocList();

		// Loop through and scan the rule for the xref id
		if ($potentials) {
			foreach ($potentials as $potential) {

				$ruleString = $potential['rules'];
				$ruleQuestionId = $potential['element_id'];
				$ruleAnswerId = $potential['xref_id'];
				$ruleOptionId = $potential['option_id'];

				// Check if rule contains the xref
				$result = ConfigboxRulesHelper::ruleContainsAnswer($ruleString, $id);

				if ($result == true) {

					// Get more info about the xref to be deleted
					$query = "SELECT `element_id`, `option_id` FROM `#__configbox_xref_element_option` WHERE `id` = ".intval($id);
					$db->setQuery($query);
					$meta = $db->loadAssoc();

					// Get more info about the xref with the rule attached

					// Get the titles for the error message
					$elementTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $meta['element_id']);
					$optionTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $meta['option_id']);
					$ruleElementTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $ruleQuestionId);
					$ruleOptionTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $ruleOptionId);

					$message = KText::sprintf('You cannot remove option assignment %s for element %s, it is referenced in the rule for option assignment %s, ID %s in element %s, ID %s. Please change the rule first.', $optionTitle, $elementTitle, $ruleOptionTitle, $ruleAnswerId, $ruleElementTitle, $ruleQuestionId);
					$this->setError($message);
					return false;
				}
			}
		}

		return true;

	}

//	function copy($data) {
//
//		KLog::log('Unsetting calcs and rules in data', 'custom_copying');
//
//		$data->calcmodel = NULL;
//		$data->calcmodel_recurring = NULL;
//
//		$data->calcmodel_weight = NULL;
//		$data->rules = '';
//
//		$data->price_calculation_overrides = '[]';
//		$data->price_recurring_calculation_overrides = '[]';
//
//		return parent::copy($data);
//
//	}

}
