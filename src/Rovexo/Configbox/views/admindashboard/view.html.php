<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmindashboard extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'admindashboard';

	/**
	 * @var object[] List of issues that absolutely need to be resolved
	 * @see ConfigboxModelAdmindashboard::getCriticalIssues
	 */
	public $criticalIssues;

	/**
	 * @var object[] List of issues that may cause problems
	 * @see ConfigboxModelAdmindashboard::getIssues
	 */
	public $issues;

	/**
	 * @var object[] List of performance tips
	 * @see ConfigboxModelAdmindashboard::getPerformanceTips
	 */
	public $performanceTips;

	/**
	 * @var object[] List of server stats
	 * @see ConfigboxModelAdmindashboard::getCurrentStats
	 */
	public $currentStats;

	/**
	 * @var bool Indicates if product tour should be shown
	 */
	public $showProductTour = false;

	/**
	 * @var string HTML for the admin product tour
	 * @see ConfigboxViewAdminproducttour
	 */
	public $tourHtml;

	/**
	 * @var string
	 */
	public $urlEndPointLicenseInfo;

	/**
	 * @var string
	 */
	public $urlEndPointDashboardInfo;

	/**
	 * @var string
	 */
	public $licenseKey;

	/**
	 * @var string
	 */
	public $configboxVersion;

	/**
	 * @return ConfigboxModelAdmindashboard
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmindashboard');
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/dashboard::initDashboard';
		return $calls;
	}

	function prepareTemplateVars() {

		if (ConfigboxSystemVars::getVar('post_install_done') == NULL) {
			KenedoPlatform::p()->redirect(KLink::getRoute('index.php?option=com_configbox&controller=adminpostinstall'));
		}

		if (ConfigboxSystemVars::getVar('admin_tour_done') == NULL) {
			$tourView = KenedoView::getView('ConfigboxViewAdminproducttour');
			$this->showProductTour = false;
			$this->tourHtml = $tourView->getHtml();
		}

		$model = $this->getDefaultModel();

		$this->licenseKey = CbSettings::getInstance()->get('product_key');
		$this->configboxVersion = KenedoPlatform::p()->getApplicationVersion();

		$serverList = explode(',', CbSettings::getInstance()->get('license_manager_satellites'));
		shuffle($serverList);

		$this->urlEndPointLicenseInfo = 'https://'.trim($serverList[0]).'/v4/getLicenseData.php';
		$this->urlEndPointDashboardInfo = 'https://www.configbox.at/external/dashboard/dashboard.php';

		$this->criticalIssues = $model->getCriticalIssues();
		$this->issues = $model->getIssues();
		$this->performanceTips = $model->getPerformanceTips();
		$this->currentStats = $model->getCurrentStats();

	}
	
}
