<?php
class KSession {

	static $data = array();
	static $instance;
	static protected $sessionName;
	static protected $sessionId;
	static protected $maxAge = 86400;
	static protected $lockName = '';

	static $lockIsAcquired = NULL;

	protected function __construct() {
		register_shutdown_function('KSession::onShutdown');
		self::onStartup();
	}

	static function set($key, $value, $context = 'default') {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		self::$data[$context][$key] = $value;
	}

	static function delete($key, $context = 'default') {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		if (isset(self::$data[$context][$key])) {
			unset(self::$data[$context][$key]);
		}
	}

	static function reset() {
		if (!self::$instance) {
			self::$instance = new KSession();
		}
		self::$data = array();
	}

	static function terminateSession() {

		self::$data = array();

		$sessionName = self::getSessionName();
		$sessionId = self::$sessionId;

		unset($_COOKIE[$sessionName]);

		// Make the cookie expire
		setcookie($sessionName, $sessionId, -10, '/');

		$db = KenedoPlatform::getDb();
		$query = "DELETE FROM `#__configbox_session` WHERE `id` = '".$db->getEscaped($sessionId)."'";
		$db->setQuery($query);
		$succ = $db->query();

		return $succ;
	}

	static function get($key, $default = NULL, $context = 'default') {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		if (isset(self::$data[$context][$key])) {
			return self::$data[$context][$key];
		}
		else {
			return $default;
		}
	}

	static function getProperties() {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		return isset(self::$data) ? self::$data : array();
	}

	public static function getSessionName() {
		$sessionName = 'cb_'.md5(__FILE__);
		return $sessionName;
	}

