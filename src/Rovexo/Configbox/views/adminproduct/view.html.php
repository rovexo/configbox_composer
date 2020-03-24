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
        $calls[] = 'configbox/adminShapediver::initBackendPropsOnce';
        return $calls;
    }

    /**
	 * For Wordpress it adds the product's shortcode under the title
	 */
	function prepareTemplateVarsForm() {

		parent::prepareTemplateVarsForm();

		if (KenedoPlatform::getName() == 'wordpress') {
			if ($this->record->id) {
				$this->contentAfterTitle = KText::_('WORDPRESS_RECORD_SHORTCODES_PRODUCT_HINT').'<br /><br />';
				$this->contentAfterTitle .= '[configbox view="product" id="'.$this->record->id.'"]';
			}
		}

	}

}
