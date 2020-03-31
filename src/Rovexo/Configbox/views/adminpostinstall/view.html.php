<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminpostinstall extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminpostinstall';

	/**
	 * @var string License Key
	 */
	public $licenseKey;

	/**
	 * @var ConfigboxShopData
	 */
	public $shopData;

	/**
	 * @var ConfigboxPlatformLanguageData[]
	 */
	public $languages;

	/**
	 * @var string[] Language tags the user has selected
	 */
	public $selectedLanguageTags;

	/**
	 * @var ConfigboxCurrencyData
	 */
	public $baseCurrency;

	/**
	 * @var ConfigboxCurrencyData[]
	 */
	public $currencies;

	/**
	 * @var ConfigboxCountryData[]
	 */
	public $countries;

	/**
	 * @var string[]
	 */
	public $countryOptions;

	/**
	 * @var float
	 */
	public $defaultTaxRate = 21;

	/**
	 * @var string
	 */
	public $urlDashboard;

	/**
	 * @var int Since shop data stores country name only we store previosly selected ID in KSession
	 * @see ConfigboxControllerAdminpostinstall::storeTaxData
	 */
	public $selectedCountryId;

	/**
	 * @var int
	 */
	public $currentStep;

	/**
	 * @var string
	 */
	public $platformName;

	/**
	 * @return ConfigboxModelAdminpostinstall
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpostinstall');
	}

	function getJsInitCallsEach() {
		return array(
			'configbox/postinstall::initPostInstall'
		);
	}

	function prepareTemplateVars() {

		$this->currentStep = KRequest::getInt('step', 1);

		$this->selectedLanguageTags = KenedoLanguageHelper::getActiveLanguageTags();
		$this->languages = KenedoPlatform::p()->getLanguages();

		// Hotfix to add a possibly failed initial adding of active language
		if (count($this->selectedLanguageTags) == 0) {
			$languageTag = KenedoPlatform::p()->getLanguageTag();
			$db = KenedoPlatform::getDb();
			$query = "INSERT INTO #__configbox_active_languages VALUES ('".$db->getEscaped($languageTag)."')";
			$db->setQuery($query);
			$db->query();
			$this->selectedLanguageTags = KenedoLanguageHelper::getActiveLanguageTags();
		}

		$this->licenseKey = CbSettings::getInstance()->get('product_key');

		$this->platformName = KenedoPlatform::getName();

		$this->urlDashboard = KLink::getRoute('index.php?option=com_configbox&controller=admindashboard', false);

		$this->shopData = KenedoModel::getModel('ConfigboxModelAdminshopdata')->getRecord(1);

		$this->countries = ConfigboxCountryHelper::getCountries();

		$this->countryOptions = array(''=>'');
		foreach ($this->countries as $country) {
			$this->countryOptions[$country->id] = $country->country_name;
		}

		$taxRates = KenedoModel::getModel('ConfigboxModelAdmintaxclasses')->getRecords();
		if (count($taxRates)) {
			$this->defaultTaxRate = $taxRates[0]->default_tax_rate;
		}

		$this->baseCurrency = ConfigboxCurrencyHelper::getBaseCurrency();

		$this->currencies = ConfigboxCurrencyHelper::getCurrencies();
		foreach ($this->currencies as $key=>$currency) {
			if ($currency->code == $this->baseCurrency->code) {
				unset($this->currencies[$key]);
			}
		}

	}
	
}
