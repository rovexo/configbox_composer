<?php
class ConfigboxModelAdminplatformgroups extends KenedoModel {

	function getGroups() {

		$platformGroups = array();

		if (KenedoPlatform::getName() == 'joomla') {
			if (KenedoPlatform::p()->getVersionShort() == '1.5') {
				$query = "SELECT *, `value` AS `title` FROM `#__core_acl_aro_groups` WHERE `value` != 'ROOT' AND `value` != 'USERS'";
				$db = KenedoPlatform::getDb();
				$db->setQuery($query);
				$platformGroups = $db->loadObjectList();

			}
			else {
				$query = "SELECT * FROM `#__usergroups`";
				$db = KenedoPlatform::getDb();
				$db->setQuery($query);
				$platformGroups = $db->loadObjectList();
			}
		}

		return $platformGroups;

	}

}