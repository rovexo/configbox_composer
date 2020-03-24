<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewBlockpricing extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var KStorage $params Joomla module parameters
	 */
	public $params;

	/**
	 * @var string $cssClass Either 'pricing-regular' or 'pricing-recurring'
	 */
	public $cssClass;

	/**
	 * @var boolean Indicates if the block title shall be shown. Depends on if there is a title set in the backend settings.
	 */
	public $showBlockTitle;

	/**
	 * @var string Title of the block. Data comes from backend settings
	 */
	public $blockTitle;

	/**
	 * @var bool If the user can see pricing
	 */
	public $canSeePricing;

	/**
	 * @var array $pricing Big array with all prices, quantity, tax, delivery, payment
	 * @see ConfigboxModelCartposition::getPricing()
	 */
	public $pricing;

	/**
	 * @var int $productId
	 */
	public $productId;

	/**
	 * @var int $pageId Page ID the visitor is currently on
	 */
	public $pageId;

	/**
	 * @var bool $showPages Indicates if individual pages should be displayed
	 */
	public $showPages;

	/**
	 * @var bool Indicates if individual questions should be displayed
	 */
	public $showQuestions;

	/**
	 * @var bool $showPrices Indicates if pricing should be displayed
	 */
	public $showPrices;

	/**
	 * @var bool $showQuestionPrices Indicates if individual question prices should be displayed
	 */
	public $showQuestionPrices;

	/**
	 * @var bool $showTaxes Indicates if tax summary should be displayed
	 */
	public $showTaxes;

	/**
	 * @var bool $showDelivery Indicates if delivery method infos should be displayed
	 */
	public $showDelivery;

	/**
	 * @var bool $showPayment Indicates if payment method infos should be displayed
	 */
	public $showPayment;

	/**
	 * @var bool $showCartButton Indicates if add-to-cart button should be displayed
	 */
	public $showCartButton;

	/**
	 * @var bool $showNetInB2c Indicates if net total should be displayed when in B2C mode
	 */
	public $showNetInB2c;

	/**
	 * @var int $expandPages If pages should be expanded initially 0 for no, 1 for yes, 2 for only the current one.
	 */
	public $expandPages;

	/**
	 * @var bool $isRegular Indicates if the template deals with regular pricing (otherwise recurrig pricing)
	 */
	public $isRegular;

	/**
	 * @var string $mode Either 'b2b' or 'b2c', depending on customer group setting
	 */
	public $mode;

	/**
	 * @var string $labelKey Either 'priceLabel' or 'priceRecurringLabel'
	 */
	public $labelKey;

	/**
	 * @var string $productPriceKey Either 'productPrice' or 'productPriceRecurring'
	 */
	public $productPriceKey;

	/**
	 * @var string $priceKey Either 'price' or 'priceRecurring'
	 */
	public $priceKey;

	/**
	 * @var string $pricePerItemGrossKey Either 'pricePerItemGross' or 'pricePerItemRecurringGross'
	 */
	public $pricePerItemGrossKey;

	/**
	 * @var string $pricePerItemNetKey Either 'pricePerItemNet' or 'pricePerItemRecurringNet'
	 */
	public $pricePerItemNetKey;

	/**
	 * @var string $totalGrossKey Either 'priceGross' or 'priceRecurringGross'
	 */
	public $totalGrossKey;

	/**
	 * @var string $totalNetKey Either 'priceNet' or 'priceRecurringNet'
	 */
	public $totalNetKey;

	/**
	 * @var string $addToCartLinkClasses CSS classes for the add-to-cart button
	 */
	public $addToCartLinkClasses;

	/**
	 * @var string $addToCartLink URL for adding the product to the cart
	 */
	public $addToCartLink;

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

	function display() {

		if (empty($this->params)) {
			$this->params = new KStorage();
		}

		$blockTitle = CbSettings::getInstance()->get('blocktitle_pricing');

		if ($blockTitle) {
			$this->showBlockTitle = true;
			$this->blockTitle = $blockTitle;
		}
		else {
			$this->showBlockTitle = false;
		}

		$wrapperClasses = array(
			'cb-content',
			'configbox-block',
			'block-pricing',
			$this->params->get('moduleclass_sfx', ''),
		);

		$this->wrapperClasses = trim(implode(' ', $wrapperClasses));

		$positionModel = KenedoModel::getModel('ConfigboxModelCartposition');
		
		$positionId = $positionModel->getId();

		if (!$positionId) {
			return;
		}

		$position = $positionModel->getPosition($positionId);

		$this->pricing = $positionModel->getPricing();

		if (!$this->pricing) {
			return;
		}

		$productModel = KenedoModel::getModel('ConfigboxModelProduct');
		$product = $productModel->getProduct($position->prod_id);

		if ($this->pageId === NULL) {
			$this->pageId = $product->firstPageId;
		}

		$this->mode = (ConfigboxPermissionHelper::canGetB2BMode()) ? 'b2b':'b2c';
		$this->canSeePricing = ConfigboxPermissionHelper::canSeePricing();
		$this->showDelivery = (CbSettings::getInstance()->get('disable_delivery') == false && $product->pm_show_delivery_options);
		$this->showPayment = $product->pm_show_payment_options;
		$this->showNetInB2c = $product->pm_show_net_in_b2c;

		$this->productId = $position->prod_id;

		$this->addToCartLink = '';
		$this->addToCartLinkClasses = 'trigger-add-to-cart wait-for-xhr';

		$this->assign('deliveryNetKey',		'priceNet');
		$this->assign('shippingNetTaxKey',	'priceTax');
		$this->assign('shippingNetGrossKey','priceGross');
			
		$this->assign('paymentNetKey',		'priceNet');
		$this->assign('paymentTaxKey',		'priceTax');
		$this->assign('paymentGrossKey',	'priceGross');

		if ($product->pm_regular_show_overview) {
			
			$this->assignRef('showPrices', 			$product->pm_regular_show_prices);
			$this->assignRef('showPages', 			$product->pm_regular_show_categories);
			$this->assignRef('showQuestions', 		$product->pm_regular_show_elements);
			$this->assignRef('showQuestionPrices', 	$product->pm_regular_show_elementprices);
			$this->assignRef('expandPages', 		$product->pm_regular_expand_categories);
			$this->assign('showTaxes', 				$product->pm_regular_show_taxes);
			$this->assign('showCartButton', 		$product->pm_regular_show_cart_button);
			$this->assign('cssClass', 				'pricing-regular');
			$this->assign('priceKey', 				'price');
			$this->assign('productPriceKey', 		'productPrice');
			$this->assign('labelKey', 				'priceLabel');
			$this->assign('totalKey', 				'price');
			
			$this->assign('pricePerItemNetKey', 	'pricePerItemNet');
			$this->assign('pricePerItemTaxKey', 	'pricePerItemTax');
			$this->assign('pricePerItemGrossKey', 	'pricePerItemGross');
			
			$this->assign('taxRateKey',				'taxRate');
			
			$this->assign('totalNetKey',			'priceNet');
			$this->assign('totalTaxKey',			'priceTax');
			$this->assign('totalGrossKey',			'priceGross');
			
			$this->assign('isRegular',			true);


            if (KenedoPlatform::getName() == 'magento') {
                $this->assign('showCartButton', 		0);
            }


            $regularTree = $this->getViewOutput();
			
		}
		else {
			$regularTree = '';
		}
		
		if ($product->use_recurring_pricing && $product->pm_recurring_show_overview) {
			
			$this->assignRef('showPrices', 			$product->pm_recurring_show_prices);
			$this->assignRef('showPages', 			$product->pm_recurring_show_categories);
			$this->assignRef('showQuestions', 		$product->pm_recurring_show_elements);
			$this->assignRef('showQuestionPrices', 	$product->pm_recurring_show_elementprices);
			$this->assignRef('expandPages', 		$product->pm_recurring_expand_categories);
			$this->assign('showTaxes', 				$product->pm_recurring_show_taxes);
			$this->assign('showCartButton', 		$product->pm_recurring_show_cart_button);
			$this->assign('cssClass', 				'pricing-recurring');
			$this->assign('priceKey', 				'priceRecurring');
			$this->assign('productPriceKey', 		'productPriceRecurring');
			$this->assign('labelKey', 				'priceRecurringLabel');
			$this->assign('totalKey', 				'priceRecurring');
			
			$this->assign('pricePerItemNetKey', 	'pricePerItemRecurringNet');
			$this->assign('pricePerItemTaxKey', 	'pricePerItemRecurringTax');
			$this->assign('pricePerItemGrossKey', 	'pricePerItemRecurringGross');
			
			$this->assign('taxRateKey',				'taxRateRecurring');
			
			$this->assign('totalNetKey',			'priceRecurringNet');
			$this->assign('totalTaxKey',			'priceRecurringTax');
			$this->assign('totalGrossKey',			'priceRecurringGross');
			
			$this->assign('isRegular',				false);


            if (KenedoPlatform::getName() == 'magento') {
                $this->assign('showCartButton', 		0);
            }


            $recurringTree = $this->getViewOutput();
		
		}
		else {
			$recurringTree = '';
		}

		$this->assign('priceKey', ( ConfigboxPrices::showNetPrices() ) ? 'priceNet':'priceGross');
		?>
		<div class="<?php echo hsc($this->wrapperClasses);?>">

			<?php if ($this->showBlockTitle) { ?>
				<h2 class="block-title"><?php echo hsc($this->blockTitle);?></h2>
			<?php } ?>

			<?php
			if ($product->pm_show_regular_first) {
				echo $regularTree."\n".$recurringTree;
			}
			else {
				echo $recurringTree."\n".$regularTree;
			}
			$this->renderView('footer');
			
			?>
		</div>
		<?php
		
	}

	/**
	 * Sets the page ID that should be expanded initially
	 * @param int $pageId
	 * @return ConfigboxViewBlockpricing
	 */
	function setPageId($pageId) {
		$this->pageId = $pageId;
		return $this;
	}

}
