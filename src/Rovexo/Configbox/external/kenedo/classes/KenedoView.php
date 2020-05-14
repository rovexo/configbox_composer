<?php
class KenedoView {

	/**
	 * Returns the default model for the view. Is overwritten in each sub class.
	 *
	 * @return KenedoModel|NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	/**
	 * @var string $component Name of the component dealing with tasks in this view (e.g. com_configbox). To be
	 * overridden in sub classes
	 */
	public $component = '';

	/**
	 * @var string $controllerName Name of the controller dealing with tasks in this view (e.g. admindashboard). To be
	 * overridden in sub classes
	 */
	public $controllerName = '';

	/**
	 * @var string $viewPath Absolute filesystem path to the view's class file. The value is figured out in getView and
	 * set in the constructor.
	 * @see KenedoView::getView, KenedoView::__construct()
	 */
	public $viewPath = '';

	/**
	 * @var string $className PHP class name of the view (think sub classes). Is automatically set in the constructor.
	 */
	public $className = '';

	/**
	 * @var string $view Name of the view
	 * @see KenedoView::getViewNameFromClass()
	 */
	public $view = '';

	/**
	 * @var array $viewCssClasses Holds strings with CSS classes to add to the view's HTML wrapper
	 * @see KenedoView::addViewCssClasses(), KenedoView::renderViewCssClasses()
	 */
	public $viewCssClasses = array();

	/**
	 * @var string This will be filled into the Kenedo form's action attribute. It better mentions option, controller and output_mode=view_only
	 */
	public $formAction = '';

	/**
	 * @var array $pageTasks Holds information about the available tasks for this view
	 * @see KenedoView::display(), KenedoModel::getListingTasks(), KenedoModel::getDetailsTasks()
	 */
	public $pageTasks = array();

	/**
	 * @var string[] Array of HTML strings for filtering a listing
	 * @see KenedoView::getFilterInputs
	 */
	public $filterInputs;

	/**
	 * @var array $instances Holds all instances of KenedoView subclasses (think singleton)
	 * @see KenedoView::getView()
	 */
	static $instances = array();

	/**
	 * @var string Page title, shown on top of the page
	 * @see KenedoView::getPageTitle()
	 */
	public $pageTitle = '';

	/**
	 * @var string Can be set in prepareTemplateVars. Allows HTML, will show beneath the page title
	 */
	public $contentAfterTitle = '';

	/**
	 * @var object[] $records Used in listings. Holds an array of objects with data used in a kenedo listing
	 * @see KenedoModel::getRecords()
	 */
	public $records;

	/**
	 * @var object $record Used in forms. Holds the data object of the view's default model
	 * @see KenedoModel::getRecord()
	 */
	public $record;

	/**
	 * @var KenedoProperty[] $properties (Sub-classes of KenedoProperty) Properties of the model used in this view
	 * @see KenedoModel::getProperties()
	 */
	public $properties;

	/**
	 * Can be set from outside in order to display a view programatically with arbitrary filters
	 *
	 * @var string[] Key/value pairs, key is the filter name (table alias . column name), value is the chosen value
	 * @see KenedoView::getFiltersFromUpdatedState()
	 */
	public $filters = array();

	/**
	 * @var array[] An array of arrays with 2 strings, keys are propertyName and direction
	 * @see KenedoView::getOrderingFromUpdatedState()
	 */
	public $orderingInfo;

	/**
	 * @var int[] An array of 2 ints, keys are start and limit
	 * @see KenedoView::getPaginationFromUpdatedState()
	 */
	public $paginationInfo;

	/**
	 * @var string $pagination Ready-made HTML for pagination
	 * @see KenedoViewHelper::getListingPagination
	 */
	public $pagination;

	/**
	 * Used in listings. Key/value pairs which will be sent as POST data when the listing gets updated.
	 * Has things like
	 * @var string[]
	 */
	public $listingData;

	/**
	 * @var bool $listing Indicates that the view deals with a listing, not an edit form
	 * @see KenedoController::display(), KenedoController::edit()
	 */
	public $listing = false;

	/**
	 * @var string $foreignKeyField Used for add-buttons of intra-listings. Holds the foreign key field name for child tables
	 */
	public $foreignKeyField;

