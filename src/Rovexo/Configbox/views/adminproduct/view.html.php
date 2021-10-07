<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminproduct extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminproducts';

	/**
	 * @return ConfigboxModelAdminproducts
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproducts');
	}

	function getPageTitle() {
		return KText::_('Product');
	}

    /**
     * @inheritDoc
     */
    function getJsInitCallsOnce() {
        $calls = parent::getJsInitCallsOnce();
        $calls[] = 'configbox/adminShapediverV2::initBackendPropsOnce';
        return $calls;
    }

	/**
	 * @inheritDoc
	 */
	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/adminShapediverV2::initBackendPropsEach';
		return $calls;
	}

    /**
	 * For Wordpress it adds the product's shortcode under the title
	 */
	function prepareTemplateVarsForm() {

		parent::prepareTemplateVarsForm();

		if (ConfigboxWordpressHelper::isWcIntegration()) {
			$this->contentAfterTitle = KText::_('WOOCOMMERCE_HINT_PRODUCT_ASSIGNMENT');
		}
		elseif (KenedoPlatform::getName() == 'wordpress') {
			if ($this->record->id) {
				$this->contentAfterTitle = KText::_('WORDPRESS_RECORD_SHORTCODES_PRODUCT_HINT').'<br /><br />';
				$this->contentAfterTitle .= '[configbox view="product" id="'.$this->record->id.'"]';

				$this->contentAfterTitle .= '<br /><br />';
				$this->contentAfterTitle .= KText::_('WORDPRESS_PRODUCT_URL_HINT').'<br /><br />';
				$url = KLink::getRoute('index.php?option=com_configbox&view=product&prod_id='.$this->record->id);
				$this->contentAfterTitle .= '<a target="_blank" href="'.hsc($url).'">'.hsc($url).'</a><br />';

			}
		}

	}

}
