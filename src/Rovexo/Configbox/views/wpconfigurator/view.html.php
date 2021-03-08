<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewWpconfigurator extends KenedoView {

	/**
	 * @var int Configurator page to use
	 */
	public $pageId;

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
		$calls[] = 'configbox/wp::loadConfigurator';

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
	 * @param int $pageId
	 * @return $this
	 */
	function setPageId($pageId) {
		$this->pageId = $pageId;
		return $this;
	}

}