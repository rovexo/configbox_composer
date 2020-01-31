<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewBlockvisualization extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var KStorage $params Joomla module parameters
	 */
	public $params;

	/**
	 * @var int Cart position ID. You need to set if if you use the view as sub view
	 */
	public $cartPositionId;

	/**
	 * @var int ID of the product that should be visualized
	 */
	public $productId;

	/**
	 * @var object[] Data of all images the visualization could contain
	 * @see ConfigboxProductImageHelper::getVisualizationImageSlots
	 */
	public $visualizationSlots;

	/**
	 * @var string URL to the base image (or empty string if there is none)
	 */
	public $urlBaseImage;

	/**
	 * @var string URL to a blank (fully transparent image). Used as placeholder for preloading and as 'cover'
	 */
	public $urlBlankImage;

	/**
	 * @var boolean Indicates if the block title shall be shown. Depends on if there is a title set in the backend settings.
	 */
	public $showBlockTitle;

	/**
	 * @var string Title of the block. Data comes from backend settings
	 */
	public $blockTitle;

	/**
	 * @var string CSS classes for the block's wrapper
	 */
	public $wrapperClasses;

	function prepareTemplateVars() {

		if (empty($this->cartPositionId)) {
			$this->cartPositionId = ConfigboxConfiguration::getInstance()->getPositionId();
		}

		if (empty($this->params)) {
			$this->params = new KStorage();
		}

		$blockTitle = CbSettings::getInstance()->get('blocktitle_visualization', '');

		if ($blockTitle) {
			$this->showBlockTitle = true;
			$this->blockTitle = $blockTitle;
		}
		else {
			$this->showBlockTitle = false;
		}

		$configuration = ConfigboxConfiguration::getInstance($this->cartPositionId);
		$this->productId = $configuration->getProductId();

		$product = KenedoModel::getModel('ConfigboxModelProduct')->getProduct($this->productId);

		$this->urlBaseImage = ($product->baseimage_href) ? $product->baseimage_href : '';
		$this->visualizationSlots = ConfigboxProductImageHelper::getVisualizationImageSlots($this->cartPositionId);
		$this->urlBlankImage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

		$wrapperClasses = array(
			'cb-content',
			'configbox-block',
			'block-visualization',
			$this->params->get('moduleclass_sfx', ''),
		);

		$this->wrapperClasses = trim(implode(' ', $wrapperClasses));

	}

	/**
	 * @param int $cartPositionId
	 * @return $this
	 */
	function setPositionId($cartPositionId) {
		$this->cartPositionId = $cartPositionId;
		return $this;
	}

	/**
	 * @param int $pageId
	 * @deprecated No longer needed
	 * @return $this
	 */
	function setPageId($pageId) {
		return $this;
	}

}
