<?php


class KenedoPlatformMagento implements InterfaceKenedoPlatform {

	protected $db;

	/**
	 * @var string[] $errors
	 */
	protected $errors;

	public $scriptDeclarations = array();

	/**
	 * @var string[]
	 * @see renderHeadScriptDeclarations, addScriptDeclaration
	 */
	public $headScriptDeclarations = array();

	/**
	 * @var string[]
	 * @see renderBodyScriptDeclarations, addScriptDeclaration
	 */
	public $bodyScriptDeclarations = array();

	/**
	 * @var string[]
	 */
	protected $stylesheetUrls = array();

	/**
	 * @var string[]
	 */
	protected $inlineStyles = array();

	/**
	 * @var string[][] array of arrays with keys 'url', 'type', 'defer' and 'async'
	 */
	protected $scriptAssets = array();

	public function initialize() {

		// Set the option request var like for Joomla, as all is built around that
		if (KRequest::getVar('option') == '') {
			KRequest::setVar('option', 'com_configbox');
		}

	}

    /**
     * Tells how to respond to an HTTP request
     * - 'view_only': Output only the content of the requested view (with nothing wrapping the output)
     * - 'in_html_doc': Output the view's content within a HTML doc (containing nothing but the view content and head data)
     * - 'in_platform_output': Output the view within the platform's output (as in along with the Joomla/WP/M1/M2 page)
     * @return string (view_only, in_html_doc, in_platform_output)
     */
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

		$config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");

		$connection = new stdClass();

		/** @noinspection PhpUndefinedFieldInspection */
		$connection->hostname 	= $config->host;
		/** @noinspection PhpUndefinedFieldInspection */
		$connection->username 	= $config->username;
		/** @noinspection PhpUndefinedFieldInspection */
		$connection->password 	= $config->password;
		/** @noinspection PhpUndefinedFieldInspection */
		$connection->database 	= $config->dbname;
		$connection->prefix 	= Mage::getConfig()->getTablePrefix();

