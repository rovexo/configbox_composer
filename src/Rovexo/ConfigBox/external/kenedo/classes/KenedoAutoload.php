<?php
class KenedoAutoload {
	
	static public $classes;
	
	static function loadClass($className) {
		$className = strtolower($className);
		if (isset(self::$classes[$className])) {
			require_once(self::$classes[$className]);
			return true;
		}
		else {
			return false;
		}
	}
	
	static function registerClass($className, $path) {
		$className = strtolower($className);
		if (is_file($path)) {
			self::$classes[$className] = $path;
			return true;
		}
		else {
			return false;
		}
	}
	
	static function getRegisteredClasses() {
		return self::$classes;
	}
	
	static function getRegisteredAutoloaders() {
		return spl_autoload_functions();
	}
	
}
