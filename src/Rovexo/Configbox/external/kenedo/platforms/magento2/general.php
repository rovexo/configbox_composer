<?php


class KenedoPlatformMagento2 implements InterfaceKenedoPlatform {
	
	protected $db;

	/**
	 * @var Magento\Framework\App\ObjectManager
	 */
	private $objectManager;

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
	private $directoryList;

    /**
     * @var Magento\Framework\Locale\OptionInterface
     */
	private $locale;

    /**
     * @var Magento\Framework\App\State
     */
	private $state;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
	private $storeManager;

    /**
     * @var Magento\Framework\Data\Form\FormKey
     */
	private $formKey;

    /**
     * @var Magento\Backend\Model\UrlInterface
     */
	private $backendUrl;

    /**
     * @var Magento\Framework\UrlInterface
     */
	private $url;

    /**
     * @var Magento\Framework\Module\Dir\Reader
     */
	private $moduleReader;

    /**
     * @var Magento\Framework\View\Asset\Repository
     */
	private $assetRepository;

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

	/**
	 * @return \Magento\Framework\Module\Dir\Reader
	 */
	protected function getM2ModuleReader() {
		if ($this->moduleReader === null) {
			$this->moduleReader =  $this->getM2ObjectManager()->get('Magento\Framework\Module\Dir\Reader');
		}
		return $this->moduleReader;
	}

	/**
	 * @return \Magento\Framework\App\State
	 */
	protected function getM2State() {
		if ($this->state === null) {
			$this->state =  $this->getM2ObjectManager()->get('Magento\Framework\App\State');
		}
		return $this->state;
    }

	/**
	 * @return \Magento\Store\Model\StoreManagerInterface
	 */
	protected function getM2StoreManager() {
		if ($this->storeManager === null) {
			$this->storeManager = $this->getM2ObjectManager()->get('Magento\Store\Model\StoreManagerInterface');
		}
		return $this->storeManager;
	}

	/**
	 * @return \Magento\Framework\App\Filesystem\DirectoryList
	 */
	protected function getM2DirectoryList() {

		if ($this->directoryList === null) {
			$this->directoryList = $this->getM2ObjectManager()->get('Magento\Framework\App\Filesystem\DirectoryList');
		}
		return $this->directoryList;

	}

	/**
	 * @return \Magento\Framework\UrlInterface
	 */
	protected function getM2Url() {

		if ($this->url === null) {
			$this->url = $this->getM2ObjectManager()->get('Magento\Framework\UrlInterface');
		}
		return $this->url;

	}

	/**
	 * @return \Magento\Framework\UrlInterface
	 */
	protected function getM2BackendUrl() {

		if ($this->backendUrl === null) {
			$this->backendUrl = $this->getM2ObjectManager()->get('Magento\Backend\Model\UrlInterface');
		}
		return $this->backendUrl;

	}

	/**
	 * @return \Magento\Framework\Data\Form\FormKey
	 */
	protected function getM2FormKey() {

		if ($this->formKey === null) {
			$this->formKey = $this->getM2ObjectManager()->get('Magento\Framework\Data\Form\FormKey');
		}
		return $this->formKey;

	}

	/**
	 * @return \Magento\Framework\View\Asset\Repository
	 */
	protected function getM2AssetsRepository() {

		if ($this->assetRepository === null) {
			$this->assetRepository = $this->getM2ObjectManager()->get('Magento\Framework\View\Asset\Repository');
		}
		return $this->assetRepository;

	}

	/**
	 * @return \Magento\Framework\App\ObjectManager
	 */
	protected function getM2ObjectManager() {
		if ($this->objectManager === null) {
			$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		}
		return $this->objectManager;
	}

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

	protected $memoConnectionData;

	public function getDbConnectionData() {

		if ($this->memoConnectionData === NULL) {

			$objectManager = $this->getM2ObjectManager();
			/**
			 * @var \Magento\Framework\App\ResourceConnection $resource
			 */
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection('default');
			$config = $connection->getConfig();

			/**
			 * @var \Magento\Framework\App\DeploymentConfig $deploymentConfig
			 */
			$deploymentConfig = $objectManager->get('Magento\Framework\App\DeploymentConfig');
			$prefix = $deploymentConfig->get('db/table_prefix');

			$connection = new stdClass();
			$connection->hostname = $config['host'];
			$connection->username = $config['username'];
			$connection->password = $config['password'];
			$connection->database = $config['dbname'];
			$connection->prefix = $prefix;

			$this->memoConnectionData = $connection;

		}

		return $this->memoConnectionData;

	}
	
