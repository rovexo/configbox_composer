<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerUserorder extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewUserorder
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewUserorder');
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

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=user', $langTag);
		if ($id) {
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view']);
			if (!empty($queryParameters['order_id'])) {
				$segments[] = 'orders';
				$segments[] = $queryParameters['order_id'];
				unset($queryParameters['order_id']);
				return $segments;
			}
			return array();
		}
		else {
			unset($queryParameters['view'], $queryParameters['Itemid']);
			return array('userorder');
		}

	}

}
