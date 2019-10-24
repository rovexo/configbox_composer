<?php
interface InterfaceKenedoPlatform {

	public function &getDb();
	public function getDbConnectionData();
	public function initialize();
	public function redirect($url, $httpCode = 303);
	public function logout();
	public function authenticate($username, $passwordClear);
	public function login($username);
	public function sendSystemMessage($text, $type = NULL);
	public function getVersionShort();
	public function getDebug();
	public function getConfigOffset();
	public function getMailerFromName();
	public function getMailerFromEmail();
	public function getTmpPath();
	public function getLogPath();
	public function getLanguageTag();
	public function getLanguageUrlCode($languageTag = NULL);
	public function getDocumentType();
	public function addScript($path, $type = "text/javascript", $defer = false, $async = false);
	public function addScriptDeclaration($js, $newTag = false, $toBody = false);
	public function addStylesheet($path, $type="text/css", $media = 'all');
	public function addStyleDeclaration($css);
	public function isAdminArea();
	public function isSiteArea();
	public function autoload($className, $classPath);
	public function processContentModifiers($text);
	public function triggerEvent($eventName, $data);
	public function raiseError($errorCode, $errorMessage);
	public function renderHtmlEditor($dataFieldKey, $content, $width, $height, $cols, $rows);
	public function sendEmail($from, $fromName, $recipient, $subject, $body, $isHtml = false, $cc = NULL, $bcc = NULL, $attachmentPath = NULL);
	public function getGeneratorTag();
	public function setGeneratorTag($string);
	public function getUrlBase();
	public function getUrlBaseAssets();
	public function getDocumentBase();
	public function setDocumentBase($string);
	public function setDocumentMimeType($mime);
	public function getDocumentTitle();
	public function setDocumentTitle($string);
	public function setMetaTag($tag,$content);
	public function isLoggedIn();
	public function getUserId();
	public function getUserName($userId = NULL);
	public function getUserFullName($userId = NULL);
	public function getUserPasswordEncoded($userId = NULL);
	public function getUserIdByUsername($username);
	public function getUserTimezoneName($userId = NULL);
	public function registerUser($data,$groupIds = array());
	public function isAuthorized($task,$userId = NULL, $minGroupId = NULL);
	public function changeUserPassword($userId, $passwordClear);
	public function passwordsMatch($passwordClear, $passwordEncrypted);
	public function getRootDirectory();
	public function getAppParameters();
	public function renderOutput(&$output);
	public function startSession();
	public function getPasswordResetLink();
	public function getPlatformLoginLink();
	public function getRoute($url, $encode = true, $secure = NULL);
	public function getActiveMenuItemId();

	/**
	 * @return object[]
	 */
	public function getLanguages();

	/**
	 * @return bool Indicates if it's technically possible to edit other users (e.g. is it possible to load a page with a user edit form)
	 */
	public function platformUserEditFormIsReachable();

	/**
	 * @return bool Indicates if the current user can edit other platform users
	 */
	public function userCanEditPlatformUsers();

	/**
	 * @param int $platformUserId User ID of the platform
	 *
	 * @return string Full URL to the user edit page
	 */
	public function getPlatformUserEditUrl($platformUserId);

	/**
	 * @param string $componentName (e.g. com_configbox)
	 *
	 * @return string Full filesystem path to the components base folder
	 */
	public function getComponentDir($componentName);

	/**
	 * @return string Full URL to the extensions assets folder (w/o trailing slash)
	 */
	public function getUrlAssets();

	/**
	 * @return string Full Filesystem path to the extensions assets folder (w/o trailing slash)
	 */
	public function getDirAssets();

	/**
	 * @return string Full filesystem path to the cache folder
	 */
	public function getDirCache();

	/**
	 * @return string Full filesystem path to the customization folder
	 */
	public function getDirCustomization();

	/**
	 * @return string Complete URL to the customizatoin folder
	 */
	public function getUrlCustomization();

	/**
	 * @return string Full filesystem path to the customization settings folder
	 */
	public function getDirCustomizationSettings();

	/**
	 * @return string Full filesystem path to customization assets folder (w/o trailing slash)
	 */
	public function getDirCustomizationAssets();

	/**
	 * @return string Complete URL to the customization assets folder (w/o trailing slash)
	 */
	public function getUrlCustomizationAssets();

	/**
	 * @return string Full filesystem path to the customer data folder (w/o trailing slash)
	 */
	public function getDirDataCustomer();

	/**
	 * @return string Complete URL (scheme to request URI) to the customer data folder (w/o trailing slash)
	 */
	public function getUrlDataCustomer();

	/**
	 * @return string Full filesystem path to the store data folder (w/o trailing slash)
	 */
	public function getDirDataStore();

	/**
	 * @return string Complete URL to the store data folder (w/o trailing slash)
	 */
	public function getUrlDataStore();

	/**
	 * @param string $component
	 * @param string $viewName
	 * @param string $templateName Name of the view's template, not the platform's main template
	 * @return string $path Absolute path to the file or empty the platform does not support template overrides
	 */
	public function getTemplateOverridePath($component, $viewName, $templateName);

	/**
	 * Should set the given error handler callable unless the app should not deal with custom error handling on this platform
	 * @param callable $errorHandler
	 * @see set_error_handler()
	 */
	public function setErrorHandler($errorHandler);

	/**
	 * Should call restore_error_handler unless the app should not deal with custom error handling on this platform.
	 * @see restore_error_handler()
	 */
	public function restoreErrorHandler();

	/**
	 * Should set the given shutdown function callable unless the app should not deal with custom error handling on
	 * this platform
	 * @param callable $callback
	 * @see register_shutdown_function()
	 */
	public function registerShutdownFunction($callback);

}