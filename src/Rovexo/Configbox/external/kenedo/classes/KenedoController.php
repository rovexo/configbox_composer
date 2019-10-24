<?php
defined('CB_VALID_ENTRY') or die();

abstract class KenedoController {

	public $component = '';

	public $redirectUrl;
	protected $message;
	protected $messageType;

	static $instances;

	/**
	 * @var string $executedTask Name of the task that is executed (set by KenedoController::execute()
	 *
	 * @see KenedoController::execute()
	 */
	public $executedTask = '';

	function __construct($component) {
		$this->component = $component;
	}

	/**
	 * Returns the model to be used for standard tasks
	 *
	 * @return KenedoModel|NULL $model Subclass of KenedoModel or null if controller uses no model
	 *
	 * @see KenedoModel::getModel()
	 */
	abstract protected function getDefaultModel();

	/**
	 * Returns the KenedoView subclass for displaying arbitrary content
	 *
	 * @return KenedoView|NULL $view Subclass of KenedoView or null if controller uses no views
	 *
	 * @see KenedoView::getView()
	 */
	abstract protected function getDefaultView();

	/**
	 * Returns the KenedoView subclass for displaying a list of records
	 *
	 * @return KenedoView|NULL $view Subclass of KenedoView or null if controller does not use views
	 *
	 * @see KenedoView::getView()
	 */
	abstract protected function getDefaultViewList();

	/**
	 * Returns the KenedoView subclass for editing the record
	 *
	 * @return KenedoView|NULL $view Subclass of KenedoView  or null if controller does not use views
	 *
	 * @see KenedoView::getView()
	 */
	abstract protected function getDefaultViewForm();

	/**
	 * Returns the controller singleton object by $className
	 * Finds the right file in customization or system automatically. You can also force the location with param $path.
	 *
	 * @param string $className Class name of the controller
	 * @param string $path (optional) Absolute path to the controller file
	 *
	 * @return KenedoController $controller KenedoController subclass
	 *
	 * @see KenedoController::getControllerClass()
	 * @throws Exception if $className is empty
	 */
	static function getController($className, $path = '') {

		// Legacy, remove with 2.7 for old client method (frontend made it load from components frontend folder, backend from backend - ignored in 2.6.0)
		if ($path == 'frontend' || $path == 'backend') {
			KLog::logLegacyCall('Method no longer uses frontend and backend as $path parameter. Use path to controller file instead if necessary.');
			$path = '';
		}

		if (trim($className) == '') {
			$identifier = KLog::log('Empty parameter $className. Parameters were '.var_export(func_get_args(), true), 'error');
			throw new Exception('A system error occured, see error log file. Identifier '.$identifier);
		}

		if (!isset(self::$instances[$className])) {

			$component = 'com_' . strtolower( substr($className, 0, strpos($className, 'Controller') ) );
			$filename = strtolower(substr($className, strpos($className, 'Controller') + 10 )).'.php';

			// MERGELEGACY
			if ($component == 'com_cbcheckout') {
				$component = 'com_configbox';
			}

			// Get the absolute path to the custom or system file
			$regularPath = KenedoPlatform::p()->getComponentDir($component) .DS. 'controllers' .DS. $filename;
			$customPath = CONFIGBOX_DIR_CUSTOMIZATION .DS. 'controllers' .DS. $filename;

			// Overwrite $path to get the file from either customization or system
			if ($path == '') {
				if (is_file($customPath)) {
					$path = $customPath;
				}
				elseif (is_file($regularPath)) {
					$path = $regularPath;
				}
			}

			// Abort if the class file cannot be found anywhere
			if (!is_file($path)) {
				$logMessage = 'Controller file for class "'.$className.'" not found in path "'.$path.'".';
				$identifier = KLog::log($logMessage, 'error');
				$publicMessage = 'Controller file for class "'.$className.'" not found. See error log file (Identifier: '.$identifier.').';
				throw new Exception($publicMessage);
			}

			// Load the model file
			require_once($path);

			// MERGELECACY
			$fallBackClass = str_replace('ConfigboxController', 'CbcheckoutController', $className);
			if (class_exists($className) == false && class_exists($fallBackClass)) {
				KLog::logLegacyCall('Change class name from "'.$fallBackClass.'" to "'.$className.'" in "'.$path.'" and change call in the file and line mentioned.');
				self::$instances[$className] = new $fallBackClass($component);
			}
			else {
				self::$instances[$className] = new $className($component);
			}

		}

		return self::$instances[$className];

	}

