<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyCountryselect
 */
$value = (isset($this->data->{$this->propertyName})) ? $this->data->{$this->propertyName} : NULL;
$stateFieldName = $this->getPropertyDefinition('stateFieldName', NULL);
$nullOptionLabel = $this->getPropertyDefinition('defaultlabel', NULL);

echo ConfigboxCountryHelper::createCountrySelect($this->propertyName, $value, $nullOptionLabel, $stateFieldName);
