<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewEmailtemplate extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var bool Indicates if the shop logo should be shown or not. Depends on if a logo was uploaded at "Store Information"
	 */
	public $useShopLogo;

	/**
	 * @var int The px width of the logo as it should be displayed (is not the actual image dimension)
	 */
	public $shopLogoWidth;

	/**
	 * @var int The px height of the logo as it should be displayed (is not the actual image dimension)
	 */
	public $shopLogoHeight;

	/**
	 * @var string Complete URL to the shop logo image
	 */
	public $shopLogoUrl;

	/**
	 * @var string HTML for the email content (excluding the decorative wrapper)
	 */
	public $emailContent;

	/**
	 * @var object Store information (see backend: Store information)
	 * @see ConfigboxModelAdminshopdata::getShopdata
	 */
	public $shopData;

	/**
	 * @var string $shopCountryName Country name of the shop data's country ID
	 */
	public $shopCountryName;

	/**
	 * @var string $shopStateName State name of the shop data's state ID
	 */
	public $shopStateName;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {
			
		$this->shopData = ConfigboxStoreHelper::getStoreRecord();

		$this->shopCountryName = ConfigboxCountryHelper::getCountryName($this->shopData->country_id);
		$this->shopStateName = ConfigboxCountryHelper::getStateName($this->shopData->state_id);

		$maxWidth = 250;
		$maxHeight = 60;
		
		$filePath = KenedoPlatform::p()->getDirDataStore().'/public/shoplogos/'. $this->shopData->shoplogo;

		if (is_file($filePath)) {

			$this->useShopLogo = true;
			$this->shopLogoUrl = KenedoPlatform::p()->getUrlDataStore().'/public/shoplogos/'. $this->shopData->shoplogo;
				
			$image = new ConfigboxImageResizer($filePath);
				
			if ($image->width > $maxWidth || $image->height > $maxHeight) {
				$dimensions = $image->getDimensions($maxWidth, $maxHeight, 'containment');
				$this->shopLogoWidth = intval($dimensions['optimalWidth']);
				$this->shopLogoHeight = intval($dimensions['optimalHeight']);
			}
			else {
				$this->shopLogoWidth = $image->width;
				$this->shopLogoHeight = $image->height;
			}
				
		}
		else {
			$this->useShopLogo = false;
		}
		
	}
	
}