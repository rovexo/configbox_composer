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

	protected function getPossibleFilterValues() {
		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));

		$choices = $this->getPropertyDefinition('choices', array());
		// Legacy - old name was items
		if (count($choices) == 0) {
			$choices = $this->getPropertyDefinition('radios', array());
		}

		foreach ($choices as $key=>$value) {
			$options[$key] = $value;
		}

		return $options;
	}


}