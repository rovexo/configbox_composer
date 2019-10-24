<?php
interface InterfaceKenedoDatabase {
		
	public function __construct();
	public function __destruct();
	public function getPrefix();
	public function getEscaped($text);
	public function getQuoted($text);
	public function getErrorNum();
	public function getErrorMsg();
	public function splitSql($string);
	public function setQuery($query, $start = 0, $limit = 0);
	public function getQuery();
	public function query();
	public function getAffectedRows();
	public function getReturnedRows();
	public function loadResult();
	public function loadResultArray($fieldNum = 0);
	public function loadAssoc();
	public function loadAssocList($key = '');
	public function loadObject();
	public function loadObjectList($indexField = '');
	public function loadRow();
	public function loadRowList($indexField = '');
	public function loadResultList($keyField, $valueField = NULL);
	public function insertObject($table, &$object, $keyName = NULL);
	public function replaceObject($table, $object);
	public function updateObject($table, &$object, $keyName = NULL, $updateNulls = true);
	public function insertid();
	public function getTableFields($table);
	
}