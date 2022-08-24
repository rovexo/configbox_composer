<?php
class KLog {

	static $starttime;

	static $takes = array();
	static $timings = array();

	static $counts = array();

	static $ignoredErrorNumbers = array(E_STRICT, E_USER_DEPRECATED);

	static $errorCodes = array(
		1	=> 'E_ERROR',
		2	=> 'E_WARNING',
		4	=> 'E_PARSE',
		8	=> 'E_NOTICE',
		2048=> 'E_STRICT',
		5096=> 'E_RECOVERABLE_ERROR',
		8192=> 'E_DEPRECATED',
	);

	static function setTiming($task, $milliSeconds) {
		self::$timings[$task] = $milliSeconds;
	}

	/**
	 *
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
	 *
	 * Get the time since start was called in ms
	 *
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
	 *
	 * Get the time since start was called in ms
	 *
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

	static function getTakes() {
		return self::$takes;
	}

	static function getTimings() {
		return self::$timings;
	}

	static function count($task) {
		if (!isset(self::$counts[$task])) {
			self::$counts[$task] = 0;
		}
		self::$counts[$task]++;
	}

	static function getCount($task) {
		if (!isset(self::$counts[$task])) {
			self::$counts[$task] = 0;
		}
		return self::$counts[$task];
	}

	static function getCounts() {
		return self::$counts;
	}

	static function logLegacyCall($info = '', $stackLevelsBack = 0) {

		$caller = self::getCallerInfo(1 + $stackLevelsBack);

		$logMessage = 'Deprecated call in file "'.$caller['file'].'", line "'.$caller['line'].'".';
		if ($info) {
			$logMessage .= 'Info: '.$info;
		}

		self::log($logMessage, 'deprecated');
	}

	/**
	 *
	 * Logs a message to the log file and optionally triggers a platform exception
	 *
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
				$logFile = 'configbox_debug.php';
				break;

			case 'warning':
				$logFile = 'configbox_warnings.php';
				break;

			case 'error':
			case 'exception':
				$logFile = 'configbox_errors.php';
				break;

			case 'payment':
				$logFile = 'configbox_payment.php';
				break;

			case 'payment_tracking':
				$logFile = 'configbox_payment_tracking.php';
				break;

			case 'calculation_code_error':
				$logFile = 'configbox_calculation_code_errors.php';
				break;

			case 'permissions':
				$logFile = 'configbox_permissions.php';
				break;

			case 'php_error':
				$logFile = 'configbox_php_messages.php';
				break;

			case 'php_platform_errors':
				$logFile = 'configbox_php_platform_errors.php';
				break;

			case 'deprecated':
				$logFile = 'configbox_deprecated.php';
				break;

			case 'external_apis':
				$logFile = 'configbox_external_apis.php';
				break;

			case 'db_error':
				$logFile = 'configbox_db_errors.php';
				break;

			case 'upgrade_errors':
				$logFile = 'configbox_upgrade_errors.php';
				break;

			case 'authorization':
				$logFile = 'configbox_authorization.php';
				break;

			case 'inconsistencies':
				$logFile = 'configbox_inconsistencies.php';
				break;

			case 'cleanup':
				$logFile = 'configbox_cleanup.php';
				break;

			default:

				// Custom log files (log level starts with custom_)
				if (strpos($logLevel, 'custom_') === 0) {
					// Clear out any nastiness
					$logFile = str_replace(array('/', '\\', '.'), '', $logLevel);
					$logFile = strtolower($logFile).'.php';

				}
				else {
					$logFile = 'configbox_general.php';
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
			file_put_contents($logPath.'/'.$logFile, "<?php\ndie('No browser access, use FTP.');\n?>\n\n", FILE_APPEND);
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
	static public function logException($e) {
		KLog::log($e->getMessage().' in '.$e->getFile().':'.$e->getLine().".\nTrace:\n".$e->getTraceAsString(), 'exception');
	}

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
			if ($number == E_DEPRECATED) {
				return;
			}
			$logLevel = 'php_platform_errors';
		}
		else {
			$logLevel = 'php_error';
		}

		self::log('PHP error (Type: '.self::getErrorType($number).', message: '.$message.', file: '.$file.', line: '.$line, $logLevel );

		// Log a backtrace in case system runs in debug mode or if error is an E_ERROR
		if (KenedoPlatform::p()->getDebug() || $number == E_ERROR) {
			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$trace = ob_get_clean();
			self::log("Backtrace:\n".$trace."\n\n", $logLevel);
		}

	}

	/**
	 * Logs uncaught exceptions
	 * @param Exception $exception
	 * @throws Exception
	 */
	static public function handleException($exception) {
		$logMessage = 'PHP exception from exception handler (Message: "'.$exception->getMessage().'", file: "'.$exception->getFile().'", line: "'.$exception->getLine().'")';
		self::log($logMessage, 'php_error');
		error_log($logMessage.'. Also see Configbox php_errors.php in Joomla log folder (subfolder configbox).');
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
			E_STRICT,
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

		self::log('PHP error (Type: '.self::getErrorType($error['type']).', message: '.$error['message'].', file: '.$error['file'].', line: '.$error['line'], $logLevel );

	}

	protected static function getErrorType($errorNumber) {
		return (isset(self::$errorCodes[$errorNumber])) ? self::$errorCodes[$errorNumber] : $errorNumber;
	}

}
