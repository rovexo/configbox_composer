<?php
class KenedoObserver {
	
	static public $observers = array();
	static protected $registrationDone = false;

	static public $legacyEventNames = array(
		'onConfigBoxGrandorderBeforeDiscounts' => 'onConfigBoxCartProcessingBeforeDiscounts',
		'onConfigBoxAfterProcessOrder'			=> 'onConfigBoxCartProcessingAfterPositions'
	);

	static function registerObservers($folder) {
		
		$files = KenedoFileHelper::getFiles($folder,'.php',false,true);
		
		foreach ($files as $file) {
			
			// Remove this when updates actually remove outdated files
			if (basename($file) == 'Cbcheckout.php') continue;

			require_once($file);
			$className = 'Observer'.KenedoFileHelper::stripExtension(basename($file));

			self::registerObserver($className);
		}
		
	}

	static function registerCustomObservers($afterSystem = true) {

		$connectors = ConfigboxCacheHelper::getCustomConnectors();

		if ($connectors) {

			$path = KenedoPlatform::p()->getDirCustomization() .'/custom_observers';

			foreach ($connectors as $connector) {
				if (is_file($path.DS.$connector->file) && ($connector->after_system == (int)$afterSystem)) {

					require_once($path.DS.$connector->file);
					$className = 'Observer'.KenedoFileHelper::stripExtension(basename($path.DS.$connector->file));

					KenedoObserver::registerObserver($className);
				}
			}

		}
	}
	
	static function registerObserver($className) {
		self::$observers[$className] = new $className();
	}
	
	static function triggerEvent($eventName, $parameters = array(), $returnLast = false) {

		// Register observers lazily only once the first event was triggered
		if (self::$registrationDone == false) {
			self::registerCustomObservers(false);
			self::registerObservers(KPATH_DIR_CB.'/observers');
			self::registerCustomObservers(true);
			self::$registrationDone = true;
		}

		if ($eventName && !empty(self::$legacyEventNames[$eventName])) {
			KLog::logLegacyCall('Event was renamed, "'.$eventName.'" is now "'.self::$legacyEventNames[$eventName].'". Change the first parameter of ::triggerEvent');
			$eventName = self::$legacyEventNames[$eventName];
		}

		$returns = array();
		
		foreach (self::$observers as &$observer) {
			
			if (method_exists($observer, $eventName)) {
				$returns[get_class($observer)] = call_user_func_array(array($observer, $eventName), $parameters);
			}
			
		}
		if ($returnLast) {
			if (count($returns) > 0) {
				return array_pop($returns);
			}
			else {
				return NULL;
			}
		}
		else {
			return $returns;
		}
		
		
	}
	
}