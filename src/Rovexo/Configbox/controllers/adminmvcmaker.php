<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminmvcmaker extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultView() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	/**
	 * Takes in component_name, name_singular, name_plural from REQUEST and creates MVC file stubs.
	 *
	 * component_name string Like 'com_configbox'
	 * name_singular string Like 'admintestitem'
	 * name_plural string Like 'admintestitems'
	 *
	 * @throws Exception
	 */
	public function createMvcCode() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		// Get input
		$componentName = KRequest::getKeyword('component_name');
		$nameSingular = KRequest::getKeyword('name_singular');
		$namePlural = KRequest::getKeyword('name_plural');

		// Validate input
		if (empty($componentName)) {
			KLog::log("Component name is not specified.", 'custom_code_creator');
			echo "Component name is not specified.";
			return;
		}

		if (empty($nameSingular)) {
			KLog::log("Singular name is not specified.", 'custom_code_creator');
			echo "name_singular is empty.";
			return;
		}

		if (empty($namePlural)) {
			KLog::log("Plural name is not specified.", 'custom_code_creator');
			echo "Parameter name_plural is empty.";
			return;
		}

		// Prepare the table name and
		$tableName = strtolower('configbox_external_' . $namePlural);
		$model = KenedoModel::getModel('ConfigboxModelAdminmvcmaker');

		// Make all files
		$model->createControllerFile($componentName, $nameSingular, $namePlural);
		$model->createModelFile($componentName, $namePlural, $tableName);
		$model->createViewFiles($componentName, $nameSingular, $namePlural, 'form');
		$model->createViewFiles($componentName, $nameSingular, $namePlural, 'list');

		echo 'All done';

	}

}
