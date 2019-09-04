<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxUserHelper {

	const viesWsdl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
	static $error;
	static $errors = array();
	static $userFields;
	static $taxRates;
	static $memoizedPlatformGroupIds;
	static $salutations;
	static protected $userExistsCheckDone = false;

	/**
	 * @var ConfigboxUserData[]
	 */
	static $orderAddresses;

	/**
	 * @var ConfigboxUserData[]
	 */
	static $users;

	/**
	 * @return int (0 if there is no user yet)
	 */
	static function getUserId() {

		$userId = KSession::get('user_id', 0, 'com_configbox');

		// First call in runtime we check if the user wasn't actually deleted (e.g. by the cleanup script)
		// Think the possibility that session data is still around while the user record was cleaned up (or simply deleted in the meantime)
		if ($userId && self::$userExistsCheckDone == false) {
			self::$userExistsCheckDone = true;
			$query = "SELECT `id` FROM `#__configbox_users` WHERE `id` = ".intval($userId);
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			$exists = $db->loadResult();
			if (!$exists) {
				KSession::delete('user_id', 'com_configbox');
				return 0;
			}
		}
		return KSession::get('user_id', 0, 'com_configbox');
	}

	/**
	 * @param int $id
	 */
	static function setUserId($id) {
		KSession::set('user_id', intval($id), 'com_configbox');
	}

	/**
	 * @param string $name ID/name of the dropdown
	 * @param int|null $selected ID of selected salutation (NULL if there is none)
	 * @param string|string[] CSS classes for the dropdown
	 * @return string
	 */
	static function getSalutationDropdown($name = 'salutation_id', $selected = NULL, $cssClasses) {

		$salutations = self::getSalutations();

		$options = array();
		foreach ($salutations as $id=>$item) {
			$options[$id] = $item->title;
		}

		$selectBox = KenedoHtml::getSelectField($name, $options, $selected, NULL, false, $cssClasses);
		return $selectBox;
	}

	/**
	 * @param $salutationId
	 * @return object $salutation Object holding salutation info (see EntitySalutations)
	 */
	static function getSalutation($salutationId) {

		if (!$salutationId) {
			return NULL;
		}

		if (!isset(self::$salutations[KText::getLanguageTag()])) {
			self::getSalutations();
		}

		return self::$salutations[KText::getLanguageTag()][$salutationId];

	}

	/**
	 * @param int $salutationId
	 * @param int $orderId
	 * @return null|object
	 */
	static function getOrderSalutation($salutationId, $orderId) {

		if (!$salutationId || !$orderId) {
			return NULL;
		}

		$query = "SELECT * FROM `#__cbcheckout_order_salutations` WHERE `id` = ".intval($salutationId)." AND `order_id` = ".intval($orderId);
		$db = KenedoPlatform::getDb();
		$db->setQuery($query);
		$salutation = $db->loadObject();
		if (!$salutation) {
			return NULL;
		}
		else {
			$salutation->title = ConfigboxCacheHelper::getOrderTranslation($orderId, 'configbox_strings', 55, $salutationId);
		}

		return $salutation;

	}

	/**
	 * @return object[]
	 */
	static function getSalutations() {

		if (!isset(self::$salutations[KText::getLanguageTag()])) {
			$query = "SELECT * FROM `#__configbox_salutations`";
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			self::$salutations[KText::getLanguageTag()] = $db->loadObjectList('id');
			foreach ( self::$salutations[KText::getLanguageTag()] AS $id=>$salutation) {
				$salutation->title = ConfigboxCacheHelper::getTranslation('#__configbox_strings',55,$id);
			}
		}

		return self::$salutations[KText::getLanguageTag()];

	}

	/**
	 * @param int $customerGroupId
	 * @return int $platformGroupId
	 */
	static function getPlatformGroupId($customerGroupId = NULL) {

		if (!$customerGroupId) {
			$customerGroupId = CbSettings::getInstance()->get('default_customer_group_id');
		}

		if (empty(self::$memoizedPlatformGroupIds[$customerGroupId])) {
			$query = "SELECT `joomla_user_group_id` FROM `#__configbox_groups` WHERE `id` = ".(int)$customerGroupId;
			$db = KenedoPlatform::getDb();
			$db->setQuery($query);
			self::$memoizedPlatformGroupIds[$customerGroupId] = $db->loadResult();
		}

		return self::$memoizedPlatformGroupIds[$customerGroupId];

	}

	/**
	 * Returns the customer field definition array. Mind that the group id is ignored (definition per group are on ice)
	 * @return array of user fields
	 */
	static function getUserFields() {

		if (!isset(self::$userFields)) {

			$db = KenedoPlatform::getDb();
			$query = 'SELECT * FROM `#__configbox_user_field_definitions`';
			$db->setQuery($query);
			self::$userFields = $db->loadObjectList('field_name');

			$copy = (array)self::$userFields['billingcity'];
			self::$userFields['billingcity_id'] = $copy;
			self::$userFields['billingcity_id']['field_name'] = 'billingcity_id';
			self::$userFields['billingcity_id'] = (object)self::$userFields['billingcity_id'];

			$copy = (array)self::$userFields['city'];
			self::$userFields['city_id'] = $copy;
			self::$userFields['city_id']['field_name'] = 'city_id';
			self::$userFields['city_id'] = (object)self::$userFields['city_id'];
		}
		return self::$userFields;
	}

	/**
	 * @param string $email
	 * @return null|int
	 */
	static function getUserIdByEmail($email) {
		if (trim($email) == '') {
			return null;
		}
		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_users` WHERE `is_temporary` = '0' AND `billingemail` = '".$db->getEscaped($email)."'";
		$db->setQuery($query);
		$id = $db->loadResult();
		return ($id) ? intval($id) : NULL;
	}

	/**
	 * @param int $platformUserId
	 * @return int|NULL $userId (or NULL if there is no such user)
	 */
	static function getUserIdByPlatformUserId($platformUserId) {

		if (!$platformUserId) {
			return null;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT `id` FROM `#__configbox_users` WHERE `platform_user_id` = ".intval($platformUserId)." LIMIT 1";
		$db->setQuery($query);
		$id = $db->loadResult();

		return ($id) ? intval($id) : NULL;

	}

	/**
	 * Resets the memo cache for either one user or all
	 * @param int|null $userId (or NULL to reset the whole memo cache)
	 */
	static function resetUserCache($userId = NULL) {
		if ($userId) {
			if (isset(self::$users[$userId])) {
				unset(self::$users[$userId]);
			}
		}
		else {
			self::$users = array();
		}
	}

	/**
	 * Resets the memo cache for either one order or all
	 * @param int|null $orderId (or NULL to reset the whole memo cache)
	 */
	static function resetOrderAddressCache($orderId = NULL) {
		if ($orderId) {
			if (isset(self::$orderAddresses[$orderId])) {
				unset(self::$orderAddresses[$orderId]);
			}
		}
		else {
			self::$orderAddresses = array();
		}
	}

	/**
	 * @param int $userId ConfigBox user id (leave empty for the current user)
	 * @param bool $augment Ignored since 3.0 (user data will always be augmented)
	 * @param bool $init If you want an "inited" record if not found (holds all fields and goes through Geo IP)
	 * @return ConfigboxUserData|NULL User data (or NULL if not found and $init is false)
	 */
	static function getUser($userId = NULL, $augment = true, $init = true) {

		if ($userId == NULL) {
			$userId = self::getUserId();
		}

		if (!isset(self::$users[$userId])) {

			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__configbox_users` WHERE `id` = ".intval($userId);
			$db->setQuery($query);
			$user = $db->loadObject();

			if (!$user && $init == false) {
				return NULL;
			}

			if (!$user) {
				$user = self::initUserRecord();
			}

			if ($user) {
				self::$users[$userId] = $user;
			}

			self::augmentUserRecord(self::$users[$userId]);

		}

		return self::$users[$userId];

	}

	/**
	 * @param int|null $userId (or NULL for current session's user)
	 * @return int
	 */
	static function getGroupId($userId = NULL) {
		$user = self::getUser($userId);
		return $user->group_id;
	}

	/**
	 * @param ConfigboxUserData|ConfigboxDbUserData $orderAddress
	 * @param string $context
	 * @return bool
	 */
	static function orderAddressComplete($orderAddress, $context = 'checkout') {

		$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');
		$response = $model->validateData($orderAddress, $context);

		return $response;

	}

	/**
	 * Change the customer and platform user password
	 * The method is very complicated given the task. That is because we cannot rely on the platform to provide a
	 * direct way to get a password encrypted. So we need to store the platform user first, then retrieve the password.
	 *
	 * @param int $cbUserId
	 * @param string $newPassword
	 * @return bool $success
	 */
	static function changeUserPassword($cbUserId, $newPassword) {

		$db = KenedoPlatform::getDb();
		$query = "SELECT `platform_user_id` FROM `#__configbox_users` WHERE `id` = ".intval($cbUserId);
		$db->setQuery($query);
		$platformId = $db->loadResult();

		if (!$platformId) {
			KLog::log('Could not change the password for customer with ID "'.$cbUserId.'". The customer account is not connected to a platform account.','error');
			self::$error = KText::_('We could not change your password because of a system error. Please contact us to solve this issue.');
			return false;
		}

		// Keep a copy of the old password for later
		$oldPassword = KenedoPlatform::p()->getUserPasswordEncoded($platformId);

		// Change the platform user password
		$success = KenedoPlatform::p()->changeUserPassword($platformId, $newPassword);

		if ($success == false) {
			self::$error = KText::_('We could not change your password because of a system error. Please contact us to solve this issue.');
			return false;
		}

		// Now get the password
		$password = KenedoPlatform::p()->getUserPasswordEncoded($platformId);

		// Update the customer password
		$query = "UPDATE `#__configbox_users` SET `password` = '".$db->getEscaped($password)."' WHERE `id` = ".intval($cbUserId);
		$db->setQuery($query);
		$success = $db->query();

		if ($success) {
			return true;
		}
		else {
			// Change back the password to avoid out-of-sync issues
			KenedoPlatform::p()->changeUserPassword($platformId, $oldPassword);
			self::$error = KText::_('We could not change your password because of a system error. Please contact us to solve this issue.');
			return false;
		}
	}

	/**
	 * @param int $orderId
	 * @param bool $augment
	 * @param bool $init
	 * @return ConfigboxUserData
	 */
	static function getOrderAddress($orderId, $augment = true, $init = true) {

		if ($orderId === NULL) {
			$return = NULL;
			return $return;
		}

		if (!isset(self::$orderAddresses[$orderId])) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT * FROM `#__cbcheckout_order_users` WHERE `order_id` = ".intval($orderId)." LIMIT 1";
			$db->setQuery($query);
			$orderAddress = $db->loadObject();

			if (!$orderAddress && $init == false) {
				$return = NULL;
				return $return;
			}

			if (!$orderAddress && $init == true) {
				$orderAddress = self::initUserRecord();
			}

			self::$orderAddresses[$orderId] = $orderAddress;
		}

		if ($augment) {
			self::augmentUserRecord( self::$orderAddresses[$orderId], $orderId );
		}

		return self::$orderAddresses[$orderId];
	}

	/**
	 * @param int $orderId
	 * @param ConfigboxUserData|ConfigboxDbUserData|null $userData
	 * @param bool $wasUpdated
	 * @return bool
	 */
	static function setOrderAddress($orderId, $userData = NULL, $wasUpdated = true) {

		if ($userData === NULL) {
			$userData = self::getUser();
		}

		/**
		 * @var ConfigboxDbUserData $record
		 */
		$record = new stdClass();

		if ($userData) {

			$record->id 			= $userData->id;

			$record->platform_user_id = $userData->platform_user_id;
			$record->language_tag 	= $userData->language_tag;
			$record->vatin 			= $userData->vatin;
			$record->samedelivery 	= $userData->samedelivery;
			$record->group_id 		= $userData->group_id;
			$record->newsletter 	= $userData->newsletter;

			if (!isset($userData->custom_1)) {
				$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				KLog::log('Missing custom_1 in user data. This is the backtrace: '.var_export($trace, true), 'warning');
			}

			$record->custom_1 = $userData->custom_1;
			$record->custom_2 = $userData->custom_2;
			$record->custom_3 = $userData->custom_3;
			$record->custom_4 = $userData->custom_4;

			$record->billingcompanyname 	= $userData->billingcompanyname;
			$record->billingfirstname 		= $userData->billingfirstname;
			$record->billinglastname 		= $userData->billinglastname;
			$record->billinggender 			= $userData->billinggender;
			$record->billingsalutation_id 	= $userData->billingsalutation_id;
			$record->billingaddress1 		= $userData->billingaddress1;
			$record->billingaddress2 		= $userData->billingaddress2;
			$record->billingzipcode 		= $userData->billingzipcode;
			$record->billingcity 			= $userData->billingcity;
			$record->billingcountry 		= $userData->billingcountry;
			$record->billingstate 			= $userData->billingstate;
			$record->billingcounty_id		= $userData->billingcounty_id;
			$record->billingcity_id			= $userData->billingcity_id;
			$record->billingemail 			= $userData->billingemail;
			$record->billingphone 			= $userData->billingphone;


			// Duplicate billing to delivery info if same delivery
			if ($userData->samedelivery == 1) {
				$record->companyname 	= $userData->billingcompanyname;
				$record->firstname 		= $userData->billingfirstname;
				$record->lastname 		= $userData->billinglastname;
				$record->gender 		= $userData->billinggender;
				$record->salutation_id 	= $userData->billingsalutation_id;
				$record->address1 		= $userData->billingaddress1;
				$record->address2 		= $userData->billingaddress2;
				$record->zipcode 		= $userData->billingzipcode;
				$record->city 			= $userData->billingcity;
				$record->country 		= $userData->billingcountry;
				$record->state 			= $userData->billingstate;
				$record->county_id		= $userData->billingcounty_id;
				$record->city_id		= $userData->billingcity_id;
				$record->email 			= $userData->billingemail;
				$record->phone 			= $userData->billingphone;
			}
			else {
				$record->companyname 	= $userData->companyname;
				$record->firstname 		= $userData->firstname;
				$record->lastname 		= $userData->lastname;
				$record->gender 		= $userData->gender;
				$record->salutation_id 	= $userData->salutation_id;
				$record->address1 		= $userData->address1;
				$record->address2 		= $userData->address2;
				$record->zipcode 		= $userData->zipcode;
				$record->city 			= $userData->city;
				$record->country 		= $userData->country;
				$record->state 			= $userData->state;
				$record->county_id		= $userData->county_id;
				$record->city_id		= $userData->city_id;
				$record->email 			= $userData->email;
				$record->phone 			= $userData->phone;
			}

		}

		// Set the order id for storing in the order_ table
		$record->order_id = $orderId;

		// Set created time in case something went foul
		if (empty($record->created) || $record->created == '0000-00-00 00:00:00') {
			$record->created = KenedoTimeHelper::getNormalizedTime('NOW', 'datetime');
		}

		// Store the copies of location and salutation data
		if ($record->billingcountry) {
			self::storeOrderCountryData($record->billingcountry, $record->order_id);
		}
		if ($record->country && $record->country != $record->billingcountry) {
			self::storeOrderCountryData($record->country, $record->order_id);
		}

		if ($record->billingstate) {
			self::storeOrderStateData($record->billingstate, $record->order_id);
		}
		if ($record->state && $record->state != $record->billingstate) {
			self::storeOrderStateData($record->state, $record->order_id);
		}

		if ($record->billingcounty_id) {
			self::storeOrderCountyData($record->billingcounty_id, $record->order_id);
		}
		if ($record->county_id && $record->county_id != $record->billingcounty_id) {
			self::storeOrderCountyData($record->county_id, $record->order_id);
		}

		if ($record->billingcounty_id) {
			self::storeOrderCityData($record->billingcity_id, $record->order_id);
		}
		if ($record->city_id && $record->city_id != $record->billingcounty_id) {
			self::storeOrderCityData($record->city_id, $record->order_id);
		}

		if ($record->billingsalutation_id) {
			self::storeOrderSalutationData($record->billingsalutation_id, $record->order_id);
		}
		if ($record->salutation_id && $record->salutation_id != $record->billingsalutation_id) {
			self::storeOrderSalutationData($record->salutation_id, $record->order_id);
		}


		// Check if there is an entry already
		$db = KenedoPlatform::getDb();
		$baseDataStored = $db->insertObject('#__cbcheckout_order_users', $record, 'id');

		// Bounce on failure
		if ($baseDataStored == false) {
			$internalMessage = 'Could not set order user record. SQL error is "'.$db->getErrorMsg().'". Record is '.var_export($record,true);
			KLog::log($internalMessage, 'error', KText::_('A system error occured during storing your order\'s address information'));
			return false;
		}

		if ($baseDataStored) {

			if (KRequest::getVar('comment',NULL) !== NULL) {
				$comment = KRequest::getString('comment');
				$db = KenedoPlatform::getDb();
				$query = "UPDATE `#__cbcheckout_order_records` SET `comment` = '".$db->getEscaped($comment)."' WHERE `id` = ".(int)$orderId;
				$db->setQuery($query);
				$db->query();
			}

			// Augment and cache the order record

			self::$orderAddresses[$orderId] = $record;

			if ($wasUpdated) {
				self::augmentUserRecord($record, $orderId);
				KenedoObserver::triggerEvent( 'onConfigBoxUpdateOrderAddress' , array(self::$orderAddresses[$orderId], $orderId) );
			}


		}

		return $baseDataStored;

	}


	static function storeOrderCountryData($countryId, $orderId) {
		$data = ConfigboxCountryHelper::getCountry($countryId);
		if (!$data) {
			return false;
		}

		$db = KenedoPlatform::getDb();
		$record = clone $data;
		$record->order_id = $orderId;
		unset($record->custom_translatable_1, $record->custom_translatable_2);
		$success = $db->insertObject('#__cbcheckout_order_countries', $record);

		if ($success) {
			ConfigboxCacheHelper::copyTranslationToOrder($orderId, '#__configbox_strings', 42, $countryId);
			ConfigboxCacheHelper::copyTranslationToOrder($orderId, '#__configbox_strings', 43, $countryId);
		}

		return $success;

	}

	static function storeOrderStateData($stateId, $orderId) {
		$data = ConfigboxCountryHelper::getState($stateId);
		if (!$data) {
			return false;
		}

		$db = KenedoPlatform::getDb();
		$record = clone $data;
		$record->order_id = $orderId;
		$success = $db->insertObject('#__cbcheckout_order_states', $record);

		return $success;

	}

	static function storeOrderCountyData($countyId, $orderId) {
		$data = ConfigboxCountryHelper::getCounty($countyId);
		if (!$data) {
			return false;
		}

		$db = KenedoPlatform::getDb();
		$record = clone $data;
		$record->order_id = $orderId;
		$success = $db->insertObject('#__cbcheckout_order_counties', $record);

		return $success;

	}

	static function storeOrderCityData($cityId, $orderId) {
		$data = ConfigboxCountryHelper::getCity($cityId);
		if (!$data) {
			return false;
		}

		$db = KenedoPlatform::getDb();
		$record = clone $data;
		$record->order_id = $orderId;
		$success = $db->insertObject('#__cbcheckout_order_cities', $record);

		return $success;

	}

	static function storeOrderSalutationData($salutationId, $orderId) {
		$data = self::getSalutation($salutationId);
		if (!$data) {
			return false;
		}

		$db = KenedoPlatform::getDb();
		$record = clone $data;
		$record->order_id = $orderId;
		unset($record->title);
		$success = $db->insertObject('#__cbcheckout_order_salutations', $record);

		if ($success) {
			ConfigboxCacheHelper::copyTranslationToOrder($orderId, '#__configbox_strings', 55, $salutationId);
		}

		return $success;

	}

	/**
	 * @param int $groupId
	 * @return ConfigboxGroupData|null Group data or NULL if group doesn't exist
	 */
	static function getGroupData($groupId) {
		return ConfigboxCacheHelper::getGroupData($groupId);
	}

	/**
	 * Adds country, state, county, city, gender and salutation info to the bare user record.
	 * BE AWARE: Alters the provided $user parameter var, does not return it
	 * Augments user records as well as order address (use $orderId param)
	 *
	 * @param ConfigboxDbUserData|ConfigboxUserData $user Bare user record (augmenting twice is fine), comes in referenced
	 * @param int $orderId Order ID, when provided, data is taken from the order_ tables instead
	 *
	 * @return bool false bare record isn't provided, true otherwise
	 */
	static function augmentUserRecord(&$user, $orderId = NULL) {

		if (!$user) {
			return false;
		}

		// Name of the customer's preferred language
		$user->language_name = '';

		if ($user->language_tag) {
			$language = KenedoLanguageHelper::getLanguageByTag($user->language_tag);
			if ($language) {
				$user->language_name = $language->label;
			}
		}

		// Country data for delivery
		$user->country_2_code = '';
		$user->country_3_code = '';
		$user->countryname   = '';

		if ($user->country) {

			if ($orderId) {
				$country = ConfigboxCountryHelper::getOrderCountry($user->country, $orderId);
			}
			else {
				$country = ConfigboxCountryHelper::getCountry($user->country);
			}

			if ($country) {
				$user->country_2_code 	= $country->country_2_code;
				$user->country_3_code 	= $country->country_3_code;
				$user->countryname  	= $country->country_name;
			}

		}

		// Country data for billing
		$user->billingcountry_2_code 	= '';
		$user->billingcountry_3_code 	= '';
		$user->billingcountryname   	= '';

		if ($user->billingcountry) {

			if ($orderId) {
				$country = ConfigboxCountryHelper::getOrderCountry($user->billingcountry, $orderId);
			}
			else {
				$country = ConfigboxCountryHelper::getCountry($user->country);
			}

			if ($country) {
				$user->billingcountry_2_code 	= $country->country_2_code;
				$user->billingcountry_3_code 	= $country->country_3_code;
				$user->billingcountryname   	= $country->country_name;
			}

		}

		// State data for delivery
		$user->statecode = '';
		$user->statefips = '';
		$user->statename = '';

		if ($user->state) {

			if ($orderId) {
				$state = ConfigboxCountryHelper::getOrderState($user->state, $orderId);
				if (!$state) {
					$state = ConfigboxCountryHelper::getState($user->state);
				}
			}
			else {
				$state = ConfigboxCountryHelper::getState($user->state);
			}

			if ($state) {
				$user->statecode = $state->iso_code;
				$user->statefips = $state->fips_number;
				$user->statename = $state->name;
			}
			else {
				KLog::log('State data not found for delivery state ID "'.$user->state.'". var_export on state ID gives us '.var_export($user->state).'. Whole user data is '.var_export($user, true), 'error');
			}

		}


		// State data for billing
		$user->billingstatecode = '';
		$user->billingstatefips = '';
		$user->billingstatename = '';

		if ($user->billingstate) {

			if ($orderId) {
				$state = ConfigboxCountryHelper::getOrderState($user->billingstate, $orderId);
				if (!$state) {
					$state = ConfigboxCountryHelper::getState($user->billingstate);
				}
			}
			else {
				$state = ConfigboxCountryHelper::getState($user->billingstate);
			}

			if ($state) {
				$user->billingstatecode = $state->iso_code;
				$user->billingstatefips = $state->fips_number;
				$user->billingstatename = $state->name;
			}
			else {
				KLog::log('State data not found for billing state ID "'.$user->state.'". var_export on state ID gives us '.var_export($user->billingstate).'. Whole user data is '.var_export($user, true), 'error');
			}

		}

		// County data for delivery
		if ($user->county_id) {
			if ($orderId) {
				$county = ConfigboxCountryHelper::getOrderCounty($user->county_id, $orderId);
			}
			else {
				$county = ConfigboxCountryHelper::getCounty($user->county_id);
			}

			$user->county = $county->county_name;
		}
		else {
			$user->county = '';
		}

		// County data for billing
		if ($user->billingcounty_id) {

			if ($orderId) {
				$county = ConfigboxCountryHelper::getOrderCounty($user->billingcounty_id, $orderId);
			}
			else {
				$county = ConfigboxCountryHelper::getCounty($user->billingcounty_id);
			}

			$user->billingcounty = $county->county_name;
		}
		else {
			$user->billingcounty = '';
		}

		// City data for delivery
		if ($user->city_id) {
			if ($orderId) {
				$city = ConfigboxCountryHelper::getOrderCity($user->city_id, $orderId);
			}
			else {
				$city = ConfigboxCountryHelper::getCity($user->city_id);
			}

			$user->city = $city->city_name;
		}

		// City data for billing
		if ($user->billingcity_id) {
			if ($orderId) {
				$city = ConfigboxCountryHelper::getOrderCity($user->billingcity_id, $orderId);
			}
			else {
				$city = ConfigboxCountryHelper::getCity($user->billingcity_id);
			}

			$user->billingcity = $city->city_name;
		}

		// Salutation data for delivery
		if ($user->salutation_id) {

			if ($orderId) {
				$salutation = self::getOrderSalutation($user->salutation_id, $orderId);
			} else {
				$salutation = self::getSalutation($user->salutation_id);
			}

			$user->salutation = $salutation->title;
			// There's some nasty stuff going on (during data preparing in entity, that field is actually figured out and also stored in the DB)
			// At some point that entity prepare thing should go away, this here will be the replacement
			$user->gender = $salutation->gender;

		}
		else {
			$user->salutation = '';
			// There's some nasty stuff going on (during data preparing in entity, that field is actually figured out and also stored in the DB)
			// At some point that entity prepare thing should go away, this here will be the replacement
			$user->gender = (!empty($user->gender)) ? $user->gender : 1;
		}

		// Salutation data for billing
		if ($user->billingsalutation_id) {

			if ($orderId) {
				$salutation = self::getOrderSalutation($user->billingsalutation_id, $orderId);
			} else {
				$salutation = self::getSalutation($user->billingsalutation_id);
			}

			$user->billingsalutation = $salutation->title;
			// There's some nasty stuff going on (during data preparing in entity, that field is actually figured out and also stored in the DB)
			// At some point that entity prepare thing should go away, this here will be the replacement
			$user->billinggender = $salutation->gender;

		}
		else {
			$user->billingsalutation = '';
			// There's some nasty stuff going on (during data preparing in entity, that field is actually figured out and also stored in the DB)
			// At some point that entity prepare thing should go away, this here will be the replacement
			$user->billinggender = (!empty($user->billinggender)) ? $user->billinggender : 1;
		}

		return true;

	}

	/**
	 * @param int $length
	 * @return string Password of defined length
	 */
	static function getPassword($length) {

		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';

		$stat = @stat(__FILE__);
		if(empty($stat) || !is_array($stat)) $stat = array(php_uname());

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}

		return $makepass;

	}

	/**
	 * @param ConfigboxUserData $cbUser ConfigBox user record
	 * @param string $passwordClear Password in clear text (leave empty to auto-generate)
	 * @return bool|object false on error, object holding registration data
	 */
	static function registerPlatformUser($cbUser, $passwordClear = NULL) {

		self::$error = null;

		if (!$passwordClear) {
			$passwordClear = self::getPassword(8);
		}

		$user = new stdClass();
		$user->email 		= $cbUser->billingemail;
		$user->username 	= $cbUser->billingemail;
		$user->password 	= $passwordClear;
		$user->password2 	= $passwordClear;
		$user->name 		= $cbUser->billingfirstname . ' ' . $cbUser->billinglastname;

		$platformGroupIds				= array(self::getPlatformGroupId($cbUser->group_id));

		// The session value notifies the onAfterStoreUser method not to create a new customer record
		// That is because the method looks for a joomla id field to decide. This cannot be set before actually having a user registered. So..

		KSession::set('noUserSetup',true);
		$newUser = KenedoPlatform::p()->registerUser($user, $platformGroupIds);
		KSession::delete('noUserSetup');

		if ($newUser == false) {
			self::addError( KenedoPlatform::p()->getError() );
			return false;
		}

		$registrationData = new stdClass();

		$registrationData->platformUserId = $newUser->id;
		$registrationData->username = $newUser->username;
		$registrationData->passwordClear = $passwordClear;
		$registrationData->passwordEncrypted = $newUser->password;

		return $registrationData;

	}

	/**
	 * @param ConfigboxUserData $user Configbox user record
	 * @param string $passwordClear
	 * @return bool $success
	 */
	static function sendRegistrationEmail($user, $passwordClear) {

		// Get the store information for email data
		$shopData = ConfigboxStoreHelper::getStoreRecord();

		// Load the registration view content
		$registrationView = KenedoView::getView('ConfigboxViewEmailcustomerregistration');
		$registrationView->assign('shopData', $shopData);
		$registrationView->assign('customer', $user);
		$registrationView->assign('passwordClear', $passwordClear);

		$registrationHtml = $registrationView->getViewOutput('default');

		// Load the general email template and put the registration view content in it
		$emailView = KenedoView::getView('ConfigboxViewEmailtemplate');
		$emailView->prepareTemplateVars();
		$emailView->assign('emailContent', $registrationHtml);
		$registrationHtml = $emailView->getViewOutput('default');

		// Use shop data email sales (and fall back to platform's mailer address
		$fromEmail = $shopData->shopemailsales;

		if (empty($fromEmail)) {
			$fromEmail = KenedoPlatform::p()->getMailerFromEmail();
		}

		// Use shop data company name (and fall back to platform's mailer name
		$fromName = $shopData->shopname;

		if (empty($fromName)) {
			$fromName = KenedoPlatform::p()->getMailerFromName();
		}

		// Prepare the email data
		$email = new stdClass();
		$email->senderEmail = $fromEmail;
		$email->senderName	= $fromName;
		$email->receipient = $user->billingemail;
		$email->subject = KText::sprintf('EMAIL_CUSTOMER_REGISTRATION_SUBJECT',$shopData->shopname);
		$email->body = $registrationHtml;
		$email->mode = 1;

		// Send the email
		$sendSuccess = KenedoPlatform::p()->sendEmail($email->senderEmail, $email->senderName, $email->receipient, $email->subject, $email->body, $email->mode);

		if (!$sendSuccess) {
			self::$error = KText::_('Registration email could not get sent.');
			KLog::log('Registration email could not get sent to "'.$email->senderEmail.'".','warning');
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * @param int $userId ConfigBox user id
	 * @param string $password Clear text password
	 * @return bool $success
	 */
	static function authenticateUser($userId, $password) {
		$user = self::getUser($userId, NULL, false);
		if (!$user) {
			return false;
		}
		$response = KenedoPlatform::p()->authenticate($user->billingemail, $password, array());
		return $response;
	}

	/**
	 * @param int $userId ConfigBox user id
	 * @return bool $success
	 */
	static function loginUser($userId) {

		$user = self::getUser($userId, NULL, false);

		if (!$user) {
			return false;
		}

		if (!$user->platform_user_id) {
			return false;
		}

		$username = KenedoPlatform::p()->getUserName($user->platform_user_id);
		return KenedoPlatform::p()->login($username);

	}

	/**
	 * @return string[]
	 */
	static function getUserFieldTranslations() {
		return array(
			'companyname'=>KText::_('Delivery Company Name'),
			'salutation_id'=>KText::_('Delivery Salutation'),
			'firstname'=>KText::_('Delivery First Name'),
			'lastname'=>KText::_('Delivery Last Name'),
			'address1'=>KText::_('Delivery Address 1'),
			'address2'=>KText::_('Delivery Address 2'),
			'zipcode'=>KText::_('Delivery ZIP Code'),
			'city'=>KText::_('Delivery City'),
			'city_id'=>KText::_('Delivery City'),
			'country'=>KText::_('Delivery Country'),
			'state'=>KText::_('Delivery State'),
			'email'=>KText::_('Delivery Email'),
			'phone'=>KText::_('Delivery Phone'),
			'language_tag'=>KText::_('Preferred Language'),

			'vatin'=>KText::_('VAT IN'),

			'billingcompanyname'=>KText::_('Billing Company Name'),
			'billingsalutation_id'=>KText::_('Billing Salutation'),
			'billingfirstname'=>KText::_('Billing First Name'),
			'billinglastname'=>KText::_('Billing Last Name'),
			'billingaddress1'=>KText::_('Billing Address'),
			'billingaddress2'=>KText::_('Billing Address 2'),
			'billingzipcode'=>KText::_('Billing ZIP Code'),
			'billingcity'=>KText::_('Billing City'),
			'billingcity_id'=>KText::_('Billing City'),
			'billingcountry'=>KText::_('Billing Country'),
			'billingstate'=>KText::_('Billing State'),
			'billingemail'=>KText::_('Billing Email'),
			'billingphone'=>KText::_('Billing Phone'),

			'samedelivery'=>KText::_('Use billing address for shipping'),
			'newsletter'=>KText::_('Newsletter'),

			'billingcounty_id'=>KText::_('Billing County'),
			'county_id'=>KText::_('Delivery County'),

		);
	}

	/**
	 * @return ConfigboxDbUserData
	 */
	static function initUserRecord() {

		/**
		 * @var ConfigboxDbUserData $info
		 */
		$info = new stdClass();

		$model = KenedoModel::getModel('ConfigboxModelAdmincustomers');
		$properties = $model->getProperties();

		foreach ($properties as $property) {

			if ($property->getType() == 'groupstart' || $property->getType() == 'groupend') {
				continue;
			}

			if ($property->getType() == 'join') {
				$info->{$property->propertyName} = NULL;
			}
			else {
				$info->{$property->propertyName} = '';
			}

		}

		$info->is_temporary = 1;
		$info->group_id = CbSettings::getInstance()->get('default_customer_group_id');
		$info->language_tag = KText::getLanguageTag();
		$info->newsletter = CbSettings::getInstance()->get('newsletter_preset');
		$info->created = KenedoTimeHelper::getFormattedOnly('NOW', 'datetime');

		// Yes, it is vice versa
		$info->samedelivery = (CbSettings::getInstance()->get('alternate_shipping_preset')) ? 0 : 1;

		if (CbSettings::getInstance()->get('enable_geolocation')) {

			$locationData = ConfigboxLocationHelper::getLocationByIp();

			if ($locationData !== false) {

				if ($locationData->city) {
					$info->city = $locationData->city;
					$info->billingcity = $locationData->city;
				}

				if ($locationData->zipcode) {
					$info->zipcode = $locationData->zipcode;
					$info->billingzipcode = $locationData->zipcode;
				}

				if ($locationData->countryCode) {
					$id = ConfigboxCountryHelper::getCountryIdByCountry2Code( $locationData->countryCode );
					$info->country = $id;
					$info->billingcountry = $id;
				}

				if ($locationData->stateFips) {
					$id = ConfigboxCountryHelper::getStateIdByFipsNumber( $info->country, $locationData->stateFips );
					$info->state = $id;
					$info->billingstate = $id;
				}

			}

		}

		if (!$info->country) {
			$info->country = CbSettings::getInstance()->get('default_country_id');
		}

		if (!$info->billingcountry) {
			$info->billingcountry = CbSettings::getInstance()->get('default_country_id');
		}

		return $info;

	}

	/**
	 * @return int $userId
	 */
	static function createNewUser() {

		$recordToSave = self::initUserRecord();

		$db = KenedoPlatform::getDb();
		$success = $db->insertObject('#__configbox_users', $recordToSave, 'id');

		if ($success == false) {
			return false;
		}
		else {
			return $recordToSave->id;
		}

	}

	/**
	 * Changes ownership over carts and orders
	 * @param int $oldUserId
	 * @param int $newUserId
	 * @return bool
	 */
	static function moveUserOrders($oldUserId, $newUserId) {

		if (!$oldUserId || !$newUserId) {
			return false;
		}

		if ($oldUserId == $newUserId) {
			return true;
		}

		$db = KenedoPlatform::getDb();

		$query = "UPDATE `#__cbcheckout_order_users` SET `id` = ".(int)$newUserId." WHERE `id` = ".(int)$oldUserId;
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__cbcheckout_order_records` SET `user_id` = ".(int)$newUserId." WHERE `user_id` = ".(int)$oldUserId;
		$db->setQuery($query);
		$db->query();

		$query = "UPDATE `#__configbox_carts` SET `user_id` = ".(int)$newUserId." WHERE `user_id` = ".(int)$oldUserId;
		$db->setQuery($query);
		$db->query();

		return true;

	}

	/**
	 * Basically terminates the session for the current user, that's all.
	 * Just not in the backend - it's just because we got no username for CB's session row and it doesn't matter.
	 */
	static function logoutUser() {

		$isAdminArea = KenedoPlatform::p()->isAdminArea();

		if ($isAdminArea) {
			return;
		}

		KSession::terminateSession();

	}

	/**
	 * Checks against order address data tables if order id is provided
	 * @param ConfigboxUserData|ConfigboxDbUserData $userRecord Object holding ConfigBox user record
	 * @param int $orderId, the order id (optional)
	 * @return boolean
	 */
	static function isVatFree($userRecord, $orderId = NULL) {

		if ($orderId) {
			$deliveryCountry = ConfigboxCountryHelper::getOrderCountry($userRecord->country, $orderId);
			$billingCountry = ConfigboxCountryHelper::getOrderCountry($userRecord->billingcountry, $orderId);
		}
		else {
			$deliveryCountry = ConfigboxCountryHelper::getCountry($userRecord->country);
			$billingCountry = ConfigboxCountryHelper::getCountry($userRecord->billingcountry);
		}

		$isVatFree = false;

		if ($billingCountry && $deliveryCountry) {

			// If both countries are vat free - OK
			if ($billingCountry->vat_free && $deliveryCountry->vat_free) {
				$isVatFree = true;
			}

			// If both countries are in the EU VAT area and VAT IN is present - OK
			if ($billingCountry->in_eu_vat_area && $deliveryCountry->in_eu_vat_area && $userRecord->vatin) {
				$isVatFree = true;
			}
			// If billing country is in the EU VAT area and delivery country is vat free and VAT IN - OK
			if ($billingCountry->in_eu_vat_area && $deliveryCountry->vat_free && $userRecord->vatin) {
				$isVatFree = true;
			}

		}
		elseif ($billingCountry) {

			if ($billingCountry->vat_free) {
				$isVatFree = true;
			}

			if ($billingCountry->in_eu_vat_area && $userRecord->vatin) {
				$isVatFree = true;
			}

		}

		return $isVatFree;
	}

	/**
	 * @param int $taxClassId
	 * @param ConfigboxDbUserData|ConfigboxDbUserData $orderAddress
	 * @param bool $checkVatFree
	 * @return float
	 */
	static function getTaxRate($taxClassId, $orderAddress, $checkVatFree = true) {

		// If this is VAT free, tax rate is zero in any case
		if ($checkVatFree && self::isVatFree($orderAddress)) {
			return 0;
		}

		$countryId = strval(intval($orderAddress->country));
		$stateId = strval(intval($orderAddress->state));
		$countyId = strval(intval($orderAddress->county_id));
		$cityId = strval(intval($orderAddress->city_id));

		if (!isset(self::$taxRates[$taxClassId][$countryId][$stateId][$countyId][$cityId])) {

			$db = KenedoPlatform::getDb();
			$query = "
			SELECT *
			FROM `#__configbox_tax_classes` AS tcr
			LEFT JOIN `#__configbox_tax_class_rates` AS tc 
				ON 
				tc.tax_class_id = ".(int)$taxClassId." 
				AND ( 
					(tc.state_id IS NOT NULL AND tc.state_id = ".intval($stateId).") 
					OR 
					(tc.country_id IS NOT NULL AND tc.country_id = ".intval($countryId)."))
			WHERE tcr.id = ".(int)$taxClassId."
			ORDER BY tc.state_id DESC LIMIT 1
			";

			$db->setQuery($query);
			$taxRate = $db->loadAssoc();

			// If we got one, return it, else continue where we get to the default configbox taxrate
			if ($taxRate['tax_rate'] !== NULL) {
				$taxRate = (float)$taxRate['tax_rate'];
			}
			else if ($taxRate['tax_rate'] === NULL && $taxRate['default_tax_rate'] !== NULL) {
				$taxRate = (float)$taxRate['default_tax_rate'];
			}
			else {
				KLog::log('Could not find tax rate for tax class id: '.$taxClassId, 'error', KText::_('Could not find tax rate for tax class id: "'.$taxClassId.'"'));
				return 0;
			}

			self::$taxRates[$taxClassId][$countryId][$stateId][$countyId][$cityId] = $taxRate;

			// Add county and city tax
			$db = KenedoPlatform::getDb();
			$query = "
			SELECT tcr.*
			FROM `#__configbox_tax_classes` AS tc
			LEFT JOIN `#__configbox_tax_class_rates` AS tcr 
				ON 
					tcr.tax_class_id = tc.id 
					AND ( 
						(tcr.county_id IS NOT NULL AND tcr.county_id = ".intval($countyId).") 
						OR 
						(tcr.city_id IS NOT NULL AND tcr.city_id = ".intval($cityId).") )
						
			WHERE tc.id = ".intval($taxClassId)."
			ORDER BY tcr.county_id DESC, tcr.city_id DESC
			";
			$db->setQuery($query);
			$taxRates = $db->loadAssocList();
			if ($taxRates) {
				foreach ($taxRates as $taxRate) {
					self::$taxRates[$taxClassId][$countryId][$stateId][$countyId][$cityId] += floatval($taxRate['tax_rate']);
				}
			}

		}

		return self::$taxRates[$taxClassId][$countryId][$stateId][$countyId][$cityId];

	}


	/**
	 * This method is used by the user plugin for Joomla. It is an event handler, not a method to login someone
	 * @param array $platformUser
	 * @return bool
	 */
	static function onLoginUser($platformUser) {

		$isAdminArea = KenedoPlatform::p()->isAdminArea();

		if ($isAdminArea) {
			return true;
		}

		// Remember old user id
		$oldUserId = KSession::get('user_id', 0, 'com_configbox');

		$platformUserId = KenedoPlatform::p()->getUserIdByUsername($platformUser['username']);

		// See if there is a customer with that platform user id
		$userId = self::getUserIdByPlatformUserId($platformUserId);

		// If there is none, create one
		if (!$userId) {
			$userId = self::createNewUser();
		}

		$fullName = '';
		if (!empty($platformUser['name'])) {
			$fullName = $platformUser['name'];
		}
		elseif(!empty($platformUser['fullname'])) {
			$fullName = $platformUser['fullname'];
		}

		// Extract first and last name from platform's name
		$nameParts = explode(' ', $fullName);

		if (count($nameParts) > 1) {
			$lastName = array_pop($nameParts);
			$firstName = implode(' ',$nameParts);
		}
		else {
			$firstName = 'No first name';
			$lastName = $fullName;
		}

		// If the platform didn't send over the user's email address, but the username appears to be an email address,
		// then use that username as email address.
		if (empty($platformUser['email'])) {
			if (filter_var($platformUser['username'], FILTER_VALIDATE_EMAIL) == $platformUser['username']) {
				$platformUser['email'] = $platformUser['username'];
			}
		}

		$password = KenedoPlatform::p()->getUserPasswordEncoded($platformUserId);

		$db = KenedoPlatform::getDb();
		$query = "
			UPDATE `#__configbox_users` SET
			`platform_user_id` = ".intval($platformUserId).",
			
			`billingfirstname` = '".$db->getEscaped($firstName)."',
			`billinglastname` = '".$db->getEscaped($lastName)."',
			
			`firstname` = '".$db->getEscaped($firstName)."',
			`lastname` = '".$db->getEscaped($lastName)."',
			
			`is_temporary` = '0',
			`password` = '".$db->getEscaped($password)."'";

		if (!empty($platformUser['email'])) {
			$query .= "
			,`billingemail` = '".$db->getEscaped($platformUser['email'])."',
			`email` = '".$db->getEscaped($platformUser['email'])."'
			";
		}

		$query .= "WHERE `id` = ".intval($userId);

		$db->setQuery($query);
		$db->query();

		// Set session var
		KSession::set('user_id', $userId, 'com_configbox');

		// If user id changed, update old user's records
		if ($oldUserId != 0) {
			self::moveUserOrders($oldUserId, $userId);
		}

		return true;
	}

	static function deleteUser($platformUser) {
		$platformUser = (array)$platformUser;

		$userId = self::getUserIdByPlatformUserId($platformUser['id']);

		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_users` WHERE `id` = ".intval($userId);
		$db->setQuery($query);
		$response = $db->query();
		return (bool) $response;
	}

	/**
	 * That method used by the user plugin for Joomla
	 * @param string[] $platformUser
	 * @param bool $isNew
	 * @param bool $success
	 * @param string $msg
	 * @return bool
	 */
	static function onAfterStoreUser($platformUser, $isNew, $success, $msg) {

		// This session variable is set in the registerPlatformUser method to prevent this method to set up a second user account.
		if (KSession::get('noUserSetup',false) == true) {
			return true;
		}

		// See if there is a customer with that platform user id
		$userId = self::getUserIdByPlatformUserId($platformUser['id']);

		// If there is none, create one
		if (!$userId) {
			$userId = self::createNewUser();
		}

		// Extract first and last name from platform's name
		$nameParts = explode(' ', $platformUser['name']);

		if (count($nameParts) > 1) {
			$lastName = array_pop($nameParts);
			$firstName = implode(' ',$nameParts);
		}
		else {
			$firstName = 'No first name';
			$lastName = $platformUser['name'];
		}

		$db = KenedoPlatform::getDb();
		$query = "
			UPDATE `#__configbox_users` SET
			`platform_user_id` = ".intval($platformUser['id']).",
			`billingfirstname` = '".$db->getEscaped($firstName)."',
			`billinglastname` = '".$db->getEscaped($lastName)."',
			`billingemail` = '".$db->getEscaped($platformUser['email'])."',
			`firstname` = '".$db->getEscaped($firstName)."',
			`lastname` = '".$db->getEscaped($lastName)."',
			`email` = '".$db->getEscaped($platformUser['email'])."',
			`is_temporary` = '0',
			`password` = '".$db->getEscaped($platformUser['password'])."'
			WHERE `id` = ".intval($userId);
		$db->setQuery($query);
		$db->query();

		if ($userId) {
			self::resetUserCache($userId);
			$customer = self::getUser($userId);
			KenedoObserver::triggerEvent('onConfigBoxUpdateUserInfo', array($customer) );
		}

		return true;

	}

	/**
	 * That method used by the authentication plugin for Joomla
	 * To check for authentication, use ConfigboxUserHelper::authenticate
	 * @param $credentials
	 * @param $options
	 * @param $response
	 * @return bool
	 */
	static function onAuthenticate( $credentials, $options, &$response ) {

		if (empty($credentials['username']) || empty($credentials['password'])) {
			self::addError(KText::_('Username or password incorrect.'));
			/** @noinspection PhpDeprecationInspection */
			$response->status = (defined('JAUTHENTICATE_STATUS_FAILURE')) ? JAUTHENTICATE_STATUS_FAILURE : JAuthentication::STATUS_FAILURE;
			$response->error_message	= '';
			return false;
		}

		$username = $credentials['username'];
		$passwordClear = $credentials['password'];

		$userId = self::getUserIdByEmail($username);
		$user = self::getUser($userId, NULL, false);

		if (!$user) {
			self::addError(KText::_('Username or password incorrect.'));
			/** @noinspection PhpDeprecationInspection */
//			$response->status = (defined('JAUTHENTICATE_STATUS_FAILURE')) ? JAUTHENTICATE_STATUS_FAILURE : JAuthentication::STATUS_FAILURE;
//			$response->error_message	= '';
			return null;
		}

		$passwordEncoded = KenedoPlatform::p()->getUserPasswordEncoded($user->platform_user_id);

		if (!$passwordEncoded) {
			self::addError(KText::_('Username or password incorrect.'));
			/** @noinspection PhpDeprecationInspection */
			$response->status = (defined('JAUTHENTICATE_STATUS_FAILURE')) ? JAUTHENTICATE_STATUS_FAILURE : JAuthentication::STATUS_FAILURE;
			$response->error_message	= '';
			return false;
		}

		$passwordsMatch = KenedoPlatform::p()->passwordsMatch($passwordClear, $passwordEncoded);

		if ($passwordsMatch == false) {
			self::addError(KText::_('Username or password incorrect.'));
			/** @noinspection PhpDeprecationInspection */
			$response->status = (defined('JAUTHENTICATE_STATUS_FAILURE')) ? JAUTHENTICATE_STATUS_FAILURE : JAuthentication::STATUS_FAILURE;
			$response->error_message	= '';
			return false;
		}

		/** @noinspection PhpDeprecationInspection */
		$response->status 			= (defined('JAUTHENTICATE_STATUS_SUCCESS')) ? JAUTHENTICATE_STATUS_SUCCESS : JAuthentication::STATUS_SUCCESS;
		$response->error_message	= '';

		$response->email = $user->billingemail;
		$response->fullname = $user->billingfirstname . ' ' . $user->billinglastname;
		$response->username = 'dummy';
		$response->language = $user->language_tag;

		if (!empty($user->platform_user_id)) {
			$username = KenedoPlatform::p()->getUserName($user->platform_user_id);
			if ($username) {
				$response->username = $username;
			}
		}

		return true;

	}

	/**
	 * @param string $vatIn
	 * @param int $billingCountryId
	 * @return bool
	 */
	static function checkVatIn($vatIn, $billingCountryId) {

		/*
		 * Store the default socket time out to change it back later (it is lowered to prevent
		 * unnecessary long overall processing timings).
		 */

		$originalSocketTimeout = ini_get("default_socket_timeout");

		// Bounce if the SOAP extension isn't installed on the server
		if (class_exists('SoapClient') == false) {
			KLog::log('The server does not support SOAP. VAT IN check cannot be done.','warning');
			return true;
		}

		self::$error = '';

		try {
			$vies = new SoapClient(self::viesWsdl, array("connection_timeout" => 3));
		}
		catch (Exception $e) {
			//KenedoPlatform::p()->sendMessage(KText::_('The VAT-IN validation service is currently unavailable. Please try again later.'));
			KLog::log('The VAT-IN validation service was not available.','warning');
			return true;
		}

		if (!is_object($vies)) {
			KLog::log('The VAT-IN validation service was not available.','warning');
			return true;
		}

		// Get the billing country
		$country = ConfigboxCountryHelper::getCountry($billingCountryId);

		// Prepare the params for the SOAP request
		$param = new stdClass();
		$param->countryCode = $country->country_2_code;

		// Normalize the provided VAT IN
		$vatIn = str_replace(' ', '', $vatIn);

		if (stripos($vatIn,$country->country_2_code) === 0) {
			$vatIn = substr($vatIn,2);
		}

		if (strpos($vatIn,'-') === 0) {
			$vatIn = substr($vatIn,1);
		}

		$param->vatNumber = $vatIn;

		try {
			// Do the request, set the timeout to 3 seconds to prevent long processing time
			ini_set("default_socket_timeout", 3);
			/** @noinspection PhpUndefinedMethodInspection */
			$response = $vies->checkVat($param);
		}
		catch (SoapFault $e) {
			$ret = $e->getMessage();

			$faults = array (
				'INVALID_INPUT'       => KText::_('The provided country code is invalid or the VAT number is empty'),
				'SERVICE_UNAVAILABLE' => KText::_('The SOAP service is unavailable, try again later'),
				'MS_UNAVAILABLE'      => KText::_('The Member State service is unavailable, try again later or with another Member State'),
				'TIMEOUT'             => KText::_('The Member State service could not be reached in time, try again later or with another Member State'),
				'SERVER_BUSY'         => KText::_('The service cannot process your request. Try again later.'),
			);

			KLog::log('The VAT IN cannot be checked because of this error "'.$ret.'". "'.$faults[$ret].'". VAT IN was "'.$vatIn.'", Country code was "'.$country->country_2_code.'".','warning');
			ini_set("default_socket_timeout", $originalSocketTimeout);
			return true;

		}

		// Reset the socket timeout to the regular value
		ini_set("default_socket_timeout", $originalSocketTimeout);

		return $response->valid;
	}


	static function addError($error) {
		self::$errors[] = $error;

		// Backward compatibility
		self::$error = $error;
	}

	static function addErrors($errors) {

		if (is_array($errors) && count($errors)) {
			self::$errors = array_merge(self::$errors, $errors);

			// Backward compatibility
			self::$error = end($errors);
		}
	}

	static function resetErrors() {
		self::$errors = array();
		// Backward compatibility
		self::$error = '';
	}

	static function getErrors() {
		return self::$errors;
	}

	static function getError() {
		if (is_array(self::$errors) && count(self::$errors)) {
			return end(self::$errors);
		}
		else {
			return '';
		}
	}


}
