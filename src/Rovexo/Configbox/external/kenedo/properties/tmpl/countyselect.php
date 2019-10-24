<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyCountyselect
 */
$value = (isset($this->data->{$this->propertyName})) ? $this->data->{$this->propertyName} : NULL;
$stateFieldName = $this->getPropertyDefinition('stateFieldName', NULL);

$stateId = ($stateFieldName) ? $this->data->$stateFieldName : NULL;
$citySelectId = (strstr($this->propertyName, 'billing')) ? 'billingcity_id' : 'city_id';

echo ConfigboxCountryHelper::createCountySelect($this->propertyName, $value, $stateId, KText::_('Select County'), $citySelectId);
