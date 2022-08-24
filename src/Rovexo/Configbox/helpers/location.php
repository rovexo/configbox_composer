<?php

class ConfigboxLocationHelper {

	protected static $maxMindGeoLiteCityFilename = 'GeoLite2-City.mmdb';
	protected static $maxMindCache = array();

	protected static $isoCodeFipsMap;
	protected static $defaultIpV4Address = '173.194.35.37';

	static public function getCoordsByIp($ipAddress = NULL) {

		if ($ipAddress === NULL) {
			$ipAddress = self::getClientIpV4Address();
		}

		$location = self::getLocationByIp($ipAddress);

		if (!$location) {
			return false;
		}
		else {
			return $location->coords;
		}

	}

	static public function getCountryCodeByIp($ipAddress = NULL) {

		if ($ipAddress === NULL) {
			$ipAddress = self::getClientIpV4Address();
		}

		$location = self::getLocationByIp($ipAddress);

		if (!$location) {
			return false;
		}
		else {
			return $location->countryCode;
		}

	}

	static public function getCityByIp($ipAddress = NULL) {

		if ($ipAddress === NULL) {
			$ipAddress = self::getClientIpV4Address();
		}

		$location = self::getLocationByIp($ipAddress);

		if (!$location) {
			return false;
		}
		else {
			return $location->city;
		}

	}

	static public function getZipCodeByIp($ipAddress = NULL) {

		if ($ipAddress === NULL) {
			$ipAddress = self::getClientIpV4Address();
		}

		$location = self::getLocationByIp($ipAddress);

		if (!$location) {
			return false;
		}
		else {
			return $location->zipcode;
		}

	}

	/**
	 * @param string $ipAddress optional - figures it out if empty
	 * @return ConfigboxLocation|bool Location object or false on failure
	 */
	static public function getLocationByIp($ipAddress = NULL) {

		if ($ipAddress === NULL) {
			$ipAddress = self::getClientIpV4Address();
		}

		if (function_exists('overrideGetLocationByIp')) {
			$location = overrideGetLocationByIp($ipAddress);

			// NULL as response means that the regular location service should be used
			if ($location === NULL) {
				$location = self::getMaxMindLocation($ipAddress);
			}
			elseif($location === false) {
				return false;
			}
			else {
				$structureOk = self::checkLocationObjectStructure($location);
				if ($structureOk == false) {
					return false;
				}
			}
		}
		else {
			$location = self::getMaxMindLocation($ipAddress);
		}

		if (!$location) {
			return false;
		}
		else {
			return $location;
		}

	}

