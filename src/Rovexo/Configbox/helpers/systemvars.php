<?php
class ConfigboxSystemVars {

	/**
	 * @param string $key
	 * @return null|string null if val does not exist, string otherwise
	 */
	static function getVar($key) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `value` FROM `#__configbox_system_vars` WHERE `key` = '".$db->getEscaped($key). "'";
		$db->setQuery($query);
		return $db->loadResult();

	}

	/**
	 * @param string $key
	 * @param string $value
	 * @throws Exception
	 */
	static function setVar($key, $value) {

		$db = KenedoPlatform::getDb();
		$query = "REPLACE INTO `#__configbox_system_vars` SET `key` = '".$db->getEscaped($key). "', `value` = '".$db->getEscaped($value). "'";
		$db->setQuery($query);
		$db->query();

	}

}