	/**
	 * @param string $className Class name of the controller
	 * @param string $path Optional path to the controller file
	 * @return bool
	 */
	static function controllerExists($className, $path = '') {

		// Shortcut in case we already instantiated one like this
		if (isset(self::$instances[$className])) {
			return true;
		}

		$component = 'com_' . strtolower( substr($className, 0, strpos($className, 'Controller') ) );
		$filename = strtolower(substr($className, strpos($className, 'Controller') + 10 )).'.php';

		// MERGELEGACY
		if ($component == 'com_cbcheckout') {
			$component = 'com_configbox';
		}

		// Get the absolute path to the custom or system file
		$regularPath = KenedoPlatform::p()->getComponentDir($component) .DS. 'controllers' .DS. $filename;
		$customPath = CONFIGBOX_DIR_CUSTOMIZATION .DS. 'controllers' .DS. $filename;

		// Overwrite $path to get the file from either customization or system
		if ($path == '') {
			if (is_file($customPath)) {
				$path = $customPath;
			}
			elseif (is_file($regularPath)) {
				$path = $regularPath;
			}
		}

		// Abort if the class file cannot be found anywhere
		if (!is_file($path)) {
			return false;
		}

		// Load the model file
		require_once($path);

		$fallBackClass = str_replace('ConfigboxController', 'CbcheckoutController', $className);
		if (class_exists($className) == true || class_exists($fallBackClass) == true) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Gets you the controller class name based on the 3 parameters
	 *
	 * @param string $component Name of the component (e.g. com_configbox, typically comes in through $_REQUEST['option'])
	 * @param string $controllerName (optional if $view is supplied) Name of the controller (e.g. cart, typically comes in through $_REQUEST['controller'])
	 * @param string $viewName (optional if $controllerName is supplied) Name of the view (e.g. cart, typically comes in through $_REQUEST['view'])
	 *
	 * @return string $className Class name ready to be used in KenedoController::getController()
	 *
	 * @see KenedoController::getController()
	 * @throws Exception if neither $controller or $view is supplied
	 */
	static function getControllerClass($component, $controllerName = '', $viewName = '') {

		if ($controllerName) {
			$namePart = $controllerName;
		}
		elseif ($viewName) {
			$namePart = $viewName;
		}
		else {
			$identifier = KLog::log('Invalid parameters, both $controllerName and $viewName parameters are empty. Request URI was "'.$_SERVER['REQUEST_URI'].'", query string was "'.$_SERVER['QUERY_STRING'].'" Function parameters were '.var_export(func_get_args(), true), 'error');
			throw new Exception('Invalid parameters, both $controllerName and $viewName parameters are empty. Log identifier is '.$identifier, '400');
		}

		$className = ucfirst(strtolower(substr($component, 4))).'Controller'.ucfirst(strtolower($namePart));

		return $className;

	}

	static function getControllerNameFromClass($className) {
		return strtolower(substr($className, strpos($className, 'Controller') + 10 ));
	}

	/**
	 * Executes a task. Basically calls a controller's method, but checks if the method exists and if it is public
	 *
	 * @param string $task Name of the task to be executed, typically comes from $_REQUEST['task']
	 *
	 * @throws Exception if the task does not exist or is not public
	 */
	public function execute($task) {

		// Check if exists
		if (method_exists($this, $task) == false) {

			$msg = 'Task "'.$task.'" was supplied, but does not exist in class. $_REQUEST variables were '.var_export($_REQUEST, true);
			if (!empty($_SERVER['HTTP_REFERER'])) {
				$msg .= '. Referer is '.$_SERVER['HTTP_REFERER'];
			}
			if (!empty($_SERVER['REQUEST_URI'])) {
				$msg .= '. Request URI was '.$_SERVER['REQUEST_URI'];
			}
			$identifier = KLog::log($msg, 'warning');
			throw new Exception('Task not found. See ConfigBox error log file. Log entry identifier is '.$identifier);
		}

		// Check if public
		$reflection = new ReflectionMethod($this, $task);
		if ($reflection->isPublic() == false) {

			$msg = 'Task "'.$task.'" was supplied, but is not a public method. $_REQUEST variables were '.var_export($_REQUEST, true);
			if (!empty($_SERVER['HTTP_REFERER'])) {
				$msg .= '. Referer is '.$_SERVER['HTTP_REFERER'];
			}
			if (!empty($_SERVER['REQUEST_URI'])) {
				$msg .= '. Request URI was '.$_SERVER['REQUEST_URI'];
			}

			$identifier = KLog::log($msg, 'warning');
			throw new Exception('Task not found. See ConfigBox error log file. Log entry identifier is '.$identifier);
		}

		// Store the task name for reference
		$this->executedTask = $task;

		// Go for it
		$this->$task();

	}

	/**
	 * Redirects the visitor. Settings are made in KenedoController::setRedirect()
	 * @see KenedoController::setRedirct()
	 */
	function redirect() {

		if ($this->redirectUrl) {
			if ($this->message) {
				KenedoPlatform::p()->sendSystemMessage($this->message, $this->messageType);
			}
			KenedoPlatform::p()->redirect($this->redirectUrl);
		}

	}

	/**
	 * By convention this method handles the default output to the browser for whatever the controller deals with.
	 * When dealing with data that has a list and an edit form, this method does the list
	 * Typically it uses a KenedoView to handle preparation of data and output.
	 * Output (as always) is thrown into the output buffer and is picked up by the entry file
	 */
	function display() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		// Get the view
		$view = $this->getDefaultView();

		if ($view == NULL) {
			throw new Exception('Display task called, but there is no view assigned to that controller. Check method getDefaultView.');
		}

		// Hint the view that it's a listing (KenedoView::display() uses it to set the right template file)
		$view->assign('listing', true);

		// Wrap the output of the views depending on the way the stuff should be shown
		$this->wrapViewAndDisplay($view);

	}

