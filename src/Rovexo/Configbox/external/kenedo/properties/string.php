<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyString extends KenedoProperty {

	protected $stringType;
	protected $displayin;
	protected $unit;
	protected $style;
	protected $size;

	function getDataFromRequest(&$data) {

		parent::getDataFromRequest($data);
	
		$stringType = $this->getPropertyDefinition('stringType', 'string');

		// In case we got a special string type 'stringOrNumber'
		if ($stringType == 'stringOrNumber') {
			$tempNumber = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->{$this->propertyName});
			// If is a number
			if(is_numeric($data->{$this->propertyName}) || is_numeric($tempNumber)){
				$data->{$this->propertyName} = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->{$this->propertyName});
			}
		}

		if ($stringType == 'price' && $data->{$this->propertyName} == '') {
			$data->{$this->propertyName} = 0;
		}

		// In case we got a number, we change the localized decimal symbol to the normalized dot
		if ($stringType == 'number' or $stringType == 'price' or $stringType == 'time') {
			$data->{$this->propertyName} = str_replace(KText::_('DECIMAL_MARK', '.'), '.', $data->{$this->propertyName});
		}

		// Convert durations to seconds, depending on what's set in the field data
		if ($stringType == 'time') {

			switch($this->getPropertyDefinition('displayin', '')) {

				case 'days':
					$data->{$this->propertyName} *= 86400;
					break;

				case 'hours':
					$data->{$this->propertyName} *= 3600;
					break;

				case 'minutes':
					$data->{$this->propertyName} *= 60;
					break;
			}

		}
		
		return true;
	
	}

	function getOutputValueFromRecordData($record) {

		$value = $record->{$this->propertyName};

		$stringType = $this->getPropertyDefinition('stringType', 'string');

		// If it's a time, see if we got a displayin, data is stored in seconds, here we bring it to the unit we want
		if ($stringType == 'time') {

			switch($this->getPropertyDefinition('displayin', '')) {

				case 'days':
					$value /= 86400;
					break;

				case 'hours':
					$value /= 3600;
					break;

				case 'minutes':
					$value /= 60;
					break;
			}

		}

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