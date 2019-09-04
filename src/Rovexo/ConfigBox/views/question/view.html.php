<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxViewQuestion extends KenedoView {

	public $component = 'com_configbox';
	public $controllerName = '';

	/**
	 * @var int
	 */
	public $questionId;

	/**
	 * @var ConfigboxQuestion
	 */
	public $question;

	/**
	 * @var string CSS ID for the question's wrapper div
	 */
	public $questionCssId;

	/**
	 * @var string CSS classes for the question's wrapper div
	 */
	public $questionCssClasses;

	/**
	 * @var string data attributes for the questoin's wrapper div
	 */
	public $questionDataAttributes;

	/**
	 * @var ConfigboxProductData
	 * @see ConfigboxModelProduct::getProduct()
	 */
	public $product;

	/**
	 * @var string $priceLabel The label for regular price (comes from product settings)
	 * @see ConfigboxModelProduct::getProduct()
	 */
	public $priceLabel = '';

	/**
	 * @var string $priceLabelRecurring The label for the recurring price (comes from product settings)
	 * @see ConfigboxModelProduct::getProduct()
	 */
	public $priceLabelRecurring = '';

	/**
	 * @var boolean $showPricing Indicates if pricing shall be visible to the customer. Depends on customer group settings.
	 */
	public $showPricing;

	/**
	 * @var mixed $selection Current selection for the question
	 */
	public $selection;

	/**
	 * @var string $outputValue Current output value for the selection for the question
	 */
	public $outputValue;

	/**
	 * @var string $price Current price of the question
	 */
	public $price;

	/**
	 * @var string $priceRecurring Current recurring price of the question
	 */
	public $priceRecurring;

	/**
	 * @var bool $canQuickEdit Indicates if the current user can quick-edit configurator data
	 */
	public $canQuickEdit;

	/**
	 * @var bool $hasValidationMessage Indicates if there is a validation message to show
	 */
	public $hasValidationMessage;

	/**
	 * @var string $validationMessage The validation message to show (if any)
	 * @see hasValidationMessage
	 */
	public $validationMessage;

	/**
	 * @var bool Indicates if the heading should be shown (depends on title_display)
	 * @see ConfigboxQuestion::title_display
	 */
	public $showHeading;

	/**
	 * @var bool Indicates if the heading should be shown (depends on title_display)
	 * @see ConfigboxQuestion::title_display
	 */
	public $showLabel;

	/**
	 * @return NULL
	 */
	function getDefaultModel() {
		return NULL;
	}

	function display() {

		$this->prepareTemplateVars();

		if (empty($this->question)) {
			return;
		}

		$this->renderView();

	}

	function prepareTemplateVars() {

		if (!$this->questionId) {
			KLog::log('This view needs the questionId assigned before rending', 'error');
			return;
		}

		$question = ConfigboxQuestion::getQuestion($this->questionId);

		if (!$question) {
			return;
		}

		$this->showPricing = ConfigboxPermissionHelper::canSeePricing();
		$this->canQuickEdit = ConfigboxPermissionHelper::canQuickEdit();

		$this->showHeading = ($question->title_display == 'heading');
		$this->showLabel = ($question->title_display == 'label');

		// Get the product
		$ass = ConfigboxCacheHelper::getAssignments();
		$productId = $ass['element_to_product'][$question->id];
		$productModel = KenedoModel::getModel('ConfigboxModelProduct');

		// LEGACY: Add product data to the template
		$this->product = $productModel->getProduct($productId);

		if ($this->product->use_recurring_pricing) {
			$this->priceLabel = $this->product->priceLabel;
			$this->priceLabelRecurring = $this->product->priceLabelRecurring;
		}

		$question->applies = $question->applies();

		// Overwrite min and max values with possibly calculated ones
		$question->minval = $question->getMinimumValue();
		$question->maxval = $question->getMaximumValue();

		$question->addCssClass('question');
		$question->addCssClass('type-'.$question->question_type);
		$question->addCssClass($question->element_css_classes);

		if ($question->display_while_disabled == 'hide') {
			$question->addCssClass('hide-non-applying');
		}
		else {
			$question->addCssClass('grey-out-non-applying');
		}

		// Set the CSS classes indicating compliance with rules
		if ($question->applies) {
			$question->addCssClass('applying-question');
		}
		else {
			$question->addCssClass('non-applying-question');
		}

		$question->disableControl = ($question->applies == false);

		// Run the question description through content modifiers
		if ($question->description) {
			$question->description = trim(KenedoPlatform::p()->processContentModifiers($question->description));
			ConfigboxViewHelper::processRelativeUrls($question->description);
		}

		// Set pre-loading and delayed loading classes and attributes
		if ($question->el_image) {

			if ($question->applies == false && $question->display_while_disabled == 'hide') {
				$question->elementImageCssClasses = 'question-decoration preload-image';
				$question->elementImagePreloadAttributes = ' data-src="'.$question->el_image_href.'"';
				$question->elementImageSrc = KPATH_URL_ASSETS.'/assets/images/blank.gif';
			}
			else {
				$question->elementImageCssClasses = 'question-decoration';
				$question->elementImagePreloadAttributes = '';
				$question->elementImageSrc = ($question->el_image) ? $question->el_image_href : '';
			}

		}

		// Prepare the current selection for that question
		$configuration = ConfigboxConfiguration::getInstance();
		$selection = $configuration->getSelection($question->id);

		$this->selection = $selection;
		$this->outputValue = $question->getOutputValue();

		$this->price = ConfigboxPrices::getElementPrice($question->id);
		$this->priceRecurring = ConfigboxPrices::getElementPriceRecurring($question->id);

		$question->addCssClass('title-display-'.$question->title_display);

		foreach ($question->answers as $answer) {

			$answer->cssId = 'answer-'.$answer->id;

			$answer->applies = $question->applies && $answer->applies();

			$answer->price = $answer->getPrice();
			$answer->price_recurring = $answer->getPriceRecurring();
			$answer->was_price = $answer->getWasPrice();
			$answer->was_price_recurring = $answer->getWasPriceRecurring();

			$answer->showAvailibilityInfo = ($answer->available == 0);
			$answer->availibility_date = (!empty($answer->availibility_date) && $answer->availibility_date != '0000 00:00:00') ? KText::sprintf('Available on %s', $answer->availibility_date) : KText::_('Not available');

			$answer->showToolTip = !empty($answer->description);

			// Disable answer if question or answer does not apply
			if ($question->applies == false || $answer->applies == false) {
				$answer->disableControl = true;
			}

			if ($answer->description) {
				$answer->description = trim(KenedoPlatform::p()->processContentModifiers($answer->description));
				ConfigboxViewHelper::processRelativeUrls($answer->description);
			}

			$answer->addCssClass('answer');

			if ($answer->option_picker_image) {
				$answer->addCssClass('has-image-picker');
			}

			if ($answer->available == 0) {
				if ($answer->disable_non_available) {
					$answer->addCssClass('not-available');
					$answer->disableControl = true;
				}
			}
			else {
				$answer->addCssClass('available');
			}

			// Set the CSS class to the selected option
			if ($selection == $answer->id) {
				$answer->addCssClass('selected');
				$answer->isSelected = true;
			}
			else {
				$answer->isSelected = false;
			}

			// Set the CSS class indicating if the option is compliant
			if ($question->applies && $answer->applies) {
				$answer->addCssClass('applying-answer');
			}
			else {
				$answer->addCssClass('non-applying-answer');
			}

			// This is for telling how the answers displays when not applying
			if ($answer->display_while_disabled == 'like_question') {
				if ($question->display_while_disabled == 'hide') {
					$answer->addCssClass('hide-non-applying');
				}
				else {
					$answer->addCssClass('grey-out-non-applying');
				}
			}
			elseif ($answer->display_while_disabled == 'hide') {
				$answer->addCssClass('hide-non-applying');
			}
			else {
				$answer->addCssClass('grey-out-non-applying');
			}

			// Make the picker image src a complete URL
			if ($answer->option_picker_image) {
				$answer->pickerImageSrc = $answer->option_picker_image_href;
			}
			else {
				$answer->pickerImageSrc = KPATH_URL_ASSETS.'/images/blank.gif';
			}

			// Set preloading/delayed loading CSS classes and attributes
			if ($answer->pickerImageSrc && (!$answer->applies || !$question->applies) && ($question->display_while_disabled == 'hide' || $answer->display_while_disabled == 'hide')) {
				$answer->pickerPreloadAttributes = ' data-src="'.$answer->pickerImageSrc.'"';
				$answer->pickerImageSrc = '';
				$answer->pickerPreloadCssClasses = 'configbox-image-button-image preload-image';
			}
			else {
				$answer->pickerPreloadAttributes = '';
				$answer->pickerPreloadCssClasses = 'configbox-image-button-image';
			}

			// Make the option image src a complete URL
			if ($answer->option_image) {
				$answer->optionImageSrc = $answer->option_image_href;
				$answer->optionImagePopupContent = '<img src="'.$answer->optionImageSrc.'" alt="'.hsc($answer->title).'" />';
				$dim = KenedoFileHelper::getImageDimensions(CONFIGBOX_DIR_ANSWER_IMAGES.DS.$answer->option_image);
				$answer->optionImagePopupWidth = $dim['width'] + 20;
			}
			else {
				$answer->optionImageSrc = '';
				$answer->optionImagePopupContent = '';
			}

			$answer->cssClasses = $answer->getCssClasses();

		}

		$this->questionCssId = 'question-'.intval($question->id);
		$this->questionCssClasses = $question->getCssClasses();

		$attributes = array(
			'question-id' => $question->id,
			'question-type' => $question->question_type,
			'selection' => $selection,
			'output-value' => $question->getOutputValue(),
		);

		$dataAttributeHtml = '';
		foreach($attributes as $attr=>$value) {
			$dataAttributeHtml .= 'data-'.$attr.'="'.hsc($value).'" ';
		}
		$this->questionDataAttributes = trim($dataAttributeHtml);

		$this->question = $question;

	}

	/**
	 * I override this method simply so developers get to this class quick via code jumping.
	 * @see renderView
	 *
	 * @param string|null $template
	 * @return string
	 */
	function getViewOutput($template = NULL) {
		return parent::getViewOutput($template);
	}

	/**
	 * This method differs slightly from the base class.
	 * @param string|null $template
	 */
	function renderView($template = NULL) {

		// Load AMD loader and CSS
		if (KenedoPlatform::p()->getDocumentType() == 'html') {
			$this->addAssets();
		}

		if ($template === NULL) {
			$template = 'default';
		}

		$template = str_replace(DS , '', $template);
		$template = str_replace('.', '', $template);

		$viewFolder = dirname($this->getViewPath());
		$viewName = strtolower(substr($viewFolder,strrpos($viewFolder, DS) + 1));

		$templatePaths = array();
		// Joomla-typical template override location
		$templatePaths['templateOverride'] 	= KenedoPlatform::p()->getTemplateOverridePath('com_configbox', $viewName, $template);
		// Custom template for the specific question type view
		$templatePaths['customTemplate'] 	= CONFIGBOX_DIR_CUSTOMIZATION .DS. 'templates' .DS. $viewName .DS. $template.'.php';
		// SPECIAL: Custom template for the base question view
		$templatePaths['customGeneralTemplate'] 	= CONFIGBOX_DIR_CUSTOMIZATION .DS. 'templates' .DS. 'question' .DS. $template.'.php';
		// Regular template for specific question type view
		$templatePaths['defaultTemplate'] 	= dirname($this->getViewPath()).DS.'tmpl'.DS.$template.'.php';
		// SPECIAL: Regular template for the base question view
		$templatePaths['baseTemplate'] 	    = KPATH_DIR_CB.'/views/question/tmpl/'.$template.'.php';

		$output = '';

		foreach ($templatePaths as $templatePath) {
			if (is_file($templatePath)) {
				ob_start();
				include($templatePath);
				$output = ob_get_clean();
				break;
			}
		}

		if ($output === false) {
			KLog::log('Template "'.$template.'" not found in "'.$templatePaths['defaultTemplate'].'" for view "'.get_class($this).'".', 'error', 'Template "'.$template.'" not found for view "'.get_class($this).'".');
		}

		echo $output;

	}

}