<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminuserfields extends KenedoModel {

	function getDetailsTasks() {
		$tasks = array(
			array(
				'title'=>KText::_('Save'),
				'task'=>'apply',
				'primary' => true,
				),
		);
		return $tasks;
	}

	function getDataFromRequest() {
		$data = new stdClass();

		$data->tableCells = KRequest::getArray('data');

		return $data;
	}

	function prepareForStorage($data) {
		return true;
	}

	function validateData($data, $context = '') {
		return true;
	}
	
	function store($data) {

		$db = KenedoPlatform::getDb();

		// Collect and sanitize user fields, prepare the row values for the insert statement
		foreach ($data->tableCells as $id=>$field) {

			if (!isset($field['field_name'])) {
				$this->setError(KText::_('User field data contained a record without a field name'));
				return false;
			}

			if (!isset($field['show_checkout'])) $field['show_checkout'] = 0;
			if (!isset($field['require_checkout'])) $field['require_checkout'] = 0;

			if (!isset($field['show_quotation'])) $field['show_quotation'] = 0;
			if (!isset($field['require_quotation'])) $field['require_quotation'] = 0;

			if (!isset($field['show_checkout'])) $field['show_checkout'] = 0;
			if (!isset($field['show_checkout'])) $field['show_checkout'] = 0;

			if (!isset($field['show_saveorder'])) $field['show_saveorder'] = 0;
			if (!isset($field['require_saveorder'])) $field['require_saveorder'] = 0;

			if (!isset($field['show_profile'])) $field['show_profile'] = 0;
			if (!isset($field['require_profile'])) $field['require_profile'] = 0;

			$inserts[] = "(	NULL ,  '".$db->getEscaped($field['field_name'])."', 
			'".(int)$field['show_checkout']."',  '".(int)$field['require_checkout']."',  
			'".(int)$field['show_quotation']."',  '".(int)$field['require_quotation']."',  
			'".(int)$field['show_saveorder']."',  '".(int)$field['require_saveorder']."',  
			'".(int)$field['show_profile']."',  '".(int)$field['require_profile']."')";

		}

		// Terminate if there were no records to insert
		if (!isset($inserts) || count($inserts) == 0) {
			$this->setError(KText::_('No userfield data was received.'));
			return false;
		}

		// Start a transaction
		$db->startTransaction();

		try {

			// Remove existing user field definitions
			$removeQuery = "DELETE FROM `#__configbox_user_field_definitions`";
			$db->setQuery($removeQuery);
			$db->query();

			// Insert the new ones
			$insertQuery = "
			INSERT INTO `#__configbox_user_field_definitions` (
				`id` ,
				`field_name` ,
				`show_checkout` ,
				`require_checkout` ,
				`show_quotation` ,
				`require_quotation` ,
				`show_saveorder` ,
				`require_saveorder` ,
				`show_profile` ,
				`require_profile`
				)
				VALUES
				".implode(", \n", $inserts);

			$db->setQuery($insertQuery);
			$db->query();

		}
		catch(Exception $e) {
			// Rollback and send feedback if things went bad (failed queries throw an exception)
			$db->rollbackTransaction();
			$this->setError($e->getMessage());
			return false;
		}

		// All went well apparently, so commit and send back true
		$db->commitTransaction();
		
		return true;
	}

	function isInsert($data) {
		return false;
	}
	
}
