<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyShapediverparametervalue extends KenedoProperty {

	function getDataFromRequest(&$data) {
		$data->{$this->propertyName} = KRequest::getVar($this->propertyName, '');
	}

}