<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminproductdetailpanes extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminproductdetailpanes';

	/**
	 * @return ConfigboxModelAdminproductdetailpanes
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproductdetailpanes');
	}

	function getPageTitle() {
		return KText::_('Product Detail Panes');
	}

}