<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewCustomerform extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var ConfigboxUserData $customerData
	 * @see ConfigboxUserHelper::getUser
	 */
	public $customerData;

	/**
	 * @var object[] $customerFields Info about which fields should be visible
	 * @see ConfigboxUserHelper::getUserFields
	 */
	public $customerFields;

	/**
	 * @var string $customerFields in JSON
	 * @see customerFields
	 */
	public $customerFieldsJson;

	/**
	 * @var string[] $fieldCssClasses Array of strings containing CSS classes for each form field. Keys of array are field names
	 */
	public $fieldCssClasses;

	/**
	 * @var string $formType (quotation|checkout|profile)
	 */
	public $formType = '';

	/**
	 * @var bool $useCityLists If dropdowns for cities shall be used (depends on if there are cities defined)
	 */
	public $useCityLists;

	/**
	 * @var boolean $allowDeliveryAddress If the delivery address toggle should be shown. Depends on if samedelivery is active in customer fields
	 */
	public $allowDeliveryAddress;

	/**
	 * @var boolean $useLoginForm If the login form shall be shown
	 */
	public $useLoginForm;

	/**
	 * @var string $viewDataAttributes HTML-data attributes for the wrapping view div
	 */
	public $viewDataAttributes;

	/**
	 * Depends on registrationOptional and if the customer is logged in already
	 * @var boolean $useOptionalRegistration Indictes if the optional register checkbox shall be shown.
	 */
	public $useOptionalRegistration;

	/**
	 * @var boolean $userIsAdmin Indicates if an admin uses the form (adds fields for custom fields, platform user id)
	 */
	public $userIsAdmin;

	/**
	 * @var string $groupDropDownHtml HTML for the select to choose the customer group.
	 */
	public $groupDropDownHtml;

	/**
	 * @var string $languageDropDownHtmlHTML for the select to choose the preferred language.
	 */
	public $languageDropDownHtml;

	/**
	 * @return ConfigboxModelCustomerForm
	 */
	public function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelCustomerForm');
	}

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.chosen-1.8.7/chosen.css';
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/customerform.css';
		return $urls;
	}

	function getJsInitCallsOnce() {
		return array(
			'configbox/customerform::initCustomerForm',
		);
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/customerform::initCustomerFormEach';
		return $calls;
	}

	function prepareTemplateVars() {

		if (empty($this->formType)) {
			$this->setFormType(KRequest::getKeyword('form_type', 'profile'));
		}

		if (empty($this->customerData)) {
			$this->customerData = ConfigboxUserHelper::getUser(NULL, NULL, true);
		}

		if (empty($this->customerFields)) {
			$this->customerFields = ConfigboxUserHelper::getUserFields();
		}

		if (empty($this->fieldCssClasses)) {
			$this->fieldCssClasses = $this->getFieldCssClasses($this->formType, $this->customerData);
		}

		if (empty($this->useCityLists)) {
			$this->useCityLists = ConfigboxCountryHelper::systemUsesCities();
		}

		if ($this->useLoginForm === NULL) {
			$this->useLoginForm = CbSettings::getInstance()->get('show_recurring_login_cart') ? true : false;
		}

		$isLoggedIn = KenedoPlatform::p()->isLoggedIn();
		if ($isLoggedIn) {
			$this->useLoginForm = false;
		}

		// Find the default value for optional registration
		if ($this->useOptionalRegistration === NULL) {

			// In case the customer is logged in, no registration obviously
			if ($isLoggedIn) {
				$this->useOptionalRegistration = false;
			}
			// For RFQ, registration is optional..
			elseif (in_array($this->formType, array('quotation'))) {
				$this->useOptionalRegistration = true;
			}
			// ..otherwise registration is mandatory
			else {
				$this->useOptionalRegistration = false;
			}

		}

		$languages = KenedoLanguageHelper::getActiveLanguages();

		$options = array();
		foreach ($languages as $language) {
			$options[$language->tag] = $language->label;
		}

		$this->languageDropDownHtml = KenedoHtml::getSelectField('language_tag', $options, $this->customerData->language_tag, KText::getLanguageCode(), false, 'chosen-dropdown');

		$this->userIsAdmin = KenedoPlatform::p()->isAdminArea() && KenedoPlatform::p()->isAuthorized('com_configbox.core.manage');

		$groupModel = KenedoModel::getModel('ConfigboxModelAdmincustomergroups');
		$groups = $groupModel->getRecords();
		$data = array();
		foreach ($groups as $group) {
			$data[$group->id] = $group->title;
		}
		$this->groupDropDownHtml = KenedoHtml::getSelectField('group_id', $data, $this->customerData->group_id, null, false, 'chosen-dropdown');

		$this->allowDeliveryAddress = ($this->customerFields['samedelivery']->{'show_'.$this->formType} == '1');

		$this->customerFieldsJson = json_encode($this->customerFields);

		$viewData = array(
			'customer-fields' => json_encode($this->customerFields),
			'view-url' => KLink::getRoute('index.php?option=com_configbox&view=customerform&formType='.$this->formType.'&output_mode=view_only', false),
		);
		$this->viewDataAttributes = '';
		foreach ($viewData as $key=>$value) {
			$this->viewDataAttributes .= ' data-'.$key.'="'.hsc($value).'"';
		}

		return $this;

	}

	/**
	 * @param string $type Valid form type
	 * @return ConfigboxViewCustomerform
	 * @throws Exception If form type is not valid
	 */
	public function setFormType($type) {

		$formTypes = array('quotation', 'checkout', 'profile', 'saveorder');

		if (!in_array($type, $formTypes)) {
			throw new Exception('Tried to set form type "'.$type.'", but only these types are possible: '.implode(', ', $formTypes));
		}

		$this->formType = $type;
		return $this;
	}

	/**
	 * @param bool $bool
	 * @return ConfigboxViewCustomerform
	 */
	public function setUseLoginForm($bool) {
		$this->useLoginForm = $bool;
		return $this;
	}

	/**
	 * @param string $formType
	 * @param ConfigboxUserData $customerData
	 * @return string[] Array with strings having CSS classes
	 */
	protected function getFieldCssClasses($formType, $customerData) {

		$model = $this->getDefaultModel();

		$customerFields = ConfigboxUserHelper::getUserFields();

		$cssClasses = array();

		foreach ($customerFields as $customerField) {
			// For convenience
			$fieldName = $customerField->field_name;

			// Add general CSS classes
			$classes = array();
			$classes[] = 'customer-field';
			$classes[] = 'customer-field-'.$fieldName;

			// Mark required fields
			if ($customerField->{'require_'.$formType}) {
				$classes[] = 'required-field';
			}

			// Make unused fields (fields that are not shown as configured in customer field settings)
			if (($customerField->{'show_'.$formType}) == false) {
				$classes[] = 'unused-field';
			}

			// Mark if delivery state dropdown got no states
			if ($fieldName == 'state' && $customerData->country) {
				$counties = $model->getStates($customerData->country);
				if (!$counties) {
					$classes[] = 'has-no-data';
				}
			}

			// Mark if billing state dropdown got no states
			if ($fieldName == 'billingstate' && $customerData->billingcountry) {
				$counties = $model->getStates($customerData->billingcountry);
				if (!$counties) {
					$classes[] = 'has-no-data';
				}
			}

			// Mark if billing county dropdown got no counties
			if ($fieldName == 'county_id') {

				// No state means we can't have any counties
				if (!$customerData->state) {
					$classes[] = 'has-no-data';
				}
				// Check if state got countes. If not, add the has-no-data flag
				else {
					$counties = $model->getCounties($customerData->state);

					if (!$counties) {
						$classes[] = 'has-no-data';
					}
				}

			}

			// Mark if billing county dropdown got no counties
			if ($fieldName == 'billingcounty_id') {

				// No state means we can't have any counties
				if (!$customerData->billingstate) {
					$classes[] = 'has-no-data';
				}
				// Check if state got countes. If not, add the has-no-data flag
				else {
					$counties = $model->getCounties($customerData->billingstate);

					if (!$counties) {
						$classes[] = 'has-no-data';
					}
				}

			}

			// Mark if delivery city dropdown got no cities
			if ($fieldName == 'city_id') {

				if ($customerData->county_id) {
					$cities = $model->getCities($customerData->county_id);
					if (!$cities) {
						$classes[] = 'has-no-data';
					}
				}
				else {
					$classes[] = 'uses-textfield-instead';
				}

			}

			// Mark if billing city dropdown got no cities
			if ($fieldName == 'billingcity_id') {

				if ($customerData->billingcounty_id) {
					$cities = $model->getCities($customerData->billingcounty_id);
					if (!$cities) {
						$classes[] = 'has-no-data';
					}
				}
				else {
					$classes[] = 'uses-textfield-instead';
				}

			}

			if ($fieldName == 'city' && $customerData->county_id) {
				$cities = $model->getCities($customerData->county_id);
				if ($cities) {
					$classes[] = 'uses-dropdown-instead';
				}
			}

			if ($fieldName == 'billingcity' && $customerData->billingcounty_id) {
				$cities = $model->getCities($customerData->billingcounty_id);
				if ($cities) {
					$classes[] = 'uses-dropdown-instead';
				}
			}

			// Finally add imploded array to cssClasses
			$cssClasses[$fieldName] = implode(' ', $classes);
		}

		return $cssClasses;

	}
	
}