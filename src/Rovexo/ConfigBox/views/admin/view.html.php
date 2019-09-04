<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmin extends KenedoView {

	/**
	 * @var string $contentHtml Supposed to be set from outside, otherwise Admindashboard view output will be inserted.
	 */
	public $contentHtml;

	function getViewCssClasses() {
		$cssClasses = parent::getViewCssClasses();
		$cssClasses[] = 'container';
		return $cssClasses;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/admin::initBackend';
		return $calls;
	}

	function prepareTemplateVars() {

		$this->addViewCssClasses();
		
		if (empty($this->contentHtml)) {
			$this->contentHtml = KenedoView::getView('ConfigboxViewAdmindashboard')->getHtml();
		}
		
	}
	
}
