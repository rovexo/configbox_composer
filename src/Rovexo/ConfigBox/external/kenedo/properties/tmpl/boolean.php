<?php
defined('CB_VALID_ENTRY') or die();

/**
 * @var $this KenedoPropertyBoolean
 */

if (isset($this->data->{$this->propertyName})) {
	$value = $this->data->{$this->propertyName};
}
elseif($this->getPropertyDefinition('default', NULL) !== NULL) {
	$value = $this->getPropertyDefinition('default');
}
else {
	$value = 0;
}

echo KenedoHtml::getRadioButtons($this->propertyName, array('1'=>KText::_('CBYES'), '0'=>KText::_('CBNO')), $value );
