<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewBlocknavigation extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var KStorage $params Joomla module parameters
	 */
	public $params;

	/**
	 * @var string CSS classes for the block's wrapper
	 */
	public $wrapperClasses;

	/**
	 * @var boolean Indicates if the block title shall be shown. Depends on if there is a title set in the backend settings.
	 */
	public $showBlockTitle;

	/**
	 * @var string Title of the block. Data comes from backend settings
	 */
	public $blockTitle;

	/**
	 * Comes from ConfigboxModelProduct::getPages() and is a bit modified (CSS classes added)
	 * @var ConfigboxPageData[]
	 * @see ConfigboxModelProduct::getPages()
	 */
	public $pages;

	/**
	 * @var ConfigboxProductData
	 */
	public $product;

	/**
	 * @var boolean $showAsTabs As per global or product settings tells if nav should display as tabs
	 */
	public $showAsTabs;

	/**
	 * @var int ID of the product shown
	 */
	public $productId;

	/**
	 * @var string[] CSS classes for the list item
	 */
	public $listItemCssClasses = array();

	/**
	 * @var string[] CSS classes for the anchor tag
	 */
	public $tabLinkClasses = array();

	/**
	 * @var int ID of the current page
	 */
	public $pageId;

	function prepareTemplateVars() {
		
		if (empty($this->params)) {
			$this->params = new KStorage();
		}

		if (empty($this->pageId)) {
			return;
		}

		$blockTitle = CbSettings::getInstance()->get('blocktitle_navigation');

		if ($blockTitle) {
			$this->showBlockTitle = true;
			$this->blockTitle = $blockTitle;
		}
		else {
			$this->showBlockTitle = false;
		}

		if (!$this->pageId) {
			return;
		}

		$prodModel = KenedoModel::getModel('ConfigboxModelProduct');
		$pageModel = KenedoModel::getModel('ConfigboxModelConfiguratorpage');

		$ass = ConfigboxCacheHelper::getAssignments();
		$this->productId = !empty($ass['page_to_product'][$this->pageId]) ? $ass['page_to_product'][$this->pageId] : 0;
		$this->product = $prodModel->getProduct($this->productId);
		$this->showAsTabs = ($this->product->page_nav_show_tabs == 1 or ($this->product->page_nav_show_tabs == 2 && CbSettings::getInstance()->get('page_nav_show_tabs')));
		$this->pages = $pageModel->getPages($this->productId);

		foreach ($this->pages as $page) {
			$this->listItemCssClasses[$page->id] = 'page page-'.$page->id . ( ($page->id == $this->pageId ) ? ' active current':'');
			$this->tabLinkClasses[$page->id] = 'wait-for-xhr';
		}

		$wrapperClasses = array(
			'cb-content',
			'configbox-block',
			'block-navigation',
			$this->params->get('moduleclass_sfx', ''),
			($this->showAsTabs) ? 'tab-style':'',
		);

		$this->wrapperClasses = trim(implode(' ', $wrapperClasses));
		
	}

	/**
	 * @param int $pageId
	 * @return $this
	 */
	function setPageId($pageId) {
		$this->pageId = $pageId;
		return $this;
	}

}
