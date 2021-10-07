<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminlisting extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminlistings';

	/**
	 * @return ConfigboxModelAdminlistings
	 * @throws Exception
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminlistings');
	}

	function getPageTitle() {
		return KText::_('Product Listing');
	}

	/**
	 * For Wordpress it adds the product's shortcode under the title
	 */
	function prepareTemplateVarsForm() {

		parent::prepareTemplateVarsForm();

		if (KenedoPlatform::getName() == 'wordpress') {
			if ($this->record->id) {
				$this->contentAfterTitle = KText::_('WORDPRESS_RECORD_SHORTCODES_LISTING_HINT').'<br /><br />';
				$this->contentAfterTitle .= '[configbox view="productlisting" id="'.$this->record->id.'"]';

				$this->contentAfterTitle .= '<br /><br />';
				$this->contentAfterTitle .= KText::_('WORDPRESS_PAGE_URL_HINT').'<br /><br />';
				$url = KLink::getRoute('index.php?option=com_configbox&view=productlisting&listing_id='.$this->record->id);
				$this->contentAfterTitle .= '<a target="_blank" href="'.hsc($url).'">'.hsc($url).'</a><br />';
			}
		}

	}

}