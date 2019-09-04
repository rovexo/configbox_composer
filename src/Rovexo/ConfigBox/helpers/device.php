<?php
class ConfigboxDeviceHelper {

	static function isTouchDevice() {
		return (self::getDevice() != 'desktop');
	}

	static function getDevice() {

		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			$device = "desktop";
		}
		elseif( stristr($_SERVER['HTTP_USER_AGENT'], 'ipad') ) {
			$device = "ipad";
		}
		elseif( stristr($_SERVER['HTTP_USER_AGENT'], 'iphone') ) {
			$device = "iphone";
		}
		elseif( stristr($_SERVER['HTTP_USER_AGENT'], 'ipod') ) {
			$device = "ipod";
		}
		elseif( stristr($_SERVER['HTTP_USER_AGENT'], 'blackberry') ) {
			$device = "blackberry";
		}
		elseif( stristr($_SERVER['HTTP_USER_AGENT'], 'android') ) {
			$device = "android";
		}
		else {
			$device = "desktop";
		}

		return $device;

	}

	static function getDeviceClasses() {
		$classes = 'device-'.self::getDevice().' touch-'.((self::isTouchDevice()) ? 'yes':'no');
		return $classes;
	}

}