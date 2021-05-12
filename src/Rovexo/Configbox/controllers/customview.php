<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerCustomview extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewProduct
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

	/**
	 * Gets the custom view's controller and calls its execute() method instead
	 * @param string $task
	 * @throws Exception
	 */
	function execute($task) {

		$viewName = KRequest::getKeyword('viewname');
		if (!$viewName) {
			throw new Exception('No view name provided in menu item settings.');
		}
		$component = 'com_configbox';
		$className = KenedoController::getControllerClass($component, '', $viewName);
		$controller = KenedoController::getController($className);

		$task = trim(KRequest::getKeyword('task', 'display'));

		$controller->execute($task);

		if ($controller->redirectUrl) {
			$this->redirectUrl = $controller->redirectUrl;
		}

	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		// Now we take the actual controller and see if it does anything specific (if not, we do it the customview way - means we check for a menu item)
		$customControllerClass = self::getControllerClass($queryParameters['option'], '', $queryParameters['viewname']);
		if (self::controllerExists($customControllerClass)) {
			$controller = self::getController($customControllerClass);
			// We make a copy of the query parameters, because in case that controller doesn't do anything specific
			$testQueryParams = $queryParameters;
			$testSegments = $controller->getUrlSegments($testQueryParams);
			$diffValues = array_diff($testQueryParams, $queryParameters);
			$diffKeys = array_diff_key($testQueryParams, $queryParameters);
			// If it does, then use it, otherwise the custom view controller deals with all that
			if (count($testSegments) || count($diffValues) || count($diffKeys)) {
				$queryParameters = $testQueryParams;
				return $testSegments;
			}
		}

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=customview&viewname='.$queryParameters['viewname'], $langTag);
		if ($id) {
			$queryParameters['Itemid'] = $id;
			unset($queryParameters['view'], $queryParameters['viewname']);
			return array();
		}
		else {
			unset($queryParameters['view']);
			return array('customview');
		}

	}
}