		return $connection;

	}

	public function &getDb() {
		if (!$this->db) {
			require_once(dirname(__FILE__).DS.'database.php');
			$this->db = new KenedoDatabaseMagento();
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
		Mage::getSingleton('customer/session')->logout();
	}

	/**
	 * @inheritDoc
	 */
	public function getApplicationVersion() {
		return Mage::getConfig()->getNode('modules/Elovaris_Configbox/version')->__toString();
	}

	//TODO: Implement
	public function authenticate($username, $passwordClear) {
		return true;
	}

	//TODO: Implement
	public function login($username) {

	}

	public function sendSystemMessage($text, $type = NULL) {

		switch ($type) {
			case 'error':
				Mage::getSingleton('core/session')->addError($text);
				break;
			case 'success':
				Mage::getSingleton('core/session')->addSuccess($text);
				break;
			default:
				Mage::getSingleton('core/session')->addNotice($text);
				break;
		}

	}

	public function getVersionShort() {
		return Mage::getVersion();
	}

	public function getDebug() {
		return intval(Mage::getIsDeveloperMode());
	}

	public function getConfigOffset() {
		return Mage::app()->getStore()->getConfig('general/locale/timezone');
	}

	public function getMailerFromName() {
		return Mage::getStoreConfig('trans_email/ident_general/name');
	}

	public function getMailerFromEmail() {
		return Mage::getStoreConfig('trans_email/ident_general/email');
	}

	public function getTmpPath() {
		return Mage::getBaseDir('tmp');
	}

	public function getLogPath() {
		return Mage::getBaseDir('log');
	}

	public function getLanguageTag() {
		$code = Mage::app()->getLocale()->getLocaleCode();
		$code = str_replace('_','-',$code);
		return $code;
	}

	public function getLanguageUrlCode($languageTag = NULL) {

		if ($languageTag == NULL) {
			$languageTag = $this->getLanguageTag();
		}

		$languages = $this->getLanguages();

		if (!empty($languages[$languageTag])) {
			return $languages[$languageTag]->urlCode;
		}
		else {
			return NULL;
		}

	}

	//TODO: Check if good enough
	public function getDocumentType() {
		return KRequest::getKeyword('format','html');
	}

	public function addScript($path, $type = "text/javascript", $defer = false, $async = false) {
		$GLOBALS['document']['scripts'][$path] = $path;

		$this->scriptAssets[$path] = array(
			'url' => $path,
			'type' => $type,
			'defer' => $defer,
			'async' => $async,
		);

	}

	public function addScriptDeclaration($js, $newTag = false, $toBody = false) {

		if ($toBody) {
			$array =& $this->bodyScriptDeclarations;
		}
		else {
			$array =& $this->headScriptDeclarations;
		}

		if ($newTag) {
			$array[] = $js;
		}
		else {
			if (count($array) == 0) {
				$array[] = $js;
			}
			else {
				end($array);
				$key = key($array);
				$array[$key] .= "\n".$js;
			}
		}

	}

	public function addStylesheet($path, $type='text/css', $media = 'all') {

		if (in_array($path, $this->stylesheetUrls) == false) {

			$this->stylesheetUrls[] = $path;
			$GLOBALS['document']['stylesheets'][$path] = $path;
		}

	}

	public function addStyleDeclaration($css) {
		$css = '<style type="text/css">'.$css.'</style>';
		$GLOBALS['document']['styles'][] = $css;

		$this->inlineStyles[] = $css;
	}

	public function isAdminArea() {

		if(Mage::getDesign()->getArea() == 'adminhtml') {
			return true;
		}

		return Mage::app()->getStore()->isAdmin();

	}

	public function isSiteArea() {
		return Mage::app()->getStore()->isAdmin() == false;
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
		return rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/');
	}

	public function getUrlBaseAssets() {
		return rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/');
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
	//TODO: Implement
	public function getDocumentTitle() {
		return '';
		//Mage::app()->getLayout()->getBlock('head')->getTitle();
	}

	//TODO: Implement
	public function setDocumentTitle($string) {
		//Mage::app()->getLayout()->getBlock('head')->setTitle($string);
	}
	//TODO: Use
	public function setMetaTag($tag,$content) {
		$GLOBALS['document']['metatags'][$tag] = $content;
	}

	public function isLoggedIn() {
		return KSession::get('logged_in',false);
	}
	//TODO: Test
	public function getUserId() {
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			return $customerData->getId();
		}
		else {
			return 0;
		}
	}

	//TODO: Test
	public function getUserName($userId = NULL) {

		if ($userId == NULL) {
			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
				$user = Mage::getSingleton('admin/session');
				/** @noinspection PhpUndefinedMethodInspection */
				$username = $user->getUser()->getUsername();
			}
			else {
				$username = NULL;
			}
		}
		else {
			/** @noinspection PhpUndefinedMethodInspection */
			$customerData = Mage::getModel('customer/customer')->load($userId)->getData();
			$username = $customerData['email'];
		}

		return $username;
	}

	//TODO: Test
	public function getUserFullName($userId = NULL) {

		if ($userId == NULL) {
			$userId = $this->getUserId();
		}

		if ($userId) {
			/** @noinspection PhpUndefinedMethodInspection */
			$customerData = Mage::getModel('customer/customer')->load($userId)->getData();
			$name = $customerData['firstname'].  ' '. $customerData['lastname'];
		}
		else {
			$name = '';
		}

		return $name;

	}
	public function getUserPasswordEncoded($userId = NULL) {
		return KSession::get('user_passwordencoded',false);
	}
	//TODO: Test
	public function getUserIdByUsername($username) {

		$customer = Mage::getModel('customer/customer');
		/** @noinspection PhpUndefinedMethodInspection */
		$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
		$customer->loadByEmail($username);

		try{
			if ($customer->getId()) {
				return $customer->getId();
			}
		}
		catch(Exception $e) {
			return NULL;
		}
		return NULL;
	}

	//TODO: Test
	public function getUserTimezoneName($userId = NULL) {
		return $this->getConfigOffset();
	}
	//TODO: Test
	public function registerUser($data, $groupIds = array()) {

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

	public function isAuthorized($task,$userId = NULL, $minGroupId = NULL) {

		/** @noinspection PhpUndefinedMethodInspection */
		$admin = Mage::getModel('admin/session')->getUser();

		/** @noinspection PhpUndefinedMethodInspection */
		if(!$admin || $admin->getId() == '') {
			return false;
		}
		else {
			return true;
		}

	}
	//TODO: Test
	public function passwordsMatch($passwordClear, $passwordEncrypted) {

		$hash = Mage::helper('core')->getHash($passwordClear, 2);

		return Mage::helper('core')->validateHash($passwordEncrypted, $hash);

	}

	//TODO: Check actual standards
	public function passwordMeetsStandards($password) {
		if (mb_strlen($password) < 8) {
			return false;
		}
		if ( preg_match("/[0-9]/", $password) == 0 || preg_match("/[a-zA-Z]/", $password) == 0) {
			return false;
		}

		return true;
	}

	//TODO: Check actual standards
	public function getPasswordStandardsText() {
		return KText::_('Your password should contain at least 8 characters and should contain numbers and letters.');
	}

	//TODO: Implement
	public function changeUserPassword($userId, $passwordClear) {
		return true;
	}

	//TODO: Test
	public function getRootDirectory() {
		return Mage::getBaseDir();
	}
	//TODO: Implement
	public function getAppParameters() {
		$params = new KStorage();
		return $params;
	}

	public function renderOutput(&$output) {

        $outputMode = $this->getOutputMode();

        if ($outputMode == 'view_only') {
            require(__DIR__.'/tmpl/raw.php');
            return;
        }
        elseif ($outputMode == 'in_html_doc') {
            require(__DIR__.'/tmpl/component.php');
            return;
        }
        else {
            require(__DIR__.'/tmpl/raw.php');
            return;
        }

	}


	public function renderStyleSheetLinks() {

		foreach ($this->stylesheetUrls as $url) {
			?>
			<link href="<?php echo hsc($url);?>" rel="stylesheet" />
			<?php
		}
	}

	public function renderStyleDeclarations() {

		foreach ($this->inlineStyles as $css) {
			?>
			<style type="text/css">
				<?php echo $css;?>
			</style>
			<?php
		}

	}

	public function renderScriptAssets() {
		foreach ($this->scriptAssets as $asset) { ?>
			<script type="<?php echo hsc($asset['type']);?>" async="<?php echo ($asset['async']) ? 'true':'false';?>" defer="<?php echo ($asset['defer']) ? 'true':'false';?>"></script>
		<?php }
	}


	public function renderHeadScriptDeclarations() {

		foreach ($this->headScriptDeclarations as $js) {
			?>
			<script type="text/javascript">
				<?php echo $js;?>
			</script>
			<?php

		}

	}

	public function renderBodyScriptDeclarations() {
		$output = '';
		foreach ($this->bodyScriptDeclarations as $js) {
			$output .= '<script type="text/javascript">'."\n";
			$output .= $js."\n";
			$output .= '</script>';
		}
		echo $output;
	}

	public function startSession() {
		session_start();
		return true;
	}

	//TODO: Implement
	public function getPasswordResetLink() {
		return Mage::getUrl('*/*/forgotpassword');
	}

	public function getPlatformLoginLink() {
		return Mage::helper('customer')->getLoginUrl();
	}

	public function getRoute($url, $encode = true, $secure = NULL) {

		if (strpos($url,'http') === 0) {
			return $url;
		}

		$parsed = parse_url($url);

		if (isset($parsed['query'])) {
			$params = array();
			parse_str($parsed['query'],$params);
		}

		$params['form_key'] = $this->getCsrfTokenValue();;

		if ($secure === NULL) {
			$params['_forced_secure'] = Mage::app()->getRequest()->isSecure();
		}
		else {
			$params['_forced_secure'] = $secure;
		}

		if ($this->isAdminArea()) {
			$url = Mage::helper('adminhtml')->getUrl('*/*/index', $params);
		}
		else {
			if (KRequest::getVar('key')) {
				$params['key'] = KRequest::getString('key');
			}
			$url = Mage::getUrl('configbox/index/index',$params);
		}

		return $url;

	}

	public function getActiveMenuItemId() {
		return 0;
	}

	public function getLanguages() {

		$languages = Mage::app()->getLocale()->getOptionLocales();

		$return = array();
		foreach ($languages as $language) {
			$tag = str_replace('_', '-', $language['value']);
			$label = $language['label'];
			$return[$tag] = new KenedoObject(array('tag'=>$tag, 'label'=>$label, 'urlCode'=>$tag));
		}
		return $return;

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
		return Mage::getBaseDir('lib').'/Rovexo/Configbox/';
	}

	public function getUrlAssets() {
		$path = rtrim(Mage::getBaseUrl('skin'), '/').'/frontend/base/default/css/elovaris/configbox/assets';
		return $path;
	}

	public function getDirAssets() {
		$path = rtrim(Mage::getBaseDir('skin'), DS).DS.'frontend'.DS.'base'.DS.'default'.DS.'css'.DS.'elovaris'.DS.'configbox'.DS.'assets';
		return $path;
	}

	public function getDirCache() {
		$path = Mage::getBaseDir('cache');
		return $path;
	}

	public function getDirCustomization() {
		$path = rtrim(Mage::getBaseDir('media'), DS).DS.'elovaris'.DS.'configbox'.DS.'customization';
		return $path;
	}

	public function getUrlCustomization() {
		$path = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '/').'/elovaris/configbox/customization';
		return $path;
	}

	public function getDirCustomizationAssets() {
		$path = trim(Mage::getBaseDir('media'), DS).DS.'elovaris'.DS.'configbox'.DS.'customization'.DS.'assets';
		return $path;
	}

	public function getUrlCustomizationAssets() {
		$path = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '/').'/elovaris/configbox/customization/assets';
		return $path;
	}

	public function getDirCustomizationSettings() {
		$path = rtrim(Mage::getBaseDir('media'), DS).DS.'elovaris'.DS.'configbox'.DS.'data'.DS.'store'.DS.'private'.DS.'settings';
		return $path;
	}

	public function getDirDataCustomer() {
		$path = rtrim(Mage::getBaseDir('media'), DS).DS.'elovaris'.DS.'configbox'.DS.'data'.DS.'customer';
		return $path;
	}

	public function getUrlDataCustomer() {
		$path = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '/') .'/elovaris/configbox/data/customer';
		return $path;
	}

	public function getDirDataStore() {
		$path = rtrim(Mage::getBaseDir('media'), DS).DS.'elovaris'.DS.'configbox'.DS.'data'.DS.'store';
		return $path;
	}

	public function getUrlDataStore() {
		$path = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA), '/') .'/elovaris/configbox/data/store';
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

	}

	/**
	 * @inheritDoc
	 */
	public function restoreErrorHandler() {

	}

	/**
	 * @inheritDoc
	 */
	public function setExceptionHandler($callable) {

	}

	/**
	 * @inheritDoc
	 */
	public function restoreExceptionHandler() {

	}

	/**
	 * Should set the given shutdown function callable unless the app should not deal with custom error handling on
	 * this platform
	 * @param callable $callback
	 * @see register_shutdown_function()
	 */
	public function registerShutdownFunction($callback) {

	}

	/**
	 * @inheritDoc
	 */
	public function getCsrfTokenName() {
		return 'form_key';
	}

	/**
	 * @inheritDoc
	 */
	public function getCsrfTokenValue() {
		return Mage::getSingleton('core/session')->getFormKey();
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
