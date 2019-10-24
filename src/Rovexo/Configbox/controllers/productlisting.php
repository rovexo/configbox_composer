<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerProductlisting extends KenedoController {

	/**
	 * Returns the model to be used for standard tasks
	 *
	 * @return ConfigboxModelProductlisting
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelProductlisting');
	}

	/**
	 * Returns the KenedoView subclass for displaying arbitrary content
	 *
	 * @return ConfigboxViewProductlisting
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewProductlisting');
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewForm() {
		return NULL;
	}

	/**
	 * Outputs the product listing view
	 */
	function display() {
		$this->getDefaultView()->setListingId(KRequest::getInt('listing_id'))->display();
	}

	function getUrlSegments(&$queryParameters) {

		// Get the right language (either from query string or from current platform language)
		$langTag = (!empty($queryParameters['lang'])) ? $queryParameters['lang'] : KenedoPlatform::p()->getLanguageTag();

		$listingId = (!empty($queryParameters['listing_id'])) ? intval($queryParameters['listing_id']) : 0;

		$id = KenedoRouterHelper::getItemIdByLink('index.php?option=com_configbox&view=productlisting&listing_id='.$listingId, $langTag);

		if ($id) {
			unset($queryParameters['view'], $queryParameters['listing_id']);
			$queryParameters['Itemid'] = $id;
			return array();
		}
		else {
			unset($queryParameters['view'], $queryParameters['Itemid']);
			return array('productlisting');
		}

	}

}