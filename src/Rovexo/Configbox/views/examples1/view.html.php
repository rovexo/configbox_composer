<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewExamples1 extends KenedoView {

	/**
	 * @var object[]
	 * @see ConfigboxModelAdminexamples::getRecords()
	 */
	var $examples;

	/**
	 *
	 * @return null
	 */
	function getDefaultModel() {
		return null;
	}

	public function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/examples1::initViewOnce';
		return $calls;
	}

	public function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/examples1::initViewEach';
		return $calls;
	}

	public function getStyleSheetUrls() {
		return [];
	}

	public function prepareTemplateVars() {

		$this->examples = KenedoModel::getModel('ConfigboxModelAdminexamples')->getRecords();

	}

}