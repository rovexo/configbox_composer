<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminlicense extends KenedoController {

	/**
	 * @return NULL
	 */
	protected function getDefaultModel() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdminlicense
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdminlicense');
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

	function storeLicenseKey() {
		
		if (KenedoPlatform::p()->isAuthorized('com_configbox.core.manage', NULL, 20) == false) {
			KenedoPlatform::p()->sendSystemMessage('Only users that have permission to manage the component can set the license key.');
			KenedoPlatform::p()->redirect(KLink::getRoute('index.php?option=com_configbox&view=adminlicense',false));
		}
		
		$db = KenedoPlatform::getDb();
		$key = KRequest::getString('license_key','');
		$key = str_ireplace(' ', '', $key);
		$key = strtoupper($key);
		
		$query = 'SELECT `id` FROM `#__configbox_config` LIMIT 1';
		$db->setQuery($query);
		$row = $db->loadResult();
		
		if (!$row) {
			$query = "INSERT INTO `#__configbox_config` (`id`) VALUES (1);";
			$db->setQuery($query);
			$db->query();
		}
		
		$query = 'UPDATE `#__configbox_config` SET `product_key` = "'.$db->getEscaped($key).'" WHERE `id` = 1 LIMIT 1';
		$db->setQuery($query);
		$db->query();

		KenedoPlatform::p()->redirect(KLink::getRoute('index.php?option=com_configbox', false));
		
	}

}
