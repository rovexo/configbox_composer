<?php
defined('CB_VALID_ENTRY') or die();

class ObserverSystem {

	function onConfigboxInitialized() {

		// Apply software updates
		ConfigboxUpdateHelper::applyUpdates();

		// Apply customization software updates
		ConfigboxUpdateHelper::applyCustomizationUpdates();

		// Load the override files
		ConfigboxOverridesHelper::loadOverrideFiles();

		// Run the data cleanup processes
		KenedoModel::getModel('ConfigboxModelCleanup')->cleanUp();

	}

}