<?php
defined('CB_VALID_ENTRY') or die();

class ObserverSystem {

	function onConfigboxInitialized() {

		// Apply software updates - just for M2 we do these only in module's Setup script
		if (KenedoPlatform::getName() !== 'magento2') {
			ConfigboxUpdateHelper::applyUpdates();
		}

		// Load the override files
		ConfigboxOverridesHelper::loadOverrideFiles();

		// Run the data cleanup processes
		if (php_sapi_name() != 'cli') {
			KenedoModel::getModel('ConfigboxModelCleanup')->cleanUp();
		}

	}

}