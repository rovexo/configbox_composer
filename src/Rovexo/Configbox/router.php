<?php
if (interface_exists('\Joomla\CMS\Component\Router\RouterInterface')) {

	class ConfigboxRouter implements Joomla\CMS\Component\Router\RouterInterface {

		public function build(&$query) {
			return ConfigboxBuildRoute($query);
		}

		public function parse(&$segments) {
			return ConfigboxParseRoute($segments);
		}

		public function preprocess($query) {
			// Since J4 one has to set the Itemid during preprocessing for getting menu item paths sorted
			$copy = $query;
			ConfigboxBuildRoute($copy);
			if (!empty($copy['Itemid'])) {
				$query['Itemid'] = $copy['Itemid'];
			}
			return $query;
		}
	}
}

/**
 * @param string[] $query
 * @return string[] $urlSegments
 */
function ConfigboxBuildRoute(&$query) {

	// In case the application files are somehow messed up, let things be to avoid breaking the whole site
	if (!is_file(dirname(__FILE__).'/external/kenedo/helpers/init.php')) {
		return array();
	}

	// Init Kenedo
	require_once(dirname(__FILE__).'/external/kenedo/helpers/init.php');
	initKenedo('com_configbox');

	// 'Translate' view names that we changed between 2.6 and 3.0
	KenedoRouterHelper::doLegacyFixesBuildRoute($query);

	// Nothing goes without a view name
	if (!isset($query['view'])) {
		return array();
	}

	// Carefully get controller and view name
	$controllerName = (!empty($query['controller'])) ? $query['controller'] : '';
	$viewName 		= (!empty($query['view'])) ? $query['view'] : '';

	// Figure out the class of the controller in charge
	$controllerClass = KenedoController::getControllerClass('com_configbox', $controllerName, $viewName);

	// In case there is no such controller, log a warning and let things be
	if (KenedoController::controllerExists($controllerClass) == false) {
		KLog::log('Got a call for buildRoute for unknown controller "'.$controllerClass.'". Query parameters were '.var_export($query, true), 'warning');
		return array();
	}

	// Let the right controller return URL segments for that query (in case you wonder - the customview controller will let the real controller do the work)
	return KenedoController::getController($controllerClass)->getUrlSegments($query);

}

/**
 * @param string[] $segments
 * @return string[] $query
 * @throws Exception
 */
