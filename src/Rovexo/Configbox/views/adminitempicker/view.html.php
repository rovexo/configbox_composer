<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminitempicker extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var array $tree
	 * @see ConfigboxModelAdminproducttree::getTree
	 */
	public $tree;

	/**
	 * @var array[] - Array with data for checking what tree nodes shall be open
	 * @see ConfigboxViewAdminproducttree::prepareTemplateVars
	 */
	public $openIds;

	/**
	 * @var boolean Indicates if internal element names shall be shown (depends on backend settings)
	 */
	public $useInternalNames;

	/**
	 * @var int To limit the item picker to certain product
	 */
	public $productId;

	/**
	 * @return ConfigboxModelAdminproducttree
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproducttree');
	}

	function prepareTemplateVars() {

		$this->tree = $this->getDefaultModel()->getTree(false, $this->productId);

		if (KRequest::getString('open_branch_ids')) {
			$this->openIds = json_decode(KRequest::getString('open_branch_ids'), true);
		}
		else {
			$this->openIds = array('products'=>array(), 'pages'=>array(), 'questions'=>array());
		}

		$this->useInternalNames = CbSettings::getInstance()->get('use_internal_question_names');

		$this->addViewCssClasses();

	}

	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}
	
}