<?php
class KText {

	/**
	 * @var bool Tells if KText has initialized already (got right language and has ini files loaded)
	 */
	protected static $isInitialized = false;

	/**
	 * @var array Holds all loaded strings grouped by language and key
	 */
	protected static $strings = array();
	
	/**
	 * @var string Format is en-GB
	 */
	protected static $languageTag = NULL;

	/**
	 * Determine and set language, load language files
	 */
	protected static function initIfNeeded() {

		if (self::$isInitialized == true) {
			return;
		}
		self::$isInitialized = true;

		$languageTag = self::getLanguageTag();

		// Load language files if not loaded already
		if (empty(self::$strings[$languageTag])) {

			$files = self::getLanguageFiles($languageTag);

			foreach ($files as $file) {
				self::load($file, $languageTag);
			}

		}

	}

	/**
	 * @param string $languageTag Language tag (format example de-DE)
	 */
	public static function setLanguage($languageTag) {

		// Get CB's active languages
		$activeLanguages = KenedoLanguageHelper::getActiveLanguageTags();

		// If the language isn't an active CB language, then fall back to default language from CB settings
		if (in_array($languageTag, $activeLanguages) == false) {
			$languageTag = CbSettings::getInstance()->get('language_tag');
		}

		// Load language files if not loaded already
		if (empty(self::$strings[$languageTag])) {

			$files = self::getLanguageFiles($languageTag);

			foreach ($files as $file) {
				self::load($file, $languageTag);
			}

		}

		self::$languageTag = $languageTag;

	}

	/**
	 * @return string Language tag is like en-GB
	 */
	public static function getLanguageTag() {

		if (empty(self::$languageTag)) {
			self::$languageTag = self::determineLanguage();
		}

		return self::$languageTag;

	}

	/**
	 * @return string First two letters of the language tag (lower case)
	 */
	public static function getLanguageCode() {

		$exp = explode('-', self::getLanguageTag());
		return strtolower($exp[0]);

	}

	/**
	 * @return string Last two letters of the language tag (lower case)
	 */
	static function getCountryCode() {

		$exp = explode('-', self::getLanguageTag());
		return strtoupper($exp[1]);

	}

	/**
	 * @return string The localized decimal symbol.
	 */
	public static function getDecimalSymbol() {
		self::initIfNeeded();
		return KText::_('DECIMAL_MARK','.');
	}

	/**
	 * @return string The localized digit grouping symbol (aka thousands separator).
	 */
	public static function getDigitGroupingSymbol() {
		self::initIfNeeded();
		return KText::_('DIGIT_GROUPING_SYMBOL','.');
	}

	/**
	 * Gives you a normalized (english notation) number from given localized notation.
	 * @param string|int|float $localizedNumber
	 * @return float|int
	 */
	public static function getNormalizedNumber($localizedNumber) {
		self::initIfNeeded();
		$normal = $localizedNumber;
		$normal = str_replace(self::getDigitGroupingSymbol(), '', $normal);
		$normal = str_replace(self::getDecimalSymbol(), '.', $normal);
		$normal = str_replace(' ', '', $normal);
		return $normal;
	}

	/**
	 * Gives you the localized notation of given normal number.
	 * Uses the current language's decimal symbol (does not group digits)
	 * @param float|int|string $normal
	 * @return string
	 */
	public static function getLocalizedNumber($normal) {
		self::initIfNeeded();
		$localizedNumber = (string)$normal;
		$localizedNumber = str_replace('.', self::getDecimalSymbol(), $localizedNumber);
		return $localizedNumber;
	}

	/**
	 * @return string language tag (format is de-DE)
	 */
	protected static function determineLanguage() {

		// Get the platform language
		$languageTag = KenedoPlatform::p()->getLanguageTag();

		// Get CB's active languages
		$activeLanguages = KenedoLanguageHelper::getActiveLanguageTags();

		// If the platform language isn't an active CB language, then fall back to default language from CB settings
		if (in_array($languageTag, $activeLanguages) == false) {
			$languageTag = CbSettings::getInstance()->get('language_tag');
		}

		return $languageTag;

	}

