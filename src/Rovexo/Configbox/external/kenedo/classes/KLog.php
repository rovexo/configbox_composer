<?php
class KLog {

	static $starttime;
	static $takes = array();
	static $timings = array();
	static $counts = array();
	static $ignoredErrorNumbers = array(E_STRICT, E_USER_DEPRECATED, E_DEPRECATED);

	static $errorCodes = array(
		1	=> 'E_ERROR',
		2	=> 'E_WARNING',
		4	=> 'E_PARSE',
		8	=> 'E_NOTICE',
		2048=> 'E_STRICT',
		5096=> 'E_RECOVERABLE_ERROR',
		8192=> 'E_DEPRECATED',
	);

	/**
	 * Start or reset the timer for time profiling
	 * @param string $task task keyword
	 */
	static function start($task = 'default') {
		self::$starttime[$task] = microtime(true)*1000;
	}

	/**
	 * Tells if there is a timer for the given task already
	 * @param string $task Defaults to 'default'
	 * @return bool
	 */
	static function timerExists($task = 'default') {
		return (isset(self::$starttime[$task]));
	}

	/**
	 * Get the time since start was called in ms
	 * @param string $task task keyword
	 * @return int Time in ms
	 */
	static function time($task = 'default') {
		if (!isset(self::$starttime[$task])) {
			return 0;
		}
		$time =  (int) ((microtime(true)*1000) - self::$starttime[$task]);
		self::$takes[$task][] = $time;
		return $time;
	}

	/**
	 * Get the time since start was called in ms
	 * @param string $task task keyword
	 * @return int Time in ms
	 */
	static function stop($task = 'default') {

		if (!isset(self::$starttime[$task])) {
			return false;
		}

		$time =  (int) ((microtime(true)*1000) - self::$starttime[$task]);
		self::$timings[$task] = $time;
		return $time;
	}

	/**
	 * Store the timing for any given task. See getTimings
	 * @param string $task
	 * @param float $milliSeconds
	 * @return void
	 */
	static function setTiming($task, $milliSeconds) {
		self::$timings[$task] = $milliSeconds;
	}

	static function getTakes() {
		return self::$takes;
	}

	static function getTimings() {
		return self::$timings;
	}

	static function getTiming($task = 'default') {
		return self::$timings[$task] ?? null;
	}

	/**
	 * Increment the counter for any task
	 * @param string $task
	 * @return void
	 */
	static function count($task) {
		if (!isset(self::$counts[$task])) {
			self::$counts[$task] = 0;
		}
		self::$counts[$task]++;
	}

	/**
	 * Get the count for a given task
	 * @param string $task
	 * @return int|mixed
	 */
	static function getCount($task) {
		if (!isset(self::$counts[$task])) {
			self::$counts[$task] = 0;
		}
		return self::$counts[$task];
	}

	/**
	 * Get counts of all tasks
	 * @return int[]
	 */
	static function getCounts() {
		return self::$counts;
	}

	/**
	 * Log anything that is deprecated in CB
	 * @param string $info
	 * @param int $stackLevelsBack
	 * @return void
	 */
	static function logLegacyCall($info = '', $stackLevelsBack = 0) {

		$caller = self::getCallerInfo(1 + $stackLevelsBack);

		$logMessage = 'Deprecated call in file "'.$caller['file'].'", line "'.$caller['line'].'".';
		if ($info) {
			$logMessage .= 'Info: '.$info;
		}

		self::log($logMessage, 'deprecated');
	}

