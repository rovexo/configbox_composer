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
		register_shutdown_function(array('KSession', 'onShutdown'));
		self::onStartup();
	}

	/**
	 * Returns the value for given key
	 * @param string $key
	 * @param mixed $fallback Value to return if no value for key was found
	 * @param string $context Name space (optional)
	 * @return mixed|null
	 */
	static function get($key, $fallback = NULL, $context = 'default') {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		if (isset(self::$data[$context][$key])) {
			return self::$data[$context][$key];
		}
		else {
			return $fallback;
		}
	}

	/**
	 * Returns all session data
	 * @return array
	 */
	static function getProperties() {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		return isset(self::$data) ? self::$data : array();
	}

	/**
	 * Sets a value in session
	 * @param string $key Key to store value for
	 * @param mixed $value Value to store
	 * @param string $context Namespace (optional)
	 */
	static function set($key, $value, $context = 'default') {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		self::$data[$context][$key] = $value;
	}

	/**
	 * Deletes the value for given key
	 * @param string $key Key to delete
	 * @param string $context Namespace (optional)
	 */
	static function delete($key, $context = 'default') {
		if (!self::$instance) {
			self::$instance = new KSession();
		}

		if (isset(self::$data[$context][$key])) {
			unset(self::$data[$context][$key]);
		}
	}

	/**
	 * Resets all data in session
	 */
	static function reset() {
		if (!self::$instance) {
			self::$instance = new KSession();
		}
		self::$data = array();
	}

	/**
	 * Terminates the session (also expires the session cookie)
	 * @return bool
	 */
	static function terminateSession() {

		self::$data = array();

		$sessionName = self::getSessionName();
		$sessionId = self::$sessionId;

		unset($_COOKIE[$sessionName]);

		// Make the cookie expire
		if (version_compare(phpversion(), '7.3', '>=')) {

			setcookie(self::$sessionName, self::$sessionId, array(
				'expires'=>time() - 10000,
				'path'=>'/',
				'domain'=>'',
				'secure'=>KenedoPlatform::p()->requestUsesHttps(),
				'httponly'=>true,
				'samesite'=>'None',
			));

		}
		else {
			$sameSiteTrick = '/;SameSite=None';
			setcookie(self::$sessionName, self::$sessionId, time() - 10000, $sameSiteTrick, '', KenedoPlatform::p()->requestUsesHttps(), true);
		}

		try {
			$db = KenedoPlatform::getDb();
			$query = "DELETE FROM `#__configbox_session` WHERE `id` = '".$db->getEscaped($sessionId)."'";
			$db->setQuery($query);
			$db->query();
		}
		catch (Exception $e) {
			return false;
		}

		return true;

	}

	/**
	 * @return string Session name to use
	 */
	public static function getSessionName() {
		$sessionName = 'cb_'.md5(__FILE__);
		return $sessionName;
	}

	public static function onStartup() {

		// Get session name
		self::$sessionName = self::getSessionName();

		// Get or create the session id
		if (!empty($_COOKIE[self::$sessionName])) {
			self::$sessionId = $_COOKIE[self::$sessionName];
		}
		else {

			self::$sessionId = uniqid(md5(microtime()),true);

			$expiry = time() + (1 * 365 * 24 * 60 * 60);

			// Set the session id cookie
			if (version_compare(phpversion(), '7.3', '>=')) {

				setcookie(self::$sessionName, self::$sessionId, array(
					'expires'=>$expiry,
					'path'=>'/',
					'domain'=>'',
					'secure'=>KenedoPlatform::p()->requestUsesHttps(),
					'httponly'=>true,
					'samesite'=>'None',
				));

			}
			else {
				$sameSiteTrick = '/;SameSite=None';
				setcookie(self::$sessionName, self::$sessionId, $expiry, $sameSiteTrick, '', KenedoPlatform::p()->requestUsesHttps(), true);
			}

		}

		$db = KenedoPlatform::getDb();

		// Remove outdated session entries
		try {
			$query = "DELETE FROM `#__configbox_session` WHERE `updated` < ".intval(time() - self::$maxAge);
			$db->setQuery($query);
			$db->query();
		}
		catch (Exception $e) {
			// In case CB is currently being installed and a session get/set triggered startup, we won't have the
			// session table yet. In that case we skip session setup
			self::$data = array();
			return;
		}

		// Load the current session entry
		$query = "SELECT * FROM `#__configbox_session` WHERE `id` = '".$db->getEscaped(self::$sessionId)."'";
		$db->setQuery($query);
		$record = $db->loadAssoc();

		if (!$record) {
			// Either init if there is none..
			$ip = ConfigboxLocationHelper::getClientIpV4Address();
			$userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';

			$query = "INSERT INTO `#__configbox_session` (`id`, `data`, `ip_address`, `user_agent`, `updated`) VALUES ('".$db->getEscaped(self::$sessionId)."', '', '".$db->getEscaped($ip)."', '".$db->getEscaped($userAgent)."', '".(int)time()."' )";
			$db->setQuery($query);
			$db->query();

			self::$data = array();


		}
		else {
			// ..or unserialize existing data

			if ($record['data'] == '') {
				self::$data = array();
			}
			else {

				$sessionData = unserialize($record['data']);

				if ($sessionData === false) {
					KLog::log('Could not unserialize session data. Serialized string was "'.$record['data'].'".', 'error');
					throw new Exception('Could not load session data. Corrupted data found');
				}
				else {
					self::$data = $sessionData;
				}

			}

		}

	}

	static public function onShutdown() {

		// Release any acquired locks
		if (self::lockIsAcquired() === true) {
			$success = self::releaseLock();
			// Log on problems
			if ($success == false) {
				$messageLog = 'An error occurred while releasing a lock. Lock name was "'.self::$lockName.'".';
				$messageFeedback = 'A database server error occurred during releasing a lock. See error and database log file for more information.';
				KLog::log($messageLog, 'db_error');
				KLog::log($messageLog, 'error', $messageFeedback);
			}
		}

		// Abort if CB got uninstalled in the runtime. Set in Joomla's uninstall procedure
		if (defined('CONFIGBOX_GOT_UNINSTALLED')) {
			return;
		}

		$db = KenedoPlatform::getDb();
		try {

			// Serialize..
			if (self::$data) {
				$sessionString = serialize(self::$data);

				if ($sessionString === false) {
					KLog::log('Serialization of session data did not work. Data was '.var_export(self::$data,true).'".','error');
					throw new Exception('Could not serialize session data');
				}
			}
			else {
				$sessionString = '';
			}

			// ..and update
			$query = "UPDATE `#__configbox_session` SET `data` = '".$db->getEscaped($sessionString)."', `updated` = ".intval(time()). " WHERE `id` = '".$db->getEscaped(self::$sessionId)."'";
			$db->setQuery($query);
			$db->query();

		}
		catch (Exception $e) {
			KLog::log('Could not store session record because of a DB error: "'.$e->getMessage().'".', 'error', 'Could not store session record.');
		}

	}

	public static function lockIsAcquired() {
		return (self::$lockIsAcquired == true);
	}

	public static function acquireLock() {

		if (self::$lockIsAcquired == true) {
			return true;
		}

		// Note that since MySQL 5.7.5 lock names cannot be longer than 64 characters.
		// A bad lock name will lead to timeouts in typical circumstances.
		// Using 32 is for a safety margin in case a lot of escaping is needed
		self::$lockName = substr('lock_'.self::$sessionId, 0, 32);

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
			return true;
		}

		if (empty(self::$lockName)) {
			$message = 'releaseLock was called, but no lock name is defined.';
			KLog::log($message, 'error');
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
