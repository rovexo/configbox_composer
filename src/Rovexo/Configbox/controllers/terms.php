<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerTerms extends KenedoController {

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

	function display() {
		KenedoView::getView('ConfigboxViewTerms')->display();
	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=terms', $langTag);
		if ($id) {
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view']);
			return array();
		}
		else {
			unset($queryParameters['view']);
			return array('terms');
		}

	}

}
