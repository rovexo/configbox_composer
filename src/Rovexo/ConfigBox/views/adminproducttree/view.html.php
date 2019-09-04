<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminproducttree extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = 'adminproducttree';

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
	 * @var string $treeUpdateUrl URL to use for updates.
	 * @see com_configbox.refreshProductTree
	 */
	public $treeUpdateUrl;

	/**
	 * @var boolean Indicates if internal element names shall be shown (depends on backend settings)
	 */
	public $useInternalNames;

	/**
	 * @var boolean Indicates if copy buttons should be shown
	 */
	public $showCopy;

	/**
	 * @var string
	 */
	public $productListsDropdown;

	/**
	 * @return ConfigboxModelAdminproducttree
	 */
	function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminproducttree');
	}

	function getJsInitCallsOnce() {
		$calls = parent::getJsInitCallsOnce();
		$calls[] = 'configbox/productTree::initProductTreeOnce';
		return $calls;
	}

	function getJsInitCallsEach() {
		$calls = parent::getJsInitCallsEach();
		$calls[] = 'configbox/productTree::initProductTreeEach';
		return $calls;
	}

	function prepareTemplateVars() {

		$productIdOnly = KRequest::getInt('only_product_id', NULL);
		$jsonOpenBranchIds = KRequest::getString('open_branch_ids');
		$controller = KRequest::getKeyword('controller');
		$recordId = KRequest::getInt('id');

		$listId = KRequest::getInt('list_id', NULL);
		
		$model = KenedoModel::getModel('ConfigboxModelAdminproducttree');
		
		$tree = $model->getTree(false, $productIdOnly, $listId);
		$openIds = array('products'=>array(),'pages'=>array(),'questions'=>array());
		$selectedRecord = array('products'=>array(),'pages'=>array(),'questions'=>array());

		// In case we got open tree ids from the request, replace the default one
		if ($jsonOpenBranchIds) {
			$openIds = json_decode($jsonOpenBranchIds, true);
		}

		// Hack something into it: If the page loads a product, page or question, then have the right tree branch open.
		if ($controller == 'adminelements') {
			if ($recordId) {
				$openIds['questions'][] = $recordId;
				$selectedRecord['questions'][$recordId] = true;
			}
		}

		if ($controller == 'adminpages') {
			if ($recordId) {
				$openIds['pages'][] = $recordId;
				$selectedRecord['pages'][$recordId] = true;
			}
		}

		if ($controller == 'adminproducts') {
			if ($recordId) {
				$openIds['products'][] = $recordId;
				$selectedRecord['products'][$recordId] = true;
			}
		}

		// Now traverse up the tree, find the expanded branches and mark their parent branches as opened
		foreach ($tree as &$product) {

			$product['active'] = (!empty($selectedRecord['products'][$product['id']]));

			foreach ($product['pages'] as &$page) {

				$page['active'] = (!empty($selectedRecord['pages'][$page['id']]));

				if (in_array($page['id'], $openIds['pages'])) {
					$openIds['products'][] = $product['id'];
				}

				foreach($page['questions'] as &$question) {

					$question['active'] = (!empty($selectedRecord['questions'][$question['id']]));

					if (in_array($question['id'], $openIds['questions'])) {
						$openIds['products'][] = $product['id'];
						$openIds['pages'][] = $page['id'];
					}

				}
			}

		}

		$openIds['questions'] = array();

		$this->treeUpdateUrl = KLink::getRoute('index.php?option=com_configbox&controller=adminproducttree&lang='.KText::getLanguageCode().'&format=raw', false);

		$this->tree = $tree;
		$this->openIds = $openIds;

		$this->useInternalNames = CbSettings::getInstance()->get('use_internal_question_names');

		$this->showCopy = ConfigboxAddonHelper::hasAddon('copying');

		// product listing select dropdown box
		$listingsModel = KenedoModel::getModel('ConfigboxModelAdminlistings');
		$listings = $listingsModel->getRecords();
		$options = array(KText::_('No Listing Filter'));
		foreach ($listings as $listing) {
			$options[$listing->id] = $listing->title;
		}
		$this->productListsDropdown = KenedoHtml::getSelectField('product_tree_list_id', $options, $listId, 0, false);

	}
	
}