	/**
	 * Logs a message to the log file and optionally triggers a platform exception
	 * @param string $messageInternal The internal log message, only shown in the log file (or on the website if debug mode is on and a JError is triggered)
	 * @param string $logLevel The log level of the message (debug, notice, warning, error etc). Controls which file is used for logging, debug is ignored unless debug mode is on.
	 * @param string $messagePublic This triggers a platform exception and uses the text to display
	 *
	 * @return string|bool|null $identifier String that identifies the log message, false on error, null if ignored because of debug setting
	 */
	static function log($messageInternal, $logLevel = 'debug', $messagePublic = '') {

		// Sanitize log level string
		$logLevel = strtolower($logLevel);

		// Ignore debug messages if not in debug mode
		if (($logLevel == 'debug' || $logLevel == 'inconsistencies') && KenedoPlatform::p()->getDebug() == 0) {
			return NULL;
		}

		// Determine which log file to use
		switch ($logLevel) {

			case 'debug':
				$logFile = 'configbox_debug.log';
				break;

			case 'warning':
				$logFile = 'configbox_warnings.log';
				break;

			case 'error':
			case 'exception':
				$logFile = 'configbox_errors.log';
				break;

			case 'payment':
				$logFile = 'configbox_payment.log';
				break;

			case 'payment_tracking':
				$logFile = 'configbox_payment_tracking.log';
				break;

			case 'calculation_code_error':
				$logFile = 'configbox_calculation_code_errors.log';
				break;

			case 'permissions':
				$logFile = 'configbox_permissions.log';
				break;

			case 'php_error':
				$logFile = 'configbox_php_messages.log';
				break;

			case 'php_platform_errors':
				$logFile = 'configbox_php_platform_errors.log';
				break;

			case 'deprecated':
				$logFile = 'configbox_deprecated.log';
				break;

			case 'external_apis':
				$logFile = 'configbox_external_apis.log';
				break;

			case 'db_error':
				$logFile = 'configbox_db_errors.log';
				break;

			case 'upgrade_errors':
				$logFile = 'configbox_upgrade_errors.log';
				break;

			case 'authorization':
				$logFile = 'configbox_authorization.log';
				break;

			case 'inconsistencies':
				$logFile = 'configbox_inconsistencies.log';
				break;

			case 'cleanup':
				$logFile = 'configbox_cleanup.log';
				break;

			default:

				// Custom log files (log level starts with custom_)
				if (strpos($logLevel, 'custom_') === 0) {
					// Clear out any nastiness
					$logFile = str_replace(array('/', '\\', '.'), '', $logLevel);
					$logFile = strtolower($logFile).'.log';

				}
				else {
					$logFile = 'configbox_general.log';
				}

		}

		$logPath = KenedoPlatform::p()->getLogPath().'/configbox';
		if (!is_dir($logPath)) {
			mkdir($logPath,0777,true);
		}

		$backTrace = self::getCallerInfo();
		$timeString = KenedoTimeHelper::getFormattedOnly('NOW', 'datetime');
		$identifier = self::getRandomString();

		$line = "\n". $timeString ."\t". $logLevel ."\t". $identifier ."\t".  $backTrace['class'].'->'.$backTrace['method'].' (Line '.$backTrace['line'].')' ."\t". $messageInternal;

		if (!is_file($logPath.'/'.$logFile)) {
			touch($logPath.'/'.$logFile);
		}

		$response = file_put_contents($logPath.'/'.$logFile, $line, FILE_APPEND);

		if (in_array($logLevel, array('error', 'db_error'))) {

			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$trace = ob_get_contents();
			ob_end_clean();

			// Remove first item from backtrace as it's this function which is redundant.
			$trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

			file_put_contents($logPath.'/'.$logFile, "\nBacktrace:\n". $trace, FILE_APPEND);

		}

		if ($response !== false) {
			$response = true;
		}

		if ( KenedoPlatform::p()->getDebug() ) {
			if (!empty($messagePublic)) {
				KenedoPlatform::p()->raiseError(500, $messagePublic . ' '. $messageInternal. '(identifier: '.$identifier.')');
			}
		}
		else {
			if (!empty($messagePublic)) {
				KenedoPlatform::p()->raiseError(500, $messagePublic . '(identifier: '.$identifier.')');
			}
		}

		if ($response == true) {
			return $identifier;
		}
		else {
			return false;
		}

	}

	/**
	 * @param Exception $e
	 */
	static public function logException($e, $logType = 'exception') {
		KLog::log($e->getMessage().' in '.$e->getFile().':'.$e->getLine().".\nTrace:\n".$e->getTraceAsString(), $logType);
	}

