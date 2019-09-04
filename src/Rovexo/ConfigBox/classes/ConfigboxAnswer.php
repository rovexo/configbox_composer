<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxAnswer {

	public $id;
	public $sku;
	public $price;
	public $price_recurring;
	public $weight;
	public $option_custom_1;
	public $option_custom_2;
	public $option_custom_3;
	public $option_custom_4;

	/**
	 * @var string File name of the option image
	 */
	public $option_image;
	/**
	 * @var string Full URL to the option image (added automatically by the framework)
	 */
	public $option_image_href;
	/**
	 * @var string Full file system path to the option image (added automatically by the framework)
	 */
	public $option_image_path;
	public $available;
	public $availibility_date;
	public $was_price;
	public $was_price_recurring;
	public $disable_non_available;
	public $element_id;
	public $option_id;
	public $default;
	public $visualization_image;
	public $visualization_stacking;
	public $visualization_view;
	/**
	 * @var string hide|grey_out
	 */
	public $display_while_disabled;
	public $calcmodel;
	public $calcmodel_recurring;
	public $ordering;
	public $published;
	public $assignment_custom_1;
	public $assignment_custom_2;
	public $assignment_custom_3;
	public $assignment_custom_4;
	public $rules;
	/**
	 * @var string Filename of the answer picker image
	 */
	public $option_picker_image;
	/**
	 * @var string Full URL to the picker image (added automatically by the framework)
	 */
	public $option_picker_image_href;
	/**
	 * @var string Full file system path to the picker image (added automatically by the framework)
	 */
	public $option_picker_image_path;
	public $calcmodel_weight;

	public $applies;
	public $cssId;
	public $cssClasses = array();
	public $extraAttributes;

	/**
	 * @var bool $disableControl Indicates if the form control should be disabled or not
	 */
	public $disableControl;

	/**
	 * @var string Title of the answer
	 */
	public $title;

	/**
	 * @var string Internal name of the answer
	 */
	public $internal_name;

	/**
	 * @var string can be 'tooltip' or 'modal'
	 */
	public $desc_display_method;

	/**
	 * @var string HTML with answer description - used in a tooltip or modal
	 */
	public $description;

	/**
	 * @var string Auto-generated in preparation. Used for pre-loading of images
	 */
	public $pickerPreloadCssClasses;

	/**
	 * @var string Set while preparing the question for display in configurator page.
	 */
	public $pickerImageSrc;

	/**
	 * Set in question view (NULL if used anywhere outside the question views)
	 * @var string HTML attributes used for preloading picker images
	 */
	public $pickerPreloadAttributes;

	/**
	 * Dynamically set in question view (NULL if used anywhere outside the question views)
	 * @var bool Indicates if the anser is currently selected
	 */
	public $isSelected;

	/**
	 * @var string $optionImageSrc Full URL to the option image (don't confuse it with visualization image or picker
	 * image, see GUI on what the option image is). Set in ConfigboxViewConfiguratorpage::display()
	 */
	public $optionImageSrc;

	/**
	 * @var string $optionImagePopupContent HTML for the popup. Set in ConfigboxViewConfiguratorpage::display()
	 */
	public $optionImagePopupContent;

	/**
	 * @var int $optionImagePopupWidth - Width of the popup
	 */
	public $optionImagePopupWidth;

	/**
	 * @var bool $showToolTip Indicates if the tooltip should be shown
	 */
	public $showToolTip;

	/**
	 * @var bool $showAvailibilityInfo Indicates if the availibility date should be shown
	 */
	public $showAvailibilityInfo;

	/**
	 * @var string HTML with tooltip trigger (set in ConfigboxViewQuestion::prepareTemplateVars) or empty if no desc.
	 * @see ConfigboxViewQuestion::prepareTemplateVars
	 * @deprecated Using Bootstrap popovers now, see templates for new usage
	 */
	public $descriptionToolTip;

	/**
	 * @var string JSON encoded array of arrays with group_id and price. Used for overriding pricing for specified groups.
	 */
	public $price_overrides;

	/**
	 * @var string JSON encoded array of arrays with group_id and price. Used for overriding pricing for specified groups.
	 */
	public $price_recurring_overrides;

	/**
	 * @var string JSON encoded array of arrays with group_id and calculation_id. Used for overriding pricing for specified groups.
	 */
	public $price_calculation_overrides;

	/**
	 * @var string JSON encoded array of arrays with group_id and calculation_id. Used for overriding pricing for specified groups.
	 */
	public $price_recurring_calculation_overrides;

	/**
	 * @var string Choice value if answer is part of a shapediver controlling question
	 */
	public $shapediver_choice_value;

	function __construct($data) {
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
	}
	
	function getPrice($getNet = NULL, $inBaseCurrency = false) {
		return ConfigboxPrices::getXrefPrice($this->id, $this->element_id, $getNet, $inBaseCurrency);
	}
	
	function getPriceRecurring($getNet = NULL, $inBaseCurrency = false) {
		return ConfigboxPrices::getXrefPriceRecurring($this->id, $this->element_id, $getNet, $inBaseCurrency);
	}
	
	function getWasPrice($getNet = NULL, $inBaseCurrency = false) {
		return ConfigboxPrices::getXrefWasPrice($this->id, $this->element_id, $getNet, $inBaseCurrency);
	}
	
	function getWasPriceRecurring($getNet = NULL, $inBaseCurrency = false) {
		return ConfigboxPrices::getXrefWasPriceRecurring($this->id, $this->element_id, $getNet, $inBaseCurrency);
	}
	
	function applies() {
		return ConfigboxRulesHelper::ruleIsFollowed($this->rules, 'option_assignment', $this->id);
	}
	
	function getWeight() {
		if ($this->calcmodel_weight) {
			return ConfigboxPrices::getXrefWeight($this->id);
		}
		else {
			return $this->weight;
		}
	}
	
	function addCssClass($class) {
		$class = trim(str_replace('  ', ' ', $class));
		$classes = explode(' ', $class);
		foreach ($classes as $class) {
			$this->cssClasses[$class] = hsc($class);
		}
	
	}
	
	function removeCssClass($class) {
		if (isset($this->cssClasses[$class])) {
			unset($this->cssClasses[$class]);
		}
	}
	
	function getCssClasses() {
		return implode(' ', $this->cssClasses);
	}
	
}