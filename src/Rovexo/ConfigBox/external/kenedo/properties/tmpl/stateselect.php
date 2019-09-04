<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoPropertyStateselect
 */
$value = (isset($this->data->{$this->propertyName})) ? $this->data->{$this->propertyName} : NULL;
$countryPropertyName = $this->getPropertyDefinition('countryFieldName', NULL);
$countryId = ($countryPropertyName) ? $this->data->$countryPropertyName : NULL;

if (strstr($this->propertyName,'billing')) {
	$countySelectId = 'billingcounty_id';
}
else {
	$countySelectId = 'county_id';
}

echo ConfigboxCountryHelper::createStateSelect($this->propertyName, $value, $countryId, KText::_('Select State'), $countySelectId);
