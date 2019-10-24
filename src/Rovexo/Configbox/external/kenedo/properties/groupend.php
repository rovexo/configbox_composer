<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyGroupend extends KenedoProperty {
	
	function usesWrapper() {
		return false;
	}
	
	function getDataFromRequest(&$data) {
		return true;
	}

	function getDataKeysForBaseTable($data) {
		return array();
	}

	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {
		return array();
	}

}