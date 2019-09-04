<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmintemplates extends KenedoController {

	/**
	 * @return ConfigboxModelAdmintemplates
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmintemplates');
	}

	/**
	 * @return ConfigboxViewAdmintemplates
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdmintemplates
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdmintemplates');
	}

	/**
	 * @return ConfigboxViewAdmintemplate
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmintemplate');
	}

	public function isAuthorized($task = '') {
		
		if ($task == '' || $task == 'display') {
			return parent::isAuthorized($task);
		}
		else {
			if (ConfigboxPermissionHelper::canEditTemplates()) {
				return true;
			}
			else {
				KenedoPlatform::p()->sendSystemMessage(KText::_('This feature is only available to super administrators.'));
				return false;
			}
		}
		
	}

	function delete() {
		$id = KRequest::getString('id');
		$model = $this->getDefaultModel();
		$model->delete($id);
		$this->setRedirect(KLink::getRoute('index.php?option=com_configbox&controller=admintemplates', false));
	}
	
}
