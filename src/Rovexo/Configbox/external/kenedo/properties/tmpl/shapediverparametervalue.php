<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyShapediverparametervalue
 */

$elementId = $this->data->element_id;

$assignments = ConfigboxCacheHelper::getAssignments();
$pageId = $assignments['element_to_page'][$elementId];
$productId = $assignments['page_to_product'][$pageId];

$productModel = KenedoModel::getModel('ConfigboxModelAdminproducts');
$questionModel = KenedoModel::getModel('ConfigboxModelAdminelements');
$product = $productModel->getRecord($productId);
$question = $questionModel->getRecord($elementId);

if ($product->visualization_type != 'shapediver') {
	?>
	<div class="not-using-shapediver"></div>
	<?php
}
elseif ($question->shapediver_parameter_id == '') {
	?>
	<div class="not-using-shapediver"></div>
	<?php
}
else {
	$modelData = json_decode($product->shapediver_model_data, true);

	$options = array();

	$parameter = $modelData['parameterData'][$question->shapediver_parameter_id];

	if (in_array($parameter['type'], array('choices', 'Dropdown', 'StringList', 'Cycle', 'Checklist'))) {

		$options = array(
			'' => KText::_('None selected'),
		);

		$selected = $this->data->{$this->propertyName};

		$options = array_merge($options, $parameter['choices']);

		if (!empty($selected)) {
			if (!isset($options[$selected])) {
				$options[$selected] = 'Currently a non-existent choice is selected. Please fix.';
			}
		}

		echo KenedoHtml::getSelectField($this->propertyName, $options, $selected);
	}
	elseif (in_array($parameter['type'], array('Boolean', 'Bool'))) {

		$options = array(
			'true' => KText::_('CBYES'),
			'false' => KText::_('CBNO'),
		);

		$selected = $this->data->{$this->propertyName};

		if (!empty($selected)) {
			if (!isset($options[$selected])) {
				$options[$selected] = 'Currently a non-existent choice is selected. Please fix.';
			}
		}

		echo KenedoHtml::getSelectField($this->propertyName, $options, $selected);

	}
	else {
		echo KenedoHtml::getTextField($this->propertyName, $this->data->{$this->propertyName}, $this->propertyName, 'form-control');
	}

}