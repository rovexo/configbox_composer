<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminproductdetailpanes extends KenedoModel {

	function getTableName() {
		return '#__configbox_product_detail_panes';
	}

	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array(
			'name'=>'id',
			'label'=>KText::_('ID'),
			'type'=>'id',
			'default'=>0,
			'listing'=>10,
			'order'=>100,
			'positionForm'=>100,
		);

		$propDefs['product_id'] = array(
			'name'=>'product_id',
			'label'=>KText::_('Product'),
			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Select Product'),

			'joinAdditionalProps'=>array(
				array('propertyName'=>'published', 	'selectAliasOverride'=>'product_published'),
				array('propertyName'=>'title', 		'selectAliasOverride'=>'product_title'),
			),

			'modelClass'=>'ConfigboxModelAdminproducts',
			'modelMethod'=>'getFilterSelectData',

			'required'=>1,
			'listing'=>20,
			'order'=>1,
			'filter'=>1,
			'listingwidth'=>'200px',
			'positionForm'=>300,
			'invisible'=>true,
		);

		$propDefs['heading'] = array(
			'name'=>'heading',
			'label'=>KText::_('Heading'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>93,
			'required'=>1,
			'order'=>20,
			'filter'=>1,
			'search'=>1,
			'listing'=>20,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminproductdetailpanes',
			'positionForm'=>400,
		);

		$propDefs['heading_icon_filename'] = array(
			'name'=>'heading_icon_filename',
			'label'=>KText::_('Heading Icon'),
			'tooltip'=>KText::_('The symbol is decorative and will be displayed next to the heading.'),
			'type'=>'file',
			'appendSerial'=>1,
			'allowedExtensions'=>array('jpg','jpeg','gif','tif','bmp','png'),
			'allow'=>array('image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png','image/x-png'),
			'size'=>'1000',
			'filetype'=>'image',
			'dirBase'=>CONFIGBOX_DIR_PRODUCT_DETAIL_PANE_ICONS,
			'urlBase'=>CONFIGBOX_URL_PRODUCT_DETAIL_PANE_ICONS,
			'required'=>0,
			'options'=>'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
			'positionForm'=>500,
		);

		$propDefs['css_classes'] = array(
			'name'=>'css_classes',
			'label'=>KText::_('CSS Classes'),
			'tooltip'=>KText::_('CSS classes enable a web developer to set specific styling for individual headings and content panes. The CSS classes entered here will be set for the heading and content wrapper.'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>600,
		);

		$propDefs['content'] = array(
			'name'=>'content',
			'label'=>KText::_('Content'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>94,
			'required'=>0,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>700,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'group'=>'product_id',
			'order'=>25,
			'listing'=>15,
			'positionForm'=>1000,
		);

		return $propDefs;

	}
}