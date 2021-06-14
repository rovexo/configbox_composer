<?php
class ConfigboxPspHelper {

	static private function getDefaultDir() {
		return KenedoPlatform::p()->getComponentDir('com_configbox').'/psp_connectors';
	}

	static private function getCustomDir() {
		return KenedoPlatform::p()->getDirCustomization().'/psp_connectors';
	}

	/**
	 * Returns an array of all available PSP connector names. Default and custom are merged
	 * @return array Array of connector names
	 */
	static function getConnectorNames() {
		$defaultConnectors = KenedoFileHelper::getFolders( self::getDefaultDir() );
		$customConnectors = KenedoFileHelper::getFolders( self::getCustomDir() );
		$connectorNames = array_unique(array_merge($defaultConnectors, $customConnectors));
		natcasesort($connectorNames);
		return $connectorNames;
	}

	/**
	 * Returns the the full filesystem path to the connectors base directory, either the custom or default connector
	 * @param string $connectorName Connector name
	 * @return string Full path to connector's base folder
	 */
	static function getPspConnectorFolder($connectorName) {

		$subFolder = KenedoFileHelper::sanitizeFileName($connectorName);

		if (is_dir(self::getCustomDir() .'/'. $subFolder)) {
			$pspBaseDirectory = self::getCustomDir() .'/'. $subFolder;
		}
		else {
			$pspBaseDirectory =  self::getDefaultDir() .'/'. $subFolder;
		}

		return $pspBaseDirectory;
	}

	/**
	 * Returns an array of keys that shall be taken from POST when payment methods get's stored.
	 * Also these keys are available as settings in the payment object in order record as well as in the IPN class
	 *
	 * @param 	string 	$connectorName Name of the connector
	 * @return 	array 	Array of key strings
	 */
	static function getPspConnectorSettingKeys($connectorName) {

		$folder = self::getPspConnectorFolder($connectorName);

		$adminFile = $folder.'/administration.php';
		$getSettingsKeysFunctionName = $connectorName.'_get_setting_keys';

		$settingKeys = array();

		if (is_file($adminFile)) {

			include_once($adminFile);

			if (function_exists($getSettingsKeysFunctionName)) {
				$settingKeys = $getSettingsKeysFunctionName();
				return $settingKeys;
			}

		}

		return $settingKeys;

	}

	/**
	 * Returns the title of a connector
	 * @param string $connectorName connector name
	 * @return string connector title
	 */
	static function getPspConnectorTitle($connectorName) {

		$folder = self::getPspConnectorFolder($connectorName);

		$functionName = $connectorName.'_get_title';

		if (is_file($folder .'/'. 'administration.php')) {
			include_once($folder.'/'. 'administration.php');

			if (function_exists($functionName)) {
				return $functionName();
			}
		}

		return $connectorName;

	}

	/**
	 * Tells if the PSP connector supports instant payment notifications
	 * @param string $connectorName connector name
	 * @return bool
	 */
	static function pspSupportsIpn($connectorName) {

		$folder = self::getPspConnectorFolder($connectorName);

		$functionName = $connectorName.'_has_instant_payment_notification';

		if (is_file($folder . '/administration.php')) {

			include_once($folder . '/administration.php');

			if (function_exists($functionName)) {
				return $functionName();
			}
		}

		return false;

	}

	/**
	 * @param string $connectorName
	 * @return IpnAuthorizenet_sim Well, to be honest it'll be the class you requested
	 * @throws Exception if IPN class wasn't to be found
	 */
	static function getIpnObject($connectorName) {

		if (!$connectorName) {
			$logId = KLog::log('IPN system called, but no system parameter was specified. Check IPN URL.', 'error', KText::_('A system error occured.'));
			throw new Exception('System error occurred. See CB error log file (identifier: '.$logId.')');
		}

		$pspFolder = ConfigboxPspHelper::getPspConnectorFolder($connectorName);

		$ipnFile = $pspFolder.'/ipn.php';

		if (is_file($ipnFile)) {
			require_once($ipnFile);
			KLog::log('Payment system "'.$connectorName.'" requested.','payment');

		} else {
			$logId = KLog::log('Payment system "'.$connectorName.'" not found or readable in "'.$ipnFile.'".', 'error', KText::_('A system error occured.'));
			throw new Exception('System error occurred. See CB error log file (identifier: '.$logId.')');
		}

		$className = 'Ipn' . ucwords(strtolower($connectorName));

		if (class_exists($className) == false) {
			$logId = KLog::log('IPN class "'.$className.'" for PSP connector "'.$connectorName.'" not found in "'.$ipnFile.'".', 'error', KText::_('A system error occured.'));
			throw new Exception('System error occurred. See CB error log file (identifier: '.$logId.')');
		}

		return new $className;

	}
}