	/**
	 * @var string $foreignKeyPresetValue Used for add-buttons of intra-listings. Holds the init value for $foreignKeyField in forms.
	 */
	public $foreignKeyPresetValue;

	/**
	 * @var string URL used for redirection after saving in edit forms, cancel etc. GET/POST param 'return' or referrer is used typically
	 * @see KenedoView::__construct
	 */
	public $returnUrl = '';

	/**
	 * Name of the template (normally called default). Some views can an individual custom template defined (products,
	 * configurator pages, elements, possibly more in the future). Most templates will have the name in the ID attribute
	 * of a wrapping div for styling etc.
	 *
	 * @var string $template Name of the template
	 */
	public $template = '';

	/**
	 * @var array
	 * @see KenedoModel::getRecordUsage()
	 */
	public $recordUsage;

	/**
	 * Gets you a singleton object of a view
	 *
	 * @param string $className
	 * @param string $path (optional) Filesystem path to the view class (if omitted Kenedo checks regular location, then
	 * customization location
	 *
	 * @return KenedoView subclass of KenedoView
	 *
	 * @throws Exception if file is not found or class in file is not found
	 */
	static function getView($className, $path = NULL) {

		// Abort if the view file cannot be found
		if ($path && is_file($path) == false) {
			$identifier = KLog::log('View class file not found in path "'.$path.'".', 'error');
			throw new Exception('View file for view class "'.$className.'" not found. Log Identifier: '.$identifier);
		}

		if (!$path) {

			// Figure out the component name by class name
			$component = self::getComponentNameFromClass($className);

			// Figure out the view name by class name
			$viewName = strtolower( substr($className, strpos($className, 'View') + 4 ) );

			// Prepare paths for both system and customization file location
			$regularPath 	= KenedoPlatform::p()->getComponentDir($component) .DS. 'views' .DS. strtolower($viewName) .DS. 'view.html.php';
			$customPath 	= KenedoPlatform::p()->getDirCustomization() .DS. 'views' .DS. strtolower($viewName) .DS. 'view.html.php';

			// Overwrite $path with the right one based on existence
			if (is_file($regularPath)) {
				$path = $regularPath;
			}
			elseif (is_file($customPath)) {
				$path = $customPath;
			}
			else {
				$identifier = KLog::log('View file not found in expected path "'.$regularPath.'" or "'.$customPath.'" for class "'.$className.'".', 'error');
				throw new Exception('View file for view class "'.$className.'" not found. Log Identifier: '.$identifier);
			}

		}

		// Load the file
		require_once($path);

		// Abort if the view class is not found in the file
		if (class_exists($className) == false) {
			$identifier = KLog::log('View class "'.$className.'" not found in file "'.$path.'".', 'error');
			throw new Exception('View class "'.$className.'" not found in the view file (File was found though). Identifier: '.$identifier);
		}

		return new $className($className, $path);

	}

	function __construct($className, $path) {

		$this->component 	= KenedoView::getComponentNameFromClass($className);
		$this->view 		= KenedoView::getViewNameFromClass($className);
		$this->className 	= $className;
		$this->viewPath		= $path;

		if (KRequest::getString('return')) {
			$this->returnUrl = KLink::base64UrlDecode( KRequest::getString('return'));
		}
		elseif (!empty($_SERVER['HTTP_REFERER'])) {
			$this->returnUrl = $_SERVER['HTTP_REFERER'];
		}
		else {
			$this->returnUrl = '';
		}

	}

	static function getComponentNameFromClass($className) {
		return 'com_' . strtolower( substr($className, 0, strpos($className, 'View') ) );
	}

	static function getViewNameFromClass($className) {
		return strtolower( substr($className, strpos($className, 'View') + 4 ) );
	}

	function getPageTitle() {
		return '';
	}

	function getHtml() {
		ob_start();
		$this->display();
		return ob_get_clean();
	}

	function display() {
		$this->prepareTemplateVars();
		$this->renderView();
	}

	function prepareTemplateVars() {

		$this->addViewCssClasses();

		if ($this->listing) {
			$this->prepareTemplateVarsList();
		}
		else {
			$this->prepareTemplateVarsForm();
		}

	}

