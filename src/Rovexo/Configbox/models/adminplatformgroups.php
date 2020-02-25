<?php
class ConfigboxModelAdminplatformgroups extends KenedoModel {

	function getGroups() {

		$platformGroups = array();

		if (KenedoPlatform::getName() == 'joomla') {
			$query = "SELECT * FROM `#__usergroups`";
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			$platformGroups = $db->loadObjectList();
		}

		return $platformGroups;

	}

}