<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxModelAdminlanguages extends KenedoModel {

	/**
	 * Used in Entity Config to get all active platform languages
	 * @return object[]
	 */
	function getActiveLanguages() {
		return KenedoLanguageHelper::getActiveLanguages();
	}

	/**
	 * Used in Entity Config to get all platform languages
	 * @return object[]
	 */
	function getAllLanguages() {
		return KenedoPlatform::p()->getLanguages();
	}
	
}