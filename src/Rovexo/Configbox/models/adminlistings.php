<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminlistings extends KenedoModel {

	function getTableName() {
		return '#__configbox_listings';
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
			'listing'=>10000,
			'order'=>10000,
			'positionForm'=>10000,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>20,
			'required'=>1,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'adminlistings',
			'listing'=>20000,
			'order'=>20000,
			'positionForm'=>20000,
		);

		$propDefs['layoutname'] = array(
			'name'=>'layoutname',
			'label'=>KText::_('Template'),
			'type'=>'join',
			'isPseudoJoin'=>true,

			'propNameKey'=>'value',
			'propNameDisplay'=>'title',

			'modelClass'=>'ConfigboxModelAdmintemplates',
			'modelMethod'=>'getProductsTemplates',

			'default'=>'default',
			'tooltip'=>KText::_('If you created special templates for product overviews you can choose it here.'),
			'required'=>0,
			'options'=>'SKIPDEFAULTFIELD NOFILTERSAPPLY',
			'listingwidth'=>'100px',

			'listing'=>30000,
			'order'=>30000,
			'positionForm'=>30000,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'default'=>1,
			'filter'=>1,
			'listingwidth'=>'50px',
			'listing'=>40000,
			'order'=>40000,
			'positionForm'=>40000,
		);

		$propDefs['description_start'] = array(
			'name'=>'description_start',
			'type'=>'groupstart',
			'title'=>KText::_('Description'),
			'toggle'=>true,
			'defaultState'=>'closed',
			'positionForm'=>50000,
		);

		$propDefs['description'] = array(
			'name'=>'description',
			'label'=>KText::_('Description'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>40,
			'required'=>0,
			'tooltip'=>KText::_('This text is shown on top of the product listing - above the listing of products.'),
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>60000,
		);

		$propDefs['description_end'] = array(
			'name'=>'description_end',
			'type'=>'groupend',
			'positionForm'=>70000,
		);

		$propDefs['assignments_group'] = array(
			'name'=>'assignments_group',
			'type'=>'groupstart',
			'title'=>KText::_('Products in this listing'),
			'notes'=>KText::_('GROUP_NOTE_LISTING_ASSIGNMENTS'),
			'positionForm'=>90000,
		);

		$propDefs['product_sorting'] = array(
			'name'=>'product_sorting',
			'label'=>KText::_('Sort products by'),
			'type'=>'dropdown',
			'default'=>0,
			'choices'=> array(0=>KText::_('Title'), 1=>KText::_('Manual ordering')),
			'positionForm'=>100000,
		);

		$propDefs['product_assignments'] = array (
			'name'=>'product_assignments',
			'label'=>KText::_('Products in this listing'),
			'hideAdminLabel'=>true,
			'type'=>'childentries',
			'viewClass'=>'ConfigboxViewAdminproductlistingassignments',
			'viewFilters'=>array(
				array('filterName'=>'adminproductlistingassignments.listing_id', 'filterValueKey'=>'id'),
			),
			'foreignKeyField'=>'product_id',
			'parentKeyField'=>'id',
			'positionForm'=>110000,
		);

		$propDefs['assignments_group_end'] = array(
			'name'=>'assignments_group_end',
			'type'=>'groupend',
			'positionForm'=>120000,
		);

		return $propDefs;

	}

	function canDelete($id) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_xref_listing_product` WHERE `listing_id` = ".intval($id)." LIMIT 1";
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result) {
			return true;
		}
		else {
			$this->setError(KText::_('Could not delete product listing, because it contains products.'));
			return false;
		}
	}

}