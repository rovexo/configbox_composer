<?php
class ConfigboxCountryHelper {

	private static $cache;

	/**
	 * @return object[]|ConfigboxCountryData[]
	 */
	static function getCountries() {

		$query = "
				SELECT c.*
				FROM `#__configbox_countries` AS c
				WHERE c.published = '1' 
				ORDER BY c.ordering, c.country_name";

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$countries = $db->loadObjectList();

		if ($countries) {
			foreach ($countries as &$country) {
				$country->custom_translatable_1 = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 42, $country->id);
				$country->custom_translatable_2 = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 43, $country->id);
			}
		}

		return $countries;
	}

	/**
	 * @return string[][]
	 */
	static function getCountryList() {

		$query = "
				SELECT c.*
				FROM `#__configbox_countries` AS c
				WHERE c.published = '1' 
				ORDER BY c.ordering, c.country_name";

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$countries = $db->loadAssocList();

		if ($countries) {
			foreach ($countries as &$country) {
				$country['custom_translatable_1'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 42, $country['id']);
				$country['custom_translatable_2'] = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 43, $country['id']);
			}
		}

		return $countries;
	}

	static function getCountyList( $stateId = NULL) {

		$query = "
		SELECT *
		FROM `#__configbox_counties`
		WHERE `published` = '1'";

		if ($stateId !== NULL) {
			$query .= " AND `state_id` = ".intval($stateId);
		}

		$query .= " ORDER BY `ordering`, `county_name`";

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$counties = $db->loadObjectList();

		return $counties;
	}

	static function getCityList( $countyId = NULL ) {

		$query = "SELECT * FROM `#__configbox_cities` WHERE `published` = '1'";

		if ($countyId) {
			$query .= " AND `county_id` = ".intval($countyId);
		}

		$query .= " ORDER BY `ordering`, `city_name`";

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$cities = $db->loadAssocList();

		return $cities;
	}

	static function countryIdExists($countryId) {

		$country = self::getCountry($countryId);
		if (!$country || $country->published == 0) {
			return false;
		}
		else {
			return true;
		}

	}

	static function stateIdExists($stateId, $countryId) {

		$state = self::getState($stateId);
		if (!$state || $state->published == 0 || ($countryId && $state->country_id != $countryId)) {
			return false;
		}
		else {
			return true;
		}
	}

	static function countyIdExists($countyId) {

		$county = self::getCounty($countyId);
		if (!$county || $county->published == 0) {
			return false;
		}
		else {
			return true;
		}

	}

	static function hasStates($countryId) {
		if (!$countryId) {
			return false;
		}

		$query = "SELECT `id` FROM `#__configbox_states` WHERE `country_id` = ".intval($countryId). " AND `published` = '1' LIMIT 1";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$has = $db->loadResult();

		if ($has) {
			return true;
		}
		else {
			return false;
		}

	}

	static function hasCounties($stateId) {

		if (!$stateId) {
			return false;
		}

		$query = "SELECT `id` FROM `#__configbox_counties` WHERE `state_id` = ".intval($stateId). " AND `published` = '1' LIMIT 1";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$has = $db->loadResult();

		if ($has) {
			return true;
		}
		else {
			return false;
		}
	}

	static function hasCities($countyId) {

		if (!$countyId) {
			return false;
		}

		$query = "SELECT `id` FROM `#__configbox_cities` WHERE `county_id` = ".intval($countyId). " AND `published` = '1' LIMIT 1";
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$has = $db->loadResult();

		if ($has) {
			return true;
		}
		else {
			return false;
		}
	}

	static function systemUsesCities() {

		if (!isset(self::$cache['systemUsesCities'])) {
			$query = "SELECT `id` FROM `#__configbox_cities` WHERE `published` = '1' LIMIT 1";
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			self::$cache['systemUsesCities'] = ($db->loadResult() == NULL) ? false : true;
		}

		return self::$cache['systemUsesCities'];

	}

	static function cityIdExists($cityId, $countyId) {

		$city = self::getCity($cityId);
		if (!$city || $city->published == 0 || ($countyId && $city->county_id != $countyId)) {
			return false;
		}
		else {
			return true;
		}
	}


	static function getCountryName($id) {

		if (!$id) return '';

		$query = "SELECT `country_name` FROM `#__configbox_countries` WHERE `id` = ".(int)$id;

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResult();

	}

	static function getStateName($id) {

		if (!$id) return '';

		$query = "SELECT `name` FROM `#__configbox_states` WHERE `id` = ".(int)$id;

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResult();

	}

	static function getCountyName($id) {

		if (!$id) return '';

		$query = "SELECT `county_name` FROM `#__configbox_counties` WHERE `id` = ".(int)$id;

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResult();

	}

	static function getCityName($id) {

		if (!$id) return '';

		$query = "SELECT `city_name` FROM `#__configbox_cities` WHERE `id` = ".(int)$id;

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResult();

	}

	static function getCountry2Code($id) {

		if (!$id) return '';


		$query = "SELECT `country_2_code` FROM `#__configbox_countries` WHERE `id` = ".(int)$id;

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResult();

	}

	/**
	 * Gives you country IDs for the given $zoneId
	 * @param int $zoneId
	 * @return int[]
	 */
	static function getZoneCountries($zoneId) {
		if (!$zoneId) {
			KLog::log(__METHOD__.': No zone ID passed','debug');
			return array();
		}
		$query = "SELECT `id` FROM `#__configbox_countries` WHERE `zone_id` = ".(int)$zoneId." AND `published` = '1'";

		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadResultList();

	}

	/**
	 * @param string $country2code
	 * @return null|int
	 */
	static function getCountryIdByCountry2Code($country2code) {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_countries` WHERE `country_2_code` = '".$db->getEscaped($country2code)."' AND `published` = '1'";
		$db->setQuery($query);
		return $db->loadResult();
	}

	static function getStateIdByFipsNumber($countryId, $fipsNumber) {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_states` WHERE `country_id` = ".(int)$countryId." AND `fips_number` = '".$db->getEscaped($fipsNumber)."' AND `published` = '1'";
		$db->setQuery($query);
		return $db->loadResult();
	}

	static function getFirstState($countryId) {
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_states` WHERE `country_id` = ".(int)$countryId." AND `published` = '1' ORDER BY `ordering` LIMIT 1";
		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * @param int $countryId
	 * @return null|object|ConfigboxCountryData
	 */
	static function &getCountry($countryId) {
		if (!$countryId) {
			$return = NULL;
			return $return;
		}

		if (!isset(self::$cache['countries'][$countryId])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_countries` WHERE `id` = ".(int)$countryId;
			$db->setQuery($query);
			self::$cache['countries'][$countryId] = $db->loadObject();

			if (self::$cache['countries'][$countryId]) {
				self::$cache['countries'][$countryId]->custom_translatable_1 = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 42, $countryId);
				self::$cache['countries'][$countryId]->custom_translatable_1 = ConfigboxCacheHelper::getTranslation('#__configbox_strings', 43, $countryId);
			}
		}

		return self::$cache['countries'][$countryId];

	}

	/**
	 * @param int $countryId
	 * @param int $orderId
	 * @return null|object|ConfigboxCountryData
	 */
	static function getOrderCountry($countryId, $orderId) {
		if (!$countryId || !$orderId) {
			return NULL;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__cbcheckout_order_countries` WHERE `order_id` = ".intval($orderId)." AND `id` = ".intval($countryId);
		$db->setQuery($query);
		$country = $db->loadObject();
		if (!$country) {
			return NULL;
		}
		else {
			$country->custom_translatable_1 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 42, $countryId);
			$country->custom_translatable_1 = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 43, $countryId);
			return $country;
		}
	}

	/**
	 * @param int $countyId
	 * @return object $country
	 */
	static function &getCounty($countyId) {
		if (!$countyId) {
			$return = NULL;
			return $return;
		}

		if (!isset(self::$cache['counties'][$countyId])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_counties` WHERE `id` = ".(int)$countyId." LIMIT 1";
			$db->setQuery($query);
			self::$cache['counties'][$countyId] = $db->loadObject();
		}
		if (!isset(self::$cache['counties'][$countyId])) {
			$return = NULL;
			return $return;
		}
		else {
			return self::$cache['counties'][$countyId];
		}

	}

	static function getOrderCounty($countyId, $orderId) {
		if (!$countyId || !$orderId) {
			return NULL;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__cbcheckout_order_counties` WHERE `order_id` = ".intval($orderId)." AND `id` = ".intval($countyId);
		$db->setQuery($query);
		$county = $db->loadObject();
		if (!$county) {
			return NULL;
		}
		else {
			return $county;
		}
	}

	static function getCity($id) {

		if (!$id) return NULL;

		$query = "SELECT * FROM `#__configbox_cities` WHERE `id` = ".intval($id);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadObject();

	}

	static function getOrderCity($cityId, $orderId) {

		if (!$cityId || !$orderId) return NULL;

		$query = "SELECT * FROM `#__cbcheckout_order_cities` WHERE `id` = ".intval($cityId)." AND `order_id` = ".intval($orderId);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		return $db->loadObject();

	}

	/**
	 * @param string $name
	 * @param int $selected
	 * @param null|string $nullOptionLabel
	 * @param null|string $stateSelectId
	 * @param null|array|string $selectClasses
	 * @param string[] $attributes
	 * @param bool $useChosen   This indicates you show it as chosen drop-down. It will not do initialization for you.
	 *                          It only handles the nullOptionLabel for you.
	 * @return string
	 */
	static function createCountrySelect(
		$name,
		$selected = 0,
		$nullOptionLabel = NULL,
		$stateSelectId = NULL,
		$selectClasses = NULL,
		$attributes = array(),
		$useChosen = false
	)
	{
		$options = array();

		// use nullOption for placeholder in chosen drop-down
		if ($nullOptionLabel) {
			if ($useChosen) {
				$options[0] = "";
				$attributes['data-placeholder'] = $nullOptionLabel;
			}
			else {
				$options[0] = $nullOptionLabel;
			}
		}
		else {
			// system uses chosen drop-down and placeholder is set
			if ($useChosen && isset($attributes['data-placeholder'])) {
				$options[0] = "";
			}
		}

		$countries = self::getCountryList();

		foreach ($countries as &$country) {
			$options[$country['id']] = $country['country_name'];
		}

		if(is_array($selectClasses) && !empty($selectClasses)){
			$selectClasses = implode(" ", $selectClasses);
		}

		if ($stateSelectId) {
			$selectClass = 'updates-states chosen-dropdown ' . $selectClasses;
			$attributes2 = array('data-state-select-id' => $stateSelectId);
			if(!empty($attributes)) $attributes2 = array_merge($attributes, $attributes2);
		}
		else {
			$selectClass = 'chosen-dropdown ' . $selectClasses;
			if(!empty($attributes)) $attributes2 = $attributes;
			else $attributes2 = array();
		}

		$selectBox = KenedoHtml::getSelectField($name, $options, $selected, NULL, false, $selectClass, NULL, $attributes2);

		return $selectBox;

	}

	static function createStateSelect($name, $selectedId = 0, $countryId = 0, $nulloptionlabel = NULL, $countySelectId = NULL) {

		$options = self::getStateSelectOptions($countryId);

		if ($countySelectId) {
			$selectClass = 'updates-counties chosen-dropdown';
			$attributes = array('data-county-select-id' => $countySelectId);
		}
		else {
			$selectClass = 'chosen-dropdown';
			$attributes = array();
		}

		$selectBox = KenedoHtml::getSelectField($name, $options, $selectedId, NULL, false, $selectClass, NULL, $attributes);
		return $selectBox;

	}


	static function createCountySelect($name, $selected, $stateId, $nulloptionlabel = NULL, $citySelectId = NULL) {

		$options = array();

		if ($nulloptionlabel) {
			$options[0] = $nulloptionlabel;
		}

		$counties = self::getCountyList($stateId);
		foreach ($counties as &$county) {
			$options[$county->id] = $county->county_name;
		}

		if ($citySelectId) {
			$selectClass = 'updates-cities chosen-dropdown';
			$attributes = array('data-city-select-id' => $citySelectId);
		}
		else {
			$selectClass = 'chosen-dropdown';
			$attributes = array();
		}

		$selectBox = KenedoHtml::getSelectField($name, $options, $selected, NULL, false, $selectClass, NULL, $attributes);

		return $selectBox;

	}

	static function getCityInputField($name, $selected = 0, $nullOptionLabel = NULL, $countyId = NULL, $cityName = '') {

		if ($countyId && self::getCityList($countyId)) {
			return self::getCitySelect($name, $selected, $countyId, $nullOptionLabel = NULL);
		}
		else {
			return self::getCityTextInput($name, $cityName);
		}

	}

	static function getCityTextInput($name, $cityName) {
		$input = '<input class="form-control" type="text" name="'.$name.'" id="'.$name.'" placeholder="'.KText::_('City').'" value="'.hsc($cityName).'" />';
		return $input;
	}

	static function getCitySelect($name, $selected, $countyId, $nullOptionLabel = NULL) {

		if (!$countyId) {
			$options = array(KText::_('Select county first'));
			$selectBox = KenedoHtml::getSelectField($name, $options, $selected, NULL, false, 'chosen-dropdown', NULL, array());
			return $selectBox;
		}

		$options = array();
		if ($nullOptionLabel) {
			$options[0] = $nullOptionLabel;
		}

		$cities = self::getCityList($countyId);

		foreach ($cities as &$city) {
			$options[$city['id']] = $city['city_name'];
		}

		$selectClass = 'chosen-dropdown';
		$attributes = array();

		$selectBox = KenedoHtml::getSelectField($name, $options, $selected, NULL, false, $selectClass, NULL, $attributes);

		return $selectBox;

	}

	static function getStateSelectOptions($countryId = 0) {

		$options = array();

		if ($countryId) {
			$states = self::getStateList($countryId);

			if ($states) {
				$options[0] = KText::_('Select a state');

				foreach ($states as &$state) {
					$options[$state->id] = $state->name;
				}
			}
			else {
				$options[0] = KText::_('No state data');
			}

		}
		else {
			$options[0] = KText::_('Select a country first');
		}

		return $options;

	}

	static function getCountySelectOptions($stateId = 0) {

		$options = array();

		if ($stateId) {
			$counties = self::getCountyList($stateId);

			if ($counties) {
				$options[0] = KText::_('Select county');

				foreach ($counties as &$county) {
					$options[$county->id] = $county->county_name;
				}
			}
			else {
				$options[0] = KText::_('No county data');
			}

		}
		else {
			$options[0] = KText::_('Select a state first');
		}

		return $options;

	}

	static function getStateList($countryId) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__configbox_states` WHERE `country_id` = ".intval($countryId)." AND `published` = '1'";
		$db->setQuery($query);
		return $db->loadObjectList();

	}

	/**
	 * @param int $stateId
	 * @return null|object
	 */
	static function &getState($stateId) {

		if (!$stateId) {
			$var = NULL;
			return $var;
		}

		if (!isset(self::$cache['states'][$stateId])) {
			$query = "SELECT * FROM `#__configbox_states` WHERE `id` = ".intval($stateId);
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			self::$cache['states'][$stateId] = $db->loadObject();
		}

		return self::$cache['states'][$stateId];

	}

	static function getOrderState($stateId, $orderId) {

		if (!$stateId || !$orderId) {
			return NULL;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT * FROM `#__cbcheckout_order_states` WHERE `order_id` = ".intval($orderId)." AND `id` = ".intval($stateId);
		$db->setQuery($query);
		$state = $db->loadObject();
		if (!$state) {
			return NULL;
		}
		else {
			return $state;
		}
	}

}
