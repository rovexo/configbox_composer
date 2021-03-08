<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdmincustomers extends KenedoModel {

	/**
	 * @var array[] $validationIssues Array of arrays with infos about validation issues (keys: fieldName, error_code, message)
	 * @see validateData(), setValidationIssues(), getValidationIssues()
	 */
	protected $validationIssues = array();

	/**
	 * @return string Table used for storage
	 */
	function getTableName() {
		return '#__configbox_users';
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
			'label'=>KText::_('ID'),
			'default'=>0,
			'order'=>10,
			'search'=>1,
			'positionForm'=>100,
		);

		$propDefs['billing_start'] = array(
			'name'=>'billing_start',
			'type'=>'groupstart',
			'title'=>KText::_('Billing'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>200,
		);

		$propDefs['billingcompanyname'] = array(
			'name'=>'billingcompanyname',
			'label'=>KText::_('Billing Company Name'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'order'=>20,
			'listing'=>10,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincustomers',
			'listingwidth'=>'80px',
			'search'=>1,
			'positionForm'=>300,
		);

		$propDefs['billingsalutation_id'] = array(
			'name'=>'billingsalutation_id',
			'label'=>KText::_('Billing Salutation'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Choose a Salutation'),
			'modelClass'=>'ConfigboxModelAdminsalutations',
			'modelMethod'=>'getRecords',
			'required'=>1,
			'positionForm'=>400,
		);

		$propDefs['billingfirstname'] = array(
			'name'=>'billingfirstname',
			'label'=>KText::_('Billing First Name'),
			'type'=>'string',
			'default'=>'',
			'listing'=>20,
			'order'=>25,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincustomers',
			'listingwidth'=>'80px',
			'required'=>1,
			'positionForm'=>500,
		);

		$propDefs['billinglastname'] = array(
			'name'=>'billinglastname',
			'label'=>KText::_('Billing Last Name'),
			'type'=>'string',
			'default'=>'',
			'listing'=>30,
			'listinglink'=>1,
			'component'=>'com_configbox',
			'controller'=>'admincustomers',
			'listingwidth'=>'80px',
			'order'=>30,
			'filter'=>1,
			'search'=>1,
			'required'=>1,
			'positionForm'=>600,
		);

		$propDefs['billingaddress1'] = array(
			'name'=>'billingaddress1',
			'label'=>KText::_('Billing Address'),
			'type'=>'string',
			'default'=>'',
			'positionForm'=>700,
		);

		$propDefs['billingaddress2'] = array(
			'name'=>'billingaddress2',
			'label'=>KText::_('Billing Address 2'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>800,
		);

		$propDefs['billingzipcode'] = array(
			'name'=>'billingzipcode',
			'label'=>KText::_('Billing ZIP Code'),
			'type'=>'string',
			'default'=>'',
			'positionForm'=>900,
		);

		$propDefs['billingcity'] = array(
			'name'=>'billingcity',
			'label'=>KText::_('Billing City'),
			'type'=>'string',
			'default'=>'',
			'positionForm'=>1000,
		);

		$propDefs['billingcountry'] = array(
			'name'=>'billingcountry',
			'label'=>KText::_('Billing Country'),
			'type'=>'countryselect',
			'stateFieldName'=>'billingstate',
			'defaultlabel'=>KText::_('Choose Country'),
			'required'=>1,
			'positionForm'=>1100,
		);

		$propDefs['billingstate'] = array(
			'name'=>'billingstate',
			'label'=>KText::_('Billing State'),
			'type'=>'stateselect',
			'countryFieldName'=>'billingcountry',
			'positionForm'=>1200,
		);

		$propDefs['billingcounty_id'] = array(
			'name'=>'billingcounty_id',
			'label'=>KText::_('Billing County'),
			'type'=>'countyselect',
			'stateFieldName'=>'billingstate',
			'positionForm'=>1300,
		);

		$propDefs['billingcity_id'] = array(
			'name'=>'billingcity_id',
			'label'=>KText::_('Billing City'),

			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'city_name',
			'defaultlabel'=>KText::_('Select City'),

			'modelClass'=>'ConfigboxModelAdmincities',
			'modelMethod'=>'getRecords',
			'positionForm'=>1400,
		);

		$propDefs['billingemail'] = array(
			'name'=>'billingemail',
			'label'=>KText::_('Billing Email'),
			'type'=>'string',
			'default'=>'',
			'required'=>1,
			'listing'=>40,
			'order'=>40,
			'search'=>1,
			'positionForm'=>1500,
		);

		$propDefs['billingphone'] = array(
			'name'=>'billingphone',
			'label'=>KText::_('Billing Phone'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>1600,
		);

		$propDefs['baseprice_end'] = array(
			'name'=>'baseprice_end',
			'type'=>'groupend',
			'positionForm'=>1700,
		);

		$propDefs['delivery_start'] = array(
			'name'=>'delivery_start',
			'type'=>'groupstart',
			'title'=>KText::_('Delivery'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>1800,
		);

		$propDefs['companyname'] = array(
			'name'=>'companyname',
			'label'=>KText::_('Company Name'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>1900,
		);

		$propDefs['salutation_id'] = array(
			'name'=>'salutation_id',
			'label'=>KText::_('Salutation'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'defaultlabel'=>KText::_('Choose a Salutation'),
			'modelClass'=>'ConfigboxModelAdminsalutations',
			'modelMethod'=>'getRecords',
			'positionForm'=>2000,
		);

		$propDefs['firstname'] = array(
			'name'=>'firstname',
			'label'=>KText::_('First Name'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'component'=>'com_configbox',
			'controller'=>'admincustomers',
			'positionForm'=>2100,
		);

		$propDefs['lastname'] = array(
			'name'=>'lastname',
			'label'=>KText::_('Last Name'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'component'=>'com_configbox',
			'controller'=>'admincustomers',
			'positionForm'=>2200,
		);

		$propDefs['address1'] = array(
			'name'=>'address1',
			'label'=>KText::_('Address'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>2300,
		);

		$propDefs['address2'] = array(
			'name'=>'address2',
			'label'=>KText::_('Address 2'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>2400,
		);

		$propDefs['zipcode'] = array(
			'name'=>'zipcode',
			'label'=>KText::_('ZIP Code'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>2500,
		);

		$propDefs['city'] = array(
			'name'=>'city',
			'label'=>KText::_('City'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>2600,
		);

		$propDefs['country'] = array(
			'name'=>'country',
			'label'=>KText::_('Country'),
			'type'=>'countryselect',
			'stateFieldName'=>'state',
			'defaultlabel'=>KText::_('Choose Country'),
			'positionForm'=>2700,
		);

		$propDefs['state'] = array(
			'name'=>'state',
			'label'=>KText::_('STATE'),
			'type'=>'stateselect',
			'countryFieldName'=>'country',
			'positionForm'=>2800,
		);

		$propDefs['county_id'] = array(
			'name'=>'county_id',
			'label'=>KText::_('County'),
			'type'=>'string',
			'positionForm'=>2900,
		);

		$propDefs['state'] = array(
			'name'=>'state',
			'label'=>KText::_('State'),
			'type'=>'stateselect',
			'countryFieldName'=>'country',
			'positionForm'=>3000,
		);

		$propDefs['county_id'] = array(
			'name'=>'county_id',
			'label'=>KText::_('County'),
			'type'=>'countyselect',
			'stateFieldName'=>'state',
			'positionForm'=>3100,
		);

		$propDefs['city_id'] = array(
			'name'=>'city_id',
			'label'=>KText::_('City'),

			'type'=>'join',

			'propNameKey'=>'id',
			'propNameDisplay'=>'city_name',
			'defaultlabel'=>KText::_('Select City'),

			'modelClass'=>'ConfigboxModelAdmincities',
			'modelMethod'=>'getRecords',
			'positionForm'=>3200,
		);

		$propDefs['email'] = array(
			'name'=>'email',
			'label'=>KText::_('Email'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>3300,
		);

		$propDefs['phone'] = array(
			'name'=>'phone',
			'label'=>KText::_('Phone'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>3400,
		);

		$propDefs['delivery_end'] = array(
			'name'=>'delivery_end',
			'type'=>'groupend',
			'positionForm'=>3500,
		);

		$propDefs['other_start'] = array(
			'name'=>'other_start',
			'type'=>'groupstart',
			'title'=>KText::_('Other'),
			'toggle'=>true,
			'defaultState'=>'opened',
			'positionForm'=>3600,
		);

		$propDefs['vatin'] = array(
			'name'=>'vatin',
			'label'=>KText::_('VAT IN'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'tooltip'=>KText::_('The VAT identification number of the customer.'),
			'positionForm'=>3700,
		);

		$propDefs['samedelivery'] = array(
			'name'=>'samedelivery',
			'label'=>KText::_('Billing address same as delivery'),
			'type'=>'boolean',
			'default'=>1,
			'positionForm'=>3800,
		);

		$propDefs['group_id'] = array(
			'name'=>'group_id',
			'label'=>KText::_('Customer Group'),
			'type'=>'join',
			'propNameKey'=>'id',
			'propNameDisplay'=>'title',
			'modelClass'=>'ConfigboxModelAdmincustomergroups',
			'modelMethod'=>'getRecords',
			'required'=>1,
			'listing'=>10000,
			'filter'=>10000,
			'order'=>10000,

			'positionForm'=>3900,
		);

		$propDefs['platform_user_id'] = array(
			'name'=>'platform_user_id',
			'label'=>KText::_('Platform User ID'),
			'type'=>'string',
			'default'=>0,
			'positionForm'=>4000,
		);

		$propDefs['language_tag'] = array(
			'name'=>'language_tag',
			'label'=>KText::_('Preferred Language'),
			'type'=>'join',
			'isPseudoJoin'=>true,
			'propNameKey'=>'tag',
			'propNameDisplay'=>'label',
			'defaultlabel'=>KText::_('Select Language'),

			'modelClass'=>'ConfigboxModelAdminlanguages',
			'modelMethod'=>'getActiveLanguages',

			'required'=>0,
			'options'=>'NOFILTERSAPPLY',
			'positionForm'=>4100,
		);

		$propDefs['newsletter'] = array(
			'name'=>'newsletter',
			'label'=>KText::_('Newsletter'),
			'type'=>'boolean',
			'default'=>0,
			'positionForm'=>4200,
		);

		$propDefs['is_temporary'] = array(
			'name'=>'is_temporary',
			'label'=>KText::_('Temporary Customer'),
			'type'=>'boolean',
			'required'=>0,
			'default'=>0,
			'positionForm'=>4300,
		);

		$propDefs['custom_1'] = array(
			'name'=>'custom_1',
			'label'=>KText::_('Custom Field 1'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>4400,
		);

		$propDefs['custom_2'] = array(
			'name'=>'custom_2',
			'label'=>KText::_('Custom Field 2'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>4500,
		);

		$propDefs['custom_3'] = array(
			'name'=>'custom_3',
			'label'=>KText::_('Custom Field 3'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>4600,
		);

		$propDefs['custom_4'] = array(
			'name'=>'custom_4',
			'label'=>KText::_('Custom Field 4'),
			'type'=>'string',
			'default'=>'',
			'required'=>0,
			'positionForm'=>4700,
		);

		$propDefs['other_end'] = array(
			'name'=>'other_end',
			'type'=>'groupend',
			'positionForm'=>4800,
		);

		return $propDefs;
	}

	/**
	 * This is overridden because the user field definitions say what fields are there (instead of model properties)
	 * @return object
	 */
	function getDataFromRequest() {

		$fieldDefinitions = ConfigboxUserHelper::getUserFields();
		$properties = $this->getProperties();

		$data = new stdClass;
		foreach ($fieldDefinitions as $fieldName=>$fieldDefinition) {
			$properties[$fieldName]->getDataFromRequest($data);
		}

		$properties['custom_1']->getDataFromRequest($data);
		$properties['custom_2']->getDataFromRequest($data);
		$properties['custom_3']->getDataFromRequest($data);
		$properties['custom_4']->getDataFromRequest($data);
		$properties['group_id']->getDataFromRequest($data);
		$properties['id']->getDataFromRequest($data);

		$data->platform_user_id = KRequest::getInt('platform_user_id');

		return $data;

	}

	/**
	 * @param ConfigboxDbUserData $data
	 * @return bool
	 */
	function prepareForStorage($data) {

		// In case 'samedelivery' wasn't set, set the default value.
		if ($data->samedelivery === null) {
			$props = $this->getProperties();
			$data->samedelivery = $props['samedelivery']->getPropertyDefinition('default', '1');
		}

		// If delivery is same as billing, copy over billing fields
		if ($data->samedelivery == 1) {
			$fieldDefinitions = ConfigboxUserHelper::getUserFields();
			foreach ($fieldDefinitions as $fieldName=>$fieldDefinition) {
				// If it's a billing field..
				if (strpos($fieldName, 'billing') === 0) {
					// ..get the corresponding delivery field name and copy the value over
					$pendant = substr($fieldName, 7);
					$data->$pendant = $data->$fieldName;
				}
			}
		}

		// If we got a city_id, overwrite the city text value
		if (!empty($data->city_id)) {
			$data->city = ConfigboxCountryHelper::getCityName($data->city_id);
		}

		// If we got a city_id, overwrite the city text value
		if (!empty($data->billingcity_id)) {
			$data->billingcity = ConfigboxCountryHelper::getCityName($data->billingcity_id);
		}

		// On updates with no user password, put the existing password in the data
		if ($data->id && empty($data->password)) {
			$user = ConfigboxUserHelper::getUser($data->id, false, false);
			if ($user && !empty($user->password)) {
				$data->password = $user->password;
			}
		}

		// Keep up the created date
		if ($data->id && empty($data->created)) {
			$user = ConfigboxUserHelper::getUser($data->id, false, false);
			if ($user && !empty($user->created)) {
				$data->created = $user->created;
			}
		}

		// Remove the flag to make the customer non-temporary
		$data->is_temporary = 0;

		return true;

	}

	/**
	 * @param ConfigboxUserData|ConfigboxDbUserData $data
	 * @param string $context
	 * @return bool
	 */
	function validateData($data, $context = '') {

		// Sneak in the right context from the form_type
		if ($context == '') {
			if (KRequest::getString('form_type')) {
				$context = KRequest::getString('form_type');
			}
		}

		$keyRequired = 'require_'.$context;
		$fieldDefinitions = ConfigboxUserHelper::getUserFields();
		$fieldTranslations = ConfigboxUserHelper::getUserFieldTranslations();
		$invalid = array();
		$validationIssues = array();

		$ignoredFields = array('samedelivery', 'newsletter');

		// First, check for required empty fields
		foreach ($fieldDefinitions as $fieldName=>$fieldDefinition) {

			// Skip ignored fields
			if (in_array($fieldName, $ignoredFields)) {
				continue;
			}

			// Prepare value
			$value = trim(strval($data->$fieldName));

			// Check for missing data
			if ($fieldDefinition->$keyRequired && empty($value)) {

				// First we skip empty fields that are empty for a reason

				// For state: If selected country got no states, ignore
				if ($fieldName == 'billingstate' && !empty($data->billingcountry)) {
					if (ConfigboxCountryHelper::hasStates($data->billingcountry) == false) {
						continue;
					}
				}
				// For state: If selected country got no states, ignore
				if ($fieldName == 'state' && !empty($data->country)) {
					if (ConfigboxCountryHelper::hasStates($data->country) == false) {
						continue;
					}
				}

				// For county: If selected state got no counties, ignore
				if ($fieldName == 'billingcounty_id' && !empty($data->billingstate)) {
					if (ConfigboxCountryHelper::hasCounties($data->billingstate) == false) {
						continue;
					}
				}
				// For county: If selected state got no counties, ignore
				if ($fieldName == 'county_id' && !empty($data->state)) {
					if (ConfigboxCountryHelper::hasCounties($data->state) == false) {
						continue;
					}
				}

				// For city ID:
				if ($fieldName == 'billingcity_id') {

					if (ConfigboxCountryHelper::systemUsesCities() == false) {
						continue;
					}

					// If selected county got no cities, ignore
					if (!empty($data->billingcounty_id) && ConfigboxCountryHelper::hasCities($data->billingcounty_id) == false) {
						continue;
					}
					// Or if the selected state got no counties (then city IDs won't be involved anyways)
					if (!empty($data->billingstate) && ConfigboxCountryHelper::hasCounties($data->billingstate) == false) {
						continue;
					}
				}
				// For city: If selected county got no cities, ignore
				if ($fieldName == 'city_id') {

					if (ConfigboxCountryHelper::systemUsesCities() == false) {
						continue;
					}

					// If selected county got no cities, ignore
					if (!empty($data->county_id) && ConfigboxCountryHelper::hasCities($data->county_id) == false) {
						continue;
					}
					// Or if the selected state got no counties (then city IDs won't be involved anyways)
					if (!empty($data->state) && ConfigboxCountryHelper::hasCounties($data->state) == false) {
						continue;
					}
				}

				// Add a validation issue
				$validationIssues[] = array(
					'fieldName' => $fieldName,
					'errorCode' => 'empty_field',
					'message' => KText::sprintf('%s is required.', $fieldTranslations[$fieldName]),
				);

				$invalid[$fieldName] = KText::sprintf('%s is required.', $fieldTranslations[$fieldName]);

			}
		}

		// Now check for non-empty fields with specific validation
		foreach ($fieldDefinitions as $fieldName=>$fieldDefinition) {

			if (in_array($fieldName, $ignoredFields)) {
				continue;
			}

			// Prepare value
			$value = trim(strval($data->$fieldName));

			// Skip non-empty fields
			if ($value === '') {
				continue;
			}

			switch ($fieldName) {

				case 'email':
					if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

				case 'billingemail':
					if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

				case 'county':
					if ($value != 0 && ConfigboxCountryHelper::countryIdExists($value) == false) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

				case 'billingcounty':
					if ($value != 0 && ConfigboxCountryHelper::countryIdExists($value) == false) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

				case 'state':
					if ($value != 0 && ConfigboxCountryHelper::stateIdExists($value, $data->country) == false) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

				case 'billingstate':
					if ($value != 0 && ConfigboxCountryHelper::stateIdExists($value, $data->billingcountry) == false) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

				case 'vatin':
					if (ConfigboxUserHelper::checkVatIn($value, $data->billingcountry) == false) {
						$validationIssues[] = array(
							'fieldName' => $fieldName,
							'errorCode' => 'invalid_value',
							'message' => KText::sprintf('%s is invalid.', $fieldTranslations[$fieldName]),
						);
					}
					break;

			}


		}

		if (!defined('CONFIGBOX_IGNORE_DUPLICATE_EMAIL')) {
			// Get the user data
			$user = ConfigboxUserHelper::getUser($data->id, NULL, false);

			// If we deal with non-registered user, see if there is another account with that email address already.
			if (empty($user->platform_user_id) && ConfigboxPermissionHelper::canEditOrders() == false) {

				$db = KenedoPlatform::getDb();
				$query = "
				SELECT `id`
				FROM `#__configbox_users`
				WHERE `id` != ".intval($data->id)." AND `platform_user_id` != 0 AND `platform_user_id` IS NOT NULL AND `billingemail` = '".$db->getEscaped($data->billingemail)."'
				LIMIT 1";
				$db->setQuery($query);
				$id = $db->loadResult();

				if ($id) {
					$validationIssues[] = array(
						'fieldName' => 'billingemail',
						'errorCode' => 'account_exists',
						'message' => KText::_('You already have an account with that email address. You can log in or recover your password above the address form.'),
					);
				}

			}
		}

		// Check if customer group exists
		$group = ConfigboxUserHelper::getGroupData($data->group_id);
		if (!$group) {
			$validationIssues[] = array(
				'fieldName' => 'group_id',
				'errorCode' => 'invalid_value',
				'message' => KText::_('Selected customer group does not exist.'),
			);
		}

		// Push the validation issue messages in the regular model errors
		$this->resetErrors();

		foreach ($validationIssues as $valIssue) {
			$this->setError($valIssue['message']);
		}

		$this->setValidationIssues($validationIssues);

		if (count($validationIssues)) {
			return false;
		}
		else {
			return true;
		}

	}

	/**
	 * @param array[] $issues
	 */
	function setValidationIssues($issues) {
		$this->validationIssues = $issues;
	}

	/**
	 * Returns more detailed infos about validation issues than getErrors
	 * Sample:
	 *
	 * array(
	 *  array(
	 * 	 'fieldName' => '',
	 *   'errorCode' => '',
	 *   'message' => '',
	 *  ),
	 * )
	 *
	 * @return array[] $issues
	 */
	function getValidationIssues() {
		return $this->validationIssues;
	}

	function registerPlatformUser($customerId) {

		ConfigboxUserHelper::resetUserCache($customerId);
		$customer = ConfigboxUserHelper::getUser($customerId);

		// Add a platform user account
		$registrationData = ConfigboxUserHelper::registerPlatformUser($customer);

		// Abort if registration failed
		if (!$registrationData) {
			KLog::log('Could not register platform user. Configbox User id is "'.$customer->id.'". Error message is "'.ConfigboxUserHelper::getError().'". Provided user data was '.var_export($customer,true).'.', 'error');
			$this->setError(KText::_('An error occured during saving your account information. Please try again or contact us directly.'));
			return false;
		}

		// Log for debugging
		KLog::log('Registered platform user account for Configbox user id "'.$customer->id.'". Platform user id is "'.(int)$registrationData->platformUserId.'"', 'debug');

		// Set the platform user id (the id gets stored in the registerPlatformUser method
		$customer->platform_user_id = $registrationData->platformUserId;

		// Store the platform user id
		$db = KenedoPlatform::getDb();
		$query = "UPDATE `".$this->getTableName()."` SET `platform_user_id` = ".intval($customer->platform_user_id)." WHERE `id` = ".intval($customer->id);
		$db->setQuery($query);
		$updateSuccess = $db->query();

		// Abort if that update failed somehow
		if ($updateSuccess == false) {
			KLog::log('Could not update the platform id of Configbox user with id "'.$customer->id.'". Error message is "'.$db->getErrorMsg().'".', 'error');
			$this->setError(KText::_('An error occured during saving your account information. Please try again or contact us directly.'));
			return false;
		}

		ConfigboxUserHelper::resetUserCache($customerId);

		// Send the registration email
		$sendSuccess = ConfigboxUserHelper::sendRegistrationEmail($customer, $registrationData->passwordClear);

		// Alert the user that he may not have gotten the registration email
		if ($sendSuccess == false) {
			$this->setError(KText::_('We had a problem sending you the account registration email. Everything else is fine. If you need to login, please use the Forgot Password function on our website.'));
		}

		KenedoObserver::triggerEvent('onCustomerRegistration', array($customer));

		return true;

	}


}
