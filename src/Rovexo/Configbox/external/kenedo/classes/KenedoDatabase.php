<?php
defined('CB_VALID_ENTRY') or die();

class KenedoDatabase {

	protected $db;

	protected $queryCount = 0;

	protected $failedQueryCount = 0;
	protected $failedQueryLog = array();

	protected $queryList = array();

	protected $link;
	protected $start = 0;
	protected $limit = NULL;

	protected $query;
	protected $result;

	protected $hostname;
	protected $username;
	protected $password;
	protected $database;
	protected $prefix;
	protected $port;
	protected $socket;

	public function __construct() {

		$this->createDatabaseLink();
		return $this;

	}

	function __destruct() {
		if (is_a($this->link , 'mysqli') || is_resource($this->link)) {
			mysqli_close($this->link);
		}
	}

	protected function createDatabaseLink() {

		$connectionData = KenedoPlatform::p()->getDbConnectionData();

		$this->hostname = $connectionData->hostname;
		$this->username = $connectionData->username;
		$this->password = $connectionData->password;
		$this->database = $connectionData->database;
		$this->prefix 	= $connectionData->prefix;

		$this->port = 3306;
		$this->socket = NULL;

		if (strpos($this->hostname,':') !== false) {
			$ex = explode(':',$this->hostname);
			$this->hostname = $ex[0];
			if (is_numeric($ex[1])) {
				$this->port = $ex[1];
			}
			else {
				$this->socket = $ex[1];
			}
		}

		$this->link = mysqli_connect( $this->hostname, $this->username, $this->password, $this->database, $this->port, $this->socket );

		if ($this->link == false) {
			$internalLogMessage = 'Could not establish a connection to the database. Connection error message is "'.mysqli_connect_error().'".';
			KLog::log($internalLogMessage,'db_error');
			KLog::log($internalLogMessage,'error', 'Could not establish a connection to the database. Check the configbox error log file for more information.');
			return false;
		}

		$query = "SET SESSION sql_mode = '', group_concat_max_len = 20000;";
		$this->setQuery($query);
		$this->query();

		mysqli_set_charset($this->link, "utf8");

		return true;

	}

	/**
	 * @return string
	 */
	public function getSchemaName() {
		return $this->database;
	}

	public function getQueryCount() {
		return $this->queryCount;
	}

	public function getTotalQueryTime() {
		$t = 0;
		foreach ($this->queryList as $caller=>$items) {
			foreach ($items as $item) {
				$t += $item['time'];
			}
		}
		return $t;
	}

	public function getQueryList() {
		$queryList = array();
		foreach ($this->queryList as $caller=>$items) {
			$t = 0;
			foreach ($items as $item) {
				$t += $item['time'];
			}
			$key = $t.'-'.count($items).'-'.$caller;
			$queryList[$key] = $items;
		}
		return $queryList;
	}

	public function getPrefix() {
		return $this->prefix;
	}

	public function getEscaped($text) {
		return mysqli_real_escape_string($this->link, $text);
	}

	public function getQuoted($text) {
		return '`'.$text.'`';
	}

	public function getErrorNum() {
		return mysqli_errno($this->link);
	}

	public function getErrorMsg() {
		return mysqli_error($this->link);
	}

	public function splitSql($string) {
		return explode("\n",$string);
	}

	public function setQuery($query, $start = 0, $limit = 0) {

		$this->query = $query;

		if ($start > 0 || $limit > 0) {
			$limiter = ' LIMIT ' . max($start, 0) . ', ' . max($limit, 0);
			$this->query .= $limiter;
		}

	}

	public function getQuery() {
		return $this->query;
	}

	public function resetFailedQueryCount() {
		$this->failedQueryCount = 0;
	}

	public function resetFailedQueryLog() {
		$this->failedQueryLog = array();
	}

	public function getFailedQueryCount() {
		return $this->failedQueryCount;
	}

	public function getFailedQueryLog() {
		return $this->failedQueryLog;
	}

