<?php 
defined('CB_VALID_ENTRY') or die(); 

class KenedoPropertyDatetime extends KenedoProperty {

	function prepareForStorage( &$data ) {

		if (empty($data->{$this->propertyName})) {
			$data->{$this->propertyName} = NULL;
		}

	}

}