	public static function onStartup() {

		// Prepare session meta data
		$ip = self::getIp();
		$userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';

		// Get session name
		self::$sessionName = self::getSessionName();

		// Get or create the session id
		if (!empty($_COOKIE[self::$sessionName])) {
			self::$sessionId = $_COOKIE[self::$sessionName];
		}
		else {
			self::$sessionId = substr(str_shuffle(uniqid('',true).uniqid('',true).uniqid('',true).uniqid('',true).uniqid('',true).uniqid('',true)), 0, 64);
			// Set the session id cookie
			setcookie(self::$sessionName, self::$sessionId, 0, '/');
		}

		$db = KenedoPlatform::getDb();

		// Remove outdated session entries
		try {
			$query = "DELETE FROM `#__configbox_session` WHERE `updated` < ".intval(time() - self::$maxAge);
			$db->setQuery($query);
			$db->query();
		}
		catch (Exception $e) {
			$query = "
			CREATE TABLE IF NOT EXISTS `#__configbox_session` (
			  `id` varchar(128) NOT NULL,
			  `user_agent` varchar(200) NOT NULL DEFAULT '',
			  `ip_address` varchar(100) NOT NULL DEFAULT '',
			  `data` text NOT NULL,
			  `updated` INT(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `updated` (`updated`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
			";
			$db->setQuery($query);
			$db->query();
		}

		// Note that since MySQL 5.7.5 lock names cannot be longer than 64 characters.
		// A bad lock name will lead to timeouts in typical circumstances.
		// Using 32 is for a safety margin in case a lot of escaping is needed
		self::$lockName = substr('lock_'.self::$sessionId, 0, 32);

		// Acquire a lock to avoid race conditions
		$success = self::acquireLock();

		// Shit in the pants if the lock could not get acquired
		if ($success == false) {
			$messageLog = 'An error occurred while setting a lock. Lock name was "'.self::$lockName.'".';
			$messageFeedback = 'A database server error occurred during setting a lock. See error and database log file for more information.';
			KLog::log($messageLog, 'db_error');
			KLog::log($messageLog, 'error', $messageFeedback);
		}

		// Load the current session entry
		$query = "SELECT * FROM `#__configbox_session` WHERE `id` = '".$db->getEscaped(self::$sessionId)."'";
		$db->setQuery($query);
		$record = $db->loadAssoc();

		if (!$record) {

			// Add new session entry
			$query = "INSERT INTO `#__configbox_session` (`id`, `data`, `ip_address`, `user_agent`, `updated`) VALUES ('".$db->getEscaped(self::$sessionId)."', '', '".$db->getEscaped($ip)."', '".$db->getEscaped($userAgent)."', '".(int)time()."' )";
			$db->setQuery($query);
			$succ = $db->query();
			if ($succ == false) {
				KLog::log('Could not add session record because of DB error: "'.$db->getErrorMsg().'".','error','Could not add session record');
				return false;
			}

			self::$data = NULL;
			return true;
		}
		else {

			if (!empty($record['data'])) {
				// Unserialize session data and store
				$sessionData = unserialize($record['data']);
				if ($sessionData === false) {
					KLog::log('Could not unserialize session data. Serialized string was "'.$record['data'].'".', 'error');
					KenedoPlatform::p()->raiseError('500','Could not load session record');
				}
				else {
					self::$data = $sessionData;
				}
			}
			else {
				self::$data = array();
			}
		}
		return true;
	}

	static public function onShutdown() {

		// Update session data
		$sessionString = '';

		if (self::$data) {
			$sessionString = serialize(self::$data);

			if ($sessionString === false) {
				KLog::log('Serialization of session data did not work. Data was '.var_export(self::$data,true).'".','error');
				throw new Exception('Could not serialize session data');
			}
		}

		$sessionUpdateOk = true;
		$dbError = '';

		if (!defined('CONFIGBOX_GOT_UNINSTALLED')) {
			$time = time();
			$db = KenedoPlatform::getDb();
			$query = "UPDATE `#__configbox_session` SET `data` = '".$db->getEscaped($sessionString)."', `updated` = ".(int)$time. " WHERE `id` = '".$db->getEscaped(self::$sessionId)."'";
			$db->setQuery($query);
			$succ = $db->query();
			if ($succ == false) {
				$sessionUpdateOk = false;
				$dbError = $db->getErrorMsg();
			}
		}

		if (self::lockIsAcquired() === true) {
			$success = self::releaseLock();
			// Shit in the pants if the lock could not get acquired
			if ($success == false) {
				$messageLog = 'An error occurred while releasing a lock. Lock name was "'.self::$lockName.'".';
				$messageFeedback = 'A database server error occurred during releasing a lock. See error and database log file for more information.';
				KLog::log($messageLog, 'db_error');
				KLog::log($messageLog, 'error', $messageFeedback);
			}
		}

		if ($sessionUpdateOk === false) {
			KLog::log('Could not store session record because of DB error: "'.$dbError.'".', 'error', 'Could not store session record.');
		}

		return true;
	}

	protected static function getIp() {

		$ip = ConfigboxLocationHelper::getClientIpV4Address();

		return $ip;
	}


	protected static function setLockName($lockName) {
		self::$lockName = $lockName;
	}

	public static function lockIsAcquired() {
		return (self::$lockIsAcquired == true);
	}

	public static function acquireLock() {

		if (self::$lockIsAcquired == true) {
			$message = 'acquireLock was called, but lock is already acquired. Lock name is "'.self::$lockName.'".';
			KLog::log($message, 'error');
			KLog::log($message, 'db_error');
			return false;
		}

		if (empty(self::$lockName)) {
			$message = 'acquireLock was called, but no lock name is defined.';
			KLog::log($message, 'error');
			KLog::log($message, 'db_error');
			return false;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT GET_LOCK('".$db->getEscaped(self::$lockName)."', 20)";
		$db->setQuery($query);
		$response = $db->loadResult();

		if ($response == '1') {
			KLog::log('Lock acquired ('.self::$lockName.')','debug');
			self::$lockIsAcquired = true;
			return true;
		}
		elseif ($response === '0') {
			$messageLog = 'Timeout on acquiring a lock. This happens when a website visitor makes multiple requests and the server cannot keep up handling them. Lock name was "'.self::$lockName.'".';
			KLog::log($messageLog, 'db_error');
			KLog::log($messageLog, 'error');
			return false;
		}
		elseif($response === NULL) {
			$messageLog = 'An error occurred while setting a lock. Lock name was "'.self::$lockName.'".';
			KLog::log($messageLog, 'db_error');
			KLog::log($messageLog, 'error');
			return false;
		}
		else {
			$messageLog = 'Unexpected response received. Response was '.var_export($response, true);
			KLog::log($messageLog, 'db_error');
			KLog::log($messageLog, 'error');
			return false;
		}

	}

	public static function releaseLock() {

		if (self::$lockIsAcquired != true) {
			$message = 'releaseLock was called, but lock is not acquired. Lock name is "'.self::$lockName.'".';
			KLog::log($message, 'error');
			KLog::log($message, 'db_error');
			return false;
		}

		if (empty(self::$lockName)) {
			$message = 'releaseLock was called, but no lock name is defined.';
			KLog::log($message, 'error');
			KLog::log($message, 'db_error');
			return false;
		}

		$db = KenedoPlatform::getDb();
		$query = "SELECT RELEASE_LOCK('".$db->getEscaped(self::$lockName)."')";
		$db->setQuery($query);
		$response = $db->loadResult();

		if ($response == '1') {
			KLog::log('Lock released ('.self::$lockName.')','debug');
			self::$lockIsAcquired = false;
			return true;
		}
		elseif ($response === '0') {
			KLog::log('Tried to release lock, DB server states that the lock was not acquired in this thread. Lock name is "'.self::$lockName.'".', 'db_error');
			self::$lockIsAcquired = false;
			return false;
		}
		elseif ($response === NULL) {
			KLog::log('Tried to release lock, DB server states that the lock does not exist (if you see a preceding log entry containing RELEASE_LOCK, then that error has occurred.). Lock name is "'.self::$lockName.'".', 'db_error');
			self::$lockIsAcquired = false;
			return false;
		}
		else {
			$messageLog = 'Unexpected response received. Response was '.var_export($response, true);
			KLog::log($messageLog, 'db_error');
			KLog::log($messageLog, 'error');
			return false;
		}

	}


}
