<?php
defined('CB_VALID_ENTRY') or die();

/**
 * Class ObserverWordpress
 *
 * This observer makes hidden WP pages so that we got URLs to load CB content.
 */
class ObserverWordpress {

	function onAfterStoreRecord($modelName, $data) {

		if (KenedoPlatform::getName() != 'wordpress') {
			return;
		}

		// Translations must get updated
		ConfigboxCacheHelper::purgeCache();

		if ($modelName == 'adminpages') {
			ConfigboxWordpressHelper::makePageForConfiguratorPage($data);
			return;
		}

		if ($modelName == 'adminproducts') {
			ConfigboxWordpressHelper::makePageForProduct($data);
			return;
		}

		if ($modelName == 'adminlistings') {
			ConfigboxWordpressHelper::makePageForListing($data);
			return;
		}

		if ($modelName == 'adminconfig') {
			ConfigboxWordpressHelper::makePageForAccount($data);
			ConfigboxWordpressHelper::makePageForCart($data);
			return;
		}

	}

	function onAfterCopyRecord($modelName, $newData) {

		if (KenedoPlatform::getName() != 'wordpress') {
			return;
		}

		// We can do the same as for storing
		$this->onAfterStoreRecord($modelName, $newData);

	}

}