	/**
	 * Get backtrace information $offset levels back from where you called this method
	 * @param int $offset
	 * @return string[]
	 */
	static protected function getCallerInfo($offset = 0) {
		$stack = debug_backtrace(false);
		foreach ($stack as $index=>$level) {
			if (empty($level['class']) || $level['class'] != 'KLog') {
				$info = array();
				$info['class'] = isset($stack[($offset+$index)]['class']) ? $stack[($offset+$index)]['class'] : NULL;
				$info['method'] = isset($stack[($offset+$index)]['function']) ? $stack[($offset+$index)]['function'] : NULL;
				$info['line'] = isset($stack[($offset+$index-1)]['line']) ? $stack[($offset+$index-1)]['line'] : NULL;
				$info['file'] = isset($stack[($offset+$index-1)]['file']) ? $stack[($offset+$index-1)]['file'] : NULL;
				return $info;
			}
		}
		// This is for weird situations
		$info = array();
		$info['class'] = isset($stack[2]['class']) ? $stack[2]['class'] : NULL;
		$info['method'] = isset($stack[2]['function']) ? $stack[2]['function'] : NULL;
		$info['line'] = isset($stack[1]['line']) ? $stack[1]['line'] : NULL;
		$info['file'] = isset($stack[1]['file']) ? $stack[1]['file'] : NULL;
		return $info;
	}

	/**
	 * @param int $length
	 * @return string
	 */
	static protected function getRandomString($length = 10) {

		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';

		for ($p = 0; $p < $length; $p++) {
			$string .= substr($characters, rand(0, strlen($characters) - 1), 1);
		}

		return $string;
	}

	static public function handleError($number, $message, $file, $line) {

		if (in_array($number, self::$ignoredErrorNumbers)) {
			return;
		}

		// We do not log the create_function deprecation messages (and do not respect error_reporting() since we want
		// so trace any errors (clients typically do not know how to give access to error log files or have
		// error_reporting off).
		if ($message == 'Function create_function() is deprecated') {
			return;
		}

		// Get the CB app dir
		$appDir = KenedoPlatform::p()->getComponentDir('com_configbox');
		// Replace any arbitrary dir separators with the system's DS
		$appDir = str_replace("\\", DIRECTORY_SEPARATOR, $appDir);
		$appDir = str_replace('/', DIRECTORY_SEPARATOR, $appDir);
		// Remove any adjacent dir separators
		$appDir = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $appDir);

		if (!strstr($file, $appDir)) {
			$logLevel = 'php_platform_errors';
		}
		else {
			$logLevel = 'php_error';
		}

		self::log('PHP error (Type: '.self::getErrorTypeConstantName($number).', message: '.$message.', file: '.$file.', line: '.$line, $logLevel );

		// Log a backtrace in case system runs in debug mode or if error is an E_ERROR
		if (KenedoPlatform::p()->getDebug() || $number == E_ERROR) {
			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$trace = ob_get_clean();
			self::log("Backtrace:\n".$trace."\n\n", $logLevel);
		}

	}

	/**
	 * Logs exceptions
	 * @param Exception $exception
	 * @throws Exception
	 */
	static public function handleException($exception) {
		self::logException($exception);
		throw $exception;
	}

	static public function handleShutdown() {

		$error = error_get_last();

		if (!$error) {
			return;
		}

		$typesNotLogged = array(
			E_WARNING,
			E_NOTICE,
			2048, // Deprecated E_STRICT
			E_DEPRECATED,
			E_USER_DEPRECATED,
		);

		if (in_array($error['type'], $typesNotLogged)) {
			return;
		}

		// Log errors from non-CB files separately

		// Get the CB app dir
		$appDir = KenedoPlatform::p()->getComponentDir('com_configbox');
		// Replace any arbitrary dir separators with the system's DS
		$appDir = str_replace("\\", DIRECTORY_SEPARATOR, $appDir);
		$appDir = str_replace('/', DIRECTORY_SEPARATOR, $appDir);
		// Remove any adjacent dir separators
		$appDir = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $appDir);

		if (!strstr($error['file'], $appDir)) {
			$logLevel = 'php_platform_errors';
		}
		else {
			$logLevel = 'php_error';
		}

		self::log('PHP error (Type: '.self::getErrorTypeConstantName($error['type']).', message: '.$error['message'].', file: '.$error['file'].', line: '.$error['line'], $logLevel );

	}

	/**
	 * @param int $errorNumber
	 * @return int|string The E_* constant name or the error number if not found
	 */
	protected static function getErrorTypeConstantName($errorNumber) {
		return (isset(self::$errorCodes[$errorNumber])) ? self::$errorCodes[$errorNumber] : $errorNumber;
	}

}