	/**
	 * Sooner or later we got to invent something that makes the files come from the component we work with.
	 * @param string $languageTag
	 * @return string[] Full file paths to the language files to load.
	 */
	protected static function getLanguageFiles($languageTag) {

		// en-GB is our fall-back, so we load english before the actual language (the actual language will write over it)
		$fallbackTag = 'en-GB';

		// Prepare the array of paths to load
		$files = array();

		$appDir = KenedoPlatform::p()->getComponentDir('com_configbox');

		$files[] = $appDir.'/language/'.$fallbackTag.'/frontend.ini';
		$files[] = $appDir.'/language/'.$languageTag.'/frontend.ini';

		// Figure if we deal with an admin page (careful, this means that an admin related page - like a record edit screen)
		$onAdminPage = ( ( KRequest::getString('controller') == NULL && KRequest::getString('view') == NULL) || strpos(KRequest::getString('controller', ''), 'admin') === 0 || strpos(KRequest::getString('view', ''), 'admin') === 0 );

		if ($onAdminPage) {
			$files[] = $appDir.'/language/'.$fallbackTag.'/backend.ini';
			$files[] = $appDir.'/language/'.$languageTag.'/backend.ini';
		}

		// Load frontend overrides
		$files[] = KenedoPlatform::p()->getDirCustomization() .'/language_overrides/'.$languageTag.'/overrides.ini';

		// Se which files exist and return those
		$filesToReturn = array();
		foreach ($files as $file) {
			if (is_file($file)) {
				$filesToReturn[] = $file;
			}
		}

		return $filesToReturn;

	}

	/**
	 * @param string $path Full path to the language ini file
	 * @param string|null $languageTag Language tag
	 * @return bool
	 */
	static function load($path, $languageTag) {

		if (is_file($path) == false) {
			KLog::log('Could not find language file in "'.$path.'".', 'error');
			return false;
		}
		
		$strings = parse_ini_file($path);
		
		if ($strings === false) {
			KLog::log('Language file in "'.$path.'" could not be processed. Please check if the syntax of the file is correct', 'error');
			return false;
		}

		foreach ($strings as $key=>$string) {
			self::$strings[$languageTag][$key] = $string;
		}

		return true;
		
	}

	/**
	 * @param string $key Key of translation (can be in any case)
	 * @param string|null $fallback Fallback text (in case translation is not found)
	 * @return string Localized text or fallback text
	 */
	static function _($key, $fallback = NULL) {

		self::initIfNeeded();

		$languageTag = self::getLanguageTag();

		// Do some normalization of problematic keys
		if ($key == 'CONFIGBOX_DATEFORMAT_PHP_DATE') {
			$key = 'KENEDO_DATEFORMAT_PHP_DATE';
		}
		if ($key == 'CB_STATE') {
			$key = 'STATE';
		}

		$key = str_replace('(','__POPEN__',$key);
		$key = str_replace(')','__PCLOSE__',$key);

		$keyWithoutColon = rtrim($key, ':');
		$hasTrailingColon = (substr($key, -1) == ':');

		if (isset(self::$strings[$languageTag][strtoupper($key)])) {
			return self::$strings[$languageTag][strtoupper($key)];
		}
		elseif ($hasTrailingColon && isset(self::$strings[$languageTag][strtoupper($keyWithoutColon)])) {
			return rtrim(self::$strings[$languageTag][strtoupper($keyWithoutColon)], ':').':';
		}
		elseif ($fallback !== NULL && is_string($fallback)) {
			return $fallback;
		}
		else {
			$key = str_replace('__POPEN__', '(', $key);
			$key = str_replace('__PCLOSE__',')', $key);
			return $key;
		}

	}

	/**
	 * @param string $key Key of translation (can be in any case), with sprintf placeholders
	 * @return string
	 */
	static function sprintf($key) {

		self::initIfNeeded();

		$funcArgs = func_get_args();
		
		if (count($funcArgs) > 0) {
			$funcArgs[0] = self::_($key);
			return call_user_func_array('sprintf', $funcArgs);
		}
		else {
			return self::_($key);
		}
		
	}
	
}