	/**
	 * @return bool|mysqli_result
	 * @throws Exception if the query went bad (or no query was set)
	 * @see KenedoDatabase::setQuery
	 */
	public function query() {

		// Return false if there is no query
		if (!$this->query) {
			throw new Exception('Query called, but no query set with setQuery().');
		}

		// Replace the table prefix
		$query = str_replace('#__', $this->prefix, $this->query);

		// Increment the query count
		$this->queryCount++;

		$startTime = microtime(true);

		// Do the query
		$this->result = mysqli_query($this->link, $query, MYSQLI_STORE_RESULT);

		// If it's a query that returns no result set, then log it right here
		if ($this->result === true) {
			$this->addQueryListItem($startTime);
		}

		// Log failed queries including the caller
		if ($this->result === false) {

			// Get the backtrace
			$stack = debug_backtrace(false);

			// Figure out which call started the query. Go into the stack and find the first entry that is outside this class
			$i = -1;
			foreach ($stack as $line) {
				if (empty($line['class']) || $line['class'] != __CLASS__ ) {
					break;
				}
				$i++;
			}

			// Prepare the caller info for the log entry
			$callerInfo = array(
				'file' => !empty($stack[$i]['file']) ? str_replace(KPATH_ROOT,'KPATH_ROOT',$stack[$i]['file']) : 'File unkown',
				'line' => !empty($stack[$i]['line']) ? $stack[$i]['line'] : 'Line unkown',
				'class' => !empty($stack[$i+1]['class']) ? $stack[$i+1]['class'] : 'No class',
				'function' => !empty($stack[$i+1]['function']) ? $stack[$i+1]['function'] : 'No function',
			);

			$data = array(
				'caller_file'=>$callerInfo['file'],
				'caller_line'=>$callerInfo['line'],
				'caller_class'=>$callerInfo['class'],
				'caller_function'=>$callerInfo['function'],
				'query'=>$query,
				'error_num'=>$this->getErrorNum(),
				'error_msg'=>$this->getErrorMsg(),
			);

			// Prepare the log entry
			$logEntry = $data['caller_class'].'::'.$data['caller_function'].'(), File '.$data['caller_file'].' on line: '.$data['caller_line'].', Error num: "'.$data['error_num'].'", error msg: "'.$data['error_msg'].'", query: "'.$data['query'].'"';

			// Increment failed query count
			$this->failedQueryCount++;

			// Add to the failed query log
			$this->failedQueryLog[] = $data;

			KLog::log($logEntry, 'db_error');

			throw new Exception($logEntry, $data['error_num']);

		}

		return $this->result;
	}

	public function getAffectedRows() {
		return mysqli_affected_rows($this->link);
	}

	public function getReturnedRows() {
		return mysqli_num_rows($this->result);
	}

	protected function addQueryListItem($startTime) {
		if (defined('CONFIGBOX_ENABLE_PERFORMANCE_TRACKING') && CONFIGBOX_ENABLE_PERFORMANCE_TRACKING) {
			$timing['time'] = sprintf("%F", (microtime(true) - $startTime) * 1000);
			$timing['query'] = $this->getQuery();

			$stack = debug_backtrace(false);
			$info['class'] = isset($stack[2]['class']) ? $stack[2]['class'] : NULL;
			$info['method'] = isset($stack[2]['function']) ? $stack[2]['function'] : NULL;
			$info['line'] = isset($stack[1]['line']) ? $stack[1]['line'] : NULL;
			$info['file'] = isset($stack[1]['file']) ? $stack[1]['file'] : NULL;

			$timing['caller'] = $info['class'].':'.$info['method']. ' Line:'.$info['line'];

			$this->queryList[$timing['caller']][] = $timing;
		}
	}

	/**
	 * @return null|string
	 */
	public function loadResult() {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}

		$ret = null;
		if ($row = mysqli_fetch_row( $res )) {
			$ret = $row[0];
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $ret;

	}

	/**
	 * @return null|string
	 */
	public function getCount() {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}

