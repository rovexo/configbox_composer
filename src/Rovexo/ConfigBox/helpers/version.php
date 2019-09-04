<?php
class ConfigboxVersionHelper {

	static $version = '3.1.3';
	static $parts;
	static $platformVersion;

	static public function getConfigBoxVersion($part = NULL) {

		if ($part == NULL) {
			return self::$version;
		}

		if (!self::$parts) {
			self::splitConfigBoxVersion();
		}

		return self::$parts[$part];
	}

	protected static function splitConfigBoxVersion() {
		$version = self::getConfigBoxVersion();
		$x = explode('.',$version,3);
		self::$parts['major'] = $x[0];
		self::$parts['minor'] = $x[1];
		if (is_int($x[2])) {
			self::$parts['patchLevel'] = $x[2];
			self::$parts['betaString'] = '';
		}
		else {
			$l = explode('-',$x[2],2);
			self::$parts['patchLevel'] = $l[0];
			self::$parts['betaString'] = !empty($l[1]) ? $l[1] : '';
		}
	}

	static function getPlatformVersion() {

		if (!self::$platformVersion) {
			self::$platformVersion = KenedoPlatform::p()->getVersionShort();
		}
		return self::$platformVersion;
	}

	static function getIdForPlatformVersion() {
		$platformVersion = self::getPlatformVersion();
		$platformName = KenedoPlatform::getName();
		$id = substr($platformVersion,0,3);
		$id = $platformName.'-'.str_replace('.', '_', $id);
		return $id;
	}

}