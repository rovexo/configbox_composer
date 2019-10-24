<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminconnectors extends KenedoController {

	/**
	 * @return ConfigboxModelAdminconnectors
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminconnectors');
	}

	/**
	 * @return ConfigboxViewAdminconnectors
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminconnectors
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminconnectors');
	}

	/**
	 * @return ConfigboxViewAdminconnector
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminconnector');
	}

	public function isAuthorized($task = '') {
		if (!ConfigboxPermissionHelper::canEditConnectors()) {
			KenedoPlatform::p()->sendSystemMessage(KText::_('This feature is only available to super administrators.'));
			return false;
		}
		else {
			return true;
		}
	}
	
}
