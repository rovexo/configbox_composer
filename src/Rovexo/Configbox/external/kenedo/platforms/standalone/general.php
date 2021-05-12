<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPlatformStandalone implements InterfaceKenedoPlatform {
	
	protected $db;
	/**
	 * @var string[] $errors
	 */
	protected $errors;
	
	public function initialize() {
		// Set the option request var like for Joomla, as all is built around that
		if (KRequest::getVar('option') == '') {
			KRequest::setVar('option', 'com_configbox');
		}
	}

    function getOutputMode() {

        if (KRequest::getString('output_mode')) {
            $outputMode = KRequest::getString('output_mode');
            if (in_array($outputMode, ['view_only', 'in_html_doc', 'in_platform_output'])) {
                return $outputMode;
            }
        }

        if (KRequest::getInt('ajax_sub_view') || KRequest::getString('format') == 'raw' || KRequest::getString('format') == 'json') {
            return 'view_only';
        }

        if (KRequest::getInt('in_modal') == 1 || KRequest::getVar('tmpl') == 'component') {
            return 'in_html_doc';
        }

        return 'in_platform_output';

    }

	public function getDbConnectionData() {
				
		require_once(KPATH_ROOT.'/configuration.php');

		$platformConfig = new JConfig();
		
		$connection = new stdClass();
		$connection->hostname 	= $platformConfig->db;
		$connection->username 	= $platformConfig->user;
		$connection->password 	= $platformConfig->password;
		$connection->database 	= $platformConfig->db;
		$connection->hostname 	= $platformConfig->host;
		$connection->prefix 	= $platformConfig->dbprefix;
				
		return $connection;
	
	}
	
	public function &getDb() {
		if (!$this->db) {
			require_once(dirname(__FILE__).DS.'database.php');
			$this->db = new KenedoDatabaseStandalone();
		}
		
		return $this->db;
	}
	
	public function redirect($url, $httpCode = 303) {
		if ($httpCode == 303) {
			$statusString = 'See Other';
		}
		elseif ($httpCode == 301) {
			$statusString = 'Moved Permanently';
		}
		else {
			$statusString = '';
		}
		header('HTTP/1.1 '.$httpCode.' '. $statusString);
		header('Location: '.$url);
		die();
	}
	//TODO: Implement
	public function logout() {
		KSession::set('logged_in',false);
	}

	/**
	 * @inheritDoc
	 */
	public function getApplicationVersion() {
		//TODO: See about how a release number makes sense for standalone
		return '1.0.0';
	}

	//TODO: Implement
	public function authenticate($username, $passwordClear) {
		return true;
	}
	//TODO: Implement
	public function login($username) {
		KSession::set('logged_in',true);
	}

	//TODO: Implement
	public function sendSystemMessage($text, $type = NULL) {
		KSession::set('message',$text);
	}
	//TODO: Implement
	public function getVersionShort() {
		return 1.0;
	}
	//TODO: Implement
	public function getDebug() {
		return 0;
	}

	//TODO: Implement
	public function getConfigOffset() {
		return 'Europe/Vienna';
	}
	//TODO: Implement
	public function getMailerFromName() {
		return 'ConfigBox';
	}
	//TODO: Implement
	public function getMailerFromEmail() {
		return '';
	}
	//TODO: Implement
	public function getTmpPath() {
		return KPATH_ROOT.DS.'tmp';
	}
	//TODO: Implement
	public function getLogPath() {
		return KPATH_ROOT.DS.'logs';
	}
	//TODO: Implement
	public function getLanguageTag() {

		$map = array(
			'en'=>'en-GB',
			'de'=>'de-DE',
			'nl'=>'nl-NL',
			'da'=>'da-DK',
		);

		$fallBack = 'en-GB';

		$tag = KRequest::getString('lang');
		if (!$tag) {
			$tag = $fallBack;
		}
		elseif (strlen($tag) != '5') {
			$tag = (isset($map[$tag])) ? $map[$tag] : $fallBack;
		}

		return $tag;
	}
	
	//TODO: Check if good enough
	public function getLanguageUrlCode($languageTag = NULL) {
		return $this->getLanguageTag();
	}
	
	//TODO: Check if good enough
	public function getDocumentType() {
		return KRequest::getKeyword('format','html');
	}
	
	public function addScript($path, $type = "text/javascript", $defer = false, $async = false) {
		$GLOBALS['document']['scripts'][$path] = 1;
	}
	
	public function addScriptDeclaration($js, $newTag = false, $toBody = false) {
		$tag = '<script type="text/javascript">'."\n//<![CDATA[\n";
		$tag.= $js;
		$tag.= "\n//]]>\n".'</script>';
		$GLOBALS['document']['script_codes'][] = $tag;
	}
	
	public function addStylesheet($path, $type='text/css', $media = 'all') {
		$GLOBALS['document']['stylesheets'][$path] = $media;
	}
	
	public function addStyleDeclaration($css) {
		$css = '<style type="text/css">'.$css.'</style>';
		$GLOBALS['document']['styles'][] = $css;
	}
	
	public function isAdminArea() {
		return false;
	}
	
	public function isSiteArea() {
		return true;
	}
	
	public function autoload($className, $classPath) {
		include_once($classPath);
	}
	
	public function processContentModifiers($text) {	
		return $text;
	}
	
	public function triggerEvent($eventName, $data) {
		return array(true);
	}
	
	public function raiseError($errorCode, $errorMessage) {
		die($errorCode . ' - '.$errorMessage);
	}

    public function renderHtmlEditor($dataFieldKey, $content, $width, $height, $cols, $rows) {
        $style = 'width:'.$width.'; height:'.$height;
        return '<textarea name="'.hsc($dataFieldKey).'" class="kenedo-html-editor not-initialized" style="'.$style.'" rows="'.intval($rows).'" cols="'.intval($cols).'">'.hsc($content).'</textarea>';
    }
	//TODO: Implement
	public function sendEmail($from, $fromName, $recipient, $subject, $body, $isHtml = false, $cc = NULL, $bcc = NULL, $attachmentPath = NULL) {
		return true;
	}
	//TODO: Use
	public function getGeneratorTag() {
		return (isset($GLOBALS['document']['metatags']['generator'])) ? $GLOBALS['document']['metatags']['generator'] : '';
	}
	//TODO: Use
	public function setGeneratorTag($string) {
		$GLOBALS['document']['metatags']['generator'] = $string;
	}
	
	public function getUrlBase() {
		$uri = str_replace('components/com_configbox/configbox.php', '', $_SERVER['REQUEST_URI']);
		$response = KPATH_SCHEME.'://'.KPATH_HOST . $uri;
		$response = rtrim($response, '/');
		return $response;
	}

	public function getUrlBaseAssets() {

		$uri = str_replace('components/com_configbox/configbox.php', '', $_SERVER['REQUEST_URI']);

		$response = KPATH_SCHEME.'://'.KPATH_HOST . $uri;
		$response = rtrim($response, '/');
		return $response;

	}
	
	public function getDocumentBase() {
		return $this->getUrlBase();
	}
	//TODO: Implement
	public function setDocumentBase($string) {
		return true;
	}
	//TODO: Implement
	public function setDocumentMimeType($mime) {
		return $GLOBALS['document']['mimetype'] = $mime;
	}
	//TODO: Use
	public function getDocumentTitle() {
		return (isset($GLOBALS['document']['title'])) ? $GLOBALS['document']['title'] : '';
	}
	
	public function setDocumentTitle($string) {
		$GLOBALS['document']['title'] = $string;
	}
	//TODO: Use
	public function setMetaTag($tag,$content) {
		$GLOBALS['document']['metatags'][$tag] = $content;
	}
	
	public function isLoggedIn() {
		return KSession::get('logged_in',false);
	}
	//TODO: Implement
	public function getUserId() {
		return KSession::get('user_id',false);
	}
	//TODO: Implement
	public function getUserName($userId = NULL) {
		return KSession::get('user_name',false);
	}
	//TODO: Implement
	public function getUserFullName($userId = NULL) {
		return KSession::get('user_fullname',false);
	}
	public function getUserPasswordEncoded($userId = NULL) {
		return KSession::get('user_passwordencoded',false);
	}
	//TODO: Implement
	public function getUserIdByUsername($username) {
		return KSession::get('user_id',false);
	}

	//TODO: Implement
	public function getUserTimezoneName($userId = NULL) {
		return KSession::get('user_tz','Europe/Vienna');
	}
	//TODO: Implement
	public function registerUser($data, $groupIds = array()) {
		
		$userObject = new stdClass();
		
		$userObject->id 		= 99;
		$userObject->name 		= 'name';
		$userObject->username 	= 'username';
		$userObject->password 	= 'password';
		
		return $userObject;
		
	}
	
	protected function unsetErrors() {
		$this->errors = array();
	}
	
	protected function setError($error) {
		$this->errors[] = $error;
	}
	
	protected function setErrors($errors) {
		if (is_array($errors) && count($errors)) {
			$this->errors = array_merge((array)$this->errors,$errors);
		}
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function getError() {
		if (is_array($this->errors) && count($this->errors)) {
			return end($this->errors);
		}
		else {
			return '';
		}
	}
	//TODO: Implement
	public function isAuthorized($task,$userId = NULL, $minGroupId = NULL) {
		return false;
	}
	//TODO: Implement
	public function passwordsMatch($passwordClear, $passwordEncrypted) {
		return true;
	}
	
	public function passwordMeetsStandards($password) {
		if (mb_strlen($password) < 8) {
			return false;
		}
		if ( preg_match("/[0-9]/", $password) == 0 || preg_match("/[a-zA-Z]/", $password) == 0) {
			return false;
		}
	
		return true;
	}
	
	public function getPasswordStandardsText() {
		return KText::_('Your password should contain at least 8 characters and should contain numbers and letters.');
	}
	
	//TODO: Implement
	public function changeUserPassword($userId, $passwordClear) {
		return true;
	}
	
	//TODO: Implement
	public function getRootDirectory() {
		$path = realpath(dirname(__FILE__).'/../../../../../../');
		return $path;
	}
	//TODO: Implement
	public function getAppParameters() {
		$params = new KStorage();
		return $params;
	}
	
	public function renderOutput(&$output) {
		if ($this->getDocumentType() != 'html') {
			require(dirname(__FILE__).DS.'tmpl'.DS. 'raw.php');
		}
		else {
			require(dirname(__FILE__).DS.'tmpl'.DS. KRequest::getKeyword('tmpl','index').'.php');
		}
		
	}
	
	public function startSession() {
		session_start();
		return true;
	}
	
	//TODO: Implement
	public function getPasswordResetLink() {
		return '';
	}
	
	public function getPlatformLoginLink() {
		return '';
	}
	
	public function getRoute($url, $encode = true, $secure = NULL) {
		
		$option = KRequest::getKeyword('option');
		
		$parsed = parse_url($url);
		
		if (isset($parsed['query'])) {
			$query = array();
			parse_str($parsed['query'],$query);
			if (isset($query['option'])) {
				$option = $query['option'];
			}
				
		}
		
		if ($secure === NULL) {
			$scheme = KPATH_SCHEME;
		}
		elseif ($secure == true) {
			$scheme = 'http';
		}
		else {
			$scheme = 'https';
		}

		$url = str_replace('index.php', $scheme .'://'. KPATH_HOST . dirname($_SERVER['PHP_SELF']) .'/../../components/'.$option.'/'.str_ireplace('com_','',$option).'.php', $url);
		return $url;
		
	}
	
	public function getActiveMenuItemId() {
		return 0;
	}
	
	//TODO: Implement
	public function getLanguages() {

		$german = new stdClass();
		$german->tag = 'de-DE';
		$german->label = 'German';

		$english = new stdClass();
		$english->tag = 'en-GB';
		$english->label = 'English';

		return array('de-DE'=>$german, 'en-GB'=>$english);

	}
	
	//TODO: Implement
	public function platformUserEditFormIsReachable() {
		return false;
	}
	
	//TODO: Implement
	public function userCanEditPlatformUsers() {
		return false;
	}
	
	//TODO: Implement
	public function getPlatformUserEditUrl($platformUserId) {
		return '';
	}

	public function getComponentDir($componentName) {
		return $this->getRootDirectory().DS.'components'.DS.strtolower($componentName);
	}

	public function getDirAssets() {
		$path = $this->getComponentDir('com_configbox').DS.'assets';
		return $path;
	}

	public function getUrlAssets() {
		$path =  $this->getUrlBaseAssets().'/components/com_configbox/assets';
		return $path;
	}

	public function getDirCache() {
		// Not using JPATH_CACHE on purpose to avoid writing into the admin cache
		return $this->getRootDirectory().DS.'cache';
	}

	public function getDirCustomization() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customization';
		return $path;
	}

	public function getUrlCustomization() {
		$path = $this->getUrlBase().'/components/com_configbox/data/customization';
		return $path;
	}

	public function getDirCustomizationAssets() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customization'.DS.'assets';
		return $path;
	}

	public function getUrlCustomizationAssets() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/data/customization/assets';
		return $path;
	}

	public function getDirCustomizationSettings() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'store'.DS.'private'.DS.'settings';
		return $path;
	}

	public function getDirDataCustomer() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customer';
		return $path;
	}

	public function getUrlDataCustomer() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/data/customer';
		return $path;
	}

	public function getDirDataStore() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'store';
		return $path;
	}

	public function getUrlDataStore() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/data/store';
		return $path;
	}

	public function getTemplateOverridePath($component, $viewName, $templateName) {
		$path = '';
		return $path;
	}

	/**
	 * Should set the given error handler callable unless the app should not deal with custom error handling on this platform
	 * @param callable $errorHandler
	 * @see set_error_handler()
	 */
	public function setErrorHandler($errorHandler) {
		set_error_handler($errorHandler);
	}

	/**
	 * Should call restore_error_handler unless the app should not deal with custom error handling on this platform.
	 * @see restore_error_handler()
	 */
	public function restoreErrorHandler() {
		restore_error_handler();
	}

	/**
	 * @inheritDoc
	 */
	public function setExceptionHandler($callable) {
		set_exception_handler($callable);
	}

	/**
	 * @inheritDoc
	 */
	public function restoreExceptionHandler() {
		restore_exception_handler();
	}

	/**
	 * Should set the given shutdown function callable unless the app should not deal with custom error handling on
	 * this platform
	 * @param callable $callback
	 * @see register_shutdown_function()
	 */
	public function registerShutdownFunction($callback) {
		register_shutdown_function($callback);
	}

	/**
	 * @inheritDoc
	 */
	public function getCsrfTokenName() {
		//Note: Not being used on this platform
		return 'cb_form_key';
	}

	/**
	 * @inheritDoc
	 */
	public function getCsrfTokenValue() {
		//Note: Not being used on this platform
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function requestUsesHttps() {

		// Check what URI scheme we're dealing with
		if (substr(PHP_SAPI, 0, 3) == 'cli') {
			$scheme = '';
		}
		else {
			// Figure out if on http or https (praying for a definite and straight-forward way in future)
			if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
				$scheme = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
			}
			elseif(!empty($_SERVER['HTTPS'])) {
				$scheme = (strtolower($_SERVER['HTTPS']) !== 'off') ? 'https':'http';
			}
			else {
				$scheme = ($_SERVER['SERVER_PORT'] == 443) ? 'https':'http';
			}
		}

		return ($scheme === 'https');
	}

	/**
	 * @return string customization dir before CB 3.3.0
	 */
	public function getOldDirCustomization() {
		return $this->getDirCustomization();
	}

	/**
	 * @return string customization assets dir before CB 3.3.0
	 */
	public function getOldDirCustomizationAssets() {
		return $this->getDirCustomizationAssets();
	}

}