	static protected function checkLocationObjectStructure($locationObject) {

		$fields = array('countryCode','stateFips','city','zipcode','coords','metrocode','areacode');

		$missingFields = array();

		foreach ($fields as $field) {
			if (!isset($locationObject->$field)) {
				$missingFields[] = $field;
			}
		}

		if (!isset($locationObject->coords->lat)) {
			$missingFields[] = 'coords->lat';
		}
		if (!isset($locationObject->coords->lon)) {
			$missingFields[] = 'coords->lon';
		}

		if (count($missingFields)) {
			$message = 'Location object is missing the properties '.implode(',',$missingFields).'. Check your override function overrideGetLocationByIp().';
			KLog::log($message,'warning');
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * @param string $ipAddress
	 * @return ConfigboxLocation|NULL Location object or NULL if no record
	 */
	static protected function getMaxMindLocation($ipAddress) {

		if (!isset(self::$maxMindCache[$ipAddress])) {

			$maxMindGeoLiteCityPathFile = KenedoPlatform::p()->getDirDataStore().'/private/maxmind/'. self::$maxMindGeoLiteCityFilename;

			$serviceType = CbSettings::getInstance()->get('geolocation_type');
			$maxMindUserId = CbSettings::getInstance()->get('maxmind_user_id');
			$maxMindLicenseKey = CbSettings::getInstance()->get('maxmind_license_key');

			if ($serviceType == 'maxmind_geoip2_db' && is_file($maxMindGeoLiteCityPathFile)) {
				$location = self::getMaxMindLocationGeoIpCity($ipAddress);
			}
			elseif ($serviceType == 'maxmind_geoip2_db' && !is_file($maxMindGeoLiteCityPathFile)) {
				KLog::log('Could not locate MaxMind database file.','warning');
				$location = NULL;
			}
			elseif ($serviceType == 'maxmind_geoip2_web' && !empty($maxMindUserId) && !empty($maxMindLicenseKey) ) {
				$location = self::getMaxMindLocationWebserviceCity($ipAddress);
			}
			elseif ($serviceType == 'maxmind_geoip2_web' && (empty($maxMindUserId) || empty($maxMindLicenseKey)) ) {
				KLog::log('Empty MaxMind WebService Credentials.','warning');
				$location = NULL;
			}
			else {
				$location = NULL;
			}

			self::$maxMindCache[$ipAddress] = $location;

		}

		return self::$maxMindCache[$ipAddress];

	}

	/**
	 * @param string $ipAddress
	 * @return ConfigboxLocation|NULL|bool Location object, false on errors or NULL if no record
	 */
	static protected function getMaxMindLocationGeoIpCity($ipAddress) {

		require_once KenedoPlatform::p()->getComponentDir('com_configbox').'/external/geoip2-2.13.0/autoload.php';

		$databaseFolder = KenedoPlatform::p()->getDirDataStore().'/private/maxmind';

		try {
			$databaseFile = $databaseFolder .'/'. self::$maxMindGeoLiteCityFilename;
			if(!is_file($databaseFile)) throw new Exception('Could not find MaxMind Database file in '.$databaseFile);
			$reader = new GeoIp2\Database\Reader($databaseFile);
		}
		catch (Exception $e) {
			KLog::log('Could not get MaxMind location because of this error. '.$e->getMessage(),'error');
			return false;
		}

		try {
			$record = $reader->city($ipAddress);
		}
		catch (Exception $e) {
			return null;
		}

		$location = new ConfigboxLocation($record);

		if ($location->countryCode == 'US' or $location->countryCode == 'CA') {
			$location->stateFips = self::getFipsNumberFromIsoCode($location->stateFips);
		}
		$reader->close();
		return $location;

	}

	/**
	 * @param string $ipAddress
	 * @return ConfigboxLocation|NULL|bool Location object, false on errors or NULL if no record
	 */
	static protected function getMaxMindLocationWebserviceCity($ipAddress) {

		require_once KenedoPlatform::p()->getComponentDir('com_configbox').'/external/geoip2/vendor/autoload.php';

		try {
			$userId = CbSettings::getInstance()->get('maxmind_user_id');
			$licenseKey = CbSettings::getInstance()->get('maxmind_license_key');

			$client = new GeoIp2\WebService\Client($userId, $licenseKey);
			$record = $client->city($ipAddress);
		}
		catch (Exception $e) {
			KLog::log('Could not get MaxMind location because of this error.'.$e->getMessage(),'warning');
			return false;
		}

		$location = new ConfigboxLocation($record);

		if ($location->countryCode == 'US' or $location->countryCode == 'CA') {
			$location->stateFips = self::getFipsNumberFromIsoCode($location->stateFips);
		}

		return $location;
	}

	/**
	 * Convenience function to get the current client's IPV4 address.
	 * @return string $ipAddress IPv4 address of client or default IP address
	 */
	static public function getClientIpV4Address() {

		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED_IP']))
			$ipAddress = $_SERVER['HTTP_X_FORWARDED_IP'];
		else if(!empty($_SERVER['X_FORWARDED_FOR']))
			$ipAddress = $_SERVER['X_FORWARDED_FOR'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED']))
			$ipAddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(!empty($_SERVER['HTTP_FORWARDED_FOR']))
			$ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(!empty($_SERVER['HTTP_FORWARDED']))
			$ipAddress = $_SERVER['HTTP_FORWARDED'];
		else if(!empty($_SERVER['REMOTE_ADDR']))
			$ipAddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipAddress = self::$defaultIpV4Address;

		// Load balancers and CDNs may put multiple IPs comma separated
		if (strstr($ipAddress, ', ')) {
			$addresses = explode(', ', $ipAddress);
			$ipAddress = trim(end($addresses));
		}

		if ($ipAddress == '127.0.0.1' || $ipAddress == '::1') {
			$ipAddress = self::$defaultIpV4Address;
		}

		return $ipAddress;

	}

	protected static function getFipsNumberFromIsoCode($iso3166_2Code) {

		if (!self::$isoCodeFipsMap) {

			self::$isoCodeFipsMap = array (
				'AB' => '01',
				'BC' => '02',
				'MB' => '03',
				'NB' => '04',
				'NL' => '05',
				'NT' => '13',
				'NS' => '07',
				'NU' => '14',
				'ON' => '08',
				'PE' => '09',
				'QC' => '10',
				'SK' => '11',
				'YT' => '12',
				'AK' => '02',
				'AL' => '01',
				'AZ' => '04',
				'AR' => '05',
				'CA' => '06',
				'CO' => '08',
				'CT' => '09',
				'DE' => '10',
				'DC' => '11',
				'FL' => '12',
				'GA' => '13',
				'HI' => '15',
				'ID' => '16',
				'IL' => '17',
				'IN' => '18',
				'IA' => '19',
				'KS' => '20',
				'KY' => '21',
				'LA' => '22',
				'ME' => '23',
				'MD' => '24',
				'MA' => '25',
				'MI' => '26',
				'MN' => '27',
				'MS' => '20',
				'MO' => '29',
				'MT' => '30',
				'NE' => '31',
				'NV' => '32',
				'NH' => '33',
				'NJ' => '34',
				'NM' => '35',
				'NY' => '36',
				'NC' => '37',
				'ND' => '38',
				'OH' => '39',
				'OK' => '40',
				'OR' => '41',
				'PA' => '42',
				'PR' => '43',
				'RI' => '44',
				'SC' => '45',
				'SD' => '46',
				'TN' => '47',
				'TX' => '48',
				'UT' => '49',
				'VT' => '50',
				'VA' => '51',
				'WA' => '53',
				'WV' => '54',
				'WI' => '55',
				'WY' => '56',
			);
		}
		return (isset(self::$isoCodeFipsMap[$iso3166_2Code])) ? self::$isoCodeFipsMap[$iso3166_2Code] : NULL;

	}

}