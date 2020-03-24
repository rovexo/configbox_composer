<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewM2configurator extends KenedoView {

	/**
	 * @var array M2 config info data
	 */
	public $configInfo;

	/**
	 * @var int M2 custom option ID
	 */
	public $magentoOptionId;

	/**
	 * @var float Tax rate for the given Magento product
	 */
	public $taxRate;

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.ui-1.12.1/jquery-ui-prefixed.css';
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/configurator.css';
		return $urls;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		// We preload configurator and server for a slight speed boost
		$calls[] = 'configbox/configurator';
		$calls[] = 'configbox/server';
		$calls[] = 'configbox/m2::loadConfigurator';

		return $calls;
	}

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

	}

	/**
	 * @param array $configInfo
	 * @return $this
	 */
	function setConfigInfo($configInfo) {
		$this->configInfo = $configInfo;
		return $this;
	}

	/**
	 * @param float $taxRate
	 * @return $this
	 */
	function setTaxRate($taxRate) {
		$this->taxRate = $taxRate;
		return $this;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	function setMagentoOptionId($id) {
		$this->magentoOptionId = $id;
		return $this;
	}

}