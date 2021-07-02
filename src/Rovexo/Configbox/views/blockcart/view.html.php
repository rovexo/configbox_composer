<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewBlockcart extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var KStorage Joomla module parameters if applicable
	 */
	public $params;

	/**
	 * @var boolean Indicates if the block title shall be shown. Depends on if there is a title set in the backend settings.
	 */
	public $showBlockTitle;

	/**
	 * @var string Title of the block. Data comes from backend settings
	 */
	public $blockTitle;

	/**
	 * @var object Cart data
	 * @see ConfigboxModelCart::getCartDetails
	 */
	public $cartDetails;

	/**
	 * @var string The property name for the cart total in cartDetails. Depends on B2B/B2C mode 'totalNet', 'totalGross'
	 */
	public $totalKey;

	/**
	 * @var string Same as totalKey for recurring price. Depends on B2B/B2C mode 'totalRecurringNet', 'totalRecurringGross'
	 * @see totalKey
	 */
	public $totalKeyRecurring;

	/**
	 * @var string CSS classes for the block's wrapper
	 */
	public $wrapperClasses;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars($tpl = null) {
			
		if (empty($this->params)) {
			$params = new KStorage();
			$this->params = $params;
		}
		
		$model = KenedoModel::getModel('ConfigboxModelCart');
		$cartId = $model->getSessionCartId();
		if ($cartId) {
			$cartDetails = $model->getCartDetails($cartId);
		}
		else {
			$cartDetails = NULL;
		}

		$this->cartDetails = $cartDetails;

		if ( ConfigboxPrices::showNetPrices() ) {
			$this->totalKey = 'totalNet';
			$this->totalKeyRecurring = 'totalRecurringNet';
		}
		else {
			$this->totalKey = 'totalGross';
			$this->totalKeyRecurring = 'totalRecurringGross';
		}

		$this->blockTitle = CbSettings::getInstance()->get('blocktitle_cart', '');
		$this->showBlockTitle = !empty($this->blockTitle);

		$wrapperClasses = array(
			'cb-content',
			'configbox-block',
			'block-cart',
			$this->params->get('moduleclass_sfx', ''),
		);

		$this->wrapperClasses = trim(implode(' ', $wrapperClasses));

	}

}
