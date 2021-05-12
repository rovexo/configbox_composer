<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewPosition extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var int Position ID - needs setting before rendering
	 */
	public $positionId;

	/**
	 * @var ConfigboxOrderPositionData Order record position data
	 * @see ConfigboxModelOrderrecord::getOrderRecord
	 */
	public $position;

	/**
	 * @var ConfigboxOrderData
	 */
	public $order;

	/**
	 * @var string Tells what the position is rendered for ('emailNotification, 'popup', 'quotation', 'admin')
	 */
	public $displayPurpose;

	/**
	 * @var boolean Indicates if product and option SKUs should be displayed. Depends on setting 'sku_in_order_record'.
	 */
	public $showSkus;

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
	 * @var string Tells where the position is used ('emailNotification', 'popup', 'quotation')
	 * @depecated Use setDisplayPurpose(') instead
	 */
	public $showIn;

	/**
	 * @var boolean Indicates if the view is used as part of the admin order view
	 * @depecated Use setDisplayPurpose('admin') instead
	 */
	public $inAdmin;

	/**
	 * @var ConfigboxOrderData
	 * @depecated Use order instead
	 */
	public $record;


	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		$this->showSkus = $this->inAdmin || CbSettings::getInstance()->get('sku_in_order_record');

		foreach ($this->order->positions as $position) {
			if ($position->id == $this->positionId) {
				$this->position = $position;
			}
		}

		$positionImageDir = KenedoPlatform::p()->getDirDataCustomer().'/public/position_images';
		$positionImageUrl = KenedoPlatform::p()->getUrlDataCustomer().'/public/position_images';

		if ($this->position->product_image && is_file($positionImageDir.'/'.$this->position->product_image)) {

			$this->positionImageSrc = $positionImageUrl.'/'.$this->position->product_image;

			$dimensions = getimagesize($positionImageDir.'/'.$this->position->product_image);
			$width = $dimensions[0];
			$height = $dimensions[1];

			if ($this->showIn == 'quotation') {
				$maxWidth = 1000;
				$maxHeight = 300;
			}
			else {
				$maxWidth = 500;
				$maxHeight = 500;
			}

			if ($width > $maxWidth) {
				$ratio = $height / $width;
				$width = $maxWidth;
				$height = intval($width * $ratio);
			}

			if ($height > $maxHeight) {
				$ratio = $width / $height;
				$height = $maxHeight;
				$width = intval($height * $ratio);
			}

			$this->positionImageWidth = $width;
			$this->positionImageHeight = $height;

		}


		// For file upload questions, make the output_value into an <a> tag
		if (!empty($this->position->configuration)) {
			foreach ($this->position->configuration as $selection) {
				if ($selection->element_type == 'upload') {
					if ($selection->value) {
						$data = json_decode($selection->value, true);
						$selection->output_value = '<a href="'.$data['url'].'" download>'.$selection->output_value.'</a>';
					}
				}
			}
		}

	}

	/**
	 * @param ConfigboxOrderData $order
	 * @return static
	 */
	function setOrder($order) {
		$this->order = $order;
		$this->record = $order;
		return $this;
	}

	/**
	 * @param int $id
	 * @return static
	 */
	function setPositionId($id) {
		$this->positionId = $id;
		return $this;
	}

	/**
	 * @param string $purpose
	 * @return static
	 */
	function setDisplayPurpose($purpose) {
		$this->displayPurpose = $purpose;

		// Legacy
		$this->showIn = $purpose;

		return $this;
	}

	/**
	 * @param bool $inAdmin
	 * @return static
	 */
	function setInAdmin($inAdmin) {
		$this->inAdmin = $inAdmin;
		return $this;
	}

}