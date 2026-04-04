<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxAnswer {

	public $disableControl;
	public $cssId;
	public $showAvailibilityInfo;
	public $applies;
	public $isSelected;
	public $pickerImageSrc;
	public $pickerPreloadAttributes;
	public $pickerPreloadCssClasses;
	public $optionImageSrc;
	public $option_image_href;
	public $optionImagePopupContent;
	public $optionImagePopupWidth;
	public $cssClasses;

	public $calcmodel_display_value;
	public $calcmodel_recurring_display_value;
	public $calcmodel_weight_display_value;
	public $is_shapediver_control;
	public $joinedby_element_id_to_adminelements_page_id;
	public $question_type;
	public $shapediver_parameter_id;
	public $joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_product_id;
	public $joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_product_published;
	public $joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_joinedby_product_id_to_adminproducts_use_recurring_pricing;
	public $joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_visualization_type;
	public $assignment_custom_1;
	public $assignment_custom_2;
	public $assignment_custom_3;
	public $assignment_custom_4;
	public $calcmodel;
	public $calcmodel_recurring;
	public $calcmodel_weight;
	public $default;
	public $display_while_disabled;
	public $element_id;
	public $id;
	public $internal_name;
	public $option_id;
	public $option_picker_image;
	public $ordering;
	public $price_calculation_overrides;
	public $price_recurring_calculation_overrides;
	public $published;
	public $rules;
	public $shapediver_choice_value;
	public $visualization_image;
	public $visualization_stacking;
	public $element_id_display_value;
	public $option_id_display_value;
	public $joinedby_element_id_to_adminelements_page_id_display_value;
	public $joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_product_id_display_value;
	public $joinedby_element_id_to_adminelements_joinedby_page_id_to_adminpages_product_title;
	public $option_picker_image_href;
	public $option_picker_image_path;
	public $available;
	public $availibility_date;
	public $desc_display_method;
	public $disable_non_available;
	public $option_custom_1;
	public $option_custom_2;
	public $option_custom_3;
	public $option_custom_4;
	public $option_image;
	public $price;
	public $price_overrides;
	public $price_recurring;
	public $price_recurring_overrides;
	public $sku;
	public $was_price;
	public $was_price_recurring;
	public $weight;
	public $description;
	public $option_custom_5;
	public $option_custom_6;
	public $title;
	public $visualization_image_path;
	public $visualization_image_href;

	function __construct(&$data) {

		foreach ($data as $k => &$v) {
			@$this->$k = $v;
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

	/**
	 * @return string
	 */
	function getCssClasses() {
		if (is_array($this->cssClasses)) {
			return implode(' ', $this->cssClasses);
		}
		elseif (is_string($this->cssClasses)) {
			return $this->cssClasses;
		}
		else {
			return '';
		}

	}

}