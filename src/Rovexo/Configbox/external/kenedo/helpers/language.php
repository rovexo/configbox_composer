<?php
class KenedoLanguageHelper {
	
	protected static $activeLanguages;
	protected static $memoizedActiveLanguageTags;

	/**
	 * Gets you the language info object by tag (e.g. en-GB). Careful: Get's you the language even if not an active one
	 * @param $tag
	 * @return object
	 * @see InterfaceKenedoPlatform::getLanguages()
	 */
	static function getLanguageByTag($tag) {
		$languages = KenedoPlatform::p()->getLanguages();
		return (!empty($languages[$tag])) ? $languages[$tag] : NULL;
	}

	/**
	 * Gets you an array of objects with all active languages
	 * What is active is defined in the ConfigBox configuration
	 *
	 * @return object[] Objects holding language info, key is language tag
	 */
	static function getActiveLanguages() {

		if (empty(self::$activeLanguages)) {
			$allLanguages = KenedoPlatform::p()->getLanguages();
			$activeTags = self::getActiveLanguageTags();
			self::$activeLanguages = array();

			foreach ($allLanguages as $language) {
				if (in_array($language->tag, $activeTags)) {
					self::$activeLanguages[$language->tag] = $language;
				}
			}
		}
		return self::$activeLanguages;

	}
	
	/**
	 * Gets you an array with the tags of active languages
	 * @return string[] language tags in form of de-DE
	 */
	static function getActiveLanguageTags() {
		if (empty(self::$memoizedActiveLanguageTags)) {
			$db = KenedoPlatform::getDb();
			$query = "SELECT `tag` FROM `#__configbox_active_languages`";
			$db->setQuery($query);
			self::$memoizedActiveLanguageTags = $db->loadResultList();
		}
		return self::$memoizedActiveLanguageTags;
	}

	/**
	 * Gets you all languages of the platform
	 *
	 * @deprecated Use KenedoPlatform::p()->getLanguages() instead
	 * @return object[]
	 */
	static function getAllLanguages() {
		return KenedoPlatform::p()->getLanguages();
	}

	/**
	 * Gets you an array of objects with all active languages
	 * What is active is defined in the ConfigBox configuration
	 *
	 * @deprecated Use KenedoLanguageHelper::getActiveLanguages() instead
	 * @see KenedoLanguageHelper::getActiveLanguages
	 * @return object[] holding language info, key is language tag
	 */
	static function getLanguages() {
		return self::getActiveLanguages();
	}

}