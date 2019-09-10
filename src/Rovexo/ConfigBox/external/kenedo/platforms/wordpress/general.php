<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPlatformWordpress implements InterfaceKenedoPlatform {

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
	 * @var KenedoDatabaseWordpress
	 */
	protected $db;

	/**
	 * @var string[] $errors
	 */
	protected $errors;

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

		/*
		 * Mind that the getRoute method also manipulates URLs a little
		 */

		// CB deals with controller or view, WP with page. So here we sneak in page (copied from controller or view)
		if (in_array(KRequest::getKeyword('page'), [NULL, 'configbox'])) {

			$controllerName = KRequest::getKeyword('controller','');
			$viewName = KRequest::getKeyword('view','');

			if ($controllerName) {
				KLog::log('Manipulating page param. Taking controller name "'.$controllerName.'"', 'custom_wp_requests');
				KRequest::setVar('page', $controllerName);
			}
			if ($viewName) {
				KLog::log('Manipulating page param. Taking view name "'.$viewName.'"', 'debug');
				KRequest::setVar('page', $viewName);
			}

		}

		// If there is no 'action' in the request, then create one (in the <page>.<task> form)
		if (in_array(KRequest::getKeyword('action'), [NULL])) {

			// We best don't manipulate the action if the request does not deal with a CB page. For now let it be existence of controller or view param
			$controllerName = KRequest::getKeyword('controller','');
			$viewName = KRequest::getKeyword('view','');
			if ($controllerName or $viewName) {
				$action = KRequest::getKeyword('page').'.'.KRequest::getKeyword('task', 'display');
				KLog::log('Manipulating action param. Making it "'.$action.'"', 'debug');
				KRequest::setVar('action', $action);
			}


		}

		if (KRequest::getVar('format') == 'raw' || KRequest::getVar('format') == 'json') {
			KRequest::setVar('noheader', '1', 'GET');
		}

		// renderOutput will make an HTML doc for us
		if (KRequest::getVar('tmpl') == 'component') {
			KRequest::setVar('noheader', '1', 'GET');
		}

		// Add the actions that make inline script tags rendered in template
		add_action('wp_head', array($this, 'renderHeadScriptDeclarations'), 100000);
		add_action('wp_footer', array($this, 'renderBodyScriptDeclarations'), 100000);

		add_action('admin_head', array($this, 'renderHeadScriptDeclarations'), 100000);
		add_action('admin_footer', array($this, 'renderBodyScriptDeclarations'), 100000);

	}
	
	public function getDbConnectionData() {

		$connection = new stdClass();
		$connection->hostname 	= DB_HOST;
		$connection->username 	= DB_USER;
		$connection->password 	= DB_PASSWORD;
		$connection->database 	= DB_NAME;
		$connection->prefix 	= $GLOBALS['table_prefix'];
				
		return $connection;
	
	}
	
	public function &getDb() {
		if (!$this->db) {
			require_once(dirname(__FILE__).DS.'database.php');
			$this->db = new KenedoDatabaseWordpress();
		}
		
		return $this->db;
	}

	//TODO: Test
	public function redirect($url, $httpCode = 303) {

		if (headers_sent()) {
			echo '<script type="text/javascript">window.location="'.$url.'"</script>';
			return;
		}
		else {
			wp_redirect($url, $httpCode);

		}

	}

	public function logout() {
		wp_logout();
	}

	//TODO: Test
	public function authenticate($username, $passwordClear) {
		$response = wp_authenticate($username, $passwordClear);
		return (is_wp_error($response) == false);
	}

	//TODO: Test
	public function login($username) {

		$platformUserId = $this->getUserIdByUsername($username);

		$credentials = array(
			'remember'=>1,
			'username'=>$username,
			'password'=>'dummy',
		);

		$secure_cookie = apply_filters( 'secure_signon_cookie', is_ssl(), $credentials );

		wp_set_auth_cookie($platformUserId, $credentials['remember'], $secure_cookie);

		$userId = ConfigboxUserHelper::getUserIdByPlatformUserId($platformUserId);

		$db = KenedoPlatform::getDb();
		$query = "UPDATE `#__configbox_users` SET `is_temporary` = '0' WHERE `id` = ".intval($userId);
		$db->setQuery($query);
		$db->query();

		// Set the CB user ID
		ConfigboxUserHelper::setUserId($userId);

		return true;

	}

	//TODO: Implement
	public function sendSystemMessage($text, $type = NULL) {

	}

	public function getVersionShort() {
		return get_bloginfo('version');
	}

	public function getDebug() {
		return WP_DEBUG;
	}

	//TODO: Test
	public function getConfigOffset() {
		$string = get_option('timezone_string');

		$validOnes = DateTimeZone::listIdentifiers();
		if (in_array($string, $validOnes)) {
			return $string;
		}
		else {
			return 'UTC';
		}
	}

	//TODO: Test
	public function getMailerFromName() {
		return get_bloginfo('name');
	}

	//TODO: Test
	public function getMailerFromEmail() {
		return get_bloginfo('admin_email');
	}

	public function getLanguageTag() {
		$tag = get_locale();
		return str_replace('_', '-', $tag);
	}
	
	public function getLanguageUrlCode($languageTag = NULL) {
		return $this->getLanguageTag();
	}
	
	//TODO: Check if good enough
	public function getDocumentType() {
		return KRequest::getKeyword('format','html');
	}

	public function addStylesheet($path, $type = 'text/css', $media = 'all') {

		if (in_array($path, $this->stylesheetUrls) == false) {

			$this->stylesheetUrls[] = $path;
			wp_enqueue_style( uniqid(), $path );

		}

	}

	public function addStyleDeclaration($css) {

		$this->inlineStyles[] = $css;
		wp_add_inline_style(uniqid(), $css);

	}

	public function addScript($path, $type = "text/javascript", $defer = false, $async = false) {

		$this->scriptAssets[$path] = array(
			'url' => $path,
			'type' => $type,
			'defer' => $defer,
			'async' => $async,
		);

		wp_enqueue_script(uniqid(), $path);

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

	public function isAdminArea() {
		return is_admin();
	}
	
	public function isSiteArea() {
		return (is_admin() == false);
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
		throw new Exception($errorMessage, intval($errorCode));
	}
	
	public function renderHtmlEditor($dataFieldKey, $content, $width, $height, $cols, $rows) {
		$style = 'width:'.intval($width).'px; height:'.intval($height).'px';

		return '<textarea name="'.hsc($dataFieldKey).'" id="'.hsc($dataFieldKey).'" class="kenedo-html-editor not-initialized" style="'.$style.'" rows="'.intval($rows).'" cols="'.intval($cols).'">'.hsc($content).'</textarea>';
	}

	public function sendEmail($from, $fromName, $recipient, $subject, $body, $isHtml = false, $cc = NULL, $bcc = NULL, $attachmentPath = NULL) {

		$headers = array(
			'From: "'.$fromName.'" <'.$from.'>',
		);

		if ($isHtml) {
			$headers[] = 'Content-Type: text/html';
		}

		if ($cc) {
			$headers[] = 'Cc: '.$cc;
		}

		if ($bcc) {
			$headers[] = 'Bcc: '.$bcc;
		}

		$response = wp_mail($recipient, $subject, $body, $headers, $attachmentPath);

		return $response;

	}

	public function getGeneratorTag() {
		return '';
	}

	public function setGeneratorTag($string) {
		$this->setMetaTag('generator', $string);
	}
	
	public function getUrlBase() {
		return get_site_url();
	}

	public function getUrlBaseAssets() {
		return get_site_url();
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
		header('Content-Type: '.$mime);
	}

	public function getDocumentTitle() {
		return wp_title(NULL, false);
	}

	//TODO: Implement
	public function setDocumentTitle($string) {

	}

	//TODO: Implement
	public function setMetaTag($tag,$content) {

	}
	
	public function isLoggedIn() {
		return is_user_logged_in();
	}

	public function getUserId() {
		$user = wp_get_current_user();
		return $user->ID;
	}

	//TODO: Test
	public function getUserName($userId = NULL) {
		if ($userId === NULL) {
			$user = get_user_by('id', $userId);
		}
		else {
			$user = wp_get_current_user();
		}

		return $user->user_login;

	}

	//TODO: Test
	public function getUserFullName($userId = NULL) {

		if ($userId === NULL) {
			$user = get_user_by('id', $userId);
		}
		else {
			$user = wp_get_current_user();
		}

		return $user->display_name;

	}

	//TODO: Test
	public function getUserPasswordEncoded($userId = NULL) {

		if ($userId === NULL) {
			$user = get_user_by('id', $userId);
		}
		else {
			$user = wp_get_current_user();
		}

		return $user->user_pass;

	}

	//TODO: Test
	public function getUserIdByUsername($username) {
		$wpUser = get_user_by('login', $username);
		if (!$wpUser) {
			return false;
		}
		return $wpUser->ID;
	}

	//TODO: Test
	public function getUserTimezoneName($userId = NULL) {

		$string = get_option('timezone_string');

		$validOnes = DateTimeZone::listIdentifiers();
		if (in_array($string, $validOnes)) {
			return $string;
		}
		else {
			return 'UTC';
		}

	}

	//TODO: Test
	public function registerUser($data, $groupIds = array()) {

		$user['email'] = $data->email;
		$user['name'] = $data->name;
		$user['username'] = $data->username;
		$user['password'] = $data->password;
		$user['password2'] = $data->password2;

		$userObject = new stdClass();

		$id = wp_create_user( $data->username, $data->password, $data->email);

		if ($id === false) {
			return false;
		}

		$userObject->id 		= $id;
		$userObject->name 		= $data->name;
		$userObject->username 	= $data->username;
		$userObject->password 	= $data->password;

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

	//TODO: Test
	public function isAuthorized($task,$userId = NULL, $minGroupId = NULL) {
		return current_user_can('edit_pages');
	}

	public function changeUserPassword($userId, $passwordClear) {
		wp_set_password($passwordClear, $userId);
		return true;
	}

	//TODO: Test
	public function passwordsMatch($passwordClear, $passwordEncrypted) {
		return wp_check_password( $passwordClear, $passwordEncrypted);
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

	public function getRootDirectory() {
		return rtrim(ABSPATH, DIRECTORY_SEPARATOR);
	}

	public function getAppParameters() {
		$params = new KStorage();
		return $params;
	}
	
	public function renderOutput(&$output) {

		$format = KRequest::getKeyword('format');

		if ($format == 'json' || $format == 'raw') {

			$level = ob_get_level();

			for ($i = 0; $i < $level; $i++) {
				ob_clean();
			}

			echo $output;

			die();

		}

		if (KRequest::getKeyword('tmpl') == 'component' || KRequest::getKeyword('in_modal') == '1') {
			require(__DIR__.'/tmpl/component.php');
			return;
		}

		if ($this->isAdminArea()) {
			echo $output;
		}
		else {
			require(__DIR__.'/tmpl/index.php');
		}

	}
	
	public function startSession() {
		return true;
	}
	
	//TODO: Test
	public function getPasswordResetLink() {
		return wp_lostpassword_url();
	}

	//TODO: Test
	public function getPlatformLoginLink() {
		return wp_login_url();
	}
	
	public function getRoute($url, $encode = true, $secure = NULL) {

		$parsed = parse_url($url);

		if (isset($parsed['query'])) {

			$query = array();

			parse_str($parsed['query'], $query);

			if (!empty($query['view'])) {

				if ($query['view'] == 'cart') {

					$postId = $this->getCartPostId();

					if ($postId) {

						$url = get_post_permalink($postId);

						unset($query['view'], $query['option']);

						if (count($query)) {
							$url .= (strstr($url, '?')) ? '&':'?';
							$url .= http_build_query($query);
						}

						return $url;

					}

				}

				if ($query['view'] == 'user') {

					$postId = $this->getUserPostId();

					if ($postId) {

						$url = get_post_permalink($postId);

						unset($query['view'], $query['option']);

						if (count($query)) {
							$url .= (strstr($url, '?')) ? '&':'?';
							$url .= http_build_query($query);
						}

						return $url;

					}

				}

				$postViews = [

					'productlisting' => [
						'meta_key' => 'cb_listing_id',
						'name_id_param' => 'listing_id',
						'unset'=> array(),
						],

					'product' => [
						'meta_key' => 'cb_product_id',
						'name_id_param' => 'prod_id',
						'unset'=> array(),
					],

					'configuratorpage' => [
						'meta_key' => 'cb_page_id',
						'name_id_param' => 'page_id',
						'unset'=> array('prod_id'),
					],

				];

				if (isset($postViews[$query['view']])) {
					$idParamName = $postViews[$query['view']]['name_id_param'];
					if (empty($query[$idParamName])) {
						KLog::log('Got a view parameter "'.$query['view'].'" in URL, but record ID parameter "'.$idParamName.'" is missing. Whole URL was '.$url, 'custom_wp_requests');
						return $url;
					}

					$db = KenedoPlatform::getDb();
					$dbQuery = "SELECT `post_id` FROM `#__postmeta` WHERE `meta_key` = '".$db->getEscaped($postViews[$query['view']]['meta_key'])."' and `meta_value` = ".intval($query[$idParamName]);
					$db->setQuery($dbQuery);
					$postId = $db->loadResult();

					$url = get_post_permalink($postId);

					foreach ($postViews[$query['view']]['unset'] as $var) {
						unset($query[$var]);
					}
					unset($query['view'], $query['option'], $query[$idParamName]);

					if (count($query)) {
						$url .= (strstr($url, '?')) ? '&':'?';
						$url .= http_build_query($query);
					}

					return $url;

				}
			}


			// Since WP wants page to be set and CB works with controller (or view), we set 'page' here (see lower for one more thing)
			if (!empty($query['view'])) {
				$query['page'] = $query['view'];
			}
			elseif (!empty($query['controller'])) {
				$query['page'] = $query['controller'];
			}

			if (!empty($query['task'])) {
				$action = '';
				if (!empty($query['controller'])) {
					$action = $query['controller'].'.';
				}
				$action .= $query['task'];
				$query['action'] = $action;
			}

			$queryString = http_build_query($query);

		}
		else {
			// And in case we got no query string, then we add our wildcard 'configbox' as page (will be dealt with in static::initialize)
			$queryString = 'page=configbox';
		}



		if ($this->isAdminArea()) {

			if (strstr($url, 'format=raw') || strstr($url, 'format=json')) {
				$frontController = 'admin-ajax.php';
			}
			else {
				$frontController = 'admin.php';
			}

			return admin_url( $frontController.'?'.$queryString );
		}
		else {
			return site_url( 'index.php?'.$queryString );
		}

	}

	protected function getCartPostId() {

		$db = KenedoPlatform::getDb();

		$query = "
				SELECT `type`.`post_id`
				FROM `#__postmeta` AS `type`
				LEFT JOIN `#__postmeta` AS `lang` ON `lang`.`post_id` = `type`.`post_id`
				
				WHERE 
					(`type`.`meta_key` = 'type' and `type`.`meta_value` = 'cart')
					AND
					(`lang`.`meta_key` = 'language_tag' and `lang`.`meta_value` = '".$db->getEscaped(KText::getLanguageTag())."')
					
				";
		$db->setQuery($query);
		$postId = $db->loadResult();

		return $postId;

	}

	protected function getUserPostId() {

		$db = KenedoPlatform::getDb();

		$query = "
				SELECT `type`.`post_id`
				FROM `#__postmeta` AS `type`
				LEFT JOIN `#__postmeta` AS `lang` ON `lang`.`post_id` = `type`.`post_id`
				
				WHERE 
					(`type`.`meta_key` = 'type' and `type`.`meta_value` = 'user')
					AND
					(`lang`.`meta_key` = 'language_tag' and `lang`.`meta_value` = '".$db->getEscaped(KText::getLanguageTag())."')
					
				";
		$db->setQuery($query);
		$postId = $db->loadResult();

		return $postId;

	}
	
	public function getActiveMenuItemId() {
		return 0;
	}
	
	//TODO: Test
	public function getLanguages() {

		$locale = str_replace('_', '-', get_locale());

		$language = new stdClass();
		$language->tag = $locale;
		$language->label = $locale;
		return array($language);

	}
	
	public function platformUserEditFormIsReachable() {
		return false;
	}
	
	public function userCanEditPlatformUsers() {
		return false;
	}
	
	public function getPlatformUserEditUrl($platformUserId) {
		return '';
	}

	public function getComponentDir($componentName) {
		return WP_PLUGIN_DIR.'/'.str_replace('com_', '', $componentName).'/app';
	}

	public function getDirAssets() {
		$path = $this->getComponentDir('com_configbox').DS.'assets';
		return $path;
	}

	public function getUrlAssets() {
		return plugins_url('configbox/app/assets');
	}

	public function getDirCustomization() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customization';
		return $path;
	}

	public function getUrlCustomization() {
		return plugins_url('configbox/app/data/customization');
	}

	public function getDirCustomizationAssets() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'customization'.DS.'assets';
		return $path;
	}

	public function getUrlCustomizationAssets() {
		return plugins_url('configbox/app/data/customization/assets');
	}

	public function getDirCustomizationSettings() {
		$path = $this->getComponentDir('com_configbox').DS.'data'.DS.'store'.DS.'private'.DS.'settings';
		return $path;
	}

	public function getDirDataCustomer() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['basedir'].'/cb-customer-data';
		return $path;
	}

	public function getUrlDataCustomer() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['baseurl'].'/cb-customer-data';
		return $path;
	}

	public function getDirDataStore() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['basedir'].'/cb-store-data';
		return $path;
	}

	public function getUrlDataStore() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['baseurl'].'/cb-store-data';
		return $path;
	}

	public function getTemplateOverridePath($component, $viewName, $templateName) {
		$path = '';
		return $path;
	}
	
	public function getDirCache() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['basedir'].'/cb-cache';
		return $path;
	}

	public function getTmpPath() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['basedir'].'/cb-tmp';
		return $path;
	}

	public function getLogPath() {
		$uploadDir = $this->getWpUploadDirInfo();
		$path = $uploadDir['basedir'].'/cb-logs';
		return $path;
	}

	/**
	 * @var null|string[] Memoizes results of getWpUploadDirInfo
	 */
	protected $memoGetUploadDir = NULL;

	/**
	 * @return string[] Wordpress dir infos as in wp_upload_dir()
	 */
	protected function getWpUploadDirInfo() {
		if (empty($this->memoGetUploadDir)) {
			$this->memoGetUploadDir = wp_upload_dir(null, false);
		}
		return $this->memoGetUploadDir;
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