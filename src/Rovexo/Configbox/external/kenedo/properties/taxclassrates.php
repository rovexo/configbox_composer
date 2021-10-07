<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyTaxclassrates extends KenedoProperty {

	protected $taxclasstype;

	function getDataFromRequest( &$data ) {
		
		$tax_classes = ConfigboxPrices::getTaxClasses();
		
		foreach ($tax_classes AS $tax_class) {
			$key = 'tax_rate_tcr_'.$tax_class['id'];
			
			if (KRequest::getVar($key,NULL) === NULL) {
				$data->$key = NULL;
			}
			else {
				$data->$key = KRequest::getString($key,'');
			}
			
			$key = 'tax_code_tcr_'.$tax_class['id'];
				
			if (KRequest::getVar($key, NULL) === NULL) {
				$data->$key = NULL;
			}
			else {
				$data->$key = KRequest::getString($key,'');
			}
			
		}
		
	}

	function getDataKeysForBaseTable($data) {
		return array();
	}
	
	function store(&$data) {
		
		$db = KenedoPlatform::getDb();
		
		if ($data->id != 0) {

			$key = '';
			switch ($this->getPropertyDefinition('taxclasstype')) {
				case 'country':
					$key = 'country_id';
					break;
				case 'state':
					$key = 'state_id';
					break;
				case 'county':
					$key = 'county_id';
					break;
				case 'city':
					$key = 'city_id';
					break;
			}
			
			$query = "DELETE FROM `#__configbox_tax_class_rates` WHERE `".$key."` = ".intval($data->id);
			$db->setQuery($query);
			$success = $db->query();
			
			if ($success === false) {
				$this->setError('Could not store tax class rate because of a DB error ("'.$db->getErrorMsg().'")');
				return false;
			}
			
		}
		
		$tax_classes = ConfigboxPrices::getTaxClasses();
		
		foreach ($tax_classes AS $tax_class) {
			$fieldName = 'tax_rate_tcr_'.$tax_class['id'];
			$value = $data->$fieldName;
			
			$codeFieldName = 'tax_code_tcr_'.$tax_class['id'];
			$codeValue = $data->$codeFieldName;

			if ($value !== '' || $codeValue !== '') {
				$tag = KenedoPlatform::p()->getLanguageTag();
				if ($tag == 'de-DE' || $tag == 'de-AT' || $tag == 'de-CH') {
					$value = str_replace(',', '.', $value);
				}
	
				$city_id = $this->getPropertyDefinition('taxclasstype') == 'city' ? intval($data->id) : 'NULL';
				$county_id = $this->getPropertyDefinition('taxclasstype') == 'county' ? intval($data->id) : 'NULL';
				$state_id = $this->getPropertyDefinition('taxclasstype') == 'state' ? intval($data->id) : 'NULL';
				$country_id = $this->getPropertyDefinition('taxclasstype') == 'country' ? intval($data->id) : 'NULL';

				// Sanitation is done above already
				$query = "
				INSERT INTO `#__configbox_tax_class_rates` (tax_class_id, city_id, county_id, state_id, country_id, tax_rate, tax_code) 
				VALUES (".intval($tax_class['id']).", ".$city_id.", ".$county_id.", ".$state_id.", ".$country_id.", ".floatval($value).", '".$db->getEscaped($codeValue)."')";
				$db->setQuery($query);
				$succ = $db->query();
				if ($succ === false) {
					$this->setError('Could not store tax class rate because of a DB error ("'.$db->getErrorMsg().'")');
					return false;
				}
			}
			
		}

		return true;
	}

	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {

		$taxClasses = ConfigboxPrices::getTaxClasses();

		$response = array();

		foreach ($taxClasses as $taxClass) {

			$taxRateAlias = 'tcr_'.intval($taxClass['id']);

			$response[] = $taxRateAlias.".tax_rate AS ".$selectAliasPrefix."tax_rate_".$taxRateAlias;
			$response[] = $taxRateAlias.".tax_code AS ".$selectAliasPrefix."tax_code_".$taxRateAlias;

		}

		return $response;

	}

	public function getJoinsForGetRecord() {

		$tax_classes = ConfigboxPrices::getTaxClasses();

		$response = array();

		foreach ($tax_classes as $tax_class) {

			$tcr_alias = 'tcr_'.intval($tax_class['id']);
			$on_clause = '';

			switch ($this->getPropertyDefinition('taxclasstype')) {

				case 'country':
					$on_clause = $tcr_alias.".country_id = `".$this->model->getModelName()."`.`".$this->model->getTableKey()."`";
					break;
				case 'state':
					$on_clause = $tcr_alias.".state_id = `".$this->model->getModelName()."`.`".$this->model->getTableKey()."`";
					break;
				case 'county':
					$on_clause = $tcr_alias.".county_id = `".$this->model->getModelName()."`.`".$this->model->getTableKey()."`";
					break;
				case 'city':
					$on_clause = $tcr_alias.".city_id = `".$this->model->getModelName()."`.`".$this->model->getTableKey()."`";
					break;

			}

			$response[$tcr_alias] = "LEFT JOIN `#__configbox_tax_class_rates` AS ".$tcr_alias." ON ".$tcr_alias.".tax_class_id = ".intval($tax_class['id'])." AND ".$on_clause;

		}

		return $response;

	}
	
	function doesShowAdminLabel() {
		return false;
	}
	
}



