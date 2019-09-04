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
			KenedoPlatform::p()->setDocumentBase( $this->getUrlBase().'/' );
		}

		// At some Joomla 3.0 built .raw URLs with format=raw and then on routing didn't set format=raw then
		if (!empty($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], -4) == '.raw') {
			KRequest::setVar('format', 'raw');
		}

		// When in_modal or ajax_sub_view is in request, set tmpl=component (makes joomla output only component's output)
		if (KRequest::getVar('in_modal') == '1' || KRequest::getVar('ajax_sub_view') == '1') {
			KRequest::setVar('tmpl', 'component');
		}

		$this->do2Point6LegacyStuff();

	}

	/**
	 * Checks for REQUEST keys that have changed between 2.6 and 3.0 and tries to 'translate'
	 */
	protected function do2Point6LegacyStuff() {


		/* LEGACY VIEW NAMES - START */
		if (KRequest::getVar('view') == 'category') {
			KRequest::setVar('view', 'configuratorpage');
		}

		if (KRequest::getVar('view') == 'products') {
			KRequest::setVar('view', 'productlisting');
		}

		if (KRequest::getVar('view') == 'grandorder') {
			KRequest::setVar('view', 'cart');
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
			require_once(dirname(__FILE__).DS.'database.php');
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
	 *
	 * Authenticate a user (in other words, figure out if the provided credentials match a user)
	 * This does not login a user.
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function authenticate($username, $password) {

		if ($this->getVersionShort() == '1.5') {

			$credentials = array(
				'username'=>$username,
				'password'=>$password,
			);

			$options = array();

			jimport( 'joomla.user.authentication');

			$authenticate = JAuthentication::getInstance();
			$response	  = $authenticate->authenticate($credentials, $options);

			/** @noinspection PhpDeprecationInspection */
			/** @noinspection PhpUndefinedConstantInspection */
			if ($response->status === JAUTHENTICATE_STATUS_SUCCESS) {
				return true;
			}
			else {
				return false;
			}

		}
		// For Joomla version starting with 2.5
		else {

			$credentials = array(
				'username'=>$username,
				'password'=>$password,
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

			$this->getJDocument()->addStyleSheet($path, $type, $media);
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

		$style = 'width:'.intval($width).'px; height:'.intval($height).'px';

		return '<textarea name="'.hsc($dataFieldKey).'" id="'.hsc($dataFieldKey).'" class="kenedo-html-editor not-initialized" style="'.$style.'" rows="'.intval($rows).'" cols="'.intval($cols).'">'.hsc($content).'</textarea>';

	}

	public function sendEmail($fromEmail, $fromName, $recipient, $subject, $body, $isHtml = false, $cc = NULL, $bcc = NULL, $attachment = NULL) {

		$mailer = $this->getJMailer();

		$reflect = new ReflectionObject($mailer);

		// At some point Joomla must have introduced that sendMail function. Seems more best-practice-like
		if ($reflect->hasMethod('sendMail')) {
			$response = $mailer->sendMail($fromEmail, $fromName, $recipient, $subject, $body, $isHtml, $cc, $bcc, $attachment);
			if ($response == false) {
				KLog::log('Sending email failed. Error message from JMailer is "'.$mailer->ErrorInfo.'". Function arguments were '.var_export(func_get_args(), true), 'error');
			}
			return $response;
		}

		$mailer->setSender(array($fromEmail, $fromName));
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->setFrom($fromEmail, $fromName);
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

		if (!empty($_SERVER['HTTP_HOST'])) {
			$path = JUri::base(true);
		}
		else {
			$path = '';
		}

		$base = KPATH_SCHEME.'://'.KPATH_HOST.'/'.$path;

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

		/*
		if (mb_strlen($password) < 8) {
			return false;
		}
		if ( preg_match("/[0-9]/", $password) == 0 || preg_match("/[a-zA-Z]/", $password) == 0) {
			return false;
		}
		*/
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

	public function startSession() {

		return true;
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
			$link = KPATH_URL_BASE.'/administrator/index.php?option=com_users&view=user&task=edit&cid[]='.$platformUserId;
		}
		else {
			$link = KPATH_URL_BASE.'/administrator/index.php?option=com_users&view=user&layout=edit&id=106'.$platformUserId;
		}

		return $link;

	}

	public function getComponentDir($componentName) {
		return JPATH_SITE.DS.'components'.DS.strtolower($componentName);
	}

	public function getUrlAssets() {
		$path = $this->getUrlBaseAssets().'/components/com_configbox/assets';
		return $path;
	}

	public function getDirAssets() {
		$path = $this->getComponentDir('com_configbox').DS.'assets';
		return $path;
	}

	public function getDirCache() {
		// Not using JPATH_CACHE on purpose to avoid writing into the admin cache
		return JPATH_SITE.DS.'cache';
	}

	public function getDirCustomization() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customization';
		return $path;
	}

	public function getDirCustomizationAssets() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customization'.DS.'assets';
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
		$path = JPATH_SITE .DS. 'templates' .DS. $this->getTemplateName() .DS. 'html' .DS. $component .DS. $viewName .DS. $templateName.'.php';
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
	 * Should set the given shutdown function callable unless the app should not deal with custom error handling on
	 * this platform
	 * @param callable $callback
	 * @see register_shutdown_function()
	 */
	public function registerShutdownFunction($callback) {
		register_shutdown_function($callback);
	}

}