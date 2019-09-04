<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminproductlistingassignments extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminproductlistingassignments';

	/**
	 * @return ConfigboxModelAdminproductlistingassignments
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproductlistingassignments');
	}

	function getPageTitle() {
		return KText::_('Product Listing Assignments');
	}

}