<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyMultiselect
 */

$options = $this->getSelectableValues();
$selectedValues = $this->data->{$this->propertyName};

// Output either checkboxes or a select with multi selection
if ($this->getPropertyDefinition('asCheckboxes')) {
	echo KenedoHtml::getCheckboxField($this->propertyName, $options, $selectedValues, NULL, true, '');
}
else {
	echo KenedoHtml::getSelectField($this->propertyName, $options, $selectedValues, NULL, true, 'extended-multiselect');
}
?>

<span class="multiselect-toggles">
	<a class="trigger-select-all-multi"><?php echo KText::_('Select All');?></a>
	<a class="trigger-deselect-all-multi"><?php echo KText::_('Deselect All');?></a>
</span>