function ConfigboxParseRoute(&$segments) {

	// In case the application files are somehow fucked up, let things be to avoid breaking the whole site
	if (!is_file(dirname(__FILE__).'/external/kenedo/helpers/init.php')) {
		return array();
	}

	// Init Kenedo
	require_once(dirname(__FILE__).'/external/kenedo/helpers/init.php');
	initKenedo('com_configbox');

	// Joomla 1.5 somehow replaces the first dash with a colon, we 'normalize' that here
	foreach ($segments as $key=>$segment) {
		$segments[$key] = str_ireplace(':', '-', $segments[$key]);
	}

	// Take in the currently active Joomla Itemid
	$activeItemId = KenedoPlatform::p()->getActiveMenuItemId();

	// Get the data from the active Joomla menu item
	$activeItem = KenedoRouterHelper::getJoomlaMenuItemData($activeItemId);

	// Change old 2.6 view names to 3.0 view names
	KenedoRouterHelper::doLegacyFixesParseRoute($activeItem);

	// Get the number of segments we're dealing with
	$segmentCount = count($segments);

	// Prepare the vars array we are supposed to send back
	$vars = array();

	// Prepare the view name from the currently active menu item (CAREFUL: That is not the view name from the route we gonna build)
	$activeViewName = (!empty($activeItem->query['view'])) ? $activeItem->query['view'] : '';

	$segmentMatching = array();
	$segmentParsing = array();
	$viewName = '';

	// Now we check the active view name and the number of segments and deduce what view we use.
	// And we figure out what segment stands for which query parameter.
	// For the latter we make a segment matching array (keys are the segment numbers, values are the parameter names)

	if ($activeViewName) {

		switch ($activeViewName) {

			case 'user':

				// For view user there can be segments like /orders/[order_id]
				if ($segmentCount == 2) {
					if ($segments[0] == 'orders') {
						$viewName = 'userorder';
						$segmentMatching = array('', 'order_id');
					}

				}
				elseif($segmentCount == 1) {
					if ($segments[0] == 'edit') {
						$viewName = 'user';
						$vars['layout'] = 'editprofile';
						unset($segments[0]);
						$segmentMatching = array();
					}
				}
				else {
					$viewName = 'user';
					$segmentMatching = array();
				}

				break;

			case 'productlisting':

				if ($segmentCount == 1) {
					$viewName = 'product';
					$segmentMatching = array('prod_id');
				}
				elseif ($segmentCount == 2) {
					$viewName = 'configuratorpage';
					$segmentMatching = array('prod_id', 'page_id');
				}
				else {
					$viewName = 'productlisting';
					$segmentMatching = array();
				}
				break;

			case 'product':

				if ($segmentCount == 1) {
					$viewName = 'configuratorpage';
					// We are cheeky and sneak in the prod_id from the active menu item
					$vars['prod_id'] = $activeItem->query['prod_id'];
					$segmentMatching = array('page_id');
				}
				else {
					$viewName = 'product';
					$segmentMatching = array();
				}
				break;

			case 'configuratorpage':

				$viewName = 'configuratorpage';

				$segmentMatching = array();

				// If we got no prod_id from the menu item, we assume that the first segment stands for the prod_id
				if (empty($activeItem->query['prod_id'])) {
					$segmentMatching[] = 'prod_id';
				}
				// Ok, so the menu item provided a prod_id, take it and move on
				else {
					$vars['prod_id'] = $activeItem->query['prod_id'];
				}

				// If we got no page_id from the menu item, we assume that the next segment stands for the page_id (see above about the prod_id)
				if (empty($activeItem->query['page_id'])) {
					$segmentMatching[] = 'page_id';
				}
				// Otherwise, take the page id from the active menu item
				else {
					$vars['page_id'] = $activeItem->query['page_id'];
				}
				break;

			case 'cart':

				$viewName = 'cart';
				$segmentMatching = array();
				break;

			// Basically the same as the 'default' case. Just setting the actual view name
			case 'customview':

				$activeViewName = $activeItem->query['viewname'];

				$controllerClass = KenedoController::getControllerClass('com_configbox', '', $activeViewName);
				$controller = KenedoController::getController($controllerClass);

				$viewName = $controller->getViewNameFromUrlSegments($segments);
				$segmentMatching = $controller->getSegmentMatching($viewName, $segments);
				$segmentParsing = $controller->getSegmentParsing($viewName, $segments);

				break;

			default:

				$controllerClass = KenedoController::getControllerClass('com_configbox', '', $activeViewName);
				$controller = KenedoController::getController($controllerClass);

				$viewName = $controller->getViewNameFromUrlSegments($segments);
				$segmentMatching = $controller->getSegmentMatching($viewName, $segments);
				$segmentParsing = $controller->getSegmentParsing($viewName, $segments);

				break;

		}

	}
	else {

		// In that case we got no active view name, that would come from a URL like /component/configbox/bla/bla
		// Given the number of segments we can still deduce what view we got and what the segments mean.
		switch (count($segments)) {

			case 1:

				// A few controllers add their view name as segment value if there is no specific menu item for them
				if (in_array($segments[0], array('productlisting', 'cart', 'checkout', 'terms', 'refundpolicy', 'user', 'userorder'))) {
					$viewName = $segments[0];
					unset($segments[0]);
					$segmentMatching = array();
				}
				// If it's not about those, then we must be dealing with a product (e.g. /component/configbox/car)
				else {
					$viewName = 'product';
					$segmentMatching = array('prod_id');
				}

				break;

			case 2:

				// In this case we must be dealing with a configurator page (e.g. /component/configbox/car/exteror)
				$viewName = 'configuratorpage';
				$segmentMatching = array('prod_id', 'page_id');
				break;

			case 3:

				// If the first segment is 'ipn' then we must be dealing with an IPN call
				if ($segments[0] == 'ipn') {
					$viewName = '';
					$segmentMatching = array('controller', 'task', 'connector_name');
				}

				break;

			default:

				// No matching view, contemplate about going to panic
				$viewName = NULL;
				$segmentMatching = array();
				break;
		}

	}

	// Now we know the view name and all our segment matchings, let's fill those vars
	switch ($viewName) {

		// For products we use labels in segments for SEO, we translate the label into a product ID
		case 'product':

			foreach ($segmentMatching as $segmentNumber => $queryParameterName) {

				// Since the segment value is a label, we get the right page ID now
				if ($queryParameterName == 'prod_id') {

					$vars[$queryParameterName] = KenedoRouterHelper::getProdId($segments[$segmentNumber]);

					// Store the used label, we may need it to recover from an outdated URL
					// See ConfigboxModelProduct::fixLabels()
					$GLOBALS['productLabel'] = $segments[$segmentNumber];

					unset($segments[$segmentNumber]);
					continue;
				}

			}
			break;

		// For configurator pages we also got labels, here we translate both into product and page IDs
		case 'configuratorpage':

			foreach ($segmentMatching as $segmentNumber => $queryParameterName) {

				// Since the segment value is a label, we get the right product ID now
				if ($queryParameterName == 'prod_id') {

					$vars[$queryParameterName] = KenedoRouterHelper::getProdId($segments[$segmentNumber]);

					// Store the used label, we may need it to recover from an outdated URL
					// See ConfigboxModelProduct::fixLabels()
					$GLOBALS['productLabel'] = $segments[$segmentNumber];
					unset($segments[$segmentNumber]);

				}

				// Since the segment value is a label, we get the right page ID now
				if ($queryParameterName == 'page_id') {

					$vars[$queryParameterName] = KenedoRouterHelper::getPageId($vars['prod_id'], $segments[$segmentNumber]);

					// Store the used label, we may need it to recover from an outdated URL
					// See ConfigboxModelProduct::fixLabels()
					$GLOBALS['pageLabel'] = $segments[$segmentNumber];

					unset($segments[$segmentNumber]);
				}

			}

			break;

		// Here is our default for any other view (including custom views)
		default:

			foreach ($segmentMatching as $segmentNumber => $queryParameterName) {

				// A parameter name can be empty, which means the segment value can be ignored
				if (empty($queryParameterName)) {
					continue;
				}

				// A controller can specify a callable that that translates the segment value to a parameter value
				if (!empty($segmentParsing[$segmentNumber])) {

					if (is_callable($segmentParsing[$segmentNumber]) == false) {
						KLog::log('The controller for view "'.$viewName.'" specified a segmentParser "'.$segmentParsing[$segmentNumber].'", but it is not callable (either does not exist, is private or other reasons). Check the settings', 'error');
						throw new Exception('There is problem with controller for view "'.$viewName.'". Segment parsing seems misconfigured, check error log file for more info.');
					}

					$vars[$queryParameterName] = call_user_func($segmentParsing[$segmentNumber], $segments[$segmentNumber]);
					unset($segments[$segmentNumber]);
				}
				else {
					$vars[$queryParameterName] = $segments[$segmentNumber];
					unset($segments[$segmentNumber]);
				}

			}

			break;

	}

	// Deal with the module assignment trick
	if ($viewName == 'configuratorpage') {
		$id = KenedoRouterHelper::getFakedConfigurationItemId($vars['prod_id'],$vars['page_id']);
		if ($id) {
			/** @noinspection PhpDeprecationInspection */
			JRequest::setVar('Itemid',$id);
			KRequest::setVar('Itemid',$id);
		}
	}

	// Last but not least, add the view name
	$vars['view'] = $viewName;

	return $vars;

}
