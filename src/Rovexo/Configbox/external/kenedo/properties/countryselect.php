<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyCountryselect extends KenedoProperty {

	protected $stateFieldName;
	protected $defaultlabel;

	/**
	 * Joins that come in with 0 are regarded as NULL (and will be stored in the DB as such)
	 * @param $data
	 */
	function getDataFromRequest( &$data ) {

		$requestVar = KRequest::getString($this->propertyName, NULL);

		if ($requestVar === '0') {
			$data->{$this->propertyName} = NULL;
		}
		else {
			$data->{$this->propertyName} = $requestVar;
		}

	}

}