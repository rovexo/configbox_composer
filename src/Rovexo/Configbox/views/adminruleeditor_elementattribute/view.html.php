<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewAdminRuleeditor_elementattribute extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var array[] Array of array of answer data objects, grouped by question ID then answer ID
	 * @see ConfigboxRulesHelper::getAnswers
	 */
	public $questionAnswers;

	/**
	 * @var object[] Array of answer data objects, grouped by question ID
	 * @see ConfigboxRulesHelper::getQuestions
	 */
	public $questions;

	/**
	 * @var array[] Infos about usable question attributes (like selected answer, custom fields etc)
	 * @see ConfigboxConditionElementAttribute::getElementAttributes
	 */
	public $questionAttributes;

	/**
	 * @var int Page ID used for filtering the question conditions
	 */
	public $selectedPageId;

	/**
	 * @var bool Indicates if there should be a filter for questions
	 */
	public $showQuestionFilters;

	/**
	 * @var string HTML with dropdowns for page filtering,
	 */
	public $pageFilterHtml;

	/**
	 * @var ConfigboxViewAdminRuleeditor
	 */
	public $ruleEditorView;

	/**
	 * @var int $productId
	 */
	public $productId;

	/**
	 * @var int $pageId
	 */
	public $pageId;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function prepareTemplateVars() {

		// The product ID to use here
		$this->productId = $this->ruleEditorView->productId;

		// Get the page id for filtering
		$this->selectedPageId = $this->ruleEditorView->pageId;

		// Prepare the question list
		$this->questions = ConfigboxRulesHelper::getQuestions($this->productId);

		if (count($this->questions) > 10) {

			// Prepare the filter drop-downs for pages
			$pagesModel = KenedoModel::getModel('ConfigboxModelAdminpages');
			$pages = $pagesModel->getRecords(array('adminpages.product_id'=>$this->productId), array(), array('propertyName' => 'ordering', 'direction'=>'ASC'));

			$options = array(0 => KText::_('All pages'));
			foreach ($pages as $page) {
				$options[$page->id] = $page->title;
			}

			$this->showQuestionFilters = true;
			$this->pageFilterHtml = KenedoHtml::getSelectField('page-filter', $options, $this->selectedPageId, 0, false);

		}
		else {
			$this->showQuestionFilters = false;
			$this->pageFilterHtml = '';
		}


		// Prepare the question attribute list
		$this->questionAttributes = ConfigboxCondition::getCondition('ElementAttribute')->getElementAttributes();

		// Prepare the answer list for question conditions
		$answers = ConfigboxRulesHelper::getAnswers($this->productId);

		// Group the answers in questions
		$this->questionAnswers = array();
		foreach ($answers as $answer) {
			$this->questionAnswers[$answer->question_id][$answer->id] = $answer;
		}

		$this->addViewCssClasses();

	}
	
}
