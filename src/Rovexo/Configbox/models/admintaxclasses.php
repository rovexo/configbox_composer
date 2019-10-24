<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmintaxclasses extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_tax_classes';
	}

	/**
	 * @return string Name of the table's primary key
	 */
	function getTableKey() {
		return 'id';
	}

	function getPropertyDefinitions() {

		$propDefs = array();

		$propDefs['id'] = array (
			'name'=>'id',
			'label'=>KText::_('ID'),
			'type'=>'id',
			'default'=>0,
			'listing'=>10,
			'listingwidth'=>'50px',
			'order'=>100,
			'positionForm'=>100,
		);

		$propDefs['title'] = array (
			'name'=>'title',
			'label'=>KText::_('Name'),
			'type'=>'string',
			'required'=>1,
			'listing'=>20,
			'order'=>20,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'admintaxclasses',
			'positionForm'=>200,
		);

		$propDefs['default_tax_rate'] = array (
			'name'=>'default_tax_rate',
			'label'=>KText::_('Default Tax'),
			'type'=>'string',
			'stringType'=>'number',
			'required'=>1,
			'unit'=>'%',
			'listing'=>30,
			'order'=>30,
			'listingwidth'=>'120px',
			'positionForm'=>300,
		);

		$propDefs['id_external'] = array (
			'name'=>'id_external',
			'label'=>KText::_('External ID'),
			'tooltip'=>KText::_('The tax class ID of your third party order management system. Leave empty if you use ConfigBox Order Management.'),
			'type'=>'string',
			'required'=>0,
			'listing'=>40,
			'order'=>40,
			'listingwidth'=>'80px',
			'positionForm'=>400,
		);

		return $propDefs;

	}

	function getTaxClasses() {
		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__configbox_tax_classes`";
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getRecordUsageInfo() {

		$usage = array(
			'com_configbox'=> array(

				'ConfigboxModelAdminproducts'=> array(
					array(
						'titleField'=>'title',
						'fkField'=>'taxclass_id',
						'controller'=>'adminproducts',
						'name'=>KText::_('Product Base Price'),
					),
					array(
						'titleField'=>'title',
						'fkField'=>'taxclass_recurring_id',
						'controller'=>'adminproducts',
						'name'=>KText::_('Product Base Price Recurring'),
					),
				),

				'ConfigboxModelAdminpaymentmethods'=> array(
					array(
						'titleField'=>'title',
						'fkField'=>'taxclass_id',
						'controller'=>'adminpaymentmethods',
						'name'=>KText::_('Payment Method Price'),
					),
				),

				'ConfigboxModelAdminshippingmethods'=> array(
					array(
						'titleField'=>'title',
						'fkField'=>'taxclass_id',
						'controller'=>'adminshippingmethods',
						'name'=>KText::_('Delivery Method Price'),
					),
				),
			),
		);

		return $usage;

	}

}
