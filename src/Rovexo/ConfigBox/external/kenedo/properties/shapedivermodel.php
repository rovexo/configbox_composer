<?php 
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyShapedivermodel extends KenedoProperty {

	/**
	 * Should make the Shapediver JSON data into nice array or similar
	 * @param object $data
	 */
	function appendDataForGetRecord( &$data ) {

	}

	/**
	 * @see KenedoPropertyShapedivermodel::appendDataForGetRecord()
	 *
	 * @param object $data
	 */
	public function appendDataForPostCaching(&$data) {
		$this->appendDataForGetRecord($data);
	}

	function getDataFromRequest(&$data) {
		$data->{$this->propertyName} = KRequest::getVar($this->propertyName, '');
	}

	/**
	 * If user provided the iframe HTML instead of the URL, extract the URL and replace the value here
	 * @param object $data
	 * @return bool
	 */
	function prepareForStorage(&$data) {

		// Make propery value an empty string if there isn't anything
		$data->{$this->propertyName} = !empty($data->{$this->propertyName}) ? $data->{$this->propertyName} : '';

		if (!empty($data->{$this->propertyName})) {

			$modelData  = json_decode($data->{$this->propertyName}, true);

			// In case we got no URL but somehow the whole iframe HTML slipped in, we extract the URL for the iFrameUrl
			if (strpos($modelData['iframeUrl'], 'http') !== 0) {
				preg_match('/src="([^"]+)"/', $modelData['iframeUrl'], $match);
				$modelData['iframeUrl'] = $match[1];

				$data->{$this->propertyName} = json_encode($modelData);
			}

		}

		return parent::prepareForStorage($data);

	}


	function check($data) {

		$this->resetErrors();

		$parentResult = parent::check($data);
		if ($parentResult == false) {
			return false;
		}

		if (empty($data->{$this->propertyName})) {
			return true;
		}

		$modelData = json_decode($data->{$this->propertyName}, true);

		$result = filter_var($modelData['iframeUrl'], FILTER_VALIDATE_URL);

		if ($result == false) {
			$this->setError(KText::_('The Shapediver model URL appears invalid'));
			return false;
		}
		else {
			return true;
		}

	}


}