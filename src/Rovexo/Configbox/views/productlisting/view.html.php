<?php
defined('CB_VALID_ENTRY') or die();


class ConfigboxViewProductlisting extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'productlisting';

	/**
	 * @var int
	 */
	public $listingId;

	/**
	 * @var ConfigboxListingData Listing Product listing information
	 */
	public $listing;

	/**
	 * @var ConfigboxProductData[] $products Array holding objects with product data
	 */
	public $products;

	/**
	 * @var float[]\null[] Array with ratings of the listing's products. Key is the product ID.
	 */
	public $productRatingAverages;

	/**
	 * @var int[] Array with ratings of the listing's products. Key is the product ID.
	 */
	public $productRatingCounts;

	/**
	 * @var string[] Array with HTML for the products' rating stars. Key is the product ID.
	 * @see ConfigboxRatingsHelper::getRatingStarHtml()
	 */
	public $productRatingStarHtml;

	/**
	 * @var string[] Array with HTML for the products' review count. Key is the product ID.
	 * @see ConfigboxRatingsHelper::getRatingCountHtml()
	 */
	public $productRatingCountHtml;

	/**
	 * @var boolean $showPricing Indicates if pricing shall be visible to the customer. Depends on customer group settings.
	 */
	public $showPricing;

	/**
	 * @var boolean $showPageHeading Whether to show the page heading or not
	 */
	public $showPageHeading;

	/**
	 * @var string $pageHeading The text of the page heading
	 */
	public $pageHeading;

	/**
	 * @var KStorage $params Object holding Joomla parameters for that page
	 */
	public $params;

	/**
	 * @var string[] URLs to the products' configurator pages (array of strings with product IDs as keys)
	 */
	public $urlsConfiguratorPage;

	/**
	 * @var string[] URLs for the products' add to cart request (array of strings with product IDs as keys)
	 */
	public $urlsAddToCart;

	/**
	 * @var string[] URLs to the products' detail pages (array of strings with product IDs as keys)
	 */
	public $urlsProductPage;

	/**
	 * @var string[] URLs to the products' review pages (array of strings with product IDs as keys)
	 */
	public $urlsReviewsPage;

	/**
	 * @var string[] URLs to use for each product's image and title (array of strings with product IDs as keys)
	 */
	public $urlsProductLink;

	/**
	 * @var bool
	 */
	public $canQuickEdit;

	/**
	 * @var bool Indicates if GA Enhanced Ecommerce Tracking should be used
	 */
	public $useGaEnhancedTracking;

	/**
	 * @return ConfigboxModelProductlisting
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelProductlisting');
	}

	function getStyleSheetUrls() {
		$urls = parent::getStyleSheetUrls();
		$urls[] = KenedoPlatform::p()->getUrlAssets().'/css/productlisting.css';
		return $urls;

	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/productlisting::initListingPage';

		if (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1') {
			$calls[] = 'configbox/ga::initEcListingPage';
		}

		return $calls;
	}

	function display() {

		$this->prepareTemplateVars();
		if (empty($this->listing)) {
			http_response_code(410);
			parent::renderView('notfound');
			return;
		}

		// In listing settings you can pick a template, use 'default' in case nothing is selected.
		$template = (!empty($this->listing->layoutname)) ? $this->listing->layoutname : 'default';

		$this->renderView($template);
	}

	function prepareTemplateVars() {

		$this->canQuickEdit = ConfigboxPermissionHelper::canQuickEdit();

		// Get the requested product listing id
		if (!$this->listingId) {
			return;
		}

		// Get listing data
		$this->listing = $this->getDefaultModel()->getProductListing($this->listingId);

		// Stop if the listing isn't there
		if (empty($this->listing) || $this->listing->published == 0) {
			return;
		}

		// Check if GA tracking should used
		$this->useGaEnhancedTracking = (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1');

		$this->showPricing = ConfigboxPermissionHelper::canSeePricing();

		// Get the layout name of the listing
		$this->template = (!empty($this->listing->layoutname)) ? $this->listing->layoutname : 'default';

		// Params is stuff like show headings etc.
		$this->params = KenedoPlatform::p()->getAppParameters();
		$this->showPageHeading = ($this->params->get('show_page_heading', 1) && $this->params->get('page_title','') != '');
		$this->pageHeading = ($this->params->get('page_heading','') != '') ? $this->params->get('page_heading','') : $this->listing->title;

		// Get the products of that listing
		$this->products = $this->getDefaultModel()->getProductsForListing($this->listingId);

		$productRatingInfos = KenedoModel::getModel('ConfigboxModelReviews')->getRatingInfoListing($this->listingId);

		foreach ($productRatingInfos as $productId=>$info) {
			$this->productRatingAverages[$productId] = $info['average'];
			$this->productRatingCounts[$productId] = $info['count'];
			$this->productRatingStarHtml[$productId] = ConfigboxRatingsHelper::getRatingStarHtml($info['average']);
			$this->productRatingCountHtml[$productId] = ConfigboxRatingsHelper::getRatingCountHtml($productId, $info['count']);
		}

		if ($this->listing->product_sorting == 0) {
			usort($this->products, array('ConfigboxModelProductlisting', 'sortProductsByTitle'));
		}

		// Augment product data
		foreach ($this->products as $key=>$product) {

			// Process description with content plugins
			$product->description = trim(KenedoPlatform::p()->processContentModifiers($product->description));
			ConfigboxViewHelper::processRelativeUrls($product->description);

			if ($product->product_details_page_type == 'configbox_page') {
				$this->urlsProductPage[$product->id] = KLink::getRoute('index.php?option=com_configbox&view=product&prod_id='.$product->id, false);
			}
			elseif($product->product_details_page_type == 'cms_page') {
				$url = $product->product_details_url;
				if (empty($url)) {
					KLog::log('Product ID '.$product->id.' has detail page type cms_page, but no URL set. Not showing the details button.', 'warning');
					$product->show_product_details_button = false;
				}
				if (strpos($url, 'http') !== 0) {
					$url = KPATH_URL_BASE.'/'.ltrim($url, '/');
				}

				$this->urlsProductPage[$product->id] = $url;
			}
			else {
				$product->show_product_details_button = false;
			}

			// Set URLs
			$this->urlsConfiguratorPage[$product->id] = KLink::getRoute('index.php?option=com_configbox&view=configuratorpage&prod_id='.$product->id.'&page_id='.$product->firstPageId, false);
			$this->urlsReviewsPage[$product->id] = KLink::getRoute('index.php?option=com_configbox&view=reviews&format=raw&product_id='.$product->id, false);
			$this->urlsAddToCart[$product->id] = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=addProductToCart&prod_id='.$product->id, false);

			if ($product->isConfigurable) {
				$this->urlsProductLink[$product->id] = $this->urlsConfiguratorPage[$product->id];
			}
			elseif($product->product_details_page_type != 'none') {
				$this->urlsProductLink[$product->id] = $this->urlsProductPage[$product->id];
			}
			else {
				$this->urlsProductLink[$product->id] = $this->urlsAddToCart[$product->id];
			}
		}

	}

	/**
	 * Looks into the listing's specified template name and uses it (or overrides of it)
	 * @param NULL|string $template IGNORED!
	 */
	function renderView($template = NULL) {

		// Load assets (we got to since we override the base method)
		if (KenedoPlatform::p()->getDocumentType() == 'html') {
			$this->addAssets();
		}

		if (empty($template)) {
			$template = 'default';
		}

		// List the template paths sorted by how specific they are
		$templates['templateOverride'] = KenedoPlatform::p()->getTemplateOverridePath('com_configbox', 'productlisting', $template);
		$templates['customTemplate'] = KenedoPlatform::p()->getDirCustomization().DS.'templates'.DS.'productlisting'.DS.$template.'.php';
		$templates['defaultTemplate'] = KPATH_DIR_CB.DS.'views'.DS.'productlisting'.DS.'tmpl'.DS.$template.'.php';

		// Try which template to use
		foreach ($templates as $template) {
			if (file_exists($template)) {
				require($template);
				break;
			}
		}

	}

	/**
	 * @param int $listingId
	 * @return $this
	 */
	function setListingId($listingId) {
		$this->listingId = $listingId;
		return $this;
	}

}
