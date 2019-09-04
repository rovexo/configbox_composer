<?php 
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyPublished
 */
if (isset($this->data->{$this->propertyName})) {
	$value = $this->data->{$this->propertyName};
}
elseif($this->getPropertyDefinition('default')) {
	$value = $this->getPropertyDefinition('default');
}
else {
	$value = 0;
}

$options = array(
	'1'=>KText::_('CBYES'),
	'0'=>KText::_('CBNO')
);

echo KenedoHtml::getRadioButtons($this->propertyName, $options, $value);

