<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewExamples extends KenedoView {

	/**
	 * @var object[]
	 * @see ConfigboxModelAdminexamples::getRecords()
	 */
	var $examples;

	/**
	 *
	 * @return ConfigboxModelAdminexamples
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminexamples');
	}

	public function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/examples::initViewOnce';
		return $calls;
	}

	public function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/examples::initViewEach';
		return $calls;
	}

	public function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/examples.css';
		return $urls;
	}

	public function prepareTemplateVars() {

		$this->examples = $this->getDefaultModel()->getRecords();

	}

}