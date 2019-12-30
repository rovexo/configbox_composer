<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewConfiguratorpage extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'configuratorpage';

	/**
	 * @var KStorage Joomla menu item and merged app parameters
	 */
	public $params;

	/**
	 * @var boolean Setting from platform (in case of Joomla it's the menu item's setting)
	 */
	public $showPageHeading;

	/**
	 * @var string Page heading. By default it is the product title if there is one page, otherwise product and page title
	 */
	public $pageHeading;

	/**
	 * @var int Cart Position ID
	 * @see ConfigboxViewConfiguratorpage::setCartPositionId
	 */
	public $cartPositionId;

	/**
	 * @var int Current product ID
	 */
	public $productId;

	/**
	 * @var int Current page ID
	 */
	public $pageId;

	/**
	 * @var ConfigboxProductData $product Typical product data (as in product listing and product details view)
	 */
	public $product;

	/**
	 * @var ConfigboxPageData Object holding all configurator page data (augmented in display method)
	 * @see ConfigboxModelConfiguratorpage::getPage()
	 */
	public $page;

	/**
	 * @var bool $showProductDetailPanes Tells if product detail panes shall be shown (depends on settings in product form)
	 */
	public $showProductDetailPanes;

	/**
	 * @var string $productDetailPanes Ready-to-use HTML with product detail panes
	 * @see ConfigboxViewProductdetailpanes
	 */
	public $productDetailPanes;

	/**
	 * @var string[] $elementsHtml Array of HTML strings containing each element's output HTML for the configurator page.
	 * 							   Array key values are the question IDs.
	 * @see ConfigboxViewQuestion
	 */
	public $questionsHtml;

	/**
	 * @var array[] Array of data with missing selections in product (title, id, pageId, productId and message)
	 * @see ConfigboxModelCartposition::getMissingSelections
	 */
	public $missingProductSelections;

	/**
	 * @var array[] Array of data with missing selections on current page (title, id, pageId, productId and message)
	 * @see ConfigboxModelCartposition::getMissingSelections
	 */
	public $missingPageSelections;

	/**
	 * @var string JSON data about the configurator (for use by the frontend JS, put in the HTML output in a data attribute)
	 */
	public $configuratorDataJson;

	/**
	 * @var bool $canQuickEdit Indicates if the current user can edit the page
	 * @see ConfigboxPermissionHelper::canQuickEdit
	 */
	public $canQuickEdit;

	/**
	 * @var bool $showVisualization Indicates if the product visualization should be shown
	 */
	public $showVisualization;

	/**
	 * @var string HTML from the Visualization View
	 * @see ConfigboxViewBlockvisualization
	 */
	public $visualizationHtml;

	/**
	 * @var string $pageEditButtonsHtml HTML with the page edit buttons
	 * @see ConfigboxQuickeditHelper::renderConfigurationPageButtons
	 */
	public $pageEditButtonsHtml;

	/**
	 * @var string $selectionsHtml HTML with the selections overview
	 * @see ConfigboxViewBlockpricing
	 */
	public $selectionsHtml;

	/**
	 * @var ConfigboxQuestion[] $questions Data of the page's questions
	 */
	public $questions;

	/**
	 * @var boolean Indicates if page tab navigation should show
	 */
	public $showTabNavigation;

	/**
	 * @var boolean Indicates if prev/next/finish buttons should show up beneath the questions
	 */
	public $showButtonNavigation;

	/**
	 * @var boolean Indicates if configurator page navigation should be blocked until required questions are answered.
	 */
	public $blockNavigationOnMissing;

	/**
	 * @var string HTML for the tab navigation
	 * @see ConfigboxViewBlocknavigation
	 */
	public $tabNavigationHtml;

	/**
	 * @var bool $showFinishButton Indicates if the finish-button should be shown
	 */
	public $showFinishButton;

	/**
	 * @var bool $showNextButton Indicates if the next-button should be shown
	 */
	public $showNextButton;

	/**
	 * @var bool $showPrevButton Indicates if the prev-button should be shown
	 */
	public $showPrevButton;

	/**
	 * @var string $finishButtonClasses CSS classes for the finish-button
	 */
	public $finishButtonClasses;

	/**
	 * @var string $nextButtonClasses CSS classes for the next-button
	 */
	public $nextButtonClasses;

	/**
	 * @var string $prevButtonClasses CSS classes for the prev-button
	 */
	public $prevButtonClasses;

	/**
	 * @var string $urlFinish URL for the finish configuration button
	 */
	public $urlFinishButton;

	/**
	 * @var ConfigboxPageData|NULL
	 */
	public $prevPage = NULL;

	/**
	 * @var ConfigboxPageData|NULL
	 */
	public $nextPage = NULL;

	/**
	 * @var ConfigboxPageData|NULL
	 */
	public $currPage = NULL;

	/**
	 * @var bool
	 */
	public $showNetPrices;

	/**
	 * @var ConfigboxPageData Object holding all configurator page data (augmented in display method)
	 * @see ConfigboxModelConfiguratorpage::getPage()
	 * @deprecated Use page instead
	 */
	public $configuratorPage;

	/**
	 * @var ConfigboxQuestion[] $elements Helper property set during looping through the page elements (makes life easier in element
	 * templates).
	 * @deprecated Since 3.1 Check the new configurator page template. Elements are rendered individually with ConfigboxViewElement
	 */
	public $elements;

	/**
	 * @var ConfigboxQuestion $element Helper property set during looping through the page elements (makes life easier in element
	 * templates).
	 * @deprecated Since 3.1 Check the new configurator page template. Elements are rendered individually with ConfigboxViewElement
	 */
	public $element;

	/**
	 * @var string JSON-LD Structured Data
	 */
	public $structuredData;

	/**
	 * @var bool Indicates if GA Enhanced Ecommerce Tracking should be used
	 */
	public $useGaEnhancedTracking;

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/kenedo/external/jquery.ui-1.12.1/jquery-ui-prefixed.css';
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/configurator.css';
		return $urls;
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/configurator::initConfiguratorPage';

		if (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1') {
			$calls[] = 'configbox/ga::initEcConfiguratorPage';
		}

		return $calls;
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/configurator::initConfiguratorPageEach';
		return $calls;
	}

	/**
	 * Adds cart position id, product id and page id to the view's attributes as data attributes
	 * @return string
	 */
	function getViewAttributes() {
		$attributeString = parent::getViewAttributes();
		$attributeString .= ' data-cart-position-id="'.intval($this->cartPositionId).'" data-product-id="'.intval($this->productId).'" data-page-id="'.intval($this->pageId).'"';
		return $attributeString;
	}

	/**
	 * Adds the custom CSS classes from the page data
	 * @return string[]
	 */
	function getViewCssClasses() {

		$viewCssClasses = parent::getViewCssClasses();

		if ($this->page && trim($this->page->css_classes)) {
			$viewCssClasses = array_merge($viewCssClasses, explode(' ', trim($this->page->css_classes)));
		}

		return $viewCssClasses;

	}

	function display() {

		// Have all view data prepared and assigned to the view object
		$this->prepareTemplateVars();

		// In case the page requested isn't there, just do the page's 410 page (and put in the right HTTP response code)
		if (empty($this->page) || empty($this->product)) {
			http_response_code(410);
			$this->renderView('pagenotfound');
			return;
		}

		// Add assets here since we do not do renderView
		$this->addAssets();

		// Get the chosen template (in pages the admin can choose from different templates)
		$layoutName = ($this->page->layoutname) ? $this->page->layoutname : 'default';

		// Collect all possible template paths (in the order the should be chosen from)
		$templates = array();
		$templates['templateOverride'] 	= KenedoPlatform::p()->getTemplateOverridePath('com_configbox', 'configuratorpage', $layoutName);
		$templates['customTemplate'] 	= KenedoPlatform::p()->getDirCustomization().DS.'templates'.DS.'configuratorpage'.DS. $layoutName .'.php';
		$templates['defaultTemplate'] 	= KPATH_DIR_CB.DS.'views'.DS.'configuratorpage'.DS.'tmpl'.DS.'default.php';

        if ((KenedoPlatform::getName() == 'magento') || (KenedoPlatform::getName() == 'magento2')) {
			$templates['defaultTemplate'] 	= KPATH_DIR_CB.DS.'views'.DS.'configuratorpage'.DS.'tmpl'.DS.'magento.php';
		}

		// Loop through and see which exists, use the first one we find
		foreach ($templates as $template) {
			if (file_exists($template)) {
				require($template);
				break;
			}
		}



	}

	function prepareTemplateVars() {

		// Stop if we got no page ID
		if (!$this->pageId) {
			return;
		}

		$ass = ConfigboxCacheHelper::getAssignments();
		$this->productId = !empty($ass['page_to_product'][$this->pageId]) ? $ass['page_to_product'][$this->pageId] : 0;

		// Stop if we can't find the product ID
		if (!$this->productId) {
			return;
		}

		// Check if GA tracking should used
		$this->useGaEnhancedTracking = (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1');


		// Get the models
		$productModel = KenedoModel::getModel('ConfigboxModelProduct');
		$pageModel = KenedoModel::getModel('ConfigboxModelConfiguratorpage');

		// Get the product
		$this->product = $productModel->getProduct($this->productId);

		// Stop if the product does not exist (or isn't active)
		if (!$this->product || $this->product->published != '1') {
			return;
		}

		// Get the page
		$this->page = $pageModel->getPage($this->pageId);

		// Stop if the page does not exist (or isn't active)
		if (!$this->page || $this->page->published != '1') {
			return;
		}

		// This makes sure that a cart and position is created if there isn't one already (or if a new one is needed)
		if (empty($this->cartPositionId)) {
			$this->cartPositionId = $pageModel->ensureProperCartEnvironment($this->productId);
		}

		// Put info on missing selections to the template
		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		$this->missingPageSelections = $positionModel->getMissingSelections($this->pageId, $this->cartPositionId);
		$this->missingProductSelections = $positionModel->getMissingSelections(NULL, $this->cartPositionId);

		// Get info on all pages of the product
		$pages = $pageModel->getPages($this->productId);

		$this->showTabNavigation = (count($pages) > 1 && ($this->product->page_nav_show_tabs == 1 or ($this->product->page_nav_show_tabs == 2 && CbSettings::getInstance()->get('page_nav_show_tabs') == 1)));
		$this->showButtonNavigation = (count($pages) > 1 && ($this->product->page_nav_show_buttons == 1 or ($this->product->page_nav_show_buttons == 2 && CbSettings::getInstance()->get('page_nav_show_buttons') == 1)));
		$this->blockNavigationOnMissing = (count($pages) > 1 && ($this->product->page_nav_block_on_missing_selections == 1 or ($this->product->page_nav_block_on_missing_selections == 2 && CbSettings::getInstance()->get('page_nav_block_on_missing_selections') == 1)));

		if ($this->showTabNavigation) {

			$tabView = KenedoView::getView('ConfigboxViewBlocknavigation');
			$tabView->setPageId($this->pageId);
			$this->tabNavigationHtml = $tabView->getHtml();

		}

		// Init prev, current and next page
		$this->prevPage = NULL;
		$this->nextPage = NULL;
		$this->currPage = NULL;

		// Figure out what's the prev, current and next page
		foreach ($pages as $index=>$page) {
			if ($page->id == $this->pageId) {
				$this->prevPage = (isset($pages[$index - 1])) ? $pages[$index - 1] : NULL;
				$this->nextPage = (isset($pages[$index + 1])) ? $pages[$index + 1] : NULL;
				$this->currPage = $page;
				break;
			}
		}

		// Hide prev or next button if there is no such page
		$this->showPrevButton = !empty($this->prevPage);
		$this->showNextButton = !empty($this->nextPage);

		// On Magento, we do not show CB's finish button in any case (platform makes it's own one)
        if ((KenedoPlatform::getName() == 'magento') || (KenedoPlatform::getName() == 'magento2')) {
			$this->showFinishButton = false;
		}
        else {
			// In global and page settings you can define if the finish button should show on the last page only or on any page
			$showFinishOnlyOnLastPage = ($this->product->page_nav_cart_button_last_page_only == 1 or ($this->product->page_nav_cart_button_last_page_only == 2 && CbSettings::getInstance()->get('page_nav_cart_button_last_page_only') == 1));

			if ($showFinishOnlyOnLastPage) {
				$this->showFinishButton = empty($this->nextPage);
			}
			else {
				$this->showFinishButton = true;
			}
		}

		// Set CSS classes for navigation buttons, indicates what shall be hidden
		if (count($this->missingPageSelections) && $this->blockNavigationOnMissing) {
			$this->nextButtonClasses    = 'configbox-disabled cb-page-nav-next wait-for-xhr';
			$this->finishButtonClasses  = 'configbox-disabled add-to-cart-button cb-page-nav-finish wait-for-xhr trigger-add-to-cart trigger-ga-track-add-to-cart';
		}
		else {
			$this->nextButtonClasses    = 'cb-page-nav-next wait-for-xhr';
			$this->finishButtonClasses  = 'cb-page-nav-finish add-to-cart-button wait-for-xhr trigger-add-to-cart trigger-ga-track-add-to-cart';
		}

		// This class let's the system know that all xhr request need to finish before navigating to another page
		$this->prevButtonClasses = 'cb-page-nav-prev wait-for-xhr';

		if ($this->prevPage) {
			$this->prevButtonClasses .= ' page-id-'.$this->prevPage->id;
		}

		if ($this->nextPage) {
			$this->nextButtonClasses .= ' page-id-'.$this->nextPage->id;
		}

		$this->urlFinishButton  = '';

		// Set the HTML document's title
		if (count($pages) == 1) {
			$documentTitle = KText::sprintf('Configure %s', $this->product->title);
		}
		else {
			$documentTitle = $this->product->title.' - '.$this->page->title;
		}
		KenedoPlatform::p()->setDocumentTitle($documentTitle);

		// Process content plugins for description
		if (!empty($this->page->description)) {
			$this->page->description = trim(KenedoPlatform::p()->processContentModifiers($this->page->description));
			ConfigboxViewHelper::processRelativeUrls($this->page->description);
		}

		// Set the template name
		$this->template = ($this->page->layoutname) ? $this->page->layoutname : 'default';

		// These are menu item parameters from Joomla
		$this->params = KenedoPlatform::p()->getAppParameters();
		$this->showPageHeading = ($this->params->get('show_page_heading', 1) && $this->params->get('page_title','') != '');
		$this->pageHeading = (count($pages) > 1) ? $this->product->title . ' - '.$this->page->title : $this->product->title;

		$this->showProductDetailPanes = false;

		if ($this->product->product_detail_panes_in_configurator_steps) {

			$detailPanes = ConfigboxCacheHelper::getProductDetailPanes($this->product->id);

			if (count($detailPanes)) {

				$view = KenedoView::getView('ConfigboxViewProductdetailpanes');
				$view->setProductId($this->productId);
				$view->productDetailPanes = $detailPanes;
				$view->parentView = 'configuratorPage';
				$this->productDetailPanes = $view->getViewOutput($this->product->product_detail_panes_method);
				$this->productDetailPanes =  trim(KenedoPlatform::p()->processContentModifiers($this->productDetailPanes));
				// Deal with faulty relative urls (for when base is set wrong)
				ConfigboxViewHelper::processRelativeUrls($this->productDetailPanes);
				$this->showProductDetailPanes = true;

			}

		}

		/** @noinspection PhpDeprecationInspection */
		$this->configuratorPage = $this->page;

		// Finally add the elements' HTML to the page
		$assignments = ConfigboxCacheHelper::getAssignments();
		$questionIds = (!empty($assignments['page_to_element'][$this->pageId])) ? $assignments['page_to_element'][$this->pageId] : array();

		// That just makes the 'base' question view load (currently views are not auto-loaded)
		KenedoView::getView('ConfigboxViewQuestion');

		// Get the HTML for the page's elements into the view
		foreach ($questionIds as $questionId) {

			$question = ConfigboxQuestion::getQuestion($questionId);

			$questionViewClass = 'ConfigboxViewQuestion_'.ucfirst($question->question_type);
			/** @var ConfigboxViewQuestion $view */
			$view = KenedoView::getView($questionViewClass);
			$view->questionId = $questionId;
			$view->prepareTemplateVars();

			$this->questionsHtml[$questionId] = $view->getViewOutput();

			//LEGACY: Collect element data for page view. Remove with CB 4.0
			/** @noinspection PhpDeprecationInspection */
			$this->elements[$questionId] = $view->question;

			$this->questions[$questionId] = $view->question;
			unset($view);
		}

		if ($this->product->visualization_type == 'shapediver') {
			$this->showVisualization = true;
			$shapeDiverView = KenedoView::getView('ConfigboxViewSdvisualization');
			$shapeDiverView->setProductId($this->productId)->setPositionId($this->cartPositionId);
			$this->visualizationHtml = $shapeDiverView->getHtml();
		}
		elseif ($this->product->visualization_type == 'composite' && ConfigboxProductImageHelper::hasProductImage($this->cartPositionId)) {
			$this->showVisualization = true;
			$visualizationView = KenedoView::getView('ConfigboxViewBlockvisualization');
			$visualizationView->setPositionId($this->cartPositionId);
			$this->visualizationHtml = $visualizationView->getHtml();
		}
		else {
			$this->showVisualization = false;
			$this->visualizationHtml = '';
		}

		$this->canQuickEdit = ConfigboxPermissionHelper::canQuickEdit();
		if ($this->canQuickEdit) {
			$this->pageEditButtonsHtml = ConfigboxQuickeditHelper::renderConfigurationPageButtons($this->page, $this->product);
		}

		$selectionsView = KenedoView::getView('ConfigboxViewBlockpricing');
		$this->selectionsHtml = $selectionsView->setPageId($this->pageId)->getHtml();

		$this->showNetPrices = (ConfigboxPermissionHelper::canGetB2BMode());

		$configuratorData = array(
			'missingProductSelections'	=> $this->missingProductSelections,
			'cartPositionId'			=> $this->cartPositionId,
			'productId'					=> $this->product->id,
			'pageId'					=> $this->page->id,
			'product'					=> $this->product,
			'page'						=> $this->page,
			'questions'					=> $this->questions,
			'dateFormat'				=> KText::_('CALENDAR_DATEFORMAT_JS', 'M d, yy'),
			'blockNavigationOnMissing'  => boolval($this->blockNavigationOnMissing)
		);

		$this->configuratorDataJson = json_encode($configuratorData);

		// json-ld output
		if (CbSettings::getInstance()->get('structureddata') == 1
			&& CbSettings::getInstance()->get('structureddata_in') == 'configurator') {
			$this->structuredData = json_encode($productModel->getStructuredData($this->product->id));
		}

	}

	/**
	 * @param int $pageId
	 * @return $this
	 */
	function setPageId($pageId) {
		$this->pageId = $pageId;
		return $this;
	}

	/**
	 * @param int $cartPositionId
	 * @return $this
	 */
	function setCartPositionId($cartPositionId) {
		$this->cartPositionId = $cartPositionId;
		return $this;
	}

}
