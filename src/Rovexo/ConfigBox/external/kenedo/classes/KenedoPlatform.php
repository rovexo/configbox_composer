<?php
class KenedoPlatform {
	
	static protected $platformName;
	static protected $platform;
	
	static public function getName() {
		if (!self::$platformName) {
			self::$platformName = self::determineName();
		}
		
		return self::$platformName;
		
	}

	static function &get($method, $p1 = NULL, $p2 = NULL, $p3 = NULL) {
		
		if (empty(self::$platform)) {
			self::setPlatformObject();
		}
		
		$return = self::$platform->{'get'.ucfirst($method)}($p1, $p2, $p3);
		
		return $return;
	}

	/**
	 * @return InterfaceKenedoPlatform|KenedoPlatformJoomla|KenedoPlatformMagento|KenedoPlatformStandalone|KenedoPlatformWordpress
	 */
	static function &p() {
		
		if (empty(self::$platform)) {
			self::setPlatformObject();
		}
		
		return self::$platform;

	}

	/**
	 * @return KenedoDatabase
	 */
	static function &getDb() {

		$db = self::p()->getDb();
		
		return $db;

	}
	
	static protected function setPlatformObject() {
		
		$name = self::determineName();
		if (!$name) {
			die('Cannot determine platform.');
		}
		self::$platformName = strtolower($name);
			
		$path = dirname(__FILE__).DS.'..'.DS.'platforms'.DS. self::$platformName .DS.'general.php';
		$className = 'KenedoPlatform'.ucfirst( self::$platformName );
		
		require_once ($path);
		self::$platform = new $className;
		
		$interfaces = class_implements(self::$platform);
		if (!isset($interfaces['InterfaceKenedoPlatform'])) {
			die($className .' does not implement KenedoPlatform interface');
		}
			
		return true;
	}
	
	static protected function determineName() {
		
		if (class_exists('JConfig')) {
			return 'joomla';
		}
		elseif (class_exists('Mage')) {
			return 'magento';
		}
		elseif (class_exists('\Magento\Framework\App\Bootstrap')) {
			return 'magento2';
		}
		elseif (defined('IS_CB_API')) {
			return 'configbox_api';
		}
		elseif (defined('ABSPATH') && function_exists('add_action')) {
			return 'wordpress';
		}
		else {
			return 'standalone';
		}

	}
	
	
}