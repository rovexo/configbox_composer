<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminpaymentmethods extends KenedoModel {

	function getTableName() {
		return '#__configbox_payment_methods';
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
			'positionList'=>10,
			'positionForm'=>1000,
		);

		$propDefs['title'] = array(
			'name'=>'title',
			'label'=>KText::_('Title'),
			'type'=>'translatable',
			'langType'=>46,
			'required'=>1,
			'positionList'=>30,
			'makeEditLink'=>true,
			'component'=>'com_configbox',
			'controller'=>'adminpaymentmethods',
			'canSortBy'=>true,
			'positionForm'=>1100,
		);

		$propDefs['connector_name'] = array(
			'name'=>'connector_name',
			'label'=>KText::_('Payment service provider'),
			'type'=>'join',
			'isPseudoJoin'=>true,
			'propNameKey'=>'value',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdminpaymentmethods',
			'modelMethod'=>'getPspConnectors',
			'default'=>'',
			'tooltip'=>KText::_('Choose from one of the installed payment classes. After saving you will see additional specific settings at the bottom of the form.'),
			'required'=>1,
			'options'=>'',
			'positionList'=>60,
			'canSortBy'=>true,
			'listCellWidth'=>'150px',
			'positionForm'=>1200,
		);

		$propDefs['params'] = array(
			'name'=>'params',
			'label'=>KText::_('PROP_PAYMENT_METHOD_PARAMS'),
			'type'=>'paymentmethodparams',
			'required'=>0,
			'positionForm'=>1250,
		);

		$propDefs['published'] = array(
			'name'=>'published',
			'label'=>KText::_('Active'),
			'type'=>'published',
			'positionList'=>70,
			'listCellWidth'=>'60px',
			'default'=>1,
			'canSortBy'=>true,
			'positionForm'=>1300,
		);

		$propDefs['price'] = array(
			'name'=>'price',
			'label'=>KText::_('Static extra charge'),
			'type'=>'string',
			'stringType'=>'price',
			'required'=>0,
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'tooltip'=>KText::_('Set an extra charge that customers have to pay for using this payment method.'),
			'positionForm'=>1400,
		);

		$propDefs['percentage'] = array(
			'name'=>'percentage',
			'label'=>KText::_('Extra charge percentage'),
			'type'=>'string',
			'stringType'=>'number',
			'unit'=>'%',
			'required'=>0,
			'tooltip'=>KText::_('Enter an percentage for the extra charge here - 0 to 100 of the order total. The amount is added to the static extra charge.'),
			'positionForm'=>1500,
		);

		$propDefs['price_min'] = array(
			'name'=>'price_min',
			'label'=>KText::_('Minimum Extra Charge'),
			'type'=>'string',
			'stringType'=>'price',
			'required'=>0,
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'tooltip'=>KText::_('The minimum extra charge for the payment method. Leave empty to be ignored.'),
			'positionForm'=>1600,
		);

		$propDefs['price_max'] = array(
			'name'=>'price_max',
			'label'=>KText::_('Maximum Extra Charge'),
			'type'=>'string',
			'stringType'=>'price',
			'required'=>0,
			'unit'=>ConfigboxCurrencyHelper::getBaseCurrency()->symbol,
			'tooltip'=>KText::_('The maximum extra charge for the payment method. Leave empty to be ignored.'),
			'positionForm'=>1700,
		);

		$propDefs['taxclass_id'] = array(
			'name'=>'taxclass_id',
			'label'=>KText::_('Tax Class'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdmintaxclasses',
			'modelMethod'=>'getRecords',
			'tooltip'=>KText::_('Choose a tax class that determines which tax rate will be used. In the order management you can override the tax rate of a tax rate for each country or state. This way you can have different tax rates for one product or service, depending on the delivery country.'),
			'required'=>0,
			'options'=>'SKIPDEFAULTFIELD NOFILTERSAPPLY',
			'positionForm'=>1800,
		);

		$propDefs['customer_group_ids'] = array(

			'name'=>'customer_group_ids',
			'label'=>KText::_('PROP_PAYMENT_METHOD_CUSTOMER_GROUPS_LABEL'),
			'tooltip'=>KText::_('PROP_PAYMENT_METHOD_CUSTOMER_GROUPS_TOOLTIP'),
			'required'=>0,
			'type'=>'multiselect',

			'modelClass'=>'ConfigboxModelAdmincustomergroups',
			'modelMethod'=>'getRecords',

			'xrefTable'=>'#__configbox_xref_group_payment_method',
			'fkOwn'=>'payment_id',
			'fkOther'=>'group_id',

			'keyOwn'=>'id',

			'tableOther'=>'#__configbox_groups',
			'keyOther'=>'id',
			'displayColumnOther'=>'title',
			'asCheckboxes' => true,
			'positionForm'=>1850,

		);

		$propDefs['country_ids'] = array(

			'name'=>'country_ids',
			'label'=>KText::_('PROP_PAYMENT_METHOD_COUNTRIES_LABEL'),
			'tooltip'=>KText::_('PROP_PAYMENT_METHOD_COUNTRIES_TOOLTIP'),
			'required'=>0,
			'type'=>'multiselect',

			'modelClass'=>'ConfigboxModelAdmincountries',
			'modelMethod'=>'getRecords',

			'xrefTable'=>'#__configbox_xref_country_payment_method',
			'fkOwn'=>'payment_id',
			'fkOther'=>'country_id',

			'keyOwn'=>'id',

			'tableOther'=>'#__configbox_countries',
			'keyOther'=>'id',
			'displayColumnOther'=>'country_name',
			'asCheckboxes' => true,
			'positionForm'=>1900,

		);

		$propDefs['desc'] = array(
			'name'=>'desc',
			'label'=>KText::_('Description'),
			'type'=>'translatable',
			'langType'=>47,
			'required'=>0,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'positionForm'=>2000,
		);

		$propDefs['ordering'] = array(
			'name'=>'ordering',
			'label'=>KText::_('Ordering'),
			'type'=>'ordering',
			'canSortBy'=>true,
			'positionList'=>20,
			'positionForm'=>2100,
		);

		return $propDefs;
	}

	/**
	 * Takes out nasty characters from connector name (those that might be used for file inclusing exploits)
	 * @param object $data
	 * @return bool
	 */
	function prepareForStorage($data) {

		$success = parent::prepareForStorage($data);
		if ($success == false) {
			return false;
		}

		$data->connector_name = str_replace('/','', $data->connector_name);
		$data->connector_name = str_replace('\\','', $data->connector_name);
		$data->connector_name = str_replace(DIRECTORY_SEPARATOR,'', $data->connector_name);
		$data->connector_name = str_replace('..','', $data->connector_name);

		return true;
	}

	function getPspConnectors() {

		$connectorNames = ConfigboxPspHelper::getConnectorNames();

		$items = array();

		foreach ($connectorNames as $connectorName) {

			$data = new stdClass;
			$data->value = $connectorName;
			$data->title = ConfigboxPspHelper::getPspConnectorTitle($connectorName);

			$items[] = $data;

		}

		return $items;

	}

}
