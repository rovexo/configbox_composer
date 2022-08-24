<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyCountryselect extends KenedoProperty {

	/**
	 * Joins that come in with 0 are regarded as NULL (and will be stored in the DB as such)
	 * @param $data
	 */
	function getDataFromRequest( &$data ) {

		$requestVar = KRequest::getInt($this->propertyName, NULL);

		if (empty($requestVar)) {
			$data->{$this->propertyName} = NULL;
		}
		else {
			$data->{$this->propertyName} = $requestVar;
		}

	}

}