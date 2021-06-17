<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyShapedivermodel extends KenedoProperty {

	/**
	 * Should make the Shapediver JSON data into nice array or similar
	 * @param object $data
	 */
	function appendDataForGetRecord( &$data ) {

	}

	/**
	 * @see KenedoPropertyShapedivermodel::appendDataForGetRecord()
	 *
	 * @param object $data
	 */
	public function appendDataForPostCaching(&$data) {
		$this->appendDataForGetRecord($data);
	}

	function getDataFromRequest(&$data) {
		$data->{$this->propertyName} = KRequest::getVar($this->propertyName, '');
	}

	function check($data) {

		$this->resetErrors();

		$parentResult = parent::check($data);
		if ($parentResult == false) {
			return false;
		}

		if (empty($data->{$this->propertyName})) {
			return true;
		}

		if ($this->applies($data) == false) {
			return true;
		}

		$modelData = json_decode($data->{$this->propertyName}, true);

		if (empty($modelData['ticket'])) {
			$this->setError(KText::_('The ShapeDiver Ticket ID must be entered'));
			return false;
		}

		return true;
	}

}