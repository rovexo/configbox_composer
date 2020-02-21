<?php
// That constant is tried in all files to prevent direct execution
if (!defined('CB_VALID_ENTRY')) {
	define('CB_VALID_ENTRY', true);
}

// Init Kenedo framework (Runs Configbox' init scripts as well)
require_once(dirname(__FILE__).'/external/kenedo/helpers/init.php');
initKenedo('com_configbox');

// Collect the request parameters to figure out which task of which controller to execute
$component = KRequest::getKeyword('option','com_configbox');
$controllerName = KRequest::getKeyword('controller','');
$viewName = KRequest::getKeyword('view','');
$task = KRequest::getKeyword('task','display');

if ($controllerName || $viewName) {

	$className = KenedoController::getControllerClass($component, $controllerName, $viewName);

	if (KenedoController::controllerExists($className) == false) {
		if ($controllerName) {
			KLog::log('Controller "'.$controllerName.'" requested, but does not exist.', 'warning');
			throw new Exception('The requested controller does not exist', 404);
		}
		if ($viewName) {
			KLog::log('View "'.$viewName.'" requested, but does not exist.', 'warning');
			throw new Exception('The requested view does not exist', 404);
		}
	}

	$controller = KenedoController::getController($className);

	// Start a new output buffer
	ob_start();

	// Execute the task
	$controller->execute($task);

	// Get the output so far in a variable
	$output = ob_get_clean();

	// Redirect if a task handler has set a redirectUrl
	if ($controller->redirectUrl) {
		$controller->redirect();
	}
	else {

		// Send output through observers
		KenedoObserver::triggerEvent('onBeforeRender', array(&$output));

		// Render the output
		KenedoPlatform::p()->renderOutput($output);

		// Restore error handler to give the platform a normal environment
		KenedoPlatform::p()->restoreErrorHandler();

	}

}
