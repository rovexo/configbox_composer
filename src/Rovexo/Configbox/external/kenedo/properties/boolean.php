<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyBoolean extends KenedoProperty {

	function getOutputValueFromRecordData($record) {

		if ($record->{$this->propertyName} == 1) {
			return KText::_('CBYES');
		}
		else {
			return KText::_('CBNO');
		}

	}

	protected function getPossibleFilterValues() {
		$options = array();
		$options['all'] = KText::sprintf('No %s filter', $this->getPropertyDefinition('label'));
		$options['1'] = KText::_('CBYES');
		$options['0'] = KText::_('CBNO');
		return $options;
	}

}