	protected function prepareTemplateVarsList() {

		$model = $this->getDefaultModel();

		$this->pageTitle = $this->getPageTitle();

		$this->properties = $model->getPropertiesForListing();

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&format=json', false);

		// Figure out what to use as default sorting prop - try ordering
		$defaultSortingProp = NULL;
		foreach ($this->properties as $property) {
			if ($property->getType() == 'ordering') {
				$defaultSortingProp = $property->propertyName;
			}
		}

		$this->filters = array_merge($this->getFiltersFromUpdatedState(), $this->filters);
		$this->paginationInfo = $this->getPaginationFromUpdatedState();
		$this->orderingInfo = $this->getOrderingFromUpdatedState($defaultSortingProp);

		$this->records = $model->getRecords($this->filters, $this->paginationInfo, $this->orderingInfo);

		// If we got no records and pagination says to show a deep page, reset the page thing
		if (count($this->records) == 0 && $this->paginationInfo['start'] != 0) {
			$this->paginationInfo['start'] = 0;
			$this->records = $model->getRecords($this->filters, $this->paginationInfo, $this->orderingInfo);
		}

		$this->filterInputs = $this->getFilterInputs($this->filters);

		// Add pagination HTML
		$totalCount = $model->getRecords($this->filters, array(), array(), NULL, true);

		$this->pagination = KenedoViewHelper::getListingPagination($totalCount, $this->paginationInfo);


		$this->pageTasks = $model->getListingTasks();

		$listingData = array(
			'base-url'				=> KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode())),
			'option'				=> hsc($this->component),
			'controller'            => hsc($this->controllerName),
			'task'					=> 'display',
			'output_mode'			=> 'view_only',
			'groupKey'				=> hsc(KenedoViewHelper::getGroupingKey($this->properties)),
			'limitstart'			=> hsc($this->paginationInfo['start']),
			'limit'					=> hsc($this->paginationInfo['limit']),
			'listing_order_property_name'	=> hsc(count($this->orderingInfo) ? $this->orderingInfo[0]['propertyName'] : ''),
			'listing_order_dir'				=> hsc(count($this->orderingInfo) ? $this->orderingInfo[0]['direction'] : ''),
			'return'				=> KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode()), false) ),
			'ids'					=> '',
			'ordering-items'		=> '',
			'foreignKeyField'		=> KRequest::getKeyword('foreignKeyField', (!empty($this->foreignKeyField)) ? $this->foreignKeyField : ''),
			'foreignKeyPresetValue'	=> KRequest::getKeyword('foreignKeyPresetValue', (!empty($this->foreignKeyPresetValue)) ? $this->foreignKeyPresetValue : ''),
		);

        // START - Prepare the href for for the add button
		$addLink = 'index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&task=edit&id=0';

        if (!empty($this->foreignKeyField)) {
            $addLink .= '&prefill_'.$this->foreignKeyField.'='.$this->foreignKeyPresetValue;
        }
        if (KRequest::getKeyword('foreignKeyField')) {
            $addLink .= '&prefill_'.KRequest::getKeyword('foreignKeyField').'='.KRequest::getInt('foreignKeyPresetValue', '0');
        }
        $addLink .= '&return='.$listingData['return'];

        $listingData['add-link'] = KLink::base64UrlEncode( KLink::getRoute($addLink, false) );
        // END - Prepare the href for for the add button

        $this->listingData = $listingData;

	}

	protected function prepareTemplateVarsForm() {

		$id = KRequest::getInt('id');
		$model = $this->getDefaultModel();

		if ($id) {
			$this->record = $model->getRecord($id);
		}
		else {
			$this->record = $model->initData();
		}

		$this->formAction = KLink::getRoute('index.php?option='.$this->component.'&controller='.$this->controllerName.'&output_mode=view_only', false);

		$this->recordUsage = $model->getRecordUsage($id);
		$this->properties = $model->getProperties();

		if (!empty($this->record->title)) {
			$this->pageTitle = $this->getPageTitle() . ': ' . $this->record->title;
		} elseif (!empty($this->record->name)) {
			$this->pageTitle = $this->getPageTitle() . ': ' . $this->record->name;
		} else {
			$this->pageTitle = $this->getPageTitle();
		}

		$this->pageTasks = $model->getDetailsTasks();

	}

    /**
     * @deprecated Remove calls, method is no longer in use
     * @return null
     */
	function isAjaxSubview() {
		return NULL;
	}

	/**
	 * @deprecated Remove calls, method is no longer in use
	 * @return null
	 */
	function isInModal() {
		return NULL;
	}

	function addViewCssClasses() {
	    $this->viewCssClasses[] = 'kenedo-view';
		$this->viewCssClasses[] = 'platform-'.KenedoPlatform::getName();
		$this->viewCssClasses[] = KenedoPlatform::p()->isAdminArea() ? 'in-backend':'in-frontend';
	}

	function renderViewCssClasses() {
		echo hsc(implode(' ',$this->viewCssClasses));
	}

	function getViewOutput($template = NULL) {
		ob_start();
		$this->renderView($template);
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Adds stylesheets and requireJS AMD loader (via platform methods)
	 */
	function addAssets() {
		$this->includeStylesheets();
		$this->addAmdLoader();
	}

	/**
	 * Adds the requireJS AMD loader (via platform methods)
	 * @see ConfigboxViewHelper::addAmdLoader
	 */
	function addAmdLoader() {
		if (defined('KENEDO_LOADER_ADDED') == false) {
			ConfigboxViewHelper::addAmdLoader();
			define('KENEDO_LOADER_ADDED', true);
		}
	}

	/**
	 * @param string|null $template Template name to use
	 */
	function renderView($template = NULL) {

		if (KenedoPlatform::p()->getDocumentType() == 'html') {
			$this->addAssets();
		}

		if ($template === NULL) {
			$template = KRequest::getKeyword('layout','default');
		}

		$template = str_replace(DS , '', $template);
		$template = str_replace('.', '', $template);

		$viewFolder = dirname($this->getViewPath());
		$viewName = strtolower(substr($viewFolder,strrpos($viewFolder, DS) + 1));

		$templatePaths = array();
		// Joomla-typical template override location
		$templatePaths['templateOverride'] 	= KenedoPlatform::p()->getTemplateOverridePath($this->component, $viewName, $template);
		// Custom template for the view's template
		$templatePaths['customTemplate'] 	= KenedoPlatform::p()->getDirCustomization() .DS. 'templates' .DS. $viewName .DS. $template.'.php';
		// Original template for that view
		$templatePaths['defaultTemplate'] 	= dirname($this->getViewPath()).DS.'tmpl'.DS.$template.'.php';

		$output = '';

		foreach ($templatePaths as $templatePath) {
			if (is_file($templatePath)) {
				ob_start();
				include($templatePath);
				$output = ob_get_clean();
				break;
			}
		}

		if ($output === false) {
			KLog::log('Template "'.$template.'" not found in "'.$templatePaths['defaultTemplate'].'" for view "'.get_class($this).'".', 'error', 'Template "'.$template.'" not found for view "'.get_class($this).'".');
		}

		echo $output;

	}

	/**
	 * @see viewPath
	 * @return string
	 */
	function getViewPath() {
		return $this->viewPath;
	}

	function assign($key,$value) {
		$this->$key = $value;
	}

	function assignRef($key,$value) {
		$this->$key =& $value;
	}

	/**
	 * @return string[] Array with filter names as keys and chosen values as value
	 */
	function getFiltersFromUpdatedState() {

		$model = $this->getDefaultModel();

		$filterNames = $model->getFilterNames();

		$filters = array();

		foreach ($filterNames as $filterName) {
			$path = $this->view.'.'.$filterName;
			$requestName = 'filter_'. str_replace('.', '$', $filterName);
			$value = KenedoViewHelper::getUpdatedState($path, $requestName, '', 'string');
			if ($value == 'all' || $value === '0' || $value === '') {
				KenedoViewHelper::unsetState($path);
			}
			else {
				$filters[$filterName] = $value;
			}

		}

		return $filters;

	}

	function getPaginationFromUpdatedState() {

		// Remember the previous limit
		$prevLimit = KenedoViewHelper::getState(strtolower($this->view).'.listing_limit');

		// Get the info from updated state
		$paginationInfo = array(
			'start'=>KenedoViewHelper::getUpdatedState( strtolower($this->view).'.listing_start',	 	'limitstart', 	0,	'int'),
			'limit'=>KenedoViewHelper::getUpdatedState( strtolower($this->view).'.listing_limit', 		'limit', 		25,	'int'),
		);

		// Limit of 0 means show all, so set the start to 0 so we don't miss out on records
		if ($paginationInfo['limit'] == '0') {
			$paginationInfo['start'] = 0;
		}

		// If the limit (so the items per page) changed, reset the start
		if ($paginationInfo['limit'] != $prevLimit ) {
			$paginationInfo['start'] = 0;
		}

		return $paginationInfo;

	}

	function getOrderingFromUpdatedState($defaultPropertyName = null, $direction = 'ASC') {

		$instructions = array(
			array(
				'propertyName'	=> KenedoViewHelper::getUpdatedState( strtolower($this->view).'.listing_order_property_name',	'listing_order_property_name', 	$defaultPropertyName, 'string'),
				'direction'		=> KenedoViewHelper::getUpdatedState( strtolower($this->view).'.listing_order_dir',				'listing_order_dir', 			$direction, 		'string'),
			)
		);

		if (empty($instructions[0]['propertyName'])) {
			return array();
		}
		else {
			return $instructions;
		}

	}

	/**
	 * @param string[] $filters array as from self::getFiltersFromUpdatedState
	 * @return string[] Array of HTML for each filter
	 * @see KenedoView::getFiltersFromUpdatedState
	 */
	function getFilterInputs($filters) {

		$model = $this->getDefaultModel();
		$props = $model->getProperties();

		$filterInputs = array();

		foreach ($props as $prop) {
			$input = $prop->getFilterInput($this, $filters);

			if (is_array($input)) {
				$filterInputs = array_merge($input, $filterInputs);
			}
			elseif(!empty($input)) {
				$name = $prop->getFilterName();
				$filterInputs[$name] = $input;
			}
		}

		return $filterInputs;

	}

	/**
	 * Gets the stylesheet URLs for the view and 'asks' the platform to add them to the HTML head. Filtering out
	 * duplicate URLs is done on the platform layer or by the platform itself.
	 * @throws Exception
	 */
	function includeStylesheets() {

		$urls = $this->getOptimizedStylesheetUrls();

		foreach ($urls as $url) {
			KenedoPlatform::p()->addStylesheet($url);
		}

	}

	/**
	 * Gets the stylesheet URLs from getStyleSheetUrls() and (depending on settings) looks for minified versions and
	 * adds a cache busting query string.
	 * @return string[]
	 * @throws Exception
	 */
	public function getOptimizedStylesheetUrls() {

		$urls = $this->getStyleSheetUrls();

		$useMinifiedCss = CbSettings::getInstance()->get('use_minified_css');
		$baseUrlAssets = KenedoPlatform::p()->getUrlAssets();
		$baseDirAssets = KenedoPlatform::p()->getDirAssets();
		$baseUrlAssetsCustom = KenedoPlatform::p()->getUrlCustomizationAssets();
		$baseDirAssetsCustom = KenedoPlatform::p()->getDirCustomizationAssets();

		$useCacheBusting = CbSettings::getInstance()->get('use_assets_cache_buster');
		$cacheBusterQs = 'version='.ConfigboxViewHelper::getCacheBusterValue();

		foreach ($urls as &$url) {

			if ($useMinifiedCss) {

				// See if there is a min.css file, if so use it instead
				if (strpos($url, $baseUrlAssets) === 0) {
					$pathDir = str_replace($baseUrlAssets, $baseDirAssets, $url);
					$pathDirMin = str_replace('.css', '.min.css', $pathDir);
					if (is_file($pathDirMin)) {
						$url = str_replace($baseDirAssets, $baseUrlAssets, $pathDirMin);
					}
				}

				// Same for customization assets
				if (strpos($url, $baseUrlAssetsCustom) === 0) {
					$pathDir = str_replace($baseUrlAssetsCustom, $baseDirAssetsCustom, $url);
					$pathDirMin = str_replace('.css', '.min.css', $pathDir);
					if (is_file($pathDirMin)) {
						$url = str_replace($baseDirAssetsCustom, $baseUrlAssetsCustom, $pathDirMin);
					}
				}

			}

			if ($useCacheBusting) {
				$url .= ((strpos($url, '?') === false) ? '?':'&') . $cacheBusterQs;
			}

		}

		return $urls;

	}

	/**
	 * These stylesheets get added server-side or by JS depending on if the view was rendered during document load or injected later.
	 * @return string[] Full URL to stylesheets that should be loaded for that view.
	 */
	function getStyleSheetUrls() {

		$urls = array(
			KenedoPlatform::p()->getUrlAssets().'/kenedo/external/bootstrap-3.3.7/css/bootstrap-prefixed.css',
			KenedoPlatform::p()->getUrlAssets().'/kenedo/assets/css/kenedo.css',
		);

		if (KenedoPlatform::p()->isAdminArea() == true || strpos($this->view, 'admin') === 0) {
			$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.ui-1.12.1/jquery-ui-prefixed.css';
			$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.chosen-1.8.7/chosen.css';
			$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/admin.css';
		}

		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/general.css';

		if (file_exists(KenedoPlatform::p()->getDirCustomizationAssets().'/css/custom.css')) {
			$urls[] = KenedoPlatform::p()->getUrlCustomizationAssets().'/css/custom.css';
		}

		return $urls;

	}

	/**
	 * Add AMD module calls that should run only the first time the view gets injected (or found in the HTML during
	 * first load).
	 * Format is 'moduleId::functionName'. You can also just state 'moduleId' so that the module gets loaded, but no
	 * function gets called.
	 * @return string[] AMD modules to be loaded (e.g. array('configbox/admin::initBackend')).
	 */
	function getJsInitCallsOnce() {

		$calls = array();

		if (KenedoPlatform::p()->isAdminArea() == true || strpos($this->view, 'admin') === 0) {
			$calls[] = 'configbox/admin::initBackendOnce';
		}

		return $calls;
	}

	/**
	 * Add AMD module calls that should run each time the view gets injected.
	 * Format is 'moduleId::functionName'. You can also just state 'moduleId' so that the module gets loaded, but no
	 * function gets called.
	 * @return string[] AMD modules to be loaded (e.g. array('configbox/admin::initBackend')).
	 */
	function getJsInitCallsEach() {

        $calls = array();

        if (KenedoPlatform::p()->isAdminArea() == true || strpos($this->view, 'admin') === 0) {
            $calls[] = 'configbox/admin::initBackendEach';
        }

        return $calls;

	}

	/**
	 * @return string[] CSS classes for the view's wrapping div
	 */
	function getViewCssClasses() {

		$classes = array(
			'cb-content',
			'kenedo-view',
			'view-'.$this->view,
		);

		// Add Joomla page classes (which can be set in the menu item parameters)
		if (KenedoPlatform::p()->isSiteArea()) {
			$pageClass = KenedoPlatform::p()->getAppParameters()->get('pageclass_sfx');
			if ($pageClass) {
				// There may be multiple classes set
				$pageClasses = explode(' ', $pageClass);
				foreach ($pageClasses as $pageClass) {
					// For preventing class conflicts, we prepend page-class- to each
					$classes[] = 'page-class-'.$pageClass;
				}
			}
		}

		return $classes;

	}

	/**
	 * @return string Gives you all HTML attributes for the view's wrapping div (incl. class)
	 */
	function getViewAttributes() {

		$attributes = array();

		$attributes['class'] = implode(' ', $this->getViewCssClasses());
		$attributes['data-view-id'] = $this->view;

		// Add required stylesheets (but only if the view gets rendered for HTML injection, because CSS combiners mess up things)
		if (KRequest::getKeyword('output_mode') == 'view_only') {
			$attributes['data-stylesheets'] = json_encode($this->getOptimizedStylesheetUrls());
		}
		else {
			$attributes['data-stylesheets'] = json_encode(array());
		}

		$attributes['data-init-calls-once'] = json_encode($this->getJsInitCallsOnce());
		$attributes['data-init-calls-each'] = json_encode($this->getJsInitCallsEach());

		$attributePairs = array();
		foreach ($attributes as $key=>$value) {
			$attributePairs[] = $key.'="'.hsc($value).'"';
		}

		return implode(' ', $attributePairs);

	}

}
