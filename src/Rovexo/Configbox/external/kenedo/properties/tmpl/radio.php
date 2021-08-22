<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyRadio
 */
$choices = $this->getPropertyDefinition('choices', array());
// Legacy - old name was items
if (count($choices) == 0) {
	$choices = $this->getPropertyDefinition('radios', array());
}

$options = array();
foreach ($choices as $key=>$item) {
	$options[$key] = $item;
}

if ($this->data->{$this->propertyName} or $this->data->{$this->propertyName} === '0' or $this->data->{$this->propertyName} === 0) {
	$value = $this->data->{$this->propertyName};
}
elseif($this->getPropertyDefinition('default')) {
	$value = $this->getPropertyDefinition('default');
}
else {
	$value = NULL;
}
echo KenedoHtml::getRadioButtons($this->propertyName, $options, $value);
