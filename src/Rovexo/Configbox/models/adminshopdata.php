<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminshopdata extends KenedoModel {

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_shopdata';
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
			'default'=>1,
			'positionForm'=>100,
		);

		$propDefs['shopname'] = array(
			'name'=>'shopname',
			'label'=>KText::_('Shop Name'),
			'tooltip'=>KText::_('TOOLTIP_SHOPDATA_SHOPNAME'),
			'type'=>'string',
			'required'=>1,
			'positionForm'=>200,
		);

		$propDefs['shopwebsite'] = array(
			'name'=>'shopwebsite',
			'label'=>KText::_('Shop Website'),
			'tooltip'=>KText::_('TOOLTIP_SHOPDATA_SHOPWEBSITE'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>300,
		);

		$propDefs['shoplogo'] = array (
			'name'=>'shoplogo',
			'label'=>KText::_('Logo'),
			'type'=>'file',
			'appendSerial'=>1,
			'allowedExtensions'=>array('jpg','jpeg','gif','tif','bmp','png'),
			'filetype'=>'image',
			'allow'=>array('image/pjpeg','image/jpg','image/jpeg','image/gif','image/tif','image/bmp','image/png'),
			'size'=>'1024',
			'dirBase'=>CONFIGBOX_DIR_SHOP_LOGOS,
			'urlBase'=>CONFIGBOX_URL_SHOP_LOGOS,
			'required'=>0,
			'options'=>'FILENAME_TO_RECORD_ID PRESERVE_EXT SAVE_FILENAME',
			'positionForm'=>400,
		);

		$propDefs['shopaddress1'] = array(
			'name'=>'shopaddress1',
			'label'=>KText::_('Address 1'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>500,
		);

		$propDefs['shopaddress2'] = array(
			'name'=>'shopaddress2',
			'label'=>KText::_('Address 2'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>600,
		);

		$propDefs['shopzipcode'] = array(
			'name'=>'shopzipcode',
			'label'=>KText::_('ZIP Code'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>700,
		);

		$propDefs['shopcity'] = array(
			'name'=>'shopcity',
			'label'=>KText::_('City'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>800,
		);

//		$propDefs['shopcountry'] = array(
//			'name'=>'shopcountry',
//			'label'=>KText::_('Country'),
//			'type'=>'string',
//			'required'=>0,
//			'positionForm'=>900,
//		);

		$propDefs['country_id'] = array(
			'name'=>'country_id',
			'label'=>KText::_('LABEL_SHOPDATA_COUNTRY_ID'),
			'tooltip'=>KText::_('TOOLTIP_SHOPDATA_COUNTRY_ID'),
			'type'=>'countryselect',
			'stateFieldName'=>'state_id',
			'defaultlabel'=>KText::_('Choose Country'),
			'required'=>1,
			'positionForm'=>950,
		);

		$propDefs['state_id'] = array(
			'name'=>'state_id',
			'label'=>KText::_('LABEL_SHOPDATA_STATE_ID'),
			'tooltip'=>KText::_('TOOLTIP_SHOPDATA_STATE_ID'),
			'type'=>'stateselect',
			'countryFieldName'=>'country_id',
			'positionForm'=>960,
		);

		$propDefs['shopphonesales'] = array(
			'name'=>'shopphonesales',
			'label'=>KText::_('Phone Sales'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1000,
		);

		$propDefs['shopphonesupport'] = array(
			'name'=>'shopphonesupport',
			'label'=>KText::_('Phone Support'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1100,
		);

		$propDefs['shopemailsales'] = array(
			'name'=>'shopemailsales',
			'label'=>KText::_('Email Sales'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1200,
		);

		$propDefs['shopemailsupport'] = array(
			'name'=>'shopemailsupport',
			'label'=>KText::_('Email Support'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1300,
		);

		$propDefs['shopfax'] = array(
			'name'=>'shopfax',
			'label'=>KText::_('Fax'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1400,
		);

		$propDefs['shopbankname'] = array(
			'name'=>'shopbankname',
			'label'=>KText::_('Bank name'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1500,
		);

		$propDefs['shopbankaccountholder'] = array(
			'name'=>'shopbankaccountholder',
			'label'=>KText::_('Bank Account Holder'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1600,
		);

		$propDefs['shopbankaccount'] = array(
			'name'=>'shopbankaccount',
			'label'=>KText::_('Bank account number'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1700,
		);

		$propDefs['shopbankcode'] = array(
			'name'=>'shopbankcode',
			'label'=>KText::_('Bank code'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1800,
		);

		$propDefs['shopbic'] = array(
			'name'=>'shopbic',
			'label'=>KText::_('BIC'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>1900,
		);

		$propDefs['shopiban'] = array(
			'name'=>'shopiban',
			'label'=>KText::_('IBAN'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>2000,
		);

		$propDefs['shopuid'] = array(
			'name'=>'shopuid',
			'label'=>KText::_('VAT IN'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>2100,
		);

		$propDefs['shopcomreg'] = array(
			'name'=>'shopcomreg',
			'label'=>KText::_('Commercial Register ID'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>2200,
		);

		$propDefs['shopowner'] = array(
			'name'=>'shopowner',
			'label'=>KText::_('Company Owner'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>2300,
		);

		$propDefs['shoplegalvenue'] = array(
			'name'=>'shoplegalvenue',
			'label'=>KText::_('Legal Venue'),
			'type'=>'string',
			'required'=>0,
			'positionForm'=>2400,
		);

		$propDefs['shopdesc'] = array(
			'name'=>'shopdesc',
			'label'=>KText::_('Shop Description'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>38,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'required'=>0,
			'positionForm'=>2900,
		);

		$propDefs['tac'] = array(
			'name'=>'tac',
			'label'=>KText::_('Terms and Conditions'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>33,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'required'=>0,
			'positionForm'=>3000,
		);

		$propDefs['refundpolicy'] = array(
			'name'=>'refundpolicy',
			'label'=>KText::_('Refund policy'),
			'type'=>'translatable',
			'stringTable'=>'#__configbox_strings',
			'langType'=>35,
			'options'=>'USE_HTMLEDITOR ALLOW_HTML',
			'required'=>0,
			'positionForm'=>3100,
		);

		return $propDefs;
	}

	/**
	 * @param int $id
	 * @param string $languageTag
	 * @return object | ConfigboxShopData
	 * @throws Exception
	 */
	function getRecord($id, $languageTag = '') {
		$record = parent::getRecord($id, $languageTag);

		// Just for legacy support (shopcountry used to be a string input, now stored as country_id)
		if ($record->country_id) {
			$country = ConfigboxCountryHelper::getCountry($record->country_id);
			$record->shopcountry = $country->country_name;
		}
		else {
			$record->shopcountry = '';
		}

		return $record;
	}

	/**
	 * @param array $filters
	 * @param array $pagination
	 * @param array $ordering
	 * @param string $languageTag
	 * @param bool $countOnly
	 * @return int|object[]|ConfigboxShopData[]
	 * @throws Exception
	 */
	function getRecords($filters = array(), $pagination = array(), $ordering = array(), $languageTag = '', $countOnly = false) {
		return parent::getRecords($filters, $pagination, $ordering, $languageTag, $countOnly);
	}

	protected function getInvoicePlaceholderInfo() {

		$info = array(
			// Yepp, we're really setting keys based on translations made externally.
			KText::_('Order','Order') => array(
				'order_id',
				'user_id',
				'invoice_number',
				'invoice_data',
				'vatin',
			),
			KText::_('Billing','Billing') => array(
				'billingcompanyname',
				'billingfirstname',
				'billinglastname',
				'billingaddress1',
				'billingaddress2',
				'billingzipcode',
				'billingcity',
				'billingcountryname',
				'billingstatename',
				'billingcounty',
				'billingemail',
				'billingphone',
			),
			KText::_('Delivery','Delivery') => array(
				'companyname',
				'firstname',
				'lastname',
				'address1',
				'address2',
				'zipcode',
				'city',
				'countryname',
				'statename',
				'county',
				'email',
				'phone',
			),
			KText::_('Store Information','Store Information') => array(
				'shopname}',
				'shoplogo',
				'shopaddress1',
				'shopaddress2',
				'shopzipcode',
				'shopcity',
				'shopcountry',
				'shopphonesales',
				'shopphonesupport',
				'shopemailsales',
				'shopemailsupport',
				'shoplinktotc',
				'shopfax',
				'shopbankname',
				'shopbankaccountholder',
				'shopbankaccount',
				'shopbankcode',
				'shopbic',
				'shopiban',
				'shopuid',
				'shopcomreg',
				'shopwebsite',
				'shopowner',
				'shoplegalvenue',
				'invoice',
				'shopdesc',
				'tac',
				'refundpolicy',
			),

		);


		$html = '';
		foreach ($info as $heading=>$keywords) {
			$html .= '<div class="shop-data-keyword-group">';
			$html .= '<h3>'.hsc($heading).'</h3>';
			$html .= '<ul>';
			foreach ($keywords as $keyword) {
				$html .= '<li>{'.hsc($keyword).'}</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}

		return $html;

	}

}