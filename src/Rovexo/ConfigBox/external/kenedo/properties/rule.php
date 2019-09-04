<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyRule extends KenedoProperty {

	protected $textWhenNoRule;
	
	function getCellContentInListingTable($record) {
		if ($record->{$this->propertyName}) {
			return ConfigboxRulesHelper::getRuleHtml($record->{$this->propertyName}, false);
		}
		else {
			if ($this->getPropertyDefinition('textWhenNoRule')) {
				return $this->getPropertyDefinition('textWhenNoRule');
			}
			else {
				return '';
			}
		}

	}
	
}