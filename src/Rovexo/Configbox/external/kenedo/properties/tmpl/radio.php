<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyRadio
 */
$optionsData = $this->getPropertyDefinition('choices');
// Legacy - old name was items
if (count($optionsData) == 0) {
	$optionsData = $this->getPropertyDefinition('radios', array());
}

$options = array();
foreach ($optionsData as $key=>$radio) {
	$options[$key] = $radio;
}



if (isset($this->data->{$this->propertyName}) &&  ($this->data->{$this->propertyName} or $this->data->{$this->propertyName} === '0' or $this->data->{$this->propertyName} === 0)) {
	$value = $this->data->{$this->propertyName};
}
elseif($this->getPropertyDefinition('default')) {
	$value = $this->getPropertyDefinition('default');
}
else {
	$value = '';
}
echo KenedoHtml::getRadioButtons($this->propertyName, $options , $value);