	/**
	 * By convention this method handles displays the edit form for the data it deals with.
	 * Typically it uses a KenedoView to handle preparation of data and output.
	 * Output (as always) is thrown into the output buffer and is picked up by the entry file
	 */
	function edit() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		// Get the view
		$view = $this->getDefaultViewForm();

		if (!$view) {
			throw new Exception('Edit task called, but there is no view assigned to that controller. Check method getDefaultView.');
		}

		// Hint the view that it's not a listing, but a form (KenedoView::display() uses it to set the right template file)
		$view->assign('listing', false);

		// Wrap the output of the views depending on the way the stuff should be shown
		$this->wrapViewAndDisplay($view);

	}

	/**
	 * General purpose store method. Uses KenedoModel::store() to deal with the data.
	 *
	 * 'Flow':
	 *
	 * 1) Kenedo.executeDetailsTask kicks off saving by submitting POST data into an iframe (see KenedoView's
	 * default-editform.php template for reference)
	 * 2) The method puts JS into the <head> of the iframe's HTML doc to instruct Kenedo what to do after saving
	 *
	 * Why all the fuzz? iframe for having file uploads along with the rest of the data, JS in the <head> because
	 * an iframe better be HTML. Method calls to Kenedo instead of providing data to be consistent with
	 * KenedoController::afterSave() which gives you flexibility in both server and browser side.
	 *
	 * @see KenedoModel::getDataFromRequest(), KenedoModel::prepareForStorage(), KenedoModel::validateData(), KenedoModel::store()
	 */
	function store() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		// Get the default model
		$model = $this->getDefaultModel();

		if (!$model) {
			throw new Exception('Store task called, but there is no model assigned to that controller. Check method getDefaultModel.');
		}

		// Make a normalized data object from HTTP request data
		$data = $model->getDataFromRequest();

		// Prepare the data (auto-fill data like empty URL segment fields and similar)
		$model->prepareForStorage($data);

		// Check if the data validates
		$checkResult = $model->validateData($data);

		$isInsert = $model->isInsert($data);

		// Abort and send feedback if validation fails
		if ($checkResult === false) {
			KenedoPlatform::p()->setDocumentMimeType('application/json');
			$response = new stdClass();
			$response->success = false;
			$response->errors = $model->getErrors();
			echo json_encode($response);
			return;
		}

		// Get the data stored
		$success = $model->store($data);

		// Run the afterSave stuff
		$this->afterStore($success);

		// Abort and send feedback if storage fails
		if ($success === false) {
			KenedoPlatform::p()->setDocumentMimeType('application/json');
			$response = new stdClass();
			$response->success = false;
			$response->errors = $model->getErrors();
			echo json_encode($response);
			return;
		}

		// Purge the cache
		$this->purgeCache();

		// Bring the good news
		KenedoPlatform::p()->setDocumentMimeType('application/json');
		$response = new stdClass();
		$response->success = true;
		$response->messages = array();
		$response->wasInsert = $isInsert;
		$response->messages[] = KText::_('Record saved.');

		// Add the current record data to the response
		if (!empty($data->id)) {
			$model->forgetRecord($data->id);
			$response->data = $model->getRecord($data->id);
		}
		else {
			$response->data = NULL;
		}

		if (KRequest::getKeyword('task') == 'apply') {
			// On inserts, we redirect to the right edit URL (have the right ID set)
			if ($isInsert) {
				// Get the controller name
				$controllerName = KenedoController::getControllerNameFromClass(get_class($this));
				// Get the redirect URL
				$url = 'index.php?option='.$this->component.'&controller='.$controllerName.'&task=edit&id='.$data->id;
				// If the return param is sent along, append it
				if (KRequest::getString('return')) {
					$url .= '&return='.KRequest::getString('return');
				}
				// Get it all together
				$response->redirectUrl = KLink::getRoute($url, false);
			}
		}
		else {
			if (KRequest::getString('return')) {
				$url = KLink::base64UrlDecode(KRequest::getString('return'));
			}
			else {
				// Get the controller name
				$controllerName = KenedoController::getControllerNameFromClass(get_class($this));
				// Get the redirect URL
				$url = KLink::getRoute('index.php?option='.$this->component.'&controller='.$controllerName, false);
			}

			$response->redirectUrl = $url;
		}

		echo json_encode($response);

	}

	/**
	 * On the backend, the same as store. Frontend JS takes care of not redirecting.
	 *
	 * @see KenedoController::apply()
	 */
	function apply() {
		$this->store();
	}

    /**
     * On the backend, the same as store. Frontend JS takes care of not redirecting.
     *
     * @see KenedoController::apply()
     */
    function storeAndNew() {
        $this->store();
    }

    /**
	 * Method you can override in subclasses to add some logic after saving
	 *
	 * @param bool $success Indicates if saving was successful
	 */
	protected function afterStore($success) {

	}

	/**
	 * Ajax save differs from regular saving that it returns some json only and that it updates individual fields
	 * of an item (as opposed to all fields)
	 *
	 * @see KenedoModel::ajaxStore()
	 */
	function ajaxStore() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();

		$id = KRequest::getInt('id');
		$success = $model->ajaxStore($id);

		// Prepare the response object
		$response = new stdClass();
		if ($success) {
			$response->status = 1;
			$response->errors = array();
		}
		else {
			$response->status = 0;
			$response->errors = $model->getErrors();
		}

		$this->afterStore($success);

		// Purge the cache
		$this->purgeCache();

		echo json_encode($response);

	}

	/**
	 * Basically the same as delete(), but responding with json data instead of loading the view
	 *
	 * @see KenedoModel::delete()
	 */
	function ajaxDelete() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();

		$ids = KRequest::getInt('id');

		// Legacy param name
		if (!$ids) {
			$ids = KRequest::getInt('cid');
		}

		$success = $model->delete($ids);

		$response = new stdClass();
		if ($success) {
			$response->success = true;
			$response->errors = array();
		}
		else {
			$response->success = false;
			$response->errors = $model->getErrors();
		}

		// Purge the cache
		$this->purgeCache();

		echo json_encode($response);

		// For now, Magento puts in some JS code for some reason
		die();

	}

	/**
	 * Removes one or more items using KenedoModel::delete() and loads the default view
	 *
	 * @see KenedoModel::delete()
	 */
	function delete() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();

		$ids = KRequest::getString('ids');
		$cid = KRequest::getInt('cid');

		if (!$ids && !$cid) {
			return;
		}

		if ($ids) {
			$ids = explode(',',$ids);
			foreach ($ids as &$id) {
				$id = intval($id);
			}
			unset($id);
		}
		if ($cid) {
			$ids = array($cid);
		}

		$success = $model->delete($ids);

		KenedoViewHelper::clearMessages();

		if ($success == false) {

			KenedoPlatform::p()->sendSystemMessage(KText::_('Could not delete record.'), 'error');

			$errors = $model->getErrors();

			foreach ($errors as $error) {
				KenedoPlatform::p()->sendSystemMessage($error, 'error');
				KenedoViewHelper::addMessage($error, 'error');
			}

		}
		else {

			$message = KText::_('Record deleted.');

			KenedoPlatform::p()->sendSystemMessage($message);
			KenedoViewHelper::addMessage($message);

			$this->purgeCache();
			$model->forgetRecords();

		}

		if (KRequest::getInt('quickedit', 0) == 1) {
			$this->setRedirect($_SERVER['HTTP_REFERER']);
		}
		else {
			$this->display();
		}

	}

	/**
	 * Takes in parameter id of the record (or comma-separated 'ids' for multiple records) and it will copy the record(s)
	 * and its child records.
	 * Responds with a JSON string or returns the Kenedo List HTML if parameter tmpl=component
	 */
    public function copy() {

		// check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		// check type
		$responseType = (KRequest::getString('tmpl') == 'component') ? 'list' : 'json';

		// Set the mime type for the response for JSON response
		if($responseType == 'json') {
			KenedoPlatform::p()->setDocumentMimeType('application/json');
		}

		// set ids or just one id
        $id = KRequest::getInt('id');
		$ids = KRequest::getString('ids');

		// The system takes in 'id' or 'ids'. In either case, we make an array $ids for looping later
		if(!empty($ids)) {
			$ids = explode(',', $ids);
		}
		elseif($id){
			$ids = [$id];
		}
		else $ids = [];

		// Cast all IDs to int for sanitation
		foreach ($ids as &$id) {
			$id = intval($id);
		}

		// Bounce if no record ID came in
		if(empty($ids)) {

			if($responseType == 'json') {
				echo json_encode(array(
					'success' => false,
					'errors' => array(KText::_('Please select a record to copy')),
				));
				return;
			}
			else {
				$error = KText::_('Please select a record to copy');
				KenedoPlatform::p()->sendSystemMessage($error, 'error');
				KenedoViewHelper::addMessage($error, 'error');
				$this->display();
				return;
			}
		}

		KLog::log('Starting copying data for Controller "' . get_class($this) . '". - ' . KLog::time('ModelCopyMethod'), 'custom_my');

		// Start a transaction, if any record fails to copy, we roll back all DB changes.
		KLog::log('Controller '.get_class($this). ' starts its transaction.', 'custom_my');
		KenedoPlatform::getDb()->startTransaction();

		try {

			$newId = null;
			$newIds = array();

			// Get the model and the record we gotta copy
			$model = $this->getDefaultModel();
			// Prepare the language tags
			$languageTags = KenedoLanguageHelper::getActiveLanguageTags();

			// loop trough ids
			foreach ($ids as $id) {

				$record = $model->getRecord($id);

				// Until we got a better way, assume there is a title (or otherwise a name) and append 'Copy' to it
				foreach ($languageTags as $languageTag) {

					if (!empty($record->{'title-' . $languageTag})) {
						$record->{'title-' . $languageTag} = $record->{'title-' . $languageTag} . ' (' . KText::_('COPY_NOUN') . ')';
					}
					elseif (!empty($record->{'name-' . $languageTag})) {
						$record->{'name-' . $languageTag} = $record->{'name-' . $languageTag} . ' (' . KText::_('COPY_NOUN') . ')';
					}

				}

				// Copy the record, $response will be false or the ID of the new record
				$response = $model->copy($record);

				// If things went bad, report (logging already happened in the model)
				if ($response === false) {
					$error = 'Record '.$record->id.' failed to copy, error messages from model are: ' . implode(', ', $model->getErrors());
					throw new Exception($error);
				}

				// Prepare new record id and controller name
				$newIds[] = $newId = $response;

			}

			// success
			KLog::log('Controller '.get_class($this). ' commits its transaction.', 'custom_my');
			KenedoPlatform::getDb()->commitTransaction();

			$controllerName = KenedoController::getControllerNameFromClass(get_class($this));

			// Purge the cache
			$this->purgeCache();

			// Deliver the good news
			if($responseType == 'json') {
				echo json_encode(array(
					'success' => true,
					'newId' => $newId,
					'newIds' => $newIds,
					'redirectUrl' => KLink::getRoute('index.php?option=' . $this->component . '&controller=' . $controllerName . '&task=edit&id=' . $newId, false),
				));
			}
			elseif($responseType == 'list'){
				$msg = KText::_('Records copied.');
				KenedoPlatform::p()->sendSystemMessage($msg, 'notice');
				KenedoViewHelper::addMessage($msg, 'notice');
				$this->display();
			}

			return;

		}
		catch (Exception $e) {

			KLog::log($e->getMessage(), 'error');
			KLog::log($e->getMessage(), 'custom_my');
			KLog::log('Controller '.get_class($this). ' rolls back its transaction.', 'custom_my');

			KenedoPlatform::getDb()->rollbackTransaction();

			// Purge the cache
			$this->purgeCache();

			$errorMsg = KText::_('A system error occurred during copying. Diagnostic data is in the ConfigBox error log. Please notify your service provider.');
			// error response
			if ($responseType == 'json') {
				echo json_encode(array(
					'success' => false,
					'errors' => $errorMsg,
				));
			} elseif ($responseType == 'list') {
				KenedoPlatform::p()->sendSystemMessage($errorMsg, 'error');
				KenedoViewHelper::addMessage($errorMsg, 'error');
				$this->display();
			}
			return;
		}
    }

	/**
	 * Basically changes the field `published` in a record, but returns json
	 *
	 * @param bool $publish (optional) omit or true to publish, false to unpublish
	 *
	 * @see KenedoController::publish(), KenedoModel::publish()
	 */
	function ajaxPublish($publish = true) {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();

		$ids = KRequest::getString('ids');
		$ids = explode(',', $ids);
		// Sanitize ids
		foreach ($ids as &$id) {
			$id = intval($id);
		}

		$success = $model->publish($ids, $publish);

		$response = new stdClass();

		if ($success) {
			$response->success = true;
			$response->errors = array();
		}
		else {
			$response->success = false;
			$response->errors = $model->getErrors();
		}

		$this->purgeCache();

		echo json_encode($response);

	}

	/**
	 * @see KenedoController::ajaxPublish()
	 */
	function ajaxUnpublish() {
		$this->ajaxPublish(false);
	}

	/**
	 * Basically changes the field `published` in a record
	 *
	 * @param bool $publish true to publish, false to unpublish
	 * @see KenedoModel::publish()
	 */
	function publish($publish = true) {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();

		$ids = KRequest::getString('ids');
		$ids = explode(',', $ids);
		// Sanitize ids
		foreach ($ids as &$id) {
			$id = intval($id);
		}

		$model->publish($ids, $publish);

		$this->purgeCache();
		$this->display();

	}

	/**
	 * @see KenedoController::publish()
	 */
	function unpublish() {
		$this->publish(false);
	}

	/**
	 * Takes a JSON string from request, sets the ordering and displays the default view
	 *
	 * See JS Kenedo.storeOrdering how the JSON string comes to be
	 *
	 * @see KenedoModel::storeOrdering()
	 */
	function storeOrdering() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$model = $this->getDefaultModel();

		$items = json_decode(html_entity_decode( KRequest::getString('ordering-items','') ), true);

		$success = $model->storeOrdering($items);

		if ($success) {
			$this->purgeCache();
			$model->forgetRecords();
		}
		else {
			KenedoPlatform::p()->sendSystemMessage(KText::_('Saving ordering failed.'), 'error');
			KenedoPlatform::p()->sendSystemMessage($model->getError(), 'error');
		}

		$this->display();

	}

	/**
	 * Essentially sets redirection, but you can use for whatever needs to be done when the user clicks 'cancel'
	 */
	function cancel() {

		if (KRequest::getVar('return')) {
			$url = KLink::base64UrlDecode(KRequest::getVar('return'));
		}
		else {
			$controllerName = KenedoController::getControllerNameFromClass(get_class($this));
			$url = KLink::getRoute('index.php?option='.$this->component.'&controller='.$controllerName.'&in_modal='.KRequest::getInt('in_modal',0), false);
		}

		$this->setRedirect($url);

	}

	/**
	 * Figures out if the current user is authorized to execute the given task
	 * ATTENTION: It's a stub basically, currently we only check for core.manage permission
	 *
	 * @param string $task (optional) Task to check for (uses KenedoController::$executedTask if omitted)
	 *
	 * @return bool
	 */
	public function isAuthorized($task = '') {

		$controllerName = KenedoController::getControllerNameFromClass(get_class($this));

		if (strpos($controllerName, 'admin') === 0) {

			$asset = $this->component.'.core.manage';
			$authorized = KenedoPlatform::p()->isAuthorized($asset, NULL, 20);

			if ($authorized == false) {
				return false;
			}
			else {
				return true;
			}
		}

		return true;
	}

	/**
	 * Convenience method to shorten tasks that handle unauthorized access with an exception
	 *
	 * @throws Exception
	 */
	protected function abortUnauthorized() {
		$identifier = KLog::log('Unauthorized execution of controller task  "'.$this->executedTask.'" was attempted. Request variables were '.var_export(KRequest::getAll(), true), 'authorization');
		throw new Exception('Application authentication and authorization needed. See authorization log file, identifier '.$identifier, 403);
	}

	/**
	 * Helper function, not an actual task. Takes a view that is ready to go and wraps it
	 *
	 * What you need to know before looking at the concept:
	 *
	 * - The component's output is thrown into the output buffer, the contents are in turn picked up by the platform
	 *   then it inserts the contents in its template
	 *
	 * - The request parameter format=raw changes the platform's behavior so that it outputs the component's output alone
	 *
	 * - The request parameter tmpl=component makes the platform put the output in a blank HTML document (blank as in
	 *   nothing but the component in the body tag, but a head tag as the platform and its template sees fit.
	 *
	 * Concept:
	 *
	 * Via HTTP request, you can get the content of a KenedoView in 3 ways:
	 *
	 * - The view within the component's admin view (ConfigboxViewAdmin) within the platform's template/HTML doc
	 * - The view within the component's blank view (ConfigboxViewBlank)
	 * - The view alone
	 *
	 * What's the admin and blank view?
	 *
	 * - The blank view has nothing but a div with CSS class cb-content.
	 * - The admin view has the same 2 div and a simple layout (header, left and right column)
	 *
	 * What's the blank template for?
	 *
	 * In a nutshell 2 important things:
	 *
	 * - Have reliable HTML elements for attaching delegated JS event handlers
	 * - Have a clearly defined wrapping HTML element so we can write CSS selectors that limit to the component's HTML
	 *
	 * @param KenedoView $view
	 */
	protected function wrapViewAndDisplay($view) {

		// Ajax Subview needs nothing, this is also used for the push state updates
		if (KRequest::getInt('ajax_sub_view') == 1) {

			$view->isAjaxView = true;
			$view->display();

		}
		// Modal view, means that we wrap the view in the blank view (almost nothing, just a div wrapper with our .cb-content class)
		elseif (KRequest::getKeyword('tmpl') == 'component' || KRequest::getInt('in_modal') == 1) {

			ob_start();
			$view->display();
			$output = ob_get_clean();

			$wrapperView = KenedoView::getView('ConfigboxViewBlank');
			$wrapperView->assignRef('output', $output);
			$wrapperView->display();

		}
		// This is the basic full page load of an admin page, the view gets wrapped in the header, menu, content structure
		elseif (KRequest::getKeyword('format') != 'raw' && strpos($view->view,'admin') === 0 && $view->view != 'admin') {

			ob_start();
			$view->display();
			$output = ob_get_clean();

			$wrapperView = KenedoView::getView('ConfigboxViewAdmin');
			$wrapperView->assignRef('contentHtml', $output);
			$wrapperView->display();

		}
		// I think that case never comes into play
		else {
			$view->display();
		}

	}

	/**
	 * Instructs the app to redirect after the task is executed
	 *
	 * @param string $url URL to redirect to (Use a complete URI)
	 * @param string $message User feedback to send (through the platform)
	 * @param string $messageType Type of the user feedback (error|notice|message)
	 */
	protected function setRedirect($url, $message = '', $messageType = 'message') {
		$this->redirectUrl = $url;
		$this->message = $message;
		$this->messageType = $messageType;
	}

	/**
	 * Purges all cache values
	 */
	protected function purgeCache() {
		ConfigboxCacheHelper::purgeCache();
	}

	/**
	 * Helper method for Joomla router function xBuildRoute. Returns the right URL segments and removes the right
	 * things from $queryParameters
	 * @param string[] $queryParameters Query parameters like we deal with in Joomla's buildRoute function
	 * @return string[] $segments URL segments like we deal with in Joomla's parseRoute function
	 */
	public function getUrlSegments(&$queryParameters) {
		return array();
	}

	/**
	 * Helper method for Joomla router function xParseRoute. Checks the active view name and segments and tells what
	 * view to use. Classic case: one controller deals with a list of items with links to a detail view. These links
	 * want to get SEFed. getUrlSegments makes segments, Itemid is still there referring to the list controller. Now
	 * this method takes the active menu item's view name and tells what view name is responsible for the detail view.
	 * @param string[] $segments URL segments like we deal with in Joomla's parseRoute function
	 * @return string
	 */
	public function getViewNameFromUrlSegments($segments) {
		//TODO: See if the out-commented line below works right

		// return KenedoView::getViewNameFromClass(get_class($this->getDefaultView()));
		return self::getControllerNameFromClass(get_class($this));
	}

	/**
	 * Helper method for Joomla router function xParseRoute. Matches the given $segments with query parameter names
	 * @param string $activeViewName This is the name of the view we have in the active menu item
	 * @param string[] $segments URL segments like we deal with in Joomla's parseRoute function
	 * @return string[]
	 */
	public function getSegmentMatching($activeViewName, $segments) {
		return array();
	}

	/**
	 * @param string $activeViewName This is the name of the view we have in the active menu item
	 * @param string[] $segments URL segments like we deal with in Joomla's parseRoute function
	 * @return callable[]
	 */
	public function getSegmentParsing($activeViewName, $segments) {
		return array();
	}

	/**
	 * @deprecated use store() instead
	 */
	function save() {
		$this->store();
	}

	/**
	 * @deprecated use delete instead
	 */
	function remove() {
		$this->delete();
	}

	/**
	 * @deprecated use ajaxDelete instead
	 */
	function ajaxRemove() {
		$this->ajaxDelete();
	}

}
