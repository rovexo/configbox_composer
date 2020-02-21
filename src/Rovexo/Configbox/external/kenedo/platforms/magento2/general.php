<?php


class KenedoPlatformMagento2 implements InterfaceKenedoPlatform {
	
	protected $db;

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
     * @var Magento\Framework\View\Element\Template\Context
     */
	private $context;

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

	public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->directoryList = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->locale = $objectManager->get('Magento\Framework\Locale\OptionInterface');
        $this->state =  $objectManager->get('Magento\Framework\App\State');
        $this->storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->formKey = $objectManager->get('Magento\Framework\Data\Form\FormKey');
        $this->backendUrl = $objectManager->get('Magento\Backend\Model\UrlInterface');
        $this->url = $objectManager->get('Magento\Framework\UrlInterface');
        $this->moduleReader = $objectManager->get('Magento\Framework\Module\Dir\Reader');
        $this->assetRepository = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $this->context = $objectManager->get('Magento\Framework\View\Element\Template\Context');
    }

    public function initialize() {
		// Set the option request var like for Joomla, as all is built around that
		if (KRequest::getVar('option') == '') {
			KRequest::setVar('option', 'com_configbox');
		}
	}
	
	public function getDbConnectionData() {
		$envPath = BP.'/app/etc/env.php';
		$info = require($envPath);

		$connection = new stdClass();
		$connection->hostname = $info['db']['connection']['default']['host'];
		$connection->username = $info['db']['connection']['default']['username'];
		$connection->password = $info['db']['connection']['default']['password'];
		$connection->database = $info['db']['connection']['default']['dbname'];
		$connection->prefix = $info['db']['table_prefix'];

		return $connection;
	}
	
	public function &getDb() {
		if (!$this->db) {
			require_once(dirname(__FILE__).DS.'database.php');
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
	
	//TODO: Implement
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

	//TODO: Implement
	public function authenticate($username, $passwordClear) {
		return true;
	}

	//TODO: Implement
	public function login($username) {
		return true;
	}

	//TODO: Implement
	public function sendSystemMessage($text, $type = NULL) {
		return;
	}
	//TODO: Implement
	public function getVersionShort() {
		return '';
	}
	//TODO: Implement
	public function getDebug() {
		return false;
	}

	public function getConfigOffset() {
		return 'UTC';
	}

	//TODO: Implement
	public function getMailerFromName() {
		return '';
	}

	//TODO: Implement
	public function getMailerFromEmail() {
		return '';
	}

	public function getTmpPath() {
		$path = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
		return $path;
	}

	public function getLogPath() {
        $path = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LOG);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
	}

    public function getLanguageTag() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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
	
/*	public function addScriptDeclaration($js, $newTag = false, $toBody = false) {
		$tag = '<script type="text/javascript">'."\n//<![CDATA[\n";
		$tag.= $js;
		$tag.= "\n//]]>\n".'</script>';
		$this->scriptDeclarations[] = $tag;
	}*/

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
		if($this->state->getAreaCode() == 'adminhtml') {
			return true;
		}

		return false;
	}

	public function isSiteArea() {
        if($this->state->getAreaCode() == 'frontend') {
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
		return '<textarea name="'.hsc($dataFieldKey).'" id="'.hsc($dataFieldKey).'" class="kenedo-html-editor" style="width:'.(int)$width.'px; height:'.$height.'px" rows="'.(int)$rows.'" cols="'.(int)$cols.'">'.hsc($content).'</textarea>';
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
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
		return rtrim($baseUrl, '/');
	}

	public function getUrlBaseAssets() {
		return $this->getUrlBase();
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
	}

	//TODO: Implement
	public function setDocumentTitle($string) {
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
		return 0;
	}

	//TODO: Test
	public function getUserName($userId = NULL) {
		return '';
	}

	//TODO: Test
	public function getUserFullName($userId = NULL) {
		return '';
	}

	public function getUserPasswordEncoded($userId = NULL) {
		return KSession::get('user_passwordencoded',false);
	}

	//TODO: Test
	public function getUserIdByUsername($username) {
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

    public function isAuthorized($task, $userId = NULL, $minGroupId = NULL) {
        // Since M2 not exposing admin cookie to frontend
        if ($this->isAdminArea()) {
            return true;
        }
        return false;
    }

	//TODO: Implement
	public function passwordsMatch($passwordClear, $passwordEncrypted) {
		return false;
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
        return $this->directoryList->getRoot();
	}

	//TODO: Implement
	public function getAppParameters() {
		$params = new KStorage();
		return $params;
	}

	public function renderOutput(&$output) {
         $getRawOutput = (KRequest::getVar('format') == 'raw' || KRequest::getVar('ajax_sub_view') == '1');
         $getAsHtmlDoc = (KRequest::getVar('in_modal') == '1' || KRequest::getVar('tmpl') == 'component');

         if ($getRawOutput) {
             require(__DIR__.'/tmpl/raw.php');
             return;
         } elseif($getAsHtmlDoc) {
             require(__DIR__.'/tmpl/component.php');
             return;
         }
         else {
             require(__DIR__.'/tmpl/raw.php');
             return;
         }
	}

	public function startSession() {
		return true;
	}

	//TODO: Implement
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

		$params['form_key'] = $this->formKey->getFormKey();

		if ($secure !== NULL) {
			$params['_secure'] = $secure;
		}

		if ($this->isAdminArea()) {
			$url = $this->backendUrl->getUrl('*/*/index', $params);
		}
		else {
			if (KRequest::getVar('key')) {
				$params['key'] = KRequest::getString('key');
			}
			$url = $this->url->getUrl('configbox/index/index',$params);
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
        foreach($stores as $store) {
            $localeCode = $scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getStoreId());
            $tag = str_replace('_', '-', $localeCode);

            $language = \Locale::getPrimaryLanguage($tag);
            $country = \Locale::getRegion($tag);
            $allLanguages = (new \Magento\Framework\Locale\Bundle\LanguageBundle())->get($tag)['Languages'];
            $allCountries = (new \Magento\Framework\Locale\Bundle\RegionBundle())->get($tag)['Countries'];

            $label = $allLanguages[$language] . ' (' . $allCountries[$country] . ')';

            $languages[] = new KenedoObject(array('tag'=>$tag, 'label'=>$label, 'urlCode'=>$tag));
        }
        return $languages;
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
        return $this->directoryList->getRoot() . DS . "vendor" . DS . "rovexo" . DS . "configbox-php" . DS . "src" . DS . "Rovexo" . DS . "Configbox";
	}

	public function getUrlAssets() {

    	$params = array('_secure' => $this->context->getRequest()->isSecure());
	    $url = $this->assetRepository->getUrlWithParams('', $params);
	    return $url . "/rovexo/configbox/assets";
	}

	public function getDirAssets() {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LIB_WEB) . DS . "rovexo" . DS . "configbox" . DS . "assets";
	}

	public function getDirCache() {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::CACHE);
	}

	protected $memoGetDirCustomization;

	/**
	 * @return string Filesystem path to the customization dir.
	 */
	public function getDirCustomization() {

		if ($this->memoGetDirCustomization === null) {

			if ($this->customizationIsModuleInstalled()) {
				$this->memoGetDirCustomization = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, "Rovexo_ConfigboxCustomizations").DS.'customizations';
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
				$this->memoGetDirCustomizationAssets = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, "Rovexo_ConfigboxCustomizations") . DS . "base" . DS . "web";
			}
			else {
				$this->memoGetDirCustomizationAssets = $this->getTmpPath();
			}

		}

		return $this->memoGetDirCustomizationAssets;

	}

	public function getUrlCustomizationAssets() {
		$params = array('_secure' => $this->context->getRequest()->isSecure());
		$url = $this->assetRepository->getUrlWithParams('', $params);
		return $url . '/Rovexo_ConfigboxCustomizations';
	}

	public function getDirCustomizationSettings() {
        return $this->getDirCustomization() . DS . "settings";
	}

	public function getDirDataCustomer() {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DS . "rovexo" . DS . "customer";
	}

	public function getUrlDataCustomer() {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . DS . "rovexo" . DS . "customer";
	}

	public function getDirDataStore() {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DS . "rovexo" . DS . "store";
	}

	public function getUrlDataStore() {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . DS . "rovexo" . DS . "store";
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

    public function renderScriptAssets() {
        foreach ($this->scriptAssets as $asset) { ?>
            <script type="<?php echo hsc($asset['type']);?>" async="<?php echo ($asset['async']) ? 'true':'false';?>" defer="<?php echo ($asset['defer']) ? 'true':'false';?>"></script>
        <?php }
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
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$moduleManager = $objectManager->get('\Magento\Framework\Module\Manager');
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
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$formKey = $objectManager->get('Magento\Framework\Data\Form\FormKey');
		return $formKey->getFormKey();
	}

}