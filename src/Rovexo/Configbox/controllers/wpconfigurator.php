<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerWpconfigurator extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	function getConfiguratorHtml() {

		$pageId = KRequest::getInt('pageId');

		KenedoView::getView('ConfigboxViewConfiguratorpage')
			->setPageId($pageId)
			->display();

	}

}