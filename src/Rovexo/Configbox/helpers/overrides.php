<?php
class ConfigboxOverridesHelper {

	static function loadOverrideFiles() {

		// Do output buffering (later drop the output). Just to avoid accidental output in any of the loaded files.
		ob_start();

		$folder = KenedoPlatform::p()->getDirCustomizationSettings();
		$files = (is_dir($folder)) ? KenedoFileHelper::getFiles( $folder, '.php$', false, true) : array();

		if (count($files)) {
			$settingDisplayErrors = ini_set('display_errors',1);
			foreach ($files as $file) {
				require_once($file);
			}
			ini_set('display_errors',$settingDisplayErrors);
		}

		$folder = KenedoPlatform::p()->getDirCustomization() .'/system_overrides';
		$files = (is_dir($folder)) ? KenedoFileHelper::getFiles( $folder, '.php$', false, true) : array();

		if (count($files)) {
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
