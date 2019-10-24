<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyNote extends KenedoProperty {

	function getDataFromRequest(&$data) {
		if ($this->getPropertyDefinition('default')) {
			$data->{$this->propertyName} = $this->getPropertyDefinition('default');
		}
	}

	function getDataKeysForBaseTable($data) {
		return array();
	}

	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {
		return array();
	}

}