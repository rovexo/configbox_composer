<?php
class KStorage {
	
	public $data;
	
	public static function __set_state($array) {
		return new KStorage($array);
	}
	
	public function __construct($data = NULL, $type = 'auto') {
		
		$this->data = new stdClass();
		
		if ($data == NULL) {
			return true;
		}
		
		if ($type == 'auto') {
			
			if (is_object($data) || is_array($data)) {
				foreach ($data as $key=>$value) {
					$this->data->$key = $value;
				}
				return true;
			}
			
			$succ = @json_decode($data);
			if ($succ) {
				$type = 'json';
			}
			else {
				$type = 'ini';
			}
		}
		
		switch ($type) {
			case 'ini':
				$succ = $this->loadIni($data);
				break;
			default:
				$succ = $this->loadJson($data);
				break;
		}
		
		return $succ;
	}
	
	public function setProperties($data) {
		foreach ($data as $key=>$value) {
			$this->data->$key = $value;
		}
	}
	
	public function getProperties() {
		return $this->data;
	}
	
	public function get($key, $default = NULL) {
		
		if (isset($this->data->$key)) {
			return $this->data->$key;
		}
		else {
			return $default;
		}
	}
	
	public function set($key, $value) {
		$this->data->$key = $value;
	}
	
	public function remove($key) {
		if (isset($this->data->$key)) {
			unset($this->data->$key);
		}
	}
	
	public function loadFile($path, $type) {
		$data = file_get_contents($path);
		$this->__construct($data, $type);
	}
	
	public function toString($format = 'json') {
		
		switch ($format) {
			case 'ini':
				$string = $this->ini_encode($this->data);
				break;
			default:
				$string = json_encode($this->data);
				break;
		}
		
		return $string;
		
	}
	
	public function loadJson($data) {
		
		$data = json_decode($data);
		
		if ($data === false) {
			return false;
		}
		else {
			foreach ($data as $key=>$value) {
				$this->data->$key = $value;
			}
			return true;
		}
	}
	
	public function loadIni($data) {
		
		$data = $this->ini_decode($data);
		
		if ($data === false) {
			return false;
		}
		else {
			foreach ($data as $key=>$value) {
				$this->data->$key = $value;
			}
			return true;
		}
	}
	
	protected function ini_encode($data) {
		$ini = '';
		foreach ($data as $key=>$value) {
			$ini .= $key.'="'.$value."\"\n";
		}
		return $ini;
	}
	
	protected function ini_decode($data) {
		
		if (function_exists('parse_ini_string')) {
			$array = parse_ini_string($data);
			if ($array !== false) {
				return $array;
			}
		}
		
		$lines = explode("\n",$data);
		$array = array();
		foreach ($lines as $line) {
			if (trim($line) == '') continue;
			$pair = explode('=',$line,2);
			
			if (isset($pair[1])) {
				
				$value = trim( $pair[1], '"');
				$array[trim($pair[0])] = trim($value);
			}
			else {
				return false;
			}
		}
		
		return $array;
	}
	
}