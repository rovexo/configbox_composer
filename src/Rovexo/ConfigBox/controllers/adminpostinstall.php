<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminpostinstall extends KenedoController {

	/**
	 * @return ConfigboxModelAdminpostinstall
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminpostinstall');
	}

	/**
	 * @return ConfigboxViewAdminpostinstall
	 */
	protected function getDefaultView() {
		return KenedoView::getView('ConfigboxViewAdminpostinstall');
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

	function display() {

		$wrapperView = KenedoView::getView('ConfigboxViewBlank');
		$wrapperView->assignRef('output', $this->getDefaultView()->getHtml());
		$wrapperView->display();

	}

	function storeLicenseKey() {

		$licenseKey = KRequest::getString('licenseKey');

		// Start validation
		$validationIssues = array();

		// Check the supplied email address
		if (trim($licenseKey) == '') {
			$validationIssues['licenseKey'] = KText::_('Please enter your license key.');
		}

		// Give feedback on validation issues
		if (count($validationIssues)) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setValidationIssues($validationIssues)->toJson();
			return;
		}

		$db = KenedoPlatform::getDb();

		$query = "UPDATE `#__configbox_config` SET `product_key` = '".$db->getEscaped($licenseKey)."' WHERE `id` = 1";
		$db->setQuery($query);
		$db->query();

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}

	function storeShopData() {

		$email = KRequest::getString('email');
		$website = KRequest::getString('shopWebsite');
		$shopName = KRequest::getString('shopName');

		// Prepend scheme to website just for validation since I can't get filter_var to ignore a missing scheme
		if (stripos($website, 'http:') !== 0 && stripos($website, 'https:') !== 0) {
			$websiteNormalized = KPATH_SCHEME.'://'.ltrim($website,'/');
		}
		else {
			$websiteNormalized = $website;
		}

		// Start validation
		$validationIssues = array();

		// Check the supplied email address
		if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
			$validationIssues['email'] = KText::_('Email does not appear to be valid.');
		}

		// Check the supplied website
		if (filter_var($websiteNormalized, FILTER_VALIDATE_URL) == false) {
			$validationIssues['shopWebsite'] = KText::_('Shop website URL does not appear to be valid.');
		}

		// See if shop name was supplied
		if (empty($shopName)) {
			$validationIssues['shopName'] = KText::_('Please enter a store name.');
		}

		// Give feedback on validation issues
		if (count($validationIssues)) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setValidationIssues($validationIssues)->toJson();
			return;
		}

		// Get the existing shop data record and update
		$shopModel = KenedoModel::getModel('ConfigboxModelAdminshopdata');
		$shopData = $shopModel->getRecord(1);
		$shopData->shopname = $shopName;
		$shopData->shopwebsite = $website;
		$shopData->shopemailsales = $email;
		$shopData->shopemailsupport = $email;

		$shopModel->prepareForStorage($shopData);

		if ($shopModel->validateData($shopData) == false) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors($shopModel->getErrors())->toJson();
			return;
		}

		if ($shopModel->store($shopData) == false) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors($shopModel->getErrors())->toJson();
			return;
		}

		$configModel = Kenedomodel::getModel('ConfigboxModelAdminconfig');
		$configData = $configModel->getRecord(1);
		$configData->review_notification_email = KRequest::getString('email');

		$configModel->prepareForStorage($configData);

		if ($configModel->validateData($configData) == false) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors($configModel->getErrors())->toJson();
			return;
		}

		if ($configModel->store($configData) == false) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors($configModel->getErrors())->toJson();
			return;
		}

		ConfigboxCacheHelper::purgeCache();

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}

	function storeTaxData() {

		$taxRate = KRequest::getString('taxRate');
		$taxRate = KText::getNormalizedNumber($taxRate);
		$countryId = KRequest::getInt('countryId');
		$taxMode = KRequest::getString('taxMode');

		$issues = array();

		if (!is_numeric($taxRate)) {
			$issues['taxRate'] = KText::_('The tax rate does not appear like a valid number.');
		}

		if ($taxRate > 100) {
			$issues['taxRate'] = KText::_('The tax rate does not appear right, it is above 100. Do you use the right decimal symbol?');
		}

		if (empty($countryId)) {
			$issues['countryId'] = KText::_('Please pick a country.');
		}
		if (empty($taxMode)) {
			$issues['taxMode'] = KText::_('Please pick tax mode.');
		}

		if (count($issues)) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setValidationIssues($issues)->toJson();
			return;
		}

		$db = KenedoPlatform::getDb();

		try {

			$db->startTransaction();

			// Store the requested default tax rate to all tax classes
			$taxClassModel = KenedoModel::getModel('ConfigboxModelAdmintaxclasses');
			$taxClasses = $taxClassModel->getRecords();
			if (count($taxClasses)) {
				foreach ($taxClasses as $taxClass) {
					$taxClass->default_tax_rate = number_format($taxRate, 3, '.', '');
					$taxClassModel->prepareForStorage($taxClass);
					$success = $taxClassModel->store($taxClass);
					if ($success == false) {
						throw new Exception($taxClassModel->getError());
					}

				}
			}

			// Store the requested country ID (and make sure state ID makes sense)
			$shopModel = KenedoModel::getModel('ConfigboxModelAdminshopdata');
			$shopData = $shopModel->getRecord(1);
			$shopData->country_id = $countryId;

			// Just in case there is a state_id set already (and it does not match the country or that state doesn't
			// exist), then set it to NULL
			if ($shopData->state_id != NULL) {
				$state = ConfigboxCountryHelper::getState($shopData->state_id);
				if (!$state || $state->country_id != $countryId) {
					$shopData->state_id = NULL;
				}
			}

			$success = $shopModel->store($shopData);

			if ($success == false) {
				throw new Exception($shopModel->getError());
			}

			// Go set any customer group to the requested tax mode
			$groupsModel = KenedoModel::getModel('ConfigboxModelAdmincustomergroups');
			$groups = $groupsModel->getRecords();

			foreach ($groups as $group) {

				$group->b2b_mode = ($taxMode == 'b2b') ? '1':'0';

				$groupsModel->prepareForStorage($group);
				$success = $groupsModel->store($group);
				if ($success == false) {
					throw new Exception($groupsModel->getError());
				}

			}

			$db->commitTransaction();

		}
		catch (Exception $e) {
			$db->rollbackTransaction();
			$msg = KText::_('A system error occured during storing tax data. Please contact Rovexo to resolve this issue.');
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors(array($msg))->toJson();
			return;
		}

		ConfigboxCacheHelper::purgeCache();

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}

	function storeLanguageTags() {

		$languageTags = KRequest::getArray('languageTags');

		$issues = array();

		if (!is_array($languageTags) || count($languageTags) == 0) {
			$issues['languageTags'] = KText::_('Please select at least one language.');
		}

		if (count($issues)) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setValidationIssues($issues)->toJson();
			return;
		}

		$db = KenedoPlatform::getDb();

		try {

			$db->startTransaction();

			// Set the default language tag to NULL to avoid inconsistencies (will be set later below)
			$query = "UPDATE `#__configbox_config` SET `language_tag` = NULL";
			$db->setQuery($query);
			$db->query();

			// Delete any existing languages
			$query = "DELETE FROM `#__configbox_active_languages`";
			$db->setQuery($query);
			$db->query();

			// Prepare the language tags for inserting
			$tags = array();
			foreach ($languageTags as $tag) {
				$tags[] = "('".$db->getEscaped($tag)."')";
			}

			// Insert the requested tags
			$query = "INSERT INTO `#__configbox_active_languages` (`tag`) VALUES ".implode(',', $tags);
			$db->setQuery($query);
			$db->query();

			// Figure out what to use as default language tag
			$platformLanguage = KenedoPlatform::p()->getLanguageTag();
			// See if the current platform's language tag is in the mix, then use it, otherwise take the first one we got
			if (in_array($platformLanguage, $languageTags)) {
				$defaultTag = $platformLanguage;
			}
			else {
				$defaultTag = $languageTags[0];
			}

			// Finally set the default language tag
			$query = "UPDATE `#__configbox_config` SET `language_tag` = '".$db->getEscaped($defaultTag)."'";
			$db->setQuery($query);
			$db->query();

			$db->commitTransaction();

		}
		catch (Exception $e) {
			$db->rollbackTransaction();
			$msg = KText::_('A system error occured during storing language data. Please contact Rovexo to resolve this issue.');
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors(array($msg))->toJson();
			return;
		}

		ConfigboxCacheHelper::purgeCache();

		if (KenedoPlatform::getName() == 'magento') {
			ConfigboxSystemVars::setVar('post_install_done', '1');
		}

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}

	function storeCurrencies() {

		$currentBaseCurrency = ConfigboxCurrencyHelper::getBaseCurrency();

		$baseCurrency = array(
			'id' => ($currentBaseCurrency) ? $currentBaseCurrency->id : NULL,
			'title' => KRequest::getString('baseCurrencyTitle'),
			'symbol' => KRequest::getString('baseCurrencySymbol'),
			'code' => KRequest::getString('baseCurrencyCode'),
			'multiplicator' => 1,
			'base' => '1',
			'default' => '1',
			'published' => '1',
			'ordering' => 0,
		);

		$reqCurrencies = KRequest::getArray('currencies');

		$currencies = array($baseCurrency);

		foreach ($reqCurrencies as $reqCurrency) {
			$currencies[] = array(
				'id' => $reqCurrency['id'],
				'title' => $reqCurrency['title'],
				'symbol' => $reqCurrency['symbol'],
				'code' => strtoupper($reqCurrency['code']),
				'multiplicator' => KText::getNormalizedNumber($reqCurrency['multiplier']),
				'base' => '0',
				'default' => '0',
				'published' => '1',
				'ordering' => 0,
			);
		}

		$usedCodes = array();
		$errors = array();

		foreach ($currencies as $currency) {

			if (empty($currency['title']) && empty($currency['symbol']) && empty($currency['code']) && empty($currency['multiplicator'])) {
				$errors[] = KText::_('Make sure all fields are filled out.');
			}

			if (strlen($currency['code']) != 3) {
				$errors[] = KText::_('Currency codes must adhere to the ISO 4217 standard. Use the typical 3 letter codes.');
			}

			if ($currency['multiplicator'] <= 0) {
				$errors[] = KText::_('One of the exchange rates seems wrong. Make sure to enter only numbers higher than zero.');
			}

			if (isset($usedCodes[$currency['code']])) {
				$errors[] = KText::_('You have a duplicate currency code in the list.');
			}
			$usedCodes[$currency['code']] = true;

		}

		if (count($errors)) {
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors($errors)->toJson();
			return;
		}

		$usedCodes = array();
		foreach ($currencies as $currency) {
			$usedCodes[] = "'".$currency['code']."'";
		}

		$db = KenedoPlatform::getDb();

		try {

			$db->startTransaction();

			$query = "DELETE FROM `#__configbox_currencies` WHERE `code` NOT IN (".implode(',', $usedCodes).")";
			$db->setQuery($query);
			$db->query();

			$model = KenedoModel::getModel('ConfigboxModelAdmincurrencies');

			$languageTags = KenedoLanguageHelper::getActiveLanguageTags();

			foreach ($currencies as $currency) {
				$record = (object) $currency;

				foreach ($languageTags as $languageTag) {
					$key = 'title-'.$languageTag;
					$record->{$key} = $record->title;
				}


				$model->prepareForStorage($record);
				$response = $model->validateData($record);

				if ($response == false) {
					throw new Exception('Validation error for currency data: '.$model->getError());
				}

				$success = $model->store($record);

				if ($success == false) {
					throw new Exception('System error during storing currency data: '.$model->getError());
				}

			}

			$db->commitTransaction();

		}
		catch (Exception $e) {
			$db->rollbackTransaction();
			$msg = KText::_('A system error occured during storing currency data. Please contact Rovexo to resolve this issue.');
			echo ConfigboxJsonResponse::makeOne()->setSuccess(false)->setErrors(array($msg))->toJson();
			return;
		}

		ConfigboxCacheHelper::purgeCache();

		ConfigboxSystemVars::setVar('post_install_done', '1');

		echo ConfigboxJsonResponse::makeOne()->setSuccess(true)->toJson();

	}

}
