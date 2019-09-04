<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyRadio extends KenedoProperty {

	protected $choices;

	function getOutputValueFromRecordData($record) {

		$choices = $this->getPropertyDefinition('choices', array());
		// Legacy - old name was items
		if (count($choices) == 0) {
			$choices = $this->getPropertyDefinition('radios', array());
		}

		$value = $record->{$this->propertyName};

		return (isset($choices[$value])) ? $choices[$value] : '';
	}

}