<?php
class KenedoProfiler {

	static $starttime = array();
	static $takes = array();
	static $timings = array();

	static function start($task = 'default') {
		self::$starttime[$task] = microtime(true)*1000;
	}

	static function time($task = 'default') {
		$time = (int) ((microtime(true)*1000) - self::$starttime[$task]);
		self::$takes[$task][] = $time;
		return $time;
	}

	static function stop($task = 'default') {
		$time = (int) ((microtime(true)*1000) - self::$starttime[$task]);
		self::$timings[$task] = $time;
		return $time;
	}

	static function getTakes() {
		return self::$takes;
	}

	static function getTimings() {
		return self::$timings;
	}

}