		$count = mysqli_num_rows($res);

		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $count;

	}

	/**
	 * Gets you a flat array with the key as in $keyField and value as in $valueField
	 * You can omit both params, then you get a flat array with the first field coming out of the query
	 * @param string $indexField The column for writing the list's array keys. No key field gets you a numeric array.
	 * @param string $valueField The column for the list's values. No value field gets you the first column.
	 * @return null|string[]|int[]|float[]
	 */
	public function loadResultList($indexField = NULL, $valueField = NULL) {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}
		$array = array();

		if (!empty($indexField)) {
			while ($row = mysqli_fetch_assoc($res)) {
				if ($valueField !== null) {
					$array[$row[$indexField]] = $row[$valueField];
				} else {
					$array[$row[$indexField]] = reset($row);
				}
			}
		}
		else {
			while ($row = mysqli_fetch_row($res)) {
				if (!empty($valueField)) {
					$array[] = $row[$valueField];
				} else {
					$array[] = $row[0];
				}
			}
		}

		mysqli_free_result($res);

		$this->addQueryListItem($start);
		return $array;
	}

	/**
	 * @return null|string[]
	 */
	public function loadAssoc() {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}
		$ret = null;

		if ($array = mysqli_fetch_assoc( $res )) {
			$ret = $array;
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $ret;
	}

	/**
	 * @param string $indexField The column for writing the list's array keys. No key field gets you a numeric array.
	 * @return null|string[][]
	 */
	public function loadAssocList($indexField = '') {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}
		$array = array();

		while ($row = mysqli_fetch_assoc( $res )) {
			if ($indexField) {
				$array[$row[$indexField]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $array;
	}

	/**
	 * @return null|object
	 */
	public function loadObject() {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}
		$ret = null;

		if ($object = mysqli_fetch_object( $res, 'KenedoObject' )) {
			$ret = $object;
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $ret;
	}

	/**
	 * @param string $indexField The column for writing the list's array keys. No key field gets you a numeric array.
	 * @return null|object[]
	 */
	public function loadObjectList($indexField = '') {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}
		$array = array();

		while ($row = mysqli_fetch_object( $res , 'KenedoObject')) {
			if ($indexField) {
				$array[$row->$indexField] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $array;
	}

	/**
	 * @return null|string[]
	 */
	public function loadRow() {

		$start = microtime(true);

		if (!($res = $this->query())) {
			return null;
		}
		$ret = null;

		if ($row = mysqli_fetch_row( $res )) {
			$ret = $row;
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $ret;
	}

	/**
	 * @param string $indexField The column for writing the list's array keys. No key field gets you a numeric array.
	 * @return null|string[]
	 */
	public function loadRowList($indexField = '') {

		$start = microtime(true);

		if ($indexField == '') $indexField = NULL;

		if (!($res = $this->query())) {
			return null;
		}
		$array = array();

		while ($row = mysqli_fetch_row( $res )) {
			if ($indexField !== null) {
				$array[$row[$indexField]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysqli_free_result( $res );

		$this->addQueryListItem($start);
		return $array;
	}

	public function replaceObject($table, &$object) {

		$fields = array();
		$values = array();
		$setPieces = array();

		foreach (get_object_vars( $object ) as $k => $v) {
			// skip Non-scalars
			if (!is_scalar($v) && $v !== null) {
				continue;
			}

			$fields[$k] = $this->getQuoted($k);
			$values[$k] = ($v === null) ? 'NULL' : "'".$this->getEscaped($v)."'";
			$setPieces[$k] = $fields[$k] .' = '.$values[$k];

		}

		$query = "REPLACE INTO `".$table."` (".implode(', ', $fields).") VALUES (".implode(', ', $values).")";
		$this->setQuery($query);
		$success = $this->query();

		if ($success === false) {
			return false;
		}

		return true;

	}

	public function insertObject($table, &$object, $keyName = NULL) {

		$fields = array();
		$values = array();
		$setPieces = array();

        $useDuplicateKey = true;

		foreach (get_object_vars( $object ) as $k => $v) {
			// skip Non-scalars
			if (!is_scalar($v) && $v !== null) {
				continue;
			}

			if ($k == $keyName && empty($v)) {
                $useDuplicateKey = false;
                continue;
            }

			$fields[$k] = $this->getQuoted($k);
			$values[$k] = ($v === null) ? 'NULL' : "'".$this->getEscaped($v)."'";
			$setPieces[$k] = $fields[$k] .' = '.$values[$k];

		}

		$query = "INSERT INTO `".$table."` (".implode(', ', $fields).") VALUES (".implode(', ', $values).")";

		if ($useDuplicateKey == true) {
            $query .= " ON DUPLICATE KEY UPDATE ".implode(",\n", $setPieces);
        }

		$this->setQuery($query);
		$success = $this->query();

		if ($success === false) {
			return false;
		}

		$insertId = $this->insertid();
		if ($keyName && $insertId) {
			$object->$keyName = $insertId;
		}

		return true;

	}

	public function updateObject($table, &$object, $keyName) {

		$fields = array();
		$values = array();
		$setPieces = array();

		foreach (get_object_vars( $object ) as $k => $v) {
			// skip Non-scalars
			if (!is_scalar($v)) {
				continue;
			}

			$fields[$k] = $this->getQuoted($k);
			$values[$k] = ($v === null) ? 'NULL' : "'".$this->getEscaped($v)."'";
			$setPieces[$k] = $fields[$k] .' = '.$values[$k];

		}

		$query = "
		UPDATE `".$table."` SET ".implode(",\n", $setPieces)."
		WHERE `".$keyName."` = '".$this->getEscaped($object->{$keyName})."'";
		$this->setQuery($query);
		$success = $this->query();

		if ($success === false) {
			return false;
		}

		$insertId = $this->insertid();
		if ($keyName && $insertId) {
			$object->$keyName = $insertId;
		}

		return true;

	}

	public function insertid() {
		return mysqli_insert_id( $this->link );
	}

	/**
	 * @param string $table Table name (with #__)
	 * @return string[][] Column information for the table (keys as in information_schema.columns)
	 */
	public function getColumnInfo($table) {

		$query = "
		SELECT *
		FROM `INFORMATION_SCHEMA`.`COLUMNS`
		WHERE `TABLE_NAME` = '".$this->getEscaped($table)."'
  		AND `TABLE_SCHEMA` = '".$this->getEscaped($this->database)."'";
		$this->setQuery($query);
		$info = $this->loadAssocList('COLUMN_NAME');

		return $info;

	}

	/**
	 * @param $tables
	 * @param bool $typeOnly
	 * @return array
	 * @deprecated Use KenedoDatabase::getColumnInfo instead and mind the different signature and return values
	 */
	public function getTableFields($tables, $typeOnly = true) {

		settype($tables, 'array');
		$result = array();

		foreach ($tables as $tblval) {
			$query = 'SHOW FIELDS FROM ' . $tblval;
			$this->setQuery($query);
			$fields = $this->loadObjectList();

			if($typeOnly) {
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
				}
			}
			else {
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = $field;
				}
			}
		}
		return $result;
	}

	/**
	 * @var string[] Each transaction start creates a savepoint id. The first one (the START TRANSACTION) creates an empty ID
	 * @see startTransaction, commitTransaction, rollbackTransaction, getNewSavepointId
	 */
	protected $savepointIds = array();

	/**
	 * @return string New savepoint id (will be unique)
	 */
	protected function getNewSavepointId() {
		$savePointId = 'savepoint_'.rand(0, 2000);
		while (in_array($savePointId, $this->savepointIds)) {
			$savePointId = 'savepoint_'.rand(0, 2000);
		}
		return $savePointId;
	}

	/**
	 * Starts a transaction (or creates a SAVEPOINT if a transaction runs already)
	 */
	public function startTransaction() {

		if (count($this->savepointIds) == 0) {
			$this->setQuery('START TRANSACTION');
			$this->query();
			$savePointId = '';
		}
		else {
			$savePointId = $this->getNewSavepointId();
			$this->setQuery('SAVEPOINT '.$savePointId);
			$this->query();
		}

		$this->savepointIds[] = $savePointId;

	}

	/**
	 * Commits a transaction (or releases a SAVEPOINT if we deal with a 'nested transaction')
	 * @throws Exception If no transaction was started yet (using startTransaction)
	 */
	public function commitTransaction() {

		if (count($this->savepointIds) == 0) {
			throw new Exception('Transaction commit was called, but no transaction was started');
		}

		if (count($this->savepointIds) == 1) {
			array_pop($this->savepointIds);
			$this->setQuery('COMMIT');
			$this->query();
		}
		else {
			$lastSavePoint = array_pop($this->savepointIds);
			$this->setQuery('RELEASE SAVEPOINT ' . $lastSavePoint);
			$this->query();
		}

	}

	/**
	 * Rolls back a transaction (or rolls back to a savepoint if there is a 'nested transaction')
	 * @throws Exception If there was no transaction
	 */
	public function rollbackTransaction() {

		if (count($this->savepointIds) == 0) {
			throw new Exception('Transaction rollback was called, but no transaction was started');
		}

		if (count($this->savepointIds) == 1) {
			array_pop($this->savepointIds);
			$this->setQuery('ROLLBACK');
			$this->query();
		}
		else {
			$lastSavePoint = array_pop($this->savepointIds);
			$this->setQuery('ROLLBACK TO SAVEPOINT ' . $lastSavePoint);
			$this->query();
		}

	}

}

