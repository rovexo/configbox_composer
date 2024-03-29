<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPlatformJoomla implements InterfaceKenedoPlatform {

	protected $db;
	protected $languages = NULL;
	protected $cache;
	protected $errors;
	public $scriptDeclarations = array();
	public $styleSheetUrls = array();

	public function initialize() {

		// Joomla's direct execution safeguard
		if (!defined('_JEXEC')) {
			define('_JEXEC',1);
		}

		// Set the document base in frontend (since many templates neglect to do so)
		if ($this->isAdminArea() == false) {
			// Set the base URL
			$this->setDocumentBase( $this->getUrlBase().'/' );
		}

		// At some Joomla 3.0 builds SEF URLs with suffix .raw when you got format=raw in query string
		// but does set format=raw on parsing the SEF URL - working around it here
		if (!empty($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], -4) == '.raw') {
			KRequest::setVar('format', 'raw');
		}

		$this->setGeneratorTag($this->getGeneratorTag().' and ConfigBox (https://www.configbox.at)');

		$this->do2Point6LegacyStuff();
		$this->defineLegacyConstants();

	}

	private function defineLegacyConstants() {

		define('CONFIGBOX_DIR_CACHE',						KenedoPlatform::p()->getDirCache().'/configbox');
		define('CONFIGBOX_DIR_SETTINGS',					KenedoPlatform::p()->getDirCustomizationSettings());
		define('CONFIGBOX_DIR_MODEL_PROPERTY_CUSTOMIZATION',KenedoPlatform::p()->getDirCustomization().'/model_property_customization');
		define('CONFIGBOX_URL_CONFIGURATOR_FILEUPLOADS',	KenedoPlatform::p()->getUrlDataCustomer().'/public/file_uploads' );
		define('CONFIGBOX_URL_POSITION_IMAGES',				KenedoPlatform::p()->getUrlDataCustomer().'/public/position_images' );

		define('CONFIGBOX_DIR_PRODUCT_IMAGES',				KenedoPlatform::p()->getDirDataStore().'/public/product_images');
		define('CONFIGBOX_DIR_PRODUCT_DETAIL_PANE_ICONS',	KenedoPlatform::p()->getDirDataStore().'/public/product_detail_pane_icons');
		define('CONFIGBOX_DIR_VIS_PRODUCT_BASE_IMAGES',		KenedoPlatform::p()->getDirDataStore().'/public/vis_product_images');
		define('CONFIGBOX_DIR_VIS_ANSWER_IMAGES', 			KenedoPlatform::p()->getDirDataStore().'/public/vis_answer_images');
		define('CONFIGBOX_DIR_DEFAULT_IMAGES',				KenedoPlatform::p()->getDirDataStore().'/public/default_images');
		define('CONFIGBOX_DIR_QUESTION_DECORATIONS',		KenedoPlatform::p()->getDirDataStore().'/public/question_decorations');
		define('CONFIGBOX_DIR_ANSWER_IMAGES',				KenedoPlatform::p()->getDirDataStore().'/public/answer_images');
		define('CONFIGBOX_DIR_ANSWER_PICKER_IMAGES',		KenedoPlatform::p()->getDirDataStore().'/public/answer_picker_images');
		define('CONFIGBOX_DIR_SHOP_LOGOS',					KenedoPlatform::p()->getDirDataStore().'/public/shoplogos');
		define('CONFIGBOX_DIR_MAXMIND_DBS',					KenedoPlatform::p()->getDirDataStore().'/private/maxmind');

		define('CONFIGBOX_URL_PRODUCT_IMAGES',				KenedoPlatform::p()->getUrlDataStore().'/public/product_images');
		define('CONFIGBOX_URL_PRODUCT_GALLERY_IMAGES',		KenedoPlatform::p()->getUrlDataStore().'/public/product_gallery_images');
		define('CONFIGBOX_URL_PRODUCT_DETAIL_PANE_ICONS',	KenedoPlatform::p()->getUrlDataStore().'/public/product_detail_pane_icons');
		define('CONFIGBOX_URL_VIS_PRODUCT_BASE_IMAGES',		KenedoPlatform::p()->getUrlDataStore().'/public/vis_product_images');
		define('CONFIGBOX_URL_VIS_ANSWER_IMAGES', 			KenedoPlatform::p()->getUrlDataStore().'/public/vis_answer_images');
		define('CONFIGBOX_URL_DEFAULT_IMAGES',				KenedoPlatform::p()->getUrlDataStore().'/public/default_images');
		define('CONFIGBOX_URL_QUESTION_DECORATIONS',		KenedoPlatform::p()->getUrlDataStore().'/public/question_decorations');
		define('CONFIGBOX_URL_ANSWER_IMAGES',				KenedoPlatform::p()->getUrlDataStore().'/public/answer_images');
		define('CONFIGBOX_URL_ANSWER_PICKER_IMAGES',		KenedoPlatform::p()->getUrlDataStore().'/public/answer_picker_images');
		define('CONFIGBOX_URL_SHOP_LOGOS',					KenedoPlatform::p()->getUrlDataStore().'/public/shoplogos');
		define('CONFIGBOX_URL_MAXMIND_DBS',					KenedoPlatform::p()->getUrlDataStore().'/private/maxmind');

		define('CONFIGBOX_DIR_QUOTATIONS',					KenedoPlatform::p()->getDirDataCustomer().'/private/quotations' );

		// CUSTOMER DATA
		define('CONFIGBOX_DIR_INVOICES',					KenedoPlatform::p()->getDirDataCustomer().'/private/invoices' );
		define('CONFIGBOX_DIR_CONFIGURATOR_FILEUPLOADS',	KenedoPlatform::p()->getDirDataCustomer().'/public/file_uploads' );
		define('CONFIGBOX_DIR_POSITION_IMAGES',				KenedoPlatform::p()->getDirDataCustomer().'/public/position_images' );



		// Define paths
		/**
		 * URL scheme (without colons or backslashes)
		 * E.g. https
		 * @const  KPATH_ROOT
		 */
		define('KPATH_SCHEME', 	KenedoPlatform::p()->requestUsesHttps() ? 'https' : 'http');

		/**
		 * HTTP Hostname
		 * E.g. configbox.dev
		 * @const  KPATH_HOST
		 */
		define('KPATH_HOST', 	(substr(PHP_SAPI, 0, 3) == 'cli') ? '' : $_SERVER['HTTP_HOST']);

		/**
		 * Platform base URL (scheme://host/path) - without a trailing slash
		 * @const  KPATH_URL_BASE
		 */
		define('KPATH_URL_BASE', KenedoPlatform::p()->getUrlBase());

		/**
		 * Full path to the platform's root directory (not the web server's root)
		 * @const  KPATH_ROOT
		 */
		define('KPATH_ROOT', KenedoPlatform::p()->getRootDirectory());

		/**
		 * Full path to CB's lib directory
		 * @const KPATH_DIR_CB
		 */
		define('KPATH_DIR_CB', KenedoPlatform::p()->getComponentDir('com_configbox') );


	}

    /**
     * @inheritDoc
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

	/**
	 * Checks for REQUEST keys that have changed between 2.6 and 3.0 and tries to 'translate'
	 */
	protected function do2Point6LegacyStuff() {


		/* LEGACY VIEW NAMES - START */
		if (KRequest::getKeyword('option') == 'com_configbox') {

			if (KRequest::getVar('view') == 'category') {
				KRequest::setVar('view', 'configuratorpage');
			}

			if (KRequest::getVar('view') == 'products') {
				KRequest::setVar('view', 'productlisting');
			}

			if (KRequest::getVar('view') == 'grandorder') {
				KRequest::setVar('view', 'cart');
			}

		}

		/* LEGACY VIEW NAMES - END */

		// Legacy, remove with CB 4.0

		if (KRequest::getInt('grandorder_id')) {
			KRequest::setVar('cart_id', KRequest::getInt('grandorder_id'));
		}

		if (KRequest::getInt('from_grandorder')) {
			KRequest::setVar('from_cart', KRequest::getInt('from_grandorder'));
		}

		// Legacy, remove with CB 4.0

		if (KRequest::getKeyword('option') == 'com_configbox') {

			if (KRequest::getInt('cat_id') != 0 && KRequest::getVar('page_id') == NULL) {
				$ref = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
				$log = 'A link with an outdated URL for configurator pages was found.';
				if ($ref) $log .= ' The link was found on page "'.$ref.'". Most likely an article, module or custom Configbox template. cat_id should be replaced by page_id. The link you see on the page may be processed. Check the source of the link, where you will see the URL parameters.';
				$log .= ' We keep supporting the old link until version 2.7 only, please change the link as soon as you can.';
				KLog::log($log,'deprecated');
				KRequest::setVar('page_id', KRequest::getVar('cat_id'));
			}

			if (KRequest::getInt('pcat_id') != 0 && KRequest::getVar('listing_id') == NULL) {
				$ref = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
				$log = 'A link with an outdated URL for product listing pages was found.';
				if ($ref) $log .= ' The link was found on page "'.$ref.'". Most likely an article, module or custom Configbox template. pcat_id should be replaced by listing_id. The link you see on the page may be processed. Check the source of the link, where you will see the URL parameters.';
				$log .= ' We keep supporting the old link until version 2.7 only, please change the link as soon as you can.';
				KLog::log($log,'deprecated');
				KRequest::setVar('listing_id', KRequest::getVar('listing_id'));
			}
		}


		// Legacy, remove with CB 4.0
		if (KRequest::getKeyword('option') == 'com_cbcheckout') {
			$ref = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
			$log = 'A link with an outdated URL for product a order management page was found.';
			if ($ref) $log .= ' The link was found on page "'.$ref.'". Most likely an article, module or custom Configbox template. The value parameter option should be replaced by com_cbcheckout (option=com_cbcheckout is now option=com_configbox). The link you see on the page may be processed. Check the source of the link, where you will see the URL parameters.';
			$log .= ' We keep supporting the old link until version 2.7 only, please change the link as soon as you can.';
			KLog::log($log,'deprecated');
			KRequest::setVar('option', 'com_configbox');
		}

	}

	public function getDbConnectionData() {

		$connection = new stdClass();

		$connection->hostname   = $this->getJoomlaConfig('host');
		$connection->username   = $this->getJoomlaConfig('user');
		$connection->password   = $this->getJoomlaConfig('password');
		$connection->database   = $this->getJoomlaConfig('db');
		$connection->prefix     = $this->getJoomlaConfig('dbprefix');

		return $connection;

	}

	public function &getDb() {

		if (!$this->db) {
			require_once(__DIR__.'/database.php');
			$this->db = new KenedoDatabaseJoomla();
		}

		return $this->db;
	}

	public function redirect($url, $httpCode = 303) {

		// Normalize the URL
		$url = str_replace('&amp;','&',$url);

		// Joomla 2.5 has a different function signature for 301 redirects, we do it the best we can here
		if ($this->getVersionShort() < 3) {
			if ($httpCode == 301) {
				$this->getJApplication()->redirect($url, '', true);
			}
			else {
				$this->getJApplication()->redirect($url, '', false);
			}
			return;

		}

		// Joomla 3 is all good
		$this->getJApplication()->redirect($url, $httpCode);

	}

	public function logout() {
		$this->getJApplication()->logout();
	}

	/**
	 * @inheritDoc
	 */
	public function getApplicationVersion() {
		$path = JPATH_ADMINISTRATOR.'/components/com_configbox/configbox.xml';
		$manifest = simplexml_load_file($path);
		$version = $manifest->version->__toString();
		return $version;
	}

	/**
	 * @inheritDoc
	 */
	public function authenticate($username, $password, $secretKey = '') {

		$credentials = array(
			'username'=>$username,
			'password'=>$password,
			'secretkey'=>$secretKey,
		);

		$options = array();

		jimport('joomla.user.authentication');

		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($credentials, $options);

		if ($response->status === JAuthentication::STATUS_SUCCESS) {
			return true;
		}
		else {
			$this->getJApplication()->triggerEvent('onUserLoginFailure', array((array)$response));
			return false;
		}

	}

	public function login($username) {

		JPluginHelper::importPlugin('user');

		$platformUserId = $this->getUserIdByUsername($username);

		if (!$platformUserId) {
			KLog::log('Platform login for username "'.$username.'" requested, but that user does not exist.');
			$this->setError('Cannot login user. User with username "'.$username.'" was not found.');
			return false;
		}

		$fakeAuthResponse = array(
			'username' => $username,
			'language' => $this->getLanguageTag(),
			'fullname' => $this->getUserFullName($platformUserId),
		);

		$options = array(
			'action' => 'core.login.site',
		);

		if ($this->getVersionShort() == '1.5') {
			$results = $this->getJApplication()->triggerEvent('onLoginUser', array($fakeAuthResponse, $options));
		}
		else {
			$results = $this->getJApplication()->triggerEvent('onUserLogin', array($fakeAuthResponse, $options));
		}

		if (in_array(true, $results)) {
			return true;
		}
		else {
			return false;
		}

	}

	public function getTemplateName() {
		return $this->getJApplication()->getTemplate();
	}

	public function sendSystemMessage($text, $type = NULL) {
		$this->getJApplication()->enqueueMessage($text, $type);
	}

	public function getVersionShort() {
		jimport('joomla.version');
		$version = new JVersion();
		return substr($version->getShortVersion(),0,3);
	}

	public function getDebug() {
		return $this->getJoomlaConfig('debug');
	}

	public function getConfigOffset() {
		return $this->getJoomlaConfig('offset');
	}

	public function getMailerFromName() {
		return $this->getJoomlaConfig('fromname');
	}

	public function getMailerFromEmail() {
		return $this->getJoomlaConfig('mailfrom');
	}

	public function getTmpPath() {
		return $this->getJoomlaConfig('tmp_path');
	}

	public function getLogPath() {
		return $this->getJoomlaConfig('log_path');
	}

	public function getLanguageTag() {
		return $this->getJLanguage()->get('tag');
	}

	/**
	 * @param string $key Joomla Config key
	 *
	 * @return string|null Value from $key in Joomla Config
	 */
	protected function getJoomlaConfig($key) {

		$app = $this->getJApplication();

		// For Joomla version lower than 2.5
		if (method_exists($app, 'getCfg')) {
			/** @noinspection PhpDeprecationInspection */
			return $app->getCfg($key);
		}
		else {
			return $this->getJConfig()->get($key);
		}

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

	public function getDocumentType() {
		return $this->getJDocument()->getType();
	}

	public function addScript($path, $type = "text/javascript", $defer = false, $async = false) {
		if ($this->getDocumentType() == 'html') {
			/** @noinspection PhpDeprecationInspection */
			$this->getJDocument()->addScript($path, $type, $defer , $async);
		}
	}

	public function addScriptDeclaration($js, $newTag = false, $toBody = false) {

		if ($this->getDocumentType() != 'html') {
			return;
		}

		if ($toBody) {
			$this->scriptDeclarations[] = $js;
			return;
		}

		if ($newTag) {
			$js = trim($js);
			if (substr($js,0,7) != '<script') {
				$tag = '<script type="text/javascript">';
				$tag.= "\n'use strict';\n";
				$tag.= $js;
				$tag.= '</script>';
				$js = $tag;
			}

			/** @noinspection PhpUndefinedMethodInspection */
			$this->getJDocument()->addCustomTag($js);
		}
		else {
			$this->getJDocument()->addScriptDeclaration($js,'text/javascript');
		}

	}


	public function addStylesheet($path, $type = 'text/css', $media = 'all') {
		if ($this->getDocumentType() == 'html') {

			if (!in_array($path, $this->styleSheetUrls)) {
				$this->styleSheetUrls[] = $path;
			}

			$joomlaVersion = KenedoPlatform::p()->getVersionShort();
			if (strpos($joomlaVersion, '4') === 0) {
				$wa  = $this->getJDocument()->getWebAssetManager();
				$wa->registerAndUseStyle('cb-'.md5($path), $path);
			}
			else {
				/** @noinspection PhpDeprecationInspection */
				$this->getJDocument()->addStyleSheet($path, $type, $media);
			}

		}
	}

	public function addStyleDeclaration($css) {
		if ($this->getDocumentType() == 'html') {
			$this->getJDocument()->addStyleDeclaration($css);
		}
	}

	public function isAdminArea() {

		$app = $this->getJApplication();

		if (method_exists($app, 'isClient')) {
			return $app->isClient('administrator');
		}
		else {
			/** @noinspection PhpDeprecationInspection */
			return $this->getJApplication()->isAdmin();
		}

	}

	public function isSiteArea() {

		$app = $this->getJApplication();

		if (method_exists($app, 'isClient')) {
			return $app->isClient('site');
		}
		else {
			/** @noinspection PhpDeprecationInspection */
			return $this->getJApplication()->isSite();
		}

	}

	public function autoload($className, $classPath) {

		return KenedoAutoload::registerClass($className, $classPath);

	}

	public function processContentModifiers($text) {

		$item = new stdClass();

		$item->text = $text;
		$item->introtext = $text;
		$item->fulltext = $text;

		$params = array();

		if ($this->getVersionShort() == 1.5) {
			$data = array(&$item, &$params);
			$results = $this->triggerEvent('onPrepareContent', $data);
			$item->beforeDisplayContent = trim(implode("\n", $results));
		}
		else {
			JPluginHelper::importPlugin('content');
			$page = 0;
			$data = array('com_configbox.content', &$item, &$params, &$page);
			$this->triggerEvent('onContentPrepare', $data);
		}

		return $item->text;

	}

	public function triggerEvent($eventName, $data) {
		return $this->getJApplication()->triggerEvent($eventName, $data);
	}

	public function raiseError($errorCode, $errorMessage) {
		throw new Exception($errorMessage, intval($errorCode));
	}

	public function renderHtmlEditor($dataFieldKey, $content, $width, $height, $cols, $rows) {

		$style = 'width:'.$width.'; height:'.$height;

		return '<textarea name="'.hsc($dataFieldKey).'" class="kenedo-html-editor not-initialized" style="'.$style.'" rows="'.intval($rows).'" cols="'.intval($cols).'">'.hsc($content).'</textarea>';

	}

	public function sendEmail($fromEmail, $fromName, $recipient, $subject, $body, $isHtml = false, $cc = NULL, $bcc = NULL, $attachment = NULL) {

		$mailer = $this->getJMailer();

		$reflect = new ReflectionObject($mailer);

		// At some point Joomla must have introduced that sendMail function. Seems more best-practice-like
		if ($reflect->hasMethod('sendMail')) {
			$response = $mailer->sendMail($fromEmail, $fromName, $recipient, $subject, $body, $isHtml, $cc, $bcc, $attachment, $fromEmail, $fromName);

			if ($response == false) {
				KLog::log('Sending email failed. Error message from JMailer is "'.$mailer->ErrorInfo.'". Function arguments were '.var_export(func_get_args(), true), 'error');
				return false;
			}
			elseif(is_a($response, 'Exception') == true) {
				/** @noinspection PhpUndefinedMethodInspection */
				KLog::log('Sending email failed. Error message from JMailer is "'.$response->getMessage().'". Function arguments were '.var_export(func_get_args(), true), 'error');
				return false;
			}
			else {
				return true;
			}

		}

		$mailer->setSender(array($fromEmail, $fromName));
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->setFrom($fromEmail, $fromName);
		$mailer->addReplyTo($fromEmail, $fromName);
		$mailer->isHtml( $isHtml );

		$mailer->addCc($cc);
		$mailer->addBcc($bcc);

		$reflect = new ReflectionObject($mailer);

		if ($reflect->hasProperty('Sender') && $reflect->getProperty('Sender')->isPublic() ) {
			$mailer->Sender = $fromEmail;
		}

		if (!empty($attachment)) {
			if (is_string($attachment)) {
				$mailer->addAttachment($attachment);
			}
			elseif (is_array($attachment)) {
				foreach ($attachment as $attachmentItem) {
					$mailer->addAttachment($attachmentItem);
				}
			}
		}

		$response = $mailer->Send();

		return $response;

	}

	public function getGeneratorTag() {
		return $this->getJDocument()->getGenerator();
	}

	public function setGeneratorTag($string) {
		return $this->getJDocument()->setGenerator($string);
	}

	public function getUrlBase() {

		if (!empty($_SERVER['HTTP_HOST'])) {
			$base = JUri::base();
		}
		else {
			$base = '';
		}

		if ($this->isAdminArea()) {
			$base = rtrim($base, '/');
			$ex = explode('/', $base);
			if (array_pop($ex) == 'administrator') {
				$base = implode('/',$ex);
			}
		}

		$base = rtrim($base, '/');

		return $base;

	}

	public function getUrlBaseAssets() {
		return $this->getUrlBase();
	}

	public function getDocumentBase() {
		return $this->getJDocument()->getBase();
	}
	public function setDocumentBase($string) {
		return $this->getJDocument()->setBase($string);
	}

	public function setDocumentMimeType($mime) {
		return $this->getJDocument()->setMimeEncoding($mime);
	}

	public function getDocumentTitle() {
		return $this->getJDocument()->getTitle();
	}

	public function setDocumentTitle($string) {
		return $this->getJDocument()->setTitle($string);
	}

	public function setMetaTag($tag,$content) {
		$this->getJDocument()->setMetaData($tag, $content);
	}

	public function isLoggedIn() {
		return ($this->getJUser()->get('id') != 0);
	}

	public function getUserId() {
		return $this->getJUser()->get('id');
	}

	public function getUserName($userId = NULL) {
		return $this->getJUser($userId)->get('username');
	}

	public function getUserFullName($userId = NULL) {
		return $this->getJUser($userId)->get('name');
	}

	public function getUserPasswordEncoded($userId = NULL) {
		return $this->getJUser($userId)->get('password');
	}

	public function getUserIdByUsername($username) {

		if (class_exists('\Joomla\CMS\User\UserHelper')) {
			return \Joomla\CMS\User\UserHelper::getUserId($username);
		}
		else {
			jimport('joomla.user.helper');
			return JUserHelper::getUserId($username);
		}

	}

	public function getUserTimezoneName($userId = NULL) {

		$version = $this->getVersionShort();

		if ($version == 1.5) {
			$user = $this->getJUser($userId);
			$offset = (int)$user->getParam('timezone', $this->getConfigOffset() );

			$oldOffsets = array(
				'-12' => 'Etc/GMT-12', '-11' => 'Pacific/Midway', '-10' => 'Pacific/Honolulu','-9.5' => 'Pacific/Marquesas',
				'-9' => 'US/Alaska','-8' => 'US/Pacific','-7' => 'US/Mountain',
				'-6' => 'US/Central','-5' => 'US/Eastern','-4.5' => 'America/Caracas',
				'-4' => 'America/Barbados','-3.5' => 'Canada/Newfoundland',
				'-3' => 'America/Buenos_Aires','-2' => 'Atlantic/South_Georgia',
				'-1' => 'Atlantic/Azores','0' => 'Europe/London',
				'1' => 'Europe/Amsterdam','2' => 'Europe/Istanbul',
				'3' => 'Asia/Riyadh','3.5' => 'Asia/Tehran',
				'4' => 'Asia/Muscat','4.5' => 'Asia/Kabul',
				'5' => 'Asia/Karachi','5.5' => 'Asia/Calcutta',
				'5.75' => 'Asia/Katmandu','6' => 'Asia/Dhaka',
				'6.5' => 'Indian/Cocos','7' => 'Asia/Bangkok',
				'8' => 'Australia/Perth','8.75' => 'Australia/West',
				'9' => 'Asia/Tokyo','9.5' => 'Australia/Adelaide',
				'10' => 'Australia/Brisbane','10.5' => 'Australia/Lord_Howe',
				'11' => 'Pacific/Kosrae','11.5' => 'Pacific/Norfolk',
				'12' => 'Pacific/Auckland','12.75' => 'Pacific/Chatham',
				'13' => 'Pacific/Tongatapu','14' => 'Pacific/Kiritimati'
			);

			$tzName = timezone_open($oldOffsets[$offset])->getName();
		}
		else {

			$user = $this->getJUser($userId);

			$tzName = $user->getParam('timezone', $this->getConfigOffset() );
		}

		return $tzName;

	}

	public function registerUser($data, $groupIds = array()) {

		$user['email'] = $data->email;
		$user['name'] = $data->name;
		$user['username'] = $data->username;
		$user['password'] = $data->password;
		$user['password2'] = $data->password2;

		if ($this->getVersionShort() == 1.5) {
			$user['gid'] = 18;
			$user['usertype'] = 'Registered';
		}
		else {
			$user['groups'] = $groupIds;
		}

		$this->unsetErrors();

		$juser = JUser::getInstance(0);
		if (!$juser->bind($user)) {
			/** @noinspection PhpDeprecationInspection */
			foreach ($juser->getErrors() as $error) {
				$this->setError($error);
			}
			return false;
		}

		// Suppress Joomla's account email dispatch
		if ($this->getVersionShort() != '1.5') {
			if (method_exists('JPluginHelper','getPlugin')) {
				$plugin = JPluginHelper::getPlugin('user', 'joomla');
				if ($plugin) {
					$data = new KStorage($plugin->params);
					$data->set('mail_to_user', 0);
					$plugin->params = $data->toString('json');
				}
			}
		}

		if (!$juser->save()) {
			/** @noinspection PhpDeprecationInspection */
			foreach ($juser->getErrors() as $error) {
				$this->setError($error);
			}
			return false;
		}

		$userObject = new stdClass();

		$userObject->id 		= $juser->get('id');
		$userObject->name 		= $juser->get('name');
		$userObject->username 	= $juser->get('username');
		$userObject->password 	= $juser->get('password');

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

	public function isAuthorized($task, $userId = NULL, $minGroupId = NULL) {

		if ($this->getVersionShort() == 1.5) {

			if ($minGroupId) {
				/** @noinspection PhpUndefinedMethodInspection */
				return (KenedoPlatform::p()->getUserGroupId($userId) >= $minGroupId);
			}
			else {
				return false;
			}
		}

		$exp = explode('.',$task,2);
		$component = $exp[0];
		$task = $exp[1];

		if ($this->getVersionShort() <= 1.7) {
			/** @noinspection PhpDeprecationInspection */
			/** @noinspection PhpUndefinedMethodInspection */
			return ( $this->getJUser($userId)->authorize($task,$component) === true) ? true : false;
		}
		else {
			return ($this->getJUser($userId)->authorise($task,$component) === true) ? true : false;
		}

	}

	public function passwordsMatch($passwordClear, $passwordEncrypted) {

		// We got an old Joomla version switch here
		// It's complicated..at some point within 2.5 Joomla added ::verifyPassword and the old way of password check was gone
		// So we go weird to check it
		if ($this->getVersionShort() < 2 || method_exists('JUserHelper', 'verifyPassword') == false) {
			jimport('joomla.user.helper');
			$exploded	= explode( ':', $passwordEncrypted );
			if (isset($exploded[1])) {
				$salt = $exploded[1];
			}
			else {
				$salt = '';
			}
			$crypt	= $exploded[0];
			/** @noinspection PhpDeprecationInspection */
			$test =  JUserHelper::getCryptedPassword($passwordClear, $salt);

			if ($test == $crypt) return true;
			else return false;
		}
		else {
			$match = JUserHelper::verifyPassword($passwordClear, $passwordEncrypted);
			return $match;
		}

	}

	public function passwordMeetsStandards($password) {

		if (mb_strlen($password) < 6) {
			return false;
		}

		return true;
	}

	public function getPasswordStandardsText() {

		return KText::_('Your password should contain at least 6 characters.');

		//return KText::_('Your password should contain at least 8 characters and should contain numbers and letters.');
	}

	public function changeUserPassword($userId, $passwordClear) {

		$user = JUser::getInstance($userId);

		$data = $user->getProperties();
		$data['password'] = $passwordClear;
		$data['password2'] = $passwordClear;

		$user->bind($data);
		$success = $user->save(true);

		if ($success == false) {
			/** @noinspection PhpDeprecationInspection */
			KLog::log('Could not change user password for user ID "'.$userId.'". Error messages from platform are "'.var_export($user->getErrors(),true),'error');
			return false;
		}
		else {
			return true;
		}

	}

	public function getRootDirectory() {
		return JPATH_SITE;
	}

	public function getAppParameters() {

		$option = KRequest::getKeyword('option');
		$params = JComponentHelper::getParams($option);

		if ($this->getVersionShort() != 1.5) {
			/** @noinspection PhpUndefinedMethodInspection */
			$appParams = $this->getJApplication()->getParams();
			$params->merge($appParams);
		}

		$obj = $params->toObject();

		$params = new KStorage();
		foreach ($obj as $key=>$value) {
			if ($key == 'show_page_title' or $key == 'show_page_heading') $key = 'show_page_heading';
			if ($key == 'page_title') $key = 'page_title';
			$params->set($key,$value);
		}
		return $params;
	}

	public function renderOutput(&$output) {

		echo $output;

		if (count($this->scriptDeclarations)) {
			?>
			<script type="text/javascript">
				'use strict';
				<?php
				foreach ($this->scriptDeclarations as $js) {
					echo $js."\n";
				}
				?>
			</script>
			<?php
		}

		if ($this->getDocumentType() == 'html') {
			?>
			<script id="cb-stylesheets" type="application/json"><?php echo json_encode($this->styleSheetUrls);?></script>
			<?php
		}

	}

	public function getPasswordResetLink() {
		if ($this->getVersionShort() == 1.5) {
			return KLink::getRoute('index.php?option=com_user&view=reset');
		}
		else {
			return KLink::getRoute('index.php?option=com_users&view=reset');
		}
	}

	public function getPlatformLoginLink() {
		if ($this->getVersionShort() == 1.5) {
			return KLink::getRoute('index.php?option=com_user&view=login');
		}
		else {
			return KLink::getRoute('index.php?option=com_users&view=login');
		}
	}


	public function getRoute($url, $encode = true, $secure = NULL) {
		if (strstr($url, 'option=com_cbcheckout')) {

			$log = 'Found a getRoute call for "'.$url.'". Most likely in a custom Configbox template or other customization. The value for parameter option should be replaced with com_cbcheckout (option=com_cbcheckout is now option=com_configbox).';
			$log .= ' We keep supporting the old link until version 2.7 only, please change the link as soon as you can.';
			KLog::logLegacyCall($log, 1);

			$url = str_replace('option=com_cbcheckout', 'option=com_configbox', $url);
		}
		return JRoute::_($url,$encode,$secure);
	}

	public function getActiveMenuItemId() {
		$activeItem	= $this->getJApplication()->getMenu()->getActive();
		if ($activeItem) {
			return $activeItem->id;
		}
		else {
			return NULL;
		}
	}

	public function getLanguages() {

		if ($this->languages === NULL) {

			if ($this->getVersionShort() == 1.5) {

				$db = $this->getDb();

				$query = "SHOW TABLES LIKE '#__languages'";
				$db->setQuery($query);
				$hasJoomfish = $db->loadResult();

				if ($hasJoomfish) {
					$query = "SELECT `code` AS `tag`, `shortcode` AS `urlCode`, `name` AS `label` FROM `#__languages`";
					$db->setQuery($query);
					$this->languages = $db->loadObjectList('tag');
				}
				else {
					$tag = $this->getLanguageTag();
					$this->languages[$tag] = new KenedoObject();
					$this->languages[$tag]->tag = $tag;
					$this->languages[$tag]->urlCode = substr($tag,0,2);
					$this->languages[$tag]->label = $this->getJLanguage()->getName();
				}
			}
			else {
				$db = $this->getDb();
				$query = "SELECT `lang_code` AS `tag`, `sef` AS `urlCode`, `title` AS `label` FROM `#__languages`";
				$db->setQuery($query);
				$this->languages = $db->loadObjectList('tag');
			}

		}

		return $this->languages;
	}

	public function platformUserEditFormIsReachable() {

		if ($this->getVersionShort() == 1.5) {
			return $this->isAdminArea();
		}
		else {
			return false;
		}

	}

	public function userCanEditPlatformUsers() {
		$user = $this->getJUser();

		if ($this->getVersionShort() == 1.5) {
			return ($user->get('gid') > 18);
		}
		else {
			return $user->authorise('core.manage', 'com_users');
		}

	}

	public function getPlatformUserEditUrl($platformUserId) {

		if ($this->getVersionShort() == 1.5) {
			$link = KenedoPlatform::p()->getUrlBase().'/administrator/index.php?option=com_users&view=user&task=edit&cid[]='.$platformUserId;
		}
		else {
			$link = KenedoPlatform::p()->getUrlBase().'/administrator/index.php?option=com_users&view=user&layout=edit&id=106'.$platformUserId;
		}

		return $link;

	}

	public function getComponentDir($componentName) {
		return JPATH_SITE.'/components/'.strtolower($componentName);
	}

	public function getUrlAssets() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/assets';
		return $path;
	}

	public function getDirAssets() {
		$path = $this->getComponentDir('com_configbox').'/assets';
		return $path;
	}

	public function getDirCache() {
		// Not using JPATH_CACHE on purpose to avoid writing into the admin cache
		return JPATH_SITE.'/cache';
	}

	public function getDirCustomization() {
		$path = $this->getComponentDir('com_configbox').'/data/customization';
		return $path;
	}

	public function getDirCustomizationAssets() {
		$path = $this->getComponentDir('com_configbox').'/data/customization/assets';
		return $path;
	}

	public function getUrlCustomization() {
		$path = $this->getUrlBase().'/components/com_configbox/data/customization';
		return $path;
	}

	public function getUrlCustomizationAssets() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/data/customization/assets';
		return $path;
	}

	public function getDirCustomizationSettings() {
		$path = $this->getComponentDir('com_configbox').'/data/store/private/settings';
		return $path;
	}

	public function getDirDataCustomer() {
		$path = $this->getComponentDir('com_configbox').'/data/customer';
		return $path;
	}

	public function getUrlDataCustomer() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/data/customer';
		return $path;
	}

	public function getDirDataStore() {
		$path = $this->getComponentDir('com_configbox').'/data/store';
		return $path;
	}

	public function getUrlDataStore() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/data/store';
		return $path;
	}

	public function getTemplateOverridePath($component, $viewName, $templateName) {
		$path = JPATH_SITE .'/templates/'. $this->getTemplateName() .'/html/'. $component .'/'. $viewName .'/'. $templateName.'.php';
		return $path;
	}

	/**
	 * Polyfill for getting JApplication
	 * @return JApplication | Joomla\CMS\Application\CMSApplication
	 *
	 * @since 3.1
	 */
	protected function getJApplication() {

		if (class_exists('\Joomla\CMS\Factory')) {
			return \Joomla\CMS\Factory::getApplication();
		}
		else {
			return JFactory::getApplication();
		}

	}

	/**
	 * Polyfill for getting JDocument
	 * @return JDocument | \Joomla\CMS\Document\Document | Joomla\CMS\Document\HtmlDocument | Joomla\CMS\Document\RawDocument
	 * @since 3.1
	 */
	protected function getJDocument() {

		if (class_exists('\Joomla\CMS\Factory')) {
			return \Joomla\CMS\Factory::getDocument();
		}
		else {
			return JFactory::getDocument();
		}

	}

	/**
	 * Polyfill for getting JMail
	 * @return JMail | \Joomla\CMS\Mail\Mail
	 *
	 * @since 3.1
	 */
	protected function getJMailer() {

		if (class_exists('\Joomla\CMS\Factory')) {
			return \Joomla\CMS\Factory::getMailer();
		}
		else {
			return JFactory::getMailer();
		}

	}

	/**
	 * Polyfill for getting JUser
	 * @param int $userId
	 * @return JUser | Joomla\CMS\User\User
	 *
	 * @since 3.1
	 */
	protected function getJUser($userId = NULL) {

		if (class_exists('\Joomla\CMS\Factory')) {
			return \Joomla\CMS\Factory::getUser($userId);
		}
		else {
			return JFactory::getUser($userId);
		}

	}

	/**
	 * Polyfill for getting JLanguage
	 * @return JLanguage | Joomla\CMS\Language\Language
	 *
	 * @since 3.1
	 */
	protected function getJLanguage() {

		if (class_exists('\Joomla\CMS\Factory')) {
			return \Joomla\CMS\Factory::getLanguage();
		}
		else {
			return JFactory::getLanguage();
		}

	}

	/**
	 * Polyfill for getting JConfig
	 * @return \Joomla\Registry\Registry
	 *
	 * @since 3.1
	 */
	protected function getJConfig() {

		if (class_exists('\Joomla\CMS\Factory')) {
			$app = \Joomla\CMS\Factory::getApplication();
			if (method_exists($app, 'getConfig')) {
				return $app->getConfig();
			}
			else {
				return \Joomla\CMS\Factory::getConfig();
			}
		}
		else {
			return JFactory::getConfig();
		}

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
	 * Sets the exception handler (pick if the platform needs it or not)
	 * @param callable $callable
	 * @see set_exception_handler()
	 */
	public function setExceptionHandler($callable) {
		set_exception_handler($callable);
	}

	/**
	 * Should set the given error handler callable unless the app should not deal with custom error handling on this platform
	 * @see restore_exception_handler()
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
		return JSession::getFormToken();
	}

	/**
	 * @inheritDoc
	 */
	public function getCsrfTokenValue() {
		return '1';
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