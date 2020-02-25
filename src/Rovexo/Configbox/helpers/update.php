<?php
class ConfigboxUpdateHelper {

	protected static $latestUpdateVersion = '';
	protected static $currentlyProcessedVersion = '';

	protected static function canInstallFresh() {

		$latestVersion = ConfigboxSystemVars::getVar('latest_update_version');

		if (empty($latestVersion)) {

			$db = KenedoPlatform::getDb();
			$query = "
			SELECT `TABLE_NAME`
			FROM `information_schema`.`TABLES` 
			WHERE `TABLE_SCHEMA` = DATABASE() AND (`TABLE_NAME` LIKE '#__configbox_%' OR `TABLE_NAME` LIKE '#__cbcheckout_%')
			";
			$db->setQuery($query);
			$tables = $db->loadResultList();

			// Table #__configbox_system_vars gets created on the fly, so 1 table is ok.
			if (count($tables) <= 1) {
				return true;
			}
			else {
				return false;
			}

		}
		else {
			return false;
		}

	}

	static function areUpdatesProcessing() {
		$markerPath = self::getMarkerFilePath();
		return is_file($markerPath);
	}

	static function setUpdatesProcessing($bool) {

		$markerPath = self::getMarkerFilePath();

		clearstatcache(true, $markerPath);

		if ($bool == true) {
			if (is_file($markerPath) == false) {
				touch($markerPath);
			}
		}
		else {
			if (is_file($markerPath) == true) {
				unlink($markerPath);
			}
		}

		clearstatcache(true, $markerPath);

	}

	static function onShutdown() {
		self::setUpdatesProcessing(false);
	}

	protected static function getMarkerFilePath() {
		return KenedoPlatform::p()->getTmpPath().'/cb_update_in_progress';
	}

