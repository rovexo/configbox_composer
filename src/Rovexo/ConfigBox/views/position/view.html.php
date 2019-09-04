<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewPosition extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var ConfigboxOrderPositionData Order record position data
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $position;

	/**
	 * @var boolean Indicates if product and option SKUs should be displayed. Depends on setting 'sku_in_order_record'.
	 */
	public $showSkus;

	/**
	 * @var boolean Indicates if the view is used as part of the admin order view
	 */
	public $inAdmin;

	/**
	 * @var string Complete URL to the position image
	 */
	public $positionImageSrc;

	/**
	 * @var int Pixel width of the position image (as it should be displayed).
	 */
	public $positionImageWidth;

	/**
	 * @var int Pixel height of the position image (as it should be displayed).
	 */
	public $positionImageHeight;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

}