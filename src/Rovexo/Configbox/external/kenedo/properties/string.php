<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyString extends KenedoProperty {

	function getDataFromRequest(&$data) {

		parent::getDataFromRequest($data);

		$stringType = $this->getPropertyDefinition('stringType', 'string');

		// In case we got a special string type 'stringOrNumber'
		if (!empty($data->{$this->propertyName}) && $stringType == 'stringOrNumber') {
			$tempNumber = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->{$this->propertyName});
			// If is a number
			if (is_numeric($data->{$this->propertyName}) || is_numeric($tempNumber)){
				$data->{$this->propertyName} = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->{$this->propertyName});
			}
		}

		if ($stringType == 'price' && $data->{$this->propertyName} == '') {
			$data->{$this->propertyName} = 0;
		}

		// In case we got a number, we change the localized decimal symbol to the normalized dot
		if (!empty($data->{$this->propertyName}) and ($stringType == 'number' or $stringType == 'price')) {
			$data->{$this->propertyName} = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->{$this->propertyName});
		}

		return true;
	
	}

	function prepareForStorage( &$data ) {

		$stringType = $this->getPropertyDefinition('stringType', 'string');

		// Be sure empty numbers/prices get stored as zero
		if ($stringType === 'number' || $stringType === 'price') {
			if (empty($data->{$this->propertyName})) {
				$data->{$this->propertyName} = 0;
			}
		}

	}

	function getOutputValueFromRecordData($record) {

		$value = $record->{$this->propertyName};

		$stringType = $this->getPropertyDefinition('stringType', 'string');

		// Numbers and times get the localized decimal symbol
		if ($stringType == 'number' or $stringType == 'time') {
			$value = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $value);
		}

		// Prices get the CB price formatting
		if ($stringType == 'price') {
			$value = cbprice($value);
		}

		// stringOrNumber
		if ($stringType == 'stringOrNumber') {
			// If is a number
			if(is_numeric($value)){
				$value = str_replace('.', KText::_('DECIMAL_MARK', '.'), (string) $value);
			}
		}

		// Unless we deal with a price, append the unit (if propdef got something)
		if ($stringType != 'price') {
			$value = $value . $this->getPropertyDefinition('unit', '');
		}

		return $value;

	}

}