	/**
	 * Deals with any database or file updates on software updates. Runs always, it runs PHP scripts for each version.
	 * Keeps track of what update scripts have been run to avoid running anything twice. It does that by storing the
	 * version number in #__configbox_system_vars, key 'latest_update_version'.
	 */
	static function applyUpdates() {

		$db = KenedoPlatform::getDb();

		// Create the system vars table here, we do not do any table creation outside the update process and can't have it within the files
		$query = "
		CREATE TABLE IF NOT EXISTS `#__configbox_system_vars` (
		  `key` varchar(128) NOT NULL,
		  `value` text NOT NULL,
		  PRIMARY KEY (`key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$db->setQuery($query);
		$db->query();

		$hadFailedUpdate = ConfigboxSystemVars::getVar('failed_update_detected');

		// In case we had a failed update, we won't go in again until an admin sorted out the mess and removed the flag
		if ($hadFailedUpdate) {
			return;
		}

		// If we got a race condition, abort
		if (self::areUpdatesProcessing()) {
			return;
		}

		// Mark that we're upgrading
		self::setUpdatesProcessing(true);

		// Failsafe to remove the race-condition safeguard
		register_shutdown_function(array('ConfigboxUpdateHelper', 'onShutdown'));

		if (self::canInstallFresh()) {

			try {
				self::$latestUpdateVersion = self::installFresh();
			}
			catch (Exception $e) {

				KLog::log('Fresh install failed. Error messages follow.', 'upgrade_errors');

				// Note that upgrade script failed (will make a note appear in the dashboard)
				ConfigboxSystemVars::setVar('failed_update_detected', '1');

				self::setUpdatesProcessing(false);

				KLog::log($e->getMessage(), 'upgrade_errors');

				// Log each failed query just in case
				foreach ($db->getFailedQueryLog() as $data) {
					$logEntry = $data['caller_class'].'::'.$data['caller_function'].'(), File '.$data['caller_file'].' on line: '.$data['caller_line'].', Error num: "'.$data['error_num'].'", error msg: "'.$data['error_msg'].'", query: "'.$data['query'].'"';
					KLog::log($logEntry, 'upgrade_errors');
				}

			}

			self::applyCustomizationUpdates();
			sleep(2);
			self::setUpdatesProcessing(false);

			return;

		}

		self::$latestUpdateVersion = ConfigboxSystemVars::getVar('latest_update_version');


		// Get the folder with update scripts and get all files there
		$configboxUpdateFileFolder 	= KenedoPlatform::p()->getComponentDir('com_configbox').DS.'helpers'.DS.'updates';
		$configboxUpdateFiles 		= KenedoFileHelper::getFiles($configboxUpdateFileFolder, '.php$', false, false);

		// Sort the files by version
		usort($configboxUpdateFiles, 'version_compare');

		// We'll count the actually processed files for later
		$countProcessedFiles = 0;

		// We will also count failed queries to enable us to react better on failures
		$db->resetFailedQueryCount();

		foreach ($configboxUpdateFiles as $configboxUpdateFile) {

			// Note down the version of that file
			self::$currentlyProcessedVersion = basename($configboxUpdateFile,'.php');

			// Figure out if we should run that file
			$doProcessFile = version_compare(self::$currentlyProcessedVersion, self::$latestUpdateVersion,'gt');

			// Skip to next otherwise
			if ($doProcessFile == false) {
				continue;
			}

			$countProcessedFiles++;

			// Include the file
			try {
				require($configboxUpdateFileFolder . DS . $configboxUpdateFile);
			}
			catch(Exception $e) {

				// Note that upgrade script failed (will make a note appear in the dashboard)
				ConfigboxSystemVars::setVar('failed_update_detected', '1');

				// Log the errors on both upgrade_errors and error log
				KLog::log($e->getMessage(), 'upgrade_errors');

				// Log the failed DB queries (because the exception message might be about a different issue)
				foreach ($db->getFailedQueryLog() as $data) {
					$logEntry = $data['caller_class'].'::'.$data['caller_function'].'(), File '.$data['caller_file'].' on line: '.$data['caller_line'].', Error num: "'.$data['error_num'].'", error msg: "'.$data['error_msg'].'", query: "'.$data['query'].'"';
					KLog::log($logEntry, 'upgrade_errors');
				}

				// Stop processing files, at this point an admin needs to step in and sort things out.
				break;

			}

			// Store the version each time in the loop so we see what was the last successful file
			if ($db->getFailedQueryCount() == 0) {
				ConfigboxSystemVars::setVar('latest_update_version', self::$currentlyProcessedVersion);
			}

		}

		// Purge the case in case we actually did process any upgrade scripts
		if ($countProcessedFiles != 0) {
			ConfigboxCacheHelper::purgeCache();
			sleep(2);
		}

		// Run the customization updates
		self::applyCustomizationUpdates();

		self::setUpdatesProcessing(false);

	}

	/**
	 * Runs queries for a fresh install (DDL and DML)
	 * @return string The version string for the internal version we'll be after the install
	 * @throws Exception if any queries fail or anything going bad
	 */
	protected static function installFresh() {

		$file = __DIR__ . '/updates/complete/3.1.0.70_ddl.sql';

		// Get the complete SQL DDL queries
		$contents = str_replace('sltxh_', '#__', file_get_contents($file));
		// Get the individual queries
		$queries = explode(';', $contents);

		$db = KenedoPlatform::getDb();

		// Go execute each
		foreach ($queries as $query) {
			$query = trim($query);
			if ($query == '') {
				continue;
			}
			$db->setQuery($query);
			$db->query();
		}

		// Run the dynamic DML stuff
		require(__DIR__.'/updates/complete/3.1.0.70_dml.php');

		// Run the file updates
		require(__DIR__.'/updates/complete/3.1.0.70_files.php');

		ConfigboxSystemVars::setVar('latest_update_version', '3.1.0.70');

		return '3.1.0.70';

	}

	/**
	 * Same as applyUpdates, but for customizations. System var key is 'latest_customization_update_version'.
	 */
	protected static function applyCustomizationUpdates() {

		$db = KenedoPlatform::getDb();

		self::$latestUpdateVersion = ConfigboxSystemVars::getVar('latest_customization_update_version');

		if (!self::$latestUpdateVersion) {
			self::$latestUpdateVersion = '0.0.0';
		}

		$configboxUpdateFileFolder 	= KenedoPlatform::p()->getDirCustomization().DS.'updates';
		$configboxUpdateFiles 		= KenedoFileHelper::getFiles($configboxUpdateFileFolder, '.php$', false, false);

		// Sort the files by version
		usort($configboxUpdateFiles, 'version_compare');

		$countProcessedFiles = 0;

		foreach ($configboxUpdateFiles as $configboxUpdateFile) {

			// Note down the version of that file
			self::$currentlyProcessedVersion = basename($configboxUpdateFile,'.php');

			// Figure out if we should run that file
			$doProcessFile = version_compare(self::$currentlyProcessedVersion, self::$latestUpdateVersion,'gt');

			if ($doProcessFile) {

				$countProcessedFiles++;

				$db->resetFailedQueryCount();

				try {
					require( $configboxUpdateFileFolder.DS.$configboxUpdateFile );
				}
				catch(Exception $e) {

					KLog::log($e->getMessage(), 'upgrade_errors');

					try {
						ConfigboxSystemVars::setVar('failed_update_detected', '1');
					}
					catch(Exception $e) {
						KLog::log('Could not set system var value "failed_update_detected". Exception message was '.$e->getMessage(), 'error');
					}

					// Log the issues (use getFailedQueryLog instead of the Exception message, because the Exception
					// may not be thrown by a query failure)
					foreach ($db->getFailedQueryLog() as $data) {
						$logEntry = $data['caller_class'].'::'.$data['caller_function'].'(), File '.$data['caller_file'].' on line: '.$data['caller_line'].', Error num: "'.$data['error_num'].'", error msg: "'.$data['error_msg'].'", query: "'.$data['query'].'"';
						KLog::log($logEntry, 'upgrade_errors');
					}

					break;

				}

				if ($db->getFailedQueryCount() == 0) {
					ConfigboxSystemVars::setVar('latest_customization_update_version', self::$currentlyProcessedVersion);
				}

			}

		}

		// Purge the case in case we actually did process any upgrade scripts
		if ($countProcessedFiles != 0) {
			ConfigboxCacheHelper::purgeCache();
			sleep(1);
		}

	}

	/**
	 * usort comparison function. Sorts using version_compare.
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	static function sortFiles($a, $b) {
		return version_compare($a, $b);
	}

	/**
	 * @param string $tableName Table name with prefix
	 * @return string[] keys and values have the column name for quick isset() use
	 */
	static function getColumnNames($tableName) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT `COLUMN_NAME`
		FROM `INFORMATION_SCHEMA`.`COLUMNS`
		WHERE `TABLE_NAME` = '".$db->getEscaped($tableName)."'
  		AND `TABLE_SCHEMA` = '".$db->getEscaped($db->getSchemaName())."'";
		$db->setQuery($query);
		return $db->loadResultList('COLUMN_NAME');

	}

	/**
	 * Gets you column info from SHOW COLUMNS
	 * @param string $tableName Table name with prefix
	 * @return object[]
	 */
	static function getTableFields($tableName) {

		$db = KenedoPlatform::getDb();
		$db->setQuery( 'SHOW COLUMNS FROM ' . $tableName );

		$fields = $db->loadObjectList();
		$return = array();
		if (!is_array($fields)) {
			return $return;
		}
		foreach ($fields as $field) {
			$return[$field->Field] = $field;
		}
		return $return;
	}

	/**
	 * @param bool $withPrefixPlaceholders True if you want table prefixes replaced by #__
	 * @return string[] List of tables (without the #__ prefix)
	 */
	static function getTableList($withPrefixPlaceholders = false) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT `TABLE_NAME`
		FROM `INFORMATION_SCHEMA`.`TABLES` 
		WHERE `TABLE_SCHEMA` = '".$db->getEscaped($db->getSchemaName())."'";
		$db->setQuery($query);
		$tables = $db->loadResultList('TABLE_NAME');

		if ($withPrefixPlaceholders == true) {

			$return = array();
			$prefix = $db->getPrefix();
			$pattern = '/'.preg_quote($prefix, '/').'/';

			foreach ($tables as $table) {
				$newName = preg_replace($pattern, '#__', $table, 1);
				$return[$newName] = $newName;
			}

			return $return;
		}
		else {
			return $tables;
		}

	}

	/**
	 * @param string $tableName Table name with #__ prefix
	 * @return bool
	 */
	static function tableExists($tableName) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT COUNT(*)
		FROM `INFORMATION_SCHEMA`.`TABLES` 
		WHERE `TABLE_SCHEMA` = '".$db->getEscaped($db->getSchemaName())."' AND `TABLE_NAME` = '".$db->getEscaped($tableName)."'";
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($count == 0) {
			return false;
		}
		else {
			return true;
		}

	}

	/**
	 * @param string $tableName
	 * @param string $columnName
	 * @return bool
	 */
	static function tableFieldExists($tableName, $columnName) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT COUNT(*)
		FROM `INFORMATION_SCHEMA`.`COLUMNS`
		WHERE `TABLE_NAME` = '".$db->getEscaped($tableName)."'
  		AND `TABLE_SCHEMA` = '".$db->getEscaped($db->getSchemaName())."'
		AND `COLUMN_NAME` = '".$db->getEscaped($columnName)."'
		LIMIT 1";
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($count == 0) {
			return false;
		}
		else {
			return true;
		}

	}

	/**
	 *
	 * @param string $tableName (with #__ as prefix placeholder)
	 * @param string $fieldName
	 * @return string Constraint name (or empty string if there is none)
	 */
	static function getFkConstraintName($tableName, $fieldName) {

		$db = KenedoPlatform::getDb();
		$query = "
		SELECT `CONSTRAINT_NAME`
		FROM `information_schema`.`KEY_COLUMN_USAGE` AS `us`
		WHERE `us`.`REFERENCED_COLUMN_NAME` IS NOT NULL AND `us`.`TABLE_SCHEMA` LIKE (SELECT DATABASE()) AND `us`.`TABLE_NAME` LIKE '".$tableName."' AND `us`.`COLUMN_NAME` LIKE '".$fieldName."'";
		$db->setQuery($query);
		$constraint = $db->loadResult();

		return ($constraint) ? $constraint : '';

	}

	/**
	 *
	 * @param string $tableName (with #__ as prefix placeholder)
	 * @param string $keyName
	 * @return boolean|null true/false or NULL if the table does not exist
	 */
	static function keyExists($tableName, $keyName) {

		if (self::tableExists($tableName) == false) {
			return NULL;
		}

		$db = KenedoPlatform::getDb();
		$query = "SHOW INDEX FROM `".$tableName."` WHERE `Key_name` = '".$keyName."'";
		$db->setQuery($query);
		$exists = $db->loadAssoc();

		if ($exists) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Returns key names for that column
	 * @param string $tableName
	 * @param string $columnName
	 * @return string[]
	 */
	static function getKeyNames($tableName, $columnName) {

		if (self::tableExists($tableName) == false) {
			return array();
		}

		$db = KenedoPlatform::getDb();
		$query = "SHOW INDEX FROM `".$tableName."` WHERE `Column_name` = '".$columnName."'";
		$db->setQuery($query);
		$indices = $db->loadAssocList();

		if (!$indices) {
			return array();
		}

		$response = array();
		foreach ($indices as $index) {
			$response[] = $index['Key_name'];
		}

		return $response;


	}

}
