<?php
defined('CB_VALID_ENTRY') or die();

class KenedoPropertyPaymentmethodparams extends KenedoProperty {

	/**
	 * @var KStorage data containing payment method parameters
	 */
	public $settings;

	/**
	 * Gets the request data coming from the selected PSP provider
	 * @param $data
	 */
	function getDataFromRequest( &$data ) {

		if (!empty($data->connector_name)) {
			$settingKeys = ConfigboxPspHelper::getPspConnectorSettingKeys($data->connector_name);

			$data->{$this->propertyName} = '';
			foreach ($settingKeys as $settingKey) {
				$data->{$this->propertyName} .= $settingKey.'="'.KRequest::getString($settingKey,'').'"'. "\n";
			}

		}

	}

}
