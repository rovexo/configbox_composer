<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmindashboard extends KenedoController {

	/**
	 * @return ConfigboxModelAdmindashboard
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmindashboard');
	}

	/**
	 * @return ConfigboxViewAdmindashboard
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdmindashboard');
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

	function removeFileStructureWarning() {

		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_system_vars` WHERE `key` = 'folder_movings'";
		$db->setQuery($query);
		$db->query();

	}

}
