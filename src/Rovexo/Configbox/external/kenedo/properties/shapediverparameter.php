<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyShapediverparameter extends KenedoProperty {

	function getDataFromRequest(&$data) {
		$data->{$this->propertyName} = KRequest::getVar($this->propertyName, '');
	}

}