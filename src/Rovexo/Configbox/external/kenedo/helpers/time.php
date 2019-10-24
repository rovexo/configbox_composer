<?php
/**
 * Class KenedoTimeHelper
 * What's important to know about Configbox dealing with time is that it stores any time in a normalized timezone (UTC)
 * and here are methods to convert to the user's timezone conveniently.
 */
class KenedoTimeHelper {

	/**
	 * @var string $normalizedTimeZoneName Valid timezone name
	 * @see DateTimeZone
	 */
	static $outputTimeZoneName = '';

	/**
	 * @var DateTimeZone
	 */
	static $outputTimeZone;

	/**
	 * @var string $normalizedTimeZoneName Valid timezone name
	 * @see DateTimeZone
	 */
	static $normalizedTimeZoneName = 'UTC';

	/**
	 * @var DateTimeZone
	 */
	static $normalizedTimeZone;

	/**
	 * Sets the output timezone
	 * @param string $timeZoneName A timezone name that DateTimeZone::__construct accepts
	 */
	public static function setOutputTimeZoneName($timeZoneName = '') {
		if ($timeZoneName) {
			self::$outputTimeZoneName = $timeZoneName;
		}
		self::$outputTimeZone = new DateTimeZone(self::$outputTimeZoneName);
	}

	/**
	 * Sets the timezone that normalized times shall have
	 * @param string $timeZoneName A timezone name that DateTimeZone::__construct accepts
	 */
	public static function setNormalizedTimeZoneName($timeZoneName = '') {
		if ($timeZoneName) {
			self::$normalizedTimeZoneName = $timeZoneName;
		}
		self::$normalizedTimeZone = new DateTimeZone(self::$normalizedTimeZoneName);
	}

	/**
	 * Takes a 'normalized' time string and returns it in the output timezone in any format.
	 *
	 * @param string $timeString Anything that DateTime::__construct accepts
	 * @param string $format Anything that Datetime::format accepts (empty to use the current language's default format)
	 * @see KenedoTimeHelper::getFormat for a few convenience keywords for $format
	 * @return string
	 */
	public static function getFormatted($timeString = 'NOW', $format = '') {

		// Set the output time zone if wasn't set already
		if (!self::$outputTimeZone) {
			$platformTimezoneName = KenedoPlatform::p()->getUserTimezoneName();
			self::setOutputTimeZoneName($platformTimezoneName);
		}

		// Set the normalized time zone if wasn't set already
		if (!self::$normalizedTimeZone) {
			self::setNormalizedTimeZoneName(self::$normalizedTimeZoneName);
		}

		$format = self::getFormat($format);
		$timeString = self::getTimeString($timeString);

		try {
			$time = new DateTime($timeString, self::$normalizedTimeZone );
			$time->setTimezone(self::$outputTimeZone);
		}
		catch(Exception $e) {
			return '';
		}
		$formatted = $time->format($format);

		return $formatted;

	}

	/**
	 * Takes a time string in the output timezone and converts it in the normalized timezone in the specified format.
	 * @param string $timeString
	 * @param string $format
	 * @return string
	 */
	public static function getNormalizedTime($timeString = 'NOW', $format = '') {

		// Set the output time zone if wasn't set already
		if (!self::$outputTimeZone) {
			$platformTimezoneName = KenedoPlatform::p()->getUserTimezoneName();
			self::setOutputTimeZoneName($platformTimezoneName);
		}

		// Set the normalized time zone if wasn't set already
		if (!self::$normalizedTimeZone) {
			self::setNormalizedTimeZoneName(self::$normalizedTimeZoneName);
		}

		$format = self::getFormat($format);
		$timeString = self::getTimeString($timeString);

		try {
			$time = new DateTime($timeString, self::$outputTimeZone );
			$time->setTimezone( self::$normalizedTimeZone );
			$formatted = $time->format($format);
		}
		catch(Exception $e) {
			return '';
		}
		return $formatted;
	}

	/**
	 * Formats a time string (and does no timezone conversion)
	 * @param string $timeString
	 * @param string $format
	 * @return string
	 */
	public static function getFormattedOnly($timeString = 'NOW', $format = '') {

		// Set the output time zone if wasn't set already
		if (!self::$outputTimeZone) {
			$platformTimezoneName = KenedoPlatform::p()->getUserTimezoneName();
			self::setOutputTimeZoneName($platformTimezoneName);
		}

		// Set the normalized time zone if wasn't set already
		if (!self::$normalizedTimeZone) {
			self::setNormalizedTimeZoneName(self::$normalizedTimeZoneName);
		}

		$format = self::getFormat($format);
		$timeString = self::getTimeString($timeString);

		try {
			$time = new DateTime($timeString, self::$normalizedTimeZone );
		}
		catch(Exception $e) {
			return '';
		}

		$formatted = $time->format($format);

		return $formatted;

	}

	/**
	 * Alters the time string used in a few functions above. Currently just makes timestamps understandable to DateTime
	 * @param string $string Valid time string
	 * @return string
	 */
	protected static function getTimeString($string) {

		if (is_numeric($string)) {
			$string = '@'.intval($string);
		}

		return $string;
	}

	/**
	 * Alters the format used in the public functions above. Makes keywords into placeholders or sets the default format.
	 * @param string $format
	 * @return string
	 */
	protected static function getFormat($format) {

		if (strtolower($format) == 'datetime') {
			$format = "Y-m-d H:i:s";
		}

		if (strtolower($format) == 'date') {
			$format = KText::_('KENEDO_DATEFORMAT_PHP_DATE', 'Y-m-d');
		}

		if (strtolower($format) == 'timestamp') {
			$format = "U";
		}

		if (empty($format)) {
			$format = KText::_('KENEDO_DATEFORMAT_PHP_DATE_AND_TIME', 'Y-m-d H:i');
		}

		return $format;

	}

}