	public function &getDb() {
		if (!$this->db) {
			require_once(dirname(__FILE__).'/database.php');
			$this->db = new KenedoDatabaseMagento2();
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

	public function logout() {
		return;
	}

	protected $memoGetApplicationVersion;

	/**
	 * @inheritDoc
	 */
	public function getApplicationVersion() {

		if ($this->memoGetApplicationVersion == NULL) {
			$moduleInfo = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Module\ModuleList')->getOne('Rovexo_Configbox');
			$this->memoGetApplicationVersion = $moduleInfo['setup_version'];
		}

		return $this->memoGetApplicationVersion;
	}

	public function authenticate($username, $passwordClear) {
		return false;
	}

	public function login($username) {
		return false;
	}

	public function sendSystemMessage($text, $type = NULL) {
		return;
	}

	public function getVersionShort() {
		$productMetadata = $this->getM2ObjectManager()->get('Magento\Framework\App\ProductMetadataInterface');
		$version = $productMetadata->getVersion();
		return $version;
	}

	public function getDebug() {
		return false;
	}

	public function getConfigOffset() {
		return 'UTC';
	}

	public function getMailerFromName() {
		return '';
	}

	public function getMailerFromEmail() {
		return '';
	}

	public function getTmpPath() {
		$path = $this->getM2DirectoryList()->getPath(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
		return $path;
	}

	public function getLogPath() {
        $path = $this->getM2DirectoryList()->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LOG);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
	}

    public function getLanguageTag() {
		$objectManager = $this->getM2ObjectManager();
		$store = $objectManager->get('Magento\Store\Api\Data\StoreInterface');
		$locale = str_replace('_', '-', $store->getLocaleCode());
		return $locale;
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
		return 'html';
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
		if ($this->getM2State()->getAreaCode() == 'adminhtml') {
			return true;
		}

		return false;
	}

	public function isSiteArea() {
        if($this->getM2State()->getAreaCode() == 'frontend') {
            return true;
        }

        return false;
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

	public function sendEmail($from, $fromName, $recipient, $subject, $body, $isHtml = false, $cc = NULL, $bcc = NULL, $attachmentPath = NULL) {
		return false;
	}

	public function getGeneratorTag() {

	}

	public function setGeneratorTag($string) {

	}

	public function getUrlBase() {
        $baseUrl = $this->getM2StoreManager()->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
		return rtrim($baseUrl, '/');
	}

	public function getUrlBaseAssets() {
		return $this->getUrlBase();
	}

	public function getDocumentBase() {
		return $this->getUrlBase();
	}

	public function setDocumentBase($string) {
		return true;
	}

	public function setDocumentMimeType($mime) {
		return $GLOBALS['document']['mimetype'] = $mime;
	}

	public function getDocumentTitle() {
	    return '';
	}

	public function setDocumentTitle($string) {
	}

	public function setMetaTag($tag,$content) {

	}

	public function isLoggedIn() {
		return KSession::get('logged_in',false);
	}

	public function getUserId() {
		return 0;
	}

	public function getUserName($userId = NULL) {
		return '';
	}

	public function getUserFullName($userId = NULL) {
		return '';
	}

	public function getUserPasswordEncoded($userId = NULL) {
		return KSession::get('user_passwordencoded',false);
	}

	public function getUserIdByUsername($username) {
		return NULL;
	}

	public function getUserTimezoneName($userId = NULL) {
		return $this->getConfigOffset();
	}

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

    public function isAuthorized($task, $userId = NULL, $minGroupId = NULL) {
        // Since M2 not exposing admin cookie to frontend
        if ($this->isAdminArea()) {
            return true;
        }
        return false;
    }

	public function passwordsMatch($passwordClear, $passwordEncrypted) {
		return false;
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

	public function changeUserPassword($userId, $passwordClear) {
		return true;
	}

	public function getRootDirectory() {
        return $this->getM2DirectoryList()->getRoot();
	}

	public function getAppParameters() {
		$params = new KStorage();
		return $params;
	}

	public function renderOutput(&$output) {

    	if ($this->getOutputMode() == 'view_only') {
            require(__DIR__.'/tmpl/raw.php');
            return;
	    }
    	elseif ($this->getOutputMode() == 'in_html_doc') {
            require(__DIR__.'/tmpl/component.php');
        }
    	else {
            require(__DIR__.'/tmpl/raw.php');
	    }

	}

	public function getPasswordResetLink() {
        return "";
	}

	public function getPlatformLoginLink() {
        return "";
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

		$params['form_key'] = $this->getM2FormKey()->getFormKey();

		if ($secure !== NULL) {
			$params['_secure'] = $secure;
		}

		if ($this->isAdminArea()) {
			$url = $this->getM2BackendUrl()->getUrl('*/*/index', $params);
		}
		else {
			if (KRequest::getVar('key')) {
				$params['key'] = KRequest::getString('key');
			}
			$url = $this->getM2Url()->getUrl('configbox/index/index',$params);
		}

		return $url;
	}
	
	public function getActiveMenuItemId() {
		return 0;
	}

    public function getLanguages() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$languages = [];

        $stores = $storeManager->getStores();
        $collectedTags = [];
        foreach($stores as $store) {
            $localeCode = $scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getStoreId());
            $tag = str_replace('_', '-', $localeCode);

            if (in_array($tag, $collectedTags)) {
            	continue;
            }
			$collectedTags[] = $tag;

            $language = \Locale::getPrimaryLanguage($tag);
            $country = \Locale::getRegion($tag);
            $allLanguages = (new \Magento\Framework\Locale\Bundle\LanguageBundle())->get($tag)['Languages'];
            $allCountries = (new \Magento\Framework\Locale\Bundle\RegionBundle())->get($tag)['Countries'];

            $label = $allLanguages[$language] . ' (' . $allCountries[$country] . ')';

            $languages[] = new KenedoObject(array('tag'=>$tag, 'label'=>$label, 'urlCode'=>$tag));
        }
        return $languages;
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
        return $this->getM2DirectoryList()->getRoot() . "/vendor/rovexo/configbox-php/src/Rovexo/Configbox";
	}

	private $memoUrlAssets;

	public function getUrlAssets() {

		if ($this->memoUrlAssets === null) {
			$fileId = 'Rovexo_Configbox::lib-assets/main.js';
			$params = [
				'area' => 'frontend'
			];
			$asset = $this->getM2AssetsRepository()->createAsset($fileId, $params);
			$url = $asset->getUrl();

			$this->memoUrlAssets = str_replace('/main.js', '', $url);
		}

		return $this->memoUrlAssets;

	}

	private $memoDirAssets;

	public function getDirAssets() {

		if ($this->memoDirAssets === null) {
			$viewDir = $this->getM2ModuleReader()->getModuleDir(
				\Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
				'Rovexo_Configbox'
			);
			$this->memoDirAssets = $viewDir . '/base/web/lib-assets';
		}

		return $this->memoDirAssets;

	}

	public function getDirCache() {
        return $this->getM2DirectoryList()->getPath(\Magento\Framework\App\Filesystem\DirectoryList::CACHE);
	}

	protected $memoGetDirCustomization;

	/**
	 * @return string Filesystem path to the customization dir.
	 */
	public function getDirCustomization() {

		if ($this->memoGetDirCustomization === null) {

			if ($this->customizationIsModuleInstalled()) {
				$this->memoGetDirCustomization = $this->getM2ModuleReader()->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, "Rovexo_ConfigboxCustomizations").'/customizations';
			}
			else {
				$this->memoGetDirCustomization = $this->getTmpPath();
			}

		}

		return $this->memoGetDirCustomization;

    }

	public function getUrlCustomization() {
        return '';
	}

	protected $memoGetDirCustomizationAssets;

	/**
	 * @return string Filesystem path to the customization assets dir.
	 */
	public function getDirCustomizationAssets() {

		if ($this->memoGetDirCustomizationAssets === null) {

			if ($this->customizationIsModuleInstalled()) {
				$this->memoGetDirCustomizationAssets = $this->getM2ModuleReader()->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, "Rovexo_ConfigboxCustomizations") . "/base/web";
			}
			else {
				$this->memoGetDirCustomizationAssets = $this->getTmpPath();
			}

		}

		return $this->memoGetDirCustomizationAssets;

	}

