<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdmincalcformula_elementattribute extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var int
	 */
	public $productId;

	/**
	 * @var array[] Array of array of xref data objects, grouped by element ID then xref ID
	 * @see ConfigboxRulesHelper::getAnswers
	 */
	public $elementXrefs;

	/**
	 * @var object[] Array of xref data objects, grouped by element ID
	 * @see ConfigboxRulesHelper::getQuestions
	 */
	public $questions;

	/**
	 * @var array[] Infos about usable element attributes (like selected option, custom fields etc)
	 * @see ConfigboxConditionElementAttribute::getElementAttributes
	 */
	public $elementAttributes;

	/**
	 * @var int Product ID used for filtering the element conditions
	 */
	public $selectedProductId;

	/**
	 * @var int Page ID used for filtering the element conditions
	 */
	public $selectedPageId;

	/**
	 * @var string Dropdown HTML for the product filter
	 */
	public $productFilterHtml;

	/**
	 * @var string[] Array of HTML with dropdowns for page filtering, grouped by product ID
	 */
	public $pageFilterDropdowns;

	/**
	 * @var boolean Indicates if internal question names shall be displayed
	 */
	public $useInternalQuestionNames;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		$this->selectedPageId = KRequest::getInt('filter_page_id', 0);

		$this->selectedProductId = $this->productId;

		$productModel = KenedoModel::getModel('ConfigboxModelAdminproducts');
		$products = $productModel->getRecords(array('adminproducts.id'=>$this->productId));

		// Get the product id for filtering
		$this->selectedProductId = 0;
		if ($this->selectedPageId) {
			// With a page filter in place, get the corresponding product id
			$ass = ConfigboxCacheHelper::getAssignments();
			$this->selectedProductId = isset($ass['page_to_product'][$this->selectedPageId]) ? $ass['page_to_product'][$this->selectedPageId] : 0;
		}
		else {
			// Otherwise get the first product id
			foreach ($products as $item) {
				$this->selectedProductId = $item->id;
				break;
			}
		}

		$productFilterOptions = array();
		foreach ($products as $item) {
			$productFilterOptions[$item->id] = $item->title;
		}
		$this->productFilterHtml = KenedoHtml::getSelectField('product-filter', $productFilterOptions, $this->selectedProductId);


		// Prepare the filter drop-downs for pages
		$pagesModel = KenedoModel::getModel('ConfigboxModelAdminpages');
		$pages = $pagesModel->getRecords(array('adminpages.product_id' => $this->productId), array(), array('propertyName' => 'ordering', 'direction'=>'ASC'));

		$filterOptions = array();
		foreach ($pages as $page) {

			if (empty($filterOptions[$page->product_id])) {
				$filterOptions[$page->product_id] = array(
					0 => KText::_('All pages'),
				);
			}

			$filterOptions[$page->product_id][$page->id] = $page->title;
		}

		$this->pageFilterDropdowns = array();
		foreach ($filterOptions as $productId=>$pageOptions) {
			$cssClasses = 'page-filter page-filter-'. $productId;
			$this->pageFilterDropdowns[$productId] = KenedoHtml::getSelectField('page-filter-'. $productId, $pageOptions, $this->selectedPageId, 0, false, $cssClasses);
		}

		// Prepare the element list
		$this->questions = ConfigboxRulesHelper::getQuestions($this->productId);

		$this->useInternalQuestionNames = CbSettings::getInstance()->get('use_internal_question_names');

		// Prepare the element attribute list
		$this->elementAttributes = ConfigboxCalcTerm::getTerm('ElementAttribute')->getElementAttributes();

		// Prepare the xref list for element conditions
		$answers = ConfigboxRulesHelper::getAnswers($this->productId);

		// Group the xrefs in elements
		$this->elementXrefs = array();
		foreach ($answers as $answer) {
			$this->elementXrefs[$answer->question_id][$answer->id] = $answer;
		}

		$this->addViewCssClasses();

	}

	function setProductId($productId) {
		$this->productId = $productId;
		return $this;
	}

}
