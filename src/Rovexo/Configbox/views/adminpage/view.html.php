<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminpage extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminpages';

	/**
	 * @return ConfigboxModelAdminpages
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpages');
	}

	function getPageTitle() {
		return KText::_('Configurator Page');
	}

	/**
	 * For Wordpress it adds the product's shortcode under the title
	 */
	function prepareTemplateVarsForm() {

		parent::prepareTemplateVarsForm();

		if (KenedoPlatform::getName() == 'wordpress') {
			if ($this->record->id) {
				$this->contentAfterTitle = KText::_('WORDPRESS_RECORD_SHORTCODES_PAGE_HINT').'<br /><br />';
				$this->contentAfterTitle .= '[configbox view="configuratorpage" id="'.$this->record->id.'"]';
			}
		}

	}

}