	public function getUrlCustomizationAssets() {
		$params = array('_secure' => $this->requestUsesHttps());
		$url = $this->getM2AssetsRepository()->getUrlWithParams('', $params);
		return $url . '/Rovexo_ConfigboxCustomizations';
	}

	public function getDirCustomizationSettings() {
        return $this->getDirCustomization() . "/settings";
	}

	public function getDirDataCustomer() {
        return $this->getM2DirectoryList()->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . "/rovexo/customer";
	}

	public function getUrlDataCustomer() {
        return $this->getM2StoreManager()->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "/rovexo/customer";
	}

	public function getDirDataStore() {
        return $this->getM2DirectoryList()->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . "/rovexo/store";
	}

	public function getUrlDataStore() {
        return $this->getM2StoreManager()->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "/rovexo/store";
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
	 * Should call restore_error_handler unless the app should not deal with custom error handling on this platform.
	 * @see restore_error_handler()
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

    public function echoOutput($output) {
		echo $output;
		exit();
    }

    protected $memoCustomizationIsModuleInstalled;

	/**
	 * @return bool
	 */
    protected function customizationIsModuleInstalled() {

    	if ($this->memoCustomizationIsModuleInstalled === NULL) {
			$moduleManager = $this->getM2ObjectManager()->get('\Magento\Framework\Module\Manager');
			$this->memoCustomizationIsModuleInstalled = $moduleManager->isEnabled('Rovexo_ConfigboxCustomizations');
	    }

		return $this->memoCustomizationIsModuleInstalled;

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
		$formKey = $this->getM2ObjectManager()->get('Magento\Framework\Data\Form\FormKey');
		return $formKey->getFormKey();
	}

	/**
	 * @inheritDoc
	 */
	public function requestUsesHttps() {
		$storeManager = $this->getM2ObjectManager()->get('Magento\Store\Model\StoreManagerInterface');
		return $storeManager->getStore()->isCurrentlySecure();
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