<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelCustomerform extends KenedoModel {

	function getStates($countryId) {
		$db = KenedoPlatform::getDb();
		$query = "
		SELECT *
		FROM `#__configbox_states`
		WHERE `country_id` = ".intval($countryId)." AND `published` = '1'
		ORDER BY `ordering`, `name`
		";
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getCounties($stateId) {
		$db = KenedoPlatform::getDb();
		$query = "
		SELECT *
		FROM `#__configbox_counties`
		WHERE `state_id` = ".intval($stateId)." AND `published` = '1'
		ORDER BY `ordering`, `county_name`
		";
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getCities($countyId) {
		$db = KenedoPlatform::getDb();
		$query = "
		SELECT *
		FROM `#__configbox_cities`
		WHERE `county_id` = ".intval($countyId)." AND `published` = '1'
		ORDER BY `ordering`, `city_name`
		";
		$db->setQuery($query);
		return $db->loadObjectList();
	}

}