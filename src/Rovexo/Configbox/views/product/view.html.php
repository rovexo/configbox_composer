<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewProduct extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'product';

	/**
	 * @return ConfigboxModelProduct
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelProduct');
	}

	/**
	 * @var KStorage Joomla menu item and merged app parameters
	 */
	public $params;

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @var ConfigboxProductData
	 */
	public $product;

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
	 * @var string $urlConfiguratorPage The URL to the product's first configurator page
	 */
	public $urlConfiguratorPage;

	/**
	 * @var string Depending on if it's a configurable product the URL for the configurator page or the add to cart URL.
	 */
	public $urlAddOrConfigure;


	/**
	 * @var string $urlAddToCart The URL that triggers a placing the product in the cart
	 */
	public $urlAddToCart;

	/**
	 * @var string $urlReviews The URL for the product's reviews page
	 */
	public $urlReviews;

	/**
	 * @var boolean $showPricing Indicates if pricing shall be visible to the customer. Depends on customer group settings.
	 */
	public $showPricing;

	/**
	 * @var string JSON-LD Structured Data
	 */
	public $structuredData;

	/**
	 * @var bool Indicates if GA Enhanced Ecommerce Tracking should be used
	 */
	public $useGaEnhancedTracking;

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();

		if (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1') {
			$calls[] = 'configbox/ga::initEcProductPage';
		}

		return $calls;
	}

	function prepareTemplateVars() {

		if (!$this->productId) {
			return;
		}

		$this->product = $this->getDefaultModel()->getProduct($this->productId);

		if (!$this->product || $this->product->published != '1') {
			return;
		}

		// Check if GA tracking should used
		$this->useGaEnhancedTracking = (CbSettings::getInstance()->get('use_ga_enhanced_ecommerce') == '1');

		// Prepare some variables that aren't practical to set up in the model
		$this->urlAddToCart = KLink::getRoute('index.php?option=com_configbox&controller=cart&task=addProductToCart&prod_id='.$this->product->id, false);
		$this->urlReviews = KLink::getRoute('index.php?option=com_configbox&view=reviews&product_id='.$this->product->id, false);

		if ($this->product->isConfigurable) {
			$this->urlConfiguratorPage = KLink::getRoute('index.php?option=com_configbox&view=configuratorpage&prod_id='.$this->product->id.'&page_id='.$this->product->firstPageId);
			$this->urlAddOrConfigure = $this->urlConfiguratorPage;
		} else {
			$this->urlConfiguratorPage = '';
			$this->urlAddOrConfigure = $this->urlAddToCart;
		}

		$this->showPricing = ConfigboxPermissionHelper::canSeePricing();

		// Replace placeholders with data
		$this->product->longdescription = str_replace('{linkbuy}', $this->urlAddToCart, $this->product->longdescription);
		$this->product->longdescription = str_replace('{linkimage}', $this->product->prod_image_href, $this->product->longdescription);
		$this->product->longdescription = str_replace('{linkconfigure}', $this->urlConfiguratorPage, $this->product->longdescription);

		$this->product->longdescription = str_replace('#linkbuy', $this->urlAddToCart, $this->product->longdescription);
		$this->product->longdescription = str_replace('#linkconfigure', $this->urlConfiguratorPage, $this->product->longdescription);

		if ($this->showPricing) {
			$this->product->longdescription = str_replace('{productprice}','<span class="product_price">'.cbprice($this->product->price).'</span>', $this->product->longdescription);
			$this->product->longdescription = str_replace('{productpricerecurring}','<span class="product_price_recurring">'.cbprice($this->product->priceRecurring).'</span>', $this->product->longdescription);
		}
		else {
			$this->product->longdescription = str_replace('{productprice}','', $this->product->longdescription);
			$this->product->longdescription = str_replace('{productpricerecurring}','', $this->product->longdescription);
		}

		$this->product->description = trim(KenedoPlatform::p()->processContentModifiers($this->product->description));
		$this->product->longdescription = trim(KenedoPlatform::p()->processContentModifiers($this->product->longdescription));

		ConfigboxViewHelper::processRelativeUrls($this->product->description);
		ConfigboxViewHelper::processRelativeUrls($this->product->longdescription);

		// Deal with product detail panes
		$this->showProductDetailPanes = false;

		if ($this->product->product_detail_panes_in_product_pages) {

			$detailPanes = ConfigboxCacheHelper::getProductDetailPanes($this->product->id);

			if (count($detailPanes)) {

				$view = KenedoView::getView('ConfigboxViewProductdetailpanes');
				$view->productId = $this->productId;
				$view->productDetailPanes = $detailPanes;
				$view->parentView = 'productPage';
				$this->productDetailPanes = $view->getViewOutput($this->product->product_detail_panes_method);
				$this->productDetailPanes =  trim(KenedoPlatform::p()->processContentModifiers($this->productDetailPanes));
				// Deal with faulty relative urls (for when base is set wrong)
				ConfigboxViewHelper::processRelativeUrls($this->productDetailPanes);
				$this->showProductDetailPanes = true;

			}

		}

		$this->params = KenedoPlatform::p()->getAppParameters();

		// Get the layout name of the product
		$this->template = (!empty($this->product->layoutname)) ? $this->product->layoutname : 'default';

		// json-ld output
		if(CbSettings::getInstance()->get('structureddata') == 1
			&& CbSettings::getInstance()->get('structureddata_in') == 'product') {
			$this->structuredData = json_encode($this->getDefaultModel()->getStructuredData($this->product->id));
		}

	}

	/**
	 * Looks into the listing's specified template name and uses it (or overrides of it)
	 * @param NULL|string $template IGNORED!
	 */
	function renderView($template = NULL) {

		if (KenedoPlatform::p()->getDocumentType() == 'html') {
			$this->addAssets();
		}

		if (empty($this->product)) {
			http_response_code(410);
			parent::renderView('notfound');
			return;
		}

		// List the template paths sorted by how specific they are
		$templates['templateOverride'] = KenedoPlatform::p()->getTemplateOverridePath('com_configbox', 'product', $this->template);
		$templates['customTemplate'] = CONFIGBOX_DIR_CUSTOMIZATION.DS.'templates'.DS.'product'.DS.$this->template.'.php';
		$templates['defaultTemplate'] = KPATH_DIR_CB.DS.'views'.DS.'product'.DS.'tmpl'.DS.'default.php';

		// Try which template to use
		foreach ($templates as $template) {
			if (is_file($template)) {
				require($template);
				break;
			}
		}

	}

	/**
	 * @param int $productId
	 * @return $this
	 */
	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}

}
