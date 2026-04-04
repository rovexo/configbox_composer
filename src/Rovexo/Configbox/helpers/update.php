<?php
class ConfigboxUpdateHelper {

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
		clearstatcache(true, $markerPath);
		return is_file($markerPath);
	}

	static function setUpdatesProcessing($bool) {

		$markerPath = self::getMarkerFilePath();

		clearstatcache(true, $markerPath);

		if ($bool == true) {
			if (is_file($markerPath) == false) {
				$dir = dirname($markerPath);
				if (!is_dir($dir)) {
					mkdir($dir);
				}
				touch($markerPath);

				register_shutdown_function(function() {
					self::setUpdatesProcessing(false);
				});
			}
		}
		else {
			if (is_file($markerPath) == true) {
				unlink($markerPath);
			}
		}

	}

	protected static function getMarkerFilePath() {
		return KenedoPlatform::p()->getTmpPath().'/cb_update_in_progress';
	}

	static function ensureSysVarsTableExistence() {
		if (self::tableExists('#__configbox_system_vars') == false) {
			$db = KenedoPlatform::getDb();
			$query = "
			CREATE TABLE IF NOT EXISTS `#__configbox_system_vars` (
			  `key` varchar(128) NOT NULL,
			  `value` text NOT NULL,
			  PRIMARY KEY (`key`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$db->setQuery($query);
			$db->query();
		}
	}

	/**
	 * Deals with any database or file updates on software updates. Runs always, it runs PHP scripts for each version.
	 * Keeps track of what update scripts have been run to avoid running anything twice. It does that by storing the
	 * version number in #__configbox_system_vars, key 'latest_update_version'.
	 */
	static function applyUpdates() {

		self::ensureSysVarsTableExistence();

		// In case we had a failed update, we won't go in again until an admin sorted out the mess and removed the flag
		if (ConfigboxSystemVars::getVar('failed_update_detected') === '1') {
			return;
		}

		// If we got a race condition, abort
		if (self::areUpdatesProcessing()) {
			return;
		}

		try {

			if (self::canInstallFresh()) {
				self::setUpdatesProcessing(true);
				self::installFresh();
			}

			$regularDir = KenedoPlatform::p()->getComponentDir('com_configbox').'/helpers/updates';
			$customDir = KenedoPlatform::p()->getDirCustomization().'/updates';

			$regularScripts = self::getUnprocessedUpdateScripts($regularDir, 'latest_update_version');
			$customScripts = self::getUnprocessedUpdateScripts($customDir, 'latest_customization_update_version');

			if (count($regularScripts) || count($customScripts)) {
				self::setUpdatesProcessing(true);
				self::runUpdateScripts($regularScripts, 'latest_update_version');
				self::runUpdateScripts($customScripts, 'latest_customization_update_version');
				self::setUpdatesProcessing(false);
			}

		}
		catch (Throwable $e) {
			ConfigboxSystemVars::setVar('failed_update_detected', '1');
			KLog::log($e->getMessage().' in file '.$e->getFile().':'.$e->getLine()."\n\nTrace:\n".$e->getTraceAsString(), 'upgrade_errors');
			self::setUpdatesProcessing(false);
			throw $e;
		}

	}

	/**
	 * Runs queries for a fresh install (DDL and DML)
	 * @return string The version string for the internal version we'll be after the install
	 * @throws Exception if any queries fail or anything going bad
	 */
	protected static function installFresh() {

		$file = __DIR__ . '/updates/complete/3.3.3.10_ddl.sql';

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
		require(__DIR__.'/updates/complete/3.3.3.10_dml.php');

		// Run the file updates
		require(__DIR__.'/updates/complete/3.3.3.10_files.php');

		ConfigboxSystemVars::setVar('latest_update_version', '3.3.3.10');

		return '3.3.3.10';

	}

	protected static function getUnprocessedUpdateScripts($pathUpdateFilesDir, $keyLatestInstalledVersion) {

		$lastestInstalledVersion = ConfigboxSystemVars::getVar($keyLatestInstalledVersion);
		if ($lastestInstalledVersion === null) {
			$lastestInstalledVersion = '0.0.0';
		}

		// Get all update files and sort by version
		$updateFiles = KenedoFileHelper::getFiles($pathUpdateFilesDir, '.php$', false, true);
		usort($updateFiles, 'version_compare');

		// Collect the files that haven't run yet
		$filesToProcess = [];
		foreach ($updateFiles as $updateFile) {
			$version = basename($updateFile,'.php');
			if (version_compare($version, $lastestInstalledVersion,'gt') === true) {
				$filesToProcess[] = $updateFile;
			}
		}

		return $filesToProcess;

	}

	protected static function runUpdateScripts($filesToProcess, $keyLatestInstalledVersion) {

		foreach ($filesToProcess as $fileToProcess) {
			require($fileToProcess);
			ConfigboxSystemVars::setVar($keyLatestInstalledVersion, basename($fileToProcess,'.php'));
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
