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
	
}