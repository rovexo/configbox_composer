<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyShapedivergeometry extends KenedoProperty {

	function getDataFromRequest(&$data) {
		$data->{$this->propertyName} = KRequest::getVar($this->propertyName, '');
	}

}