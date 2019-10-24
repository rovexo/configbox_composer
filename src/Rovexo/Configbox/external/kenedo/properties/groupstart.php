<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyGroupstart extends KenedoProperty {

	protected $toggle;
	protected $defaultState;

	function usesWrapper() {
		return false;
	}

	function getDataFromRequest(&$data) {

		// If the group has an open/close toggle, read out the state and save it in session data
		if ($this->getPropertyDefinition('toggle')) {
			$sessionKey = $this->getSessionKey();
			$defaultState = $this->getPropertyDefinition('defaultState', 'closed');
			$value = KRequest::getKeyword('toggle-state-'.$this->propertyName, $defaultState);
			KSession::set($sessionKey, $value, 'kenedo');
		}

	}

	function getDataKeysForBaseTable($data) {
		return array();
	}

	public function getSelectsForGetRecord($selectAliasPrefix = '', $selectAliasOverride = '') {
		return array();
	}

	protected function getSessionKey() {
		return 'toggles.'. $this->model->getModelName() .'.'. $this->propertyName;
	}

}