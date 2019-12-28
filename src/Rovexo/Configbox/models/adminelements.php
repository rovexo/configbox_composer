<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminelements extends KenedoModel {

	function getTableName() {
		return '#__configbox_elements';
	}

	function getTableKey() {
		return 'id';
	}

	/**
	 * We do not state adminxrefelementoptions here because the copy method already copies those because of the
	 * childentries property in this model
	 * @return string
	 */
	function getChildModel() {
		return '';
	}

    function getChildModelForeignKey() {
        return '';
    }

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'type'=>'id',
			'default'=>0,
			'label'=>KText::_('ID'),
			'listing'=>1,
			'listingwidth'=>'50px',
			'order'=>100,
			'positionForm'=>1000,
		);

		$propDefs['general_start'] = array(
			'name'=>'general_start',
			'type'=>'groupstart',
			'title'=>KText::_('General'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'notes'=>KText::_('GROUP_NOTE_ELEMENT_GENERAL', ''),
			'positionForm'=>2000,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'tooltip'=>KText::_('TOOLTIP_ELEMENT_TITLE'),
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>4,
			'required'=>1,
			'listing'=>10,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminelements',
			'order'=>30,
			'positionForm'=>3000,
		);

		$propDefs['internal_name'] = array(
			'name'=>'internal_name',
			'label'=>KText::_('Internal Name'),
			'type'=>'string',
			'listing'=>15,
			'order'=>40,
			'positionForm'=>4000,
		);

		// Depending on CB settings, use the internal name as list edit link (or hide it from the form otherwise)
		$goInternal = CbSettings::getInstance()->get('use_internal_question_names');

		if ($goInternal) {
			$propDefs['internal_name']['listing'] = 10;
			$propDefs['internal_name']['listinglink'] = 1;
			unset($propDefs['title']['listinglink']);
		}
		else {
			$propDefs['internal_name']['invisible'] = 1;
		}

		$propDefs['required'] = array(
			'name'=>'required',
			'label'=>KText::_('FIELD_LABEL_QUESTION_REQUIRED'),
			'type'=>'boolean',
			'tooltip'=>KText::_('TOOLTIP_QUESTION_REQUIRED'),
			'positionForm'=>5900,
		);

		$propDefs['question_type'] = array (
			'name'=>'question_type',
			'label'=>KText::_('FIELD_LABEL_QUESTION_TYPE'),
			'type'=>'dropdown',
			'choices'=>array(
				'textbox' => KText::_('As text box'),
				'textarea' => KText::_('As multi line text box'),
				'checkbox' => KText::_('As checkbox'),
				'radiobuttons' => KText::_('As radio buttons'),
				'dropdown' => KText::_('As dropdown'),
				'upload' => KText::_('As file upload'),
				'calendar' => KText::_('As calendar'),
				'colorpicker' => KText::_('As color picker'),
				'ralcolorpicker' => KText::_('As RAL color picker'),
				'images' => KText::_('As clickable images'),
				'slider' => KText::_('As slider'),
				'choices' => KText::_('As choices plus text field'),
			),
			'default'=>'textbox',
			'positionForm'=>6000,
		);

		$customTypes = $this->getCustomQuestionTypes();
		$propDefs['question_type']['choices'] = array_merge($propDefs['question_type']['choices'], $customTypes);

		$propDefs['slider_steps'] = array (
			'name'=>'slider_steps',
			'label'=>KText::_('FIELD_LABEL_QUESTION_SLIDER_STEPS'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_SLIDER_STEPS'),
			'type'=>'string',
			'stringType'=>'number',
			'default'=>'1',
			'positionForm'=>7000,
			'appliesWhen'=>array(
				'question_type'=>'slider',
			),
		);

		$propDefs['choices'] = array (
			'name'=>'choices',
			'label'=>KText::_('FIELD_LABEL_QUESTION_CHOICES'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_CHOICES'),
			'type'=>'string',
			'stringType'=>'string',
			'options'=>'USE_TEXTAREA',
			'style'=>'min-height:100px',
			'positionForm'=>8000,
			'appliesWhen'=>array(
				'question_type'=>'choices',
			),
		);

		$propDefs['general_end'] = array(
			'name'=>'general_end',
			'type'=>'groupend',
			'positionForm'=>9000,
		);

		$propDefs['upload_extensions'] = array(
			'name'=>'upload_extensions',
			'label'=>KText::_('FIELD_LABEL_QUESTION_UPLOAD_EXTENSIONS'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_UPLOAD_EXTENSIONS'),
			'type'=>'string',
			'default'=>'png, gif, jpg, jpeg',
			'positionForm'=>10000,
			'appliesWhen'=>array(
				'question_type' => 'upload',
			),
		);

		$propDefs['upload_mime_types'] = array(
			'name'=>'upload_mime_types',
			'label'=>KText::_('FIELD_LABEL_QUESTION_UPLOAD_MIME_TYPES'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_UPLOAD_MIME_TYPES'),
			'type'=>'string',
			'default'=>'image/pjpeg, image/jpg, image/jpeg, image/gif, image/tif, image/bmp, image/png, image/x-png',
			'positionForm'=>11000,
			'appliesWhen'=>array(
				'question_type' => 'upload',
			),
		);

		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$maxUploadSize = min($max_upload, $max_post, $memory_limit);

		$propDefs['upload_size_mb'] = array (
			'name'=>'upload_size_mb',
			'label'=>KText::_('FIELD_LABEL_QUESTION_UPLOAD_SIZE_MB'),
			'tooltip'=>KText::sprintf('TOOLTIP_QUESTION_UPLOAD_SIZE_MB', $maxUploadSize),
			'type'=>'string',
			'unit'=>'MB',
			'stringType'=>'number',
			'positionForm'=>12000,
			'appliesWhen'=>array(
				'question_type' => 'upload',
			),
		);

		$propDefs['textfield_settings'] = array(
			'name'=>'textfield_settings',
			'type'=>'groupstart',
			'title'=>KText::_('Text box settings'),
			'positionForm'=>13000,
			'toggle'=>true,
			'defaultState'=>'opened',
			'appliesWhen' => array(
				'question_type'=>array('textbox', 'textarea', 'slider', 'choices', 'colorpicker',  'ralcolorpicker'),
			)
		);

		$propDefs['prefill_on_init'] = array(
			'name'=>'prefill_on_init',
			'label'=>KText::_('Do you want to prefill the field with a default value?'),
			'type'=>'boolean',
			'default'=>0,
			'tooltip'=>KText::_('Choose yes if you want a default value in that text field when the customer starts the configuration.'),
			'positionForm'=>14000,
			'appliesWhen' => array(
				'question_type'=>array('textbox', 'textarea', 'slider', 'choices', 'colorpicker', 'ralcolorpicker'),
			)
		);

		$propDefs['default_value'] = array(
			'name'=>'default_value',
			'label'=>KText::_('Default Value'),
			'type'=>'string',
			'stringType'=>'stringOrNumber',
			'positionForm'=>15000,
			'appliesWhen' => array(
				'prefill_on_init'=>1,
			),
		);

		$propDefs['validate'] = array(
			'name'=>'validate',
			'label'=>KText::_('Do you want to restrict what the customer can enter?'),
			'type'=>'boolean',
			'tooltip'=>KText::_('You can make the text box allow numbers only and set the minimum and maximum.'),
			'default'=>0,
			'positionForm'=>16000,
			'appliesWhen' => array(
				'question_type'=>array('textbox', 'textarea', 'slider', 'choices'),
			)
		);

		$propDefs['input_restriction'] = array(
			'name'=>'input_restriction',
			'label'=>KText::_('What can a customer enter?'),
			'tooltip'=>KText::_('You can make the text box allow numbers only and set the minimum and maximum.'),
			'type'=>'dropdown',
			'choices'=>array(
				'plaintext'=>KText::_('Plain text'),
				'integer'=>KText::_('An integer number'),
				'decimal'=>KText::_('An integer or decimal number'),
			),
			'default'=>'plaintext',
			'positionForm'=>17000,
			'appliesWhen' => array(
				'validate'=>1,
			)
		);

		$propDefs['set_min_value'] = array(
			'name'=>'set_min_value',
			'label'=>KText::_('Do you want to set a minimum value?'),
			'tooltip'=>KText::_('The value can be a static or calculated one. Calculated values will update while the customer configures.'),
			'type'=>'dropdown',
			'choices'=>array(
				'none' => KText::_('KTEXT_NO'),
				'static' => KText::_('Yes, a static value'),
				'calculated' => KText::_('Yes, a calculated value'),
			),
			'default'=>'none',
			'positionForm'=>18000,
			'appliesWhen' => array(
				'input_restriction'=>array('integer', 'decimal'),
			)
		);

		$propDefs['minval'] = array (
			'name'=>'minval',
			'label'=>KText::_('Static Minimum Value'),
			'type'=>'string',
			'stringType'=>'number',
			'tooltip'=>KText::_('You can specify a minimum value for entries. Lower values will not be accepted.'),
			'positionForm'=>19000,
			'required'=>true,
			'appliesWhen' => array(
				'set_min_value'=>'static',
			)
		);

		$propDefs['calcmodel_id_min_val'] = array (
			'name'=>'calcmodel_id_min_val',
			'label'=>KText::_('Calculated Minimum Value'),
			'tooltip'=>KText::_('You can specify a calculation model that returns the value instead of the static value.'),
			'type'=>'calculation',
			'showLinks'=>true,

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>20000,
			'required'=>true,
			'appliesWhen' => array(
				'set_min_value'=>'calculated',
			)
		);

		$propDefs['set_max_value'] = array(
			'name'=>'set_max_value',
			'label'=>KText::_('Do you want to set a maximum value?'),
			'tooltip'=>KText::_('The value can be a static or calculated one. Calculated values will update while the customer configures.'),
			'type'=>'dropdown',
			'choices'=>array(
				'none' => KText::_('KTEXT_NO'),
				'static' => KText::_('Yes, a static value'),
				'calculated' => KText::_('Yes, a calculated value'),
			),
			'default'=>'none',
			'positionForm'=>21000,
			'appliesWhen' => array(
				'input_restriction'=>array('integer', 'decimal'),
			)
		);

		$propDefs['maxval'] = array (
			'name'=>'maxval',
			'label'=>KText::_('Static Maximum Value'),
			'type'=>'string',
			'stringType'=>'number',
			'tooltip'=>KText::_('You can specify a maximum value for entries. Higer values will not be accepted.'),
			'positionForm'=>22000,
			'required'=>true,
			'appliesWhen' => array(
				'set_max_value'=>'static',
			)
		);

		$propDefs['calcmodel_id_max_val'] = array (
			'name'=>'calcmodel_id_max_val',
			'label'=>KText::_('Calculated Maximum Value'),
			'tooltip'=>KText::_('You can specify a calculation model that returns the value instead of the static value.'),
			'type'=>'calculation',
			'showLinks'=>true,

			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),

			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>23000,
			'required'=>true,

			'appliesWhen' => array(
				'set_max_value'=>'calculated',
			)
		);

		$propDefs['show_unit'] = array(
			'name'=>'show_unit',
			'label'=>KText::_('FIELD_LABEL_QUESTION_SHOW_UNIT'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_SHOW_UNIT'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>24000,
			'appliesWhen' => array(
				'question_type'=>array('textbox', 'textarea', 'slider', 'choices'),
			)
		);

		$propDefs['unit'] = array(
			'name'=>'unit',
			'label'=>KText::_('FIELD_LABEL_QUESTION_UNIT'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>54,
			'positionForm'=>25000,
			'required'=>true,

			'appliesWhen'=>array(
				'show_unit' => 1,
			),
		);

		$propDefs['textfield_settings_end'] = array(
			'name'=>'textfield_settings_end',
			'type'=>'groupend',
			'positionForm'=>26000,
		);

		$propDefs['assignments_group'] = array(
			'name'=>'assignments_group',
			'type'=>'groupstart',
			'title'=>KText::_('Predefined Answers'),
			'positionForm'=>27000,
			'appliesWhen' => array(
				'question_type'=>array('checkbox', 'radiobuttons', 'dropdown', 'images'),
			)
		);

		$propDefs['assignments'] = array (
			'name'=>'assignments',
			'label'=>KText::_('Predefined Answers'),
			'hideAdminLabel'=>true,
			'type'=>'childentries',
			'viewClass'=>'ConfigboxViewAdminoptionassignments',
			'viewFilters'=>array(
				array('filterName'=>'adminoptionassignments.element_id', 'filterValueKey'=>'id'),
			),
			'foreignKeyField'=>'element_id',
			'parentKeyField'=>'id',
			'positionForm'=>27500,
		);

		$propDefs['assignments_group_end'] = array(
			'name'=>'assignments_group_end',
			'type'=>'groupend',
			'positionForm'=>28000,
		);

		$propDefs['rule_start'] = array(
			'name'=>'rule_start',
			'type'=>'groupstart',
			'title'=>KText::_('GROUP_TITLE_QUESTION_RULE'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('GROUP_NOTE_QUESTION_RULE',''),
			'positionForm'=>29000,
		);

		$propDefs['rules'] = array(
			'name'=>'rules',
			'label'=>KText::_('FIELD_LABEL_ELEMENT_RULE'),
			'tooltip'=>KText::_('TOOLTIP_ELEMENT_RULE'),
			'type'=>'rule',
			'textWhenNoRule'=>KText::_('Show the question in any case.'),
			'required'=>0,
			'options'=>'ALLOW_RAW',
			'listing'=>70,
			'positionForm'=>30000,
		);

		$propDefs['display_while_disabled'] = array (
			'name'=>'display_while_disabled',
			'label'=>KText::_('FIELD_LABEL_ELEMENT_DISPLAY_WHILE_DISABLED'),
			'tooltip'=>KText::_('TOOLTIP_ELEMENT_DISPLAY_WHILE_DISABLED'),
			'type'=>'dropdown',
			'choices'=>array(
				'hide' => KText::_('Hide the question'),
				'grey_out' => KText::_('Grey out the question'),
			),
			'default'=>'hide',
			'positionForm'=>31000,
			'appliesWhen' => array(
				'rules'=>array('*'),
			)
		);

		$propDefs['behavior_on_activation'] = array (
			'name'=>'behavior_on_activation',
			'label'=>KText::_('FIELD_LABEL_BEHAVIOR_ACTIVATION'),
			'tooltip'=>KText::_('TOOLTIP_BEHAVIOR_ACTIVATION'),
			'type'=>'dropdown',
			'choices'=>array(
				'none' => KText::_('Just show the question'),
				'select_default' => KText::_('Auto-select the default answer'),
				'select_any' => KText::_('Auto-select the first answer'),
			),
			'default'=>'none',
			'positionForm'=>32000,
			'appliesWhen' => array(
				'rules'=>array('*'),
			)
		);

		$propDefs['behavior_on_inconsistency'] = array (
			'name'=>'behavior_on_inconsistency',
			'label'=>KText::_('FIELD_LABEL_BEHAVIOR_INCONSISTENCY'),
			'tooltip'=>KText::_('TOOLTIP_BEHAVIOR_INCONSISTENCY'),
			'type'=>'dropdown',
			'choices'=>array(
				'deselect' => KText::_('Just deselect the answer'),
				'replace_with_default' => KText::_('Try to select the default answer'),
				'replace_with_any' => KText::_('Try to select the first possible answer'),
			),
			'default'=>'deselect',
			'positionForm'=>32500,
		);

		$propDefs['behavior_on_changes'] = array (
			'name'=>'behavior_on_changes',
			'label'=>KText::_('FIELD_LABEL_QUESTION_BEHAVIOR_CHANGES'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_BEHAVIOR_CHANGES'),
			'type'=>'dropdown',
			'choices'=>array(
				'silent' => KText::_('CHOICE_SILENT_CHANGE'),
				'confirm' => KText::_('CHOICE_CHANGE_AFTER_CONFIRMATION'),
			),
			'default'=>'silent',
			'positionForm'=>33000,

		);

		$propDefs['rule_end'] = array(
			'name'=>'rule_end',
			'type'=>'groupend',
			'positionForm'=>34000,
		);


		$propDefs['visualization_start'] = array(
			'name' => 'visualization_start',
			'type' => 'groupstart',
			'title' => KText::_('Visualization'),
			'toggle' => true,
			'defaultState' => 'closed',
			'positionForm' => 34010,
//			'appliesWhen'=>array(
//				'joinedby_page_id_to_adminpages_visualization_type'=>'shapediver',
//			),
		);

		$propDefs['is_shapediver_control'] = array(
			'name' => 'is_shapediver_control',
			'label' => KText::_('Does this question control a ShapeDiver parameter?'),
			'tooltip' => KText::_('If answers to this question manipulate the ShapeDiver visualization, choose yes.'),
			'type' => 'boolean',
			'default' => 0,
			'positionForm' => 34020,
		);

		$propDefs['shapediver_parameter_id'] = array(
			'name'=>'shapediver_parameter_id',
			'label'=>KText::_('Please choose the ShapeDiver parameter'),
			'tooltip' => KText::_('Be sure to use an approprate question type for the parameter you pick here. If you use parameter types like Dropdown, choices or similar, then add predefined answers and assign the right parameter values there.'),
			'type'=>'shapediverparameter',
			'required'=>0,
			'positionForm'=>34030,
			'appliesWhen'=>array(
				'is_shapediver_control'=>'1',
				'question_type'=>'!upload',
			),
		);

		$propDefs['shapediver_geometry_name'] = array(
			'name'=>'shapediver_geometry_name',
			'label'=>KText::_('Please choose a ShapeDiver geometry'),
			'tooltip' => KText::_('Be sure to make the file validation so that only images like jpegs, pngs or gifs can be uploaded.'),
			'type'=>'shapedivergeometry',
			'required'=>0,
			'positionForm'=>34035,
			'appliesWhen'=>array(
				'is_shapediver_control'=>'1',
				'question_type'=>'upload',
			),
		);

		$propDefs['visualization_end'] = array(
			'name'=>'visualization_end',
			'type'=>'groupend',
			'positionForm' => 34040,
		);


		$propDefs['calc_start'] = array(
			'name'=>'calc_start',
			'type'=>'groupstart',
			'title'=>KText::_('Calculations'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('GROUP_NOTE_ELEMENT_CALCULATIONS'),
			'positionForm'=>35000,
			'appliesWhen'=>array(
				'question_type' => array(
					'textbox',
					'slider',
					'textarea',
				),
			),
		);

		$propDefs['calcmodel'] = array(
			'name'=>'calcmodel',
			'label'=>KText::_('Price Calculation'),
			'type'=>'calculation',
			'showLinks'=>true,
			'tooltip'=>KText::_('TOOLTIP_QUESTION_CALCMODEL'),
			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),
			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>36000,
			'appliesWhen'=>array(
				'question_type' => array(
					'textbox',
					'slider',
					'textarea',
				),
			),
		);

		$propDefs['multiplicator'] = array(
			'name'=>'multiplicator',
			'label'=>KText::_('Price Multiplicator'),
			'type'=>'string',
			'stringType'=>'number',
			'default'=>1,
			'tooltip'=>KText::_('TOOLTIP_QUESTION_MULTIPLIER'),
			'positionForm'=>37000,
			'appliesWhen'=>array(
				'calcmodel' => '*',
			),
		);

		$propDefs['text_calcmodel'] = array(
			'name'=>'text_calcmodel',
			'label'=>KText::_('Display question in configurator'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_SHOW_QUESTION'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>38000,
			'appliesWhen'=>array(
				'calcmodel' => '*',
			),
		);

		$propDefs['calcmodel_recurring'] = array(
			'name'=>'calcmodel_recurring',
			'label'=>KText::_('Recurring Price Calculation'),
			'type'=>'calculation',
			'showLinks'=>true,
			'tooltip'=>KText::_('TOOLTIP_QUESTION_CALCMODEL'),
			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),
			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'appliesWhen'=>array(
				'joinedby_page_id_to_adminpages_joinedby_product_id_to_adminproducts_use_recurring_pricing' => '1',
				'question_type' => array(
					'textbox',
					'slider',
					'textarea',
				),
			),
			'positionForm'=>39000,
		);

		$propDefs['calcmodel_weight'] = array(
			'name'=>'calcmodel_weight',
			'label'=>KText::_('Weight Calculation'),
			'type'=>'calculation',
			'showLinks'=>true,
			'propNameKey'=>'id',
			'propNameDisplay'=>'name',
			'defaultlabel'=>KText::_('No Calculation'),
			'modelClass'=>'ConfigboxModelAdmincalculations',
			'modelMethod'=>'getRecords',

			'positionForm'=>40000,
			'appliesWhen'=>array(
				'question_type' => array(
					'textbox',
					'slider',
					'textarea',
				),
			),
		);

		$propDefs['calc_end'] = array(
			'name'=>'calc_end',
			'type'=>'groupend',
			'positionForm'=>41000,
			'appliesWhen'=>array(
				'question_type' => array(
					'textbox',
					'slider',
					'textarea',
				),
			),
		);

		$propDefs['desc_start'] = array(
			'name'=>'desc_start',
			'type'=>'groupstart',
			'title'=>KText::_('Description'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>42000,
		);

		$propDefs['desc_display_method'] = array(
			'name'=>'desc_display_method',
			'label'=>KText::_('LABEL_DESCRIPTION_DISPLAY_METHOD'),
			'type'=>'dropdown',
			'choices'=> array(
				1=>KText::_('QUESTION_DESC_DISPLAY_TYPE_UNDER'),
				2=>KText::_('QUESTION_DESC_DISPLAY_TYPE_TOOLTIP'),
				3=>KText::_('QUESTION_DESC_DISPLAY_TYPE_MODAL'),
			),
			'default'=>1,
			'positionForm'=>43000,
		);

		$propDefs['description'] = array(
			'name'=>'description',
			'label'=>KText::_('Description'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>14,
			'required'=>0,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>44000,
		);

		$propDefs['desc_end'] = array(
			'name'=>'desc_end',
			'type'=>'groupend',
			'positionForm'=>45000,
		);

		$propDefs['custom_fields_start'] = array(
			'name'=>'custom_fields_start',
			'type'=>'groupstart',
			'title'=>KText::_('Custom Fields'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'notes'=>KText::_('With these fields you can add your own data. You can use this data in your own templates and calculation models.'),
			'positionForm'=>46000,
		);

		$label = CbSettings::getInstance()->get('label_element_custom_1');
		if (!$label) {
			$label = KText::_('Custom Field 1');
		}

		$propDefs['element_custom_1'] = array(
			'name'=>'element_custom_1',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','element_custom_1'),
			'positionForm'=>47000,
		);

		$label = CbSettings::getInstance()->get('label_element_custom_2');
		if (!$label) {
			$label = KText::_('Custom Field 2');
		}

		$propDefs['element_custom_2'] = array(
			'name'=>'element_custom_2',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','element_custom_2'),
			'positionForm'=>48000,
		);

		$label = CbSettings::getInstance()->get('label_element_custom_3');
		if (!$label) {
			$label = KText::_('Custom Field 3');
		}

		$propDefs['element_custom_3'] = array(
			'name'=>'element_custom_3',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','element_custom_3'),
			'positionForm'=>49000,
		);

		$label = CbSettings::getInstance()->get('label_element_custom_4');
		if (!$label) {
			$label = KText::_('Custom Field 4');
		}

		$propDefs['element_custom_4'] = array(
			'name'=>'element_custom_4',
			'label'=> $label,
			'type'=>'string',
			'default'=>'',
			'tooltip'=>KText::sprintf('You can access this field with the key %s','element_custom_4'),
			'positionForm'=>50000,
		);

		$label = CbSettings::getInstance()->get('label_element_custom_translatable_1');
		if (!$label) {
			$label = KText::_('Custom Translatable 1');
		}

		$propDefs['element_custom_translatable_1'] = array(
			'name'=>'element_custom_translatable_1',
			'label'=> $label,
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>50,
			'required'=>0,
			'positionForm'=>51000,
		);

		$label = CbSettings::getInstance()->get('label_element_custom_translatable_2');
		if (!$label) {
			$label = KText::_('Custom Translatable 2');
		}

		$propDefs['element_custom_translatable_2'] = array(
			'name'=>'element_custom_translatable_2',
			'label'=> $label,
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>51,
			'required'=>0,
			'positionForm'=>52000,
		);

		$propDefs['custom_fields_end'] = array(
			'name'=>'custom_fields_end',
			'type'=>'groupend',
			'positionForm'=>53000,
		);

		$propDefs['misc_start'] = array(
			'name'=>'misc_start',
			'title'=>KText::_('Misc'),
			'type'=>'groupstart',
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>54000,
		);


		$propDefs['title_display'] = array (
			'name'=>'title_display',
			'label'=>KText::_('FIELD_LABEL_QUESTION_TITLE_DISPLAY'),
			'type'=>'dropdown',
			'choices'=>array(
				'heading' => KText::_('QUESTION_TITLE_DISPLAY_HEADING'),
				'label' => KText::_('QUESTION_TITLE_DISPLAY_LABEL'),
				'none' => KText::_('QUESTION_TITLE_DISPLAY_NONE'),
			),
			'default'=>'heading',
			'positionForm'=>54100,
		);

		$propDefs['element_css_classes'] = array(
			'name'=>'element_css_classes',
			'label'=>KText::_('CSS Classes'),
			'type'=>'string',
			'tooltip'=>KText::_('This helps you styling your elements. Enter one or more space separated CSS classnames. These will be set in the wrapping DIV of the element. Only use letters a to z, no umlauts or special characters. Do not use the words element, noapply or any css classnames that are already used by the system.'),
			'positionForm'=>56000,
		);

		if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') {
			$propDefs['asproducttitle'] = array(
				'name'=>'asproducttitle',
				'label'=>KText::_('Use Selection as Product Title'),
				'type'=>'boolean',
				'tooltip'=>KText::_('Choose yes to show the selected option of this element as product title in cart, quotation and order confirmation.'),
				'positionForm'=>57000,
			);
		}

		$propDefs['show_in_overview'] = array(
			'name'=>'show_in_overview',
			'label'=>KText::_('Show in Overview'),
			'type'=>'boolean',
			'default'=>1,
			'tooltip'=>KText::_('Choose yes to show the element in configuration overview pages.'),
			'positionForm'=>58000,
		);


		$propDefs['el_image'] = array (
			'name'=>'el_image',
			'label'=>KText::_('Decoration Image'),
			'type'=>'file',
			'appendSerial'=>1,
			'allowedExtensions'=>array('svg', 'jpg','jpeg','gif','tif','bmp','png'),
			'filetype'=>'image',
			'tooltip'=>KText::_('Image to show next to the answers.'),
			'allow'=>array('image/svg+xml','image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png','image/x-png'),
			'required'=>0,
			'size'=>'1000',
			'dirBase'=>CONFIGBOX_DIR_QUESTION_DECORATIONS,
			'urlBase'=>CONFIGBOX_URL_QUESTION_DECORATIONS,
			'options'=>'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
			'positionForm'=>59000,
		);


		$propDefs['page_id'] = array(
			'name'=>'page_id',
			'label'=>KText::_('Configurator Page'),
			'defaultlabel'=>KText::_('Select Configurator Page'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'groupby'=>'product_id_display_value',

			'modelClass'=>'ConfigboxModelAdminpages',
			'modelMethod'=>'getRecords',

			'parent'=>1,
			'filterparents'=>1,

			'tooltip'=>KText::_('The configurator page in which the question is displayed.'),
			'required'=>1,
			'listing'=>30,
			'order'=>20,
			'filter'=>1,
			'positionForm'=>60000,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'tooltip'=>KText::_('TOOLTIP_QUESTION_PUBLISHED'),
			'type'=>'published',
			'default'=>1,
			'listing'=>110,
			'filter'=>2,
			'listingwidth'=>'50px',
			'positionForm'=>61000,
		);

		$propDefs['misc_end'] = array(
			'name'=>'misc_end',
			'type'=>'groupend',
			'positionForm'=>62000,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Pos.'),
			'type'=>'ordering',
			'group'=>'page_id',
			'order'=>25,
			'listing'=>2,
			'positionForm'=>63000,
		);

		return $propDefs;

	}

	function prepareForStorage($data) {

		$response = parent::prepareForStorage($data);
		if ($response === false) {
			return false;
		}

		// If internal name is empty, populate it with the element title
		if (empty($data->internal_name)) {
			$props = $this->getProperties();
			/** @var  $titleProperty KenedoPropertyTranslatable */
			$titleProperty = $props['title'];
			$titles = new stdClass();
			$titleProperty->getDataFromRequest($titles);

			$titleKey = 'title-'.KText::getLanguageTag();
			$data->internal_name = $titles->$titleKey;
		}

		return true;
	}

	function validateData($data, $context = '')
	{

		$parentResponse = parent::validateData($data, $context);
		if ($parentResponse == false) {
			return false;
		}

		// perform validation checks on default value if is set
		if(isset($data->default_value) && strlen($data->default_value)) {

			// validate == 1 AND input_restriction == 'integer'
			if ($data->validate == 1 && $data->input_restriction == 'integer') {
				if (is_numeric($data->default_value)) {
					$data->default_value = intval($data->default_value);
				}
				// is integer
				if (!is_integer($data->default_value)) {
					$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_DEFAULT_VALUE_NOT_INTEGER'));
					return false;
				}
			}

			// validate == 1 AND input_restriction == 'decimal'
			if ($data->validate == 1 && $data->input_restriction == 'decimal') {
				// temporary float number
				$tempNumber = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->default_value);
				// temporary float number is numeric
				if (is_numeric($tempNumber)) {
					$data->default_value = floatval($tempNumber);
				}
				// is float
				if (!is_float($data->default_value)) {
					$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_DEFAULT_VALUE_NOT_DECIMAL'));
					return false;
				}
			}

			// Validate Slider default value
			if (in_array($data->question_type, array('slider'))) {
				// temporary float number
				$tempNumber = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->default_value);
				// temporary float number is numeric
				if (is_numeric($tempNumber)) {
					$data->default_value = floatval($tempNumber);
				}
				// is integer or float
				$patternIsInteger = is_integer($data->default_value);
				$patternIsFloat = is_float($data->default_value);
				if (!($patternIsInteger || $patternIsFloat)) {
					$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_DEFAULT_VALUE_SLIDER_NOT_NUMERIC'));
					return false;
				}
			}

			// Validate Color Picker hexadecimal default value Color code
			if (in_array($data->question_type, array('colorpicker'))) {
				// is valid hexadecimal color code
				$stringLength = strlen($data->default_value);
				$patternLength = in_array($stringLength, [4, 7]);
				$patternSymbol = ($data->default_value[0] == '#');
				$patternHexDec = ctype_xdigit(substr($data->default_value, 1));
				if (!$patternLength || !$patternSymbol || !$patternHexDec) {
					$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_DEFAULT_VALUE_NOT_COLOR'));
					return false;
				}
			}

			// Validate RAL Color Picker default value RAL Color code
			if (in_array($data->question_type, array('ralcolorpicker'))) {
				// load all available RAL color codes
				$ralColorsModel = KenedoModel::getModel('ConfigboxModelAdminRalcolors');
				$availableRalColors = $ralColorsModel->getColors();
				$allColorCodes = [];
				foreach ($availableRalColors as $colorId => $ralColor) {
					$allColorCodes[] = 'RAL ' . $colorId;
				}
				// check if default value is valid RAL color code
				$pattern = in_array($data->default_value, $allColorCodes);
				if ($pattern == false) {
					$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_DEFAULT_VALUE_NOT_RAL_COLOR'));
					return false;
				}
			}

		}

		if ($data->id) {

			$db = KenedoPlatform::getDb();
			$query = "SELECT COUNT(*) FROM `#__configbox_xref_element_option` WHERE `element_id` = ".intval($data->id);
			$db->setQuery($query);
			$answerCount = $db->loadResult();

			if ($data->question_type == 'checkbox' && $answerCount != 1) {
				$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_CHECKBOX'));
				return false;
			}

			if ($data->question_type == 'images' && $answerCount == 0) {
				$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_IMAGES'));
				return false;
			}

			if (in_array($data->question_type, array('textbox', 'textarea', 'upload', 'slider', 'calendar', 'choices', 'colorpicker')) && $answerCount != 0) {
				$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_FREE'));
				return false;
			}

			if (in_array($data->question_type, array('radiobuttons', 'dropdown')) && $answerCount < 2) {
				$this->setError(KText::_('VALIDATION_FEEDBACK_QUESTION_BAD_ANSWERS_MULTIPLE'));
				return false;
			}

			return true;

		}

		return true;

	}

	function canDelete($id) {

		$db = KenedoPlatform::getDb();

		// See if there are answers assigned to the question
		$query = "SELECT COUNT(*) FROM `#__configbox_xref_element_option` WHERE `element_id` = ".intval($id);
		$db->setQuery($query);
		$count = $db->loadResult();
		if ($count) {
			$this->setError(KText::_('COULD NOT DELETE QUESTION, BECAUSE IT CONTAINS ANSWERS.'));
			return false;
		}

		// Checks if element is used in one of the calculation code placeholders
		$query = "
		SELECT `calculation`.`id`, `calculation`.`name`
		FROM `#__configbox_calculation_codes` as `codes`
		LEFT JOIN `#__configbox_calculations` AS `calculation` ON `calculation`.id = `codes`.`id`
		WHERE
			`codes`.`element_id_a` = ".intval($id)." OR
			`codes`.`element_id_b` = ".intval($id)." OR
			`codes`.`element_id_c` = ".intval($id)." OR
			`codes`.`element_id_d` = ".intval($id)."
		";
		$db->setQuery($query);
		$potentials = $db->loadAssocList();

		if (count($potentials)) {
			$calculationNames = array();
			foreach ($potentials as $potential) {
				$calculationNames[] = $potential['name'].' (ID: '.$potential['id'].')';
			}
			$msg = KText::sprintf('Cannot delete element because it is used in these calculations: %s', implode(', ', $calculationNames));
			$this->setError($msg);
			return false;
		}


		// Checks if element is used in matrices as parameter or multiplier
		$query = "
		SELECT `calculation`.`id`, `calculation`.`name`
		FROM `#__configbox_calculation_matrices` as `matrices`
		LEFT JOIN `#__configbox_calculations` AS `calculation` ON `calculation`.id = `matrices`.`id`
		WHERE
			`matrices`.`column_element_id` = ".intval($id)." OR
			`matrices`.`row_element_id` = ".intval($id)." OR
			`matrices`.`multielementid` = ".intval($id)."
		";
		$db->setQuery($query);
		$potentials = $db->loadAssocList();

		if (count($potentials)) {
			$calculationNames = array();
			foreach ($potentials as $potential) {
				$calculationNames[] = $potential['name'].' (ID: '.$potential['id'].')';
			}
			$msg = KText::sprintf('Cannot delete element because it is used in these calculations: %s', implode(', ', $calculationNames));
			$this->setError($msg);
			return false;
		}




		// Find elements with rules containing the element id in the string (just roughly - the ID might mean something else)
		// This just saves some processing, we check more carefully later.
		$query = "SELECT `id` AS `element_id`, `rules` FROM `#__configbox_elements` WHERE `rules` LIKE '%".intval($id) . "%'";
		$db->setQuery($query);
		$potentials = $db->loadAssocList();

		$occurencesInRules = 0;

		// Loop through and scan the rule for the xref id
		if ($potentials) {
			foreach ($potentials as $potential) {

				$ruleString = $potential['rules'];
				$ruleQuestionId = $potential['element_id'];

				$result = ConfigboxRulesHelper::ruleContainsQuestion($ruleString, $id);

				if ($result == true) {

					// Get the titles for the error message
					$elementTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $id);
					$ruleElementTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $ruleQuestionId);

					$message = KText::sprintf('You cannot remove element %s, ID %s. It is referenced in the rule for element %s, ID %s. Please change the rule first.', $elementTitle, $id, $ruleElementTitle, $ruleQuestionId);
					$this->setError($message);
					$occurencesInRules++;
				}
			}
		}

		// Find elements with rules containing the xref id in the string (just roughly - the ID might mean something else)
		// This just saves some processing, we check more carefully later.
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
				$result = ConfigboxRulesHelper::ruleContainsQuestion($ruleString, $id);

				if ($result == true) {

					// Get more info about the xref with the rule attached

					// Get the titles for the error message
					$elementTitle 		= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $id);
					$ruleElementTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 4, $ruleQuestionId);
					$ruleOptionTitle 	= ConfigboxCacheHelper::getTranslation('#__configbox_strings', 5, $ruleOptionId);

					$message = KText::sprintf('You cannot remove element %s, ID %s. It is referenced in the rule for option assignment %s, ID %s in element %s, ID %s. Please change the rule first.', $elementTitle, $id, $ruleOptionTitle, $ruleAnswerId, $ruleElementTitle, $ruleQuestionId);
					$this->setError($message);
					$occurencesInRules++;

				}
			}
		}

		if ($occurencesInRules == 0) {
			return true;
		}
		else {
			return false;
		}

	}

	function afterDelete($id) {

		$query = "SELECT `id` FROM `#__configbox_xref_element_option` WHERE `element_id` = ".intval($id);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$xrefs = $db->loadResultList('id','id');

		if ($xrefs) {
			$xrefModel = KenedoModel::getModel('ConfigboxModelAdminoptionassignments');
			$success = $xrefModel->delete($xrefs);
			if ($success == false) {
				$this->setErrors($xrefModel->getErrors());
				return false;
			}
		}

		$query = "DELETE FROM `#__configbox_cart_position_configurations` WHERE `element_id` = ".(int)$id;
		$db->setQuery($query);
		$db->query();

		$query = "SELECT `id` FROM `#__configbox_xref_element_option` WHERE `element_id` = ".(int)$id;
		$db->setQuery($query);
		$ids = $db->loadResultList('id','id');

		if ($ids) {
			$model = KenedoModel::getModel('ConfigboxModelAdminxrefelementoptions');
			$success = $model->delete($ids);
			if ($success == false) {
				$this->setErrors($model->getErrors());
				return false;
			}
		}

		return true;

	}

	function getCustomQuestionTypes() {
		$folder = KenedoPlatform::p()->getDirCustomization().'/views/';
		$folders = KenedoFileHelper::getFolders($folder, 'question_');

		$types = array();
		foreach ($folders as $folder) {
			$type = str_replace('question_', '', $folder);
			$name = ucfirst($type);
			$types[$type] = $name;
		}

		return $types;
	}

	function copy($data) {

        $data->calcmodel_id_min_val = NULL;
        $data->calcmodel_id_max_val = NULL;

        $data->calcmodel = NULL;
        $data->calcmodel_recurring = NULL;

        $data->calcmodel_weight = NULL;
        $data->rules = '';

	    return parent::copy($data);

    }

}