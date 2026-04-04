<?php
class ConfigboxOverridesHelper {

	static function loadOverrideFiles() {

		// Do output buffering (later drop the output). Just to avoid accidental output in any of the loaded files.
		ob_start();

		$folder = KenedoPlatform::p()->getDirCustomizationSettings();
		$files = (is_dir($folder)) ? KenedoFileHelper::getFiles( $folder, '.php$', false, true) : array();

		if (count($files)) {
			usort($files, function($a, $b) {
				// Bring strings starting with underscore first
				if ($a[0] === '_' && $b[0] !== '_') return -1;
				if ($b[0] === '_' && $a[0] !== '_') return 1;
				return strcasecmp($a, $b); // case-insensitive sort otherwise
			});
			foreach ($files as $file) {
				require_once($file);
			}
		}

		$folder = KenedoPlatform::p()->getDirCustomization() .'/system_overrides';
		$files = (is_dir($folder)) ? KenedoFileHelper::getFiles( $folder, '.php$', false, true) : array();

		if (count($files)) {
			usort($files, function($a, $b) {
				// Bring strings starting with underscore first
				if ($a[0] === '_' && $b[0] !== '_') return -1;
				if ($b[0] === '_' && $a[0] !== '_') return 1;
				return strcasecmp($a, $b); // case-insensitive sort otherwise
			});
			$settingDisplayErrors = ini_set('display_errors',1);
			foreach ($files as $file) {
				require_once($file);
			}
			ini_set('display_errors',$settingDisplayErrors);
		}

		// And drop any output done.
		